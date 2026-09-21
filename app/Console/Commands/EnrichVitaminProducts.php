<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrichVitaminProducts extends Command
{
    protected $signature = 'products:enrich-vitamin-health
                            {--dry-run : Chỉ hiển thị thay đổi, không ghi database}';

    protected $description = 'Bổ sung dữ liệu chi tiết cho các sản phẩm Vitamin & sức khỏe';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $items = $this->products();

        $this->newLine();
        $this->info(
            $dryRun
                ? 'DRY RUN - Không ghi dữ liệu.'
                : 'Đang enrich sản phẩm Vitamin & sức khỏe...'
        );
        $this->newLine();

        foreach ($items as $slug => $data) {
            $product = Product::query()
                ->where('slug', $slug)
                ->first();

            if (!$product) {
                $this->warn("SKIP  {$slug} - không tìm thấy sản phẩm");
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    'CHECK #%d %-58s [%s]',
                    $product->id,
                    $product->name,
                    $data['type']
                ));

                continue;
            }

            DB::transaction(function () use ($product, $data) {
                $product->fill([
                    'name' => $data['name'] ?? $product->name,
                    'description' => $data['description'],
                    'origin' => $data['origin'] ?? $product->origin,
                    'manufacturer' => $data['manufacturer'] ?? $product->manufacturer,
                    'ingredients' => implode("\n", $data['ingredients']),
                    'usage_instructions' => implode("\n", $data['usage']),
                    'storage_instructions' => implode("\n", $data['storage']),
                    'warning' => implode("\n", $data['warning']),
                    'highlights' => [
                        'items' => $data['highlights'],
                        'message' => $data['highlight_message'],
                        'submessage' => 'Đọc kỹ nhãn sản phẩm và dùng đúng hướng dẫn theo độ tuổi.',
                    ],
                ]);

                $product->save();

                /*
                 * Mỗi sản phẩm chỉ nên có một brand.
                 * Seed ban đầu của Ferrodue từng dùng brand "Ferrodue";
                 * ở đây chuẩn hóa lại thành Buona.
                 */
                $currentBrandIds = $product->tags()
                    ->where('tags.type', 'brand')
                    ->pluck('tags.id')
                    ->all();

                if ($currentBrandIds !== []) {
                    $product->tags()->detach($currentBrandIds);
                }

                $brand = $this->tag(
                    $data['brand']['slug'],
                    $data['brand']['name'],
                    'brand'
                );

                $product->tags()->syncWithoutDetaching([$brand->id]);

                /*
                 * Gỡ các attribute thuộc nhóm Vitamin cũ của chính dataset này,
                 * sau đó gắn lại tập attribute đã được chuẩn hóa.
                 * Không đụng các tag ngoài danh sách managed slugs.
                 */
                $managedSlugs = $this->managedAttributeSlugs();

                $oldManagedAttributeIds = $product->tags()
                    ->where('tags.type', 'attribute')
                    ->whereIn('tags.slug', $managedSlugs)
                    ->pluck('tags.id')
                    ->all();

                if ($oldManagedAttributeIds !== []) {
                    $product->tags()->detach($oldManagedAttributeIds);
                }

                $attributeIds = [];

                foreach ($data['attributes'] as $attribute) {
                    $tag = $this->tag(
                        $attribute['slug'],
                        $attribute['name'],
                        'attribute'
                    );

                    $attributeIds[] = $tag->id;
                }

                if ($attributeIds !== []) {
                    $product->tags()->syncWithoutDetaching(
                        array_values(array_unique($attributeIds))
                    );
                }
            });

            $this->info("OK    #{$product->id} {$data['name']}");
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry-run hoàn tất.');
            $this->line('Chạy thật: php artisan products:enrich-vitamin-health');

            return self::SUCCESS;
        }

        $this->info('Enrich Vitamin & sức khỏe hoàn tất.');

        return self::SUCCESS;
    }

    private function tag(string $slug, string $name, string $type): Tag
    {
        $tag = Tag::withTrashed()
            ->where('slug', $slug)
            ->first();

        if (!$tag) {
            $tag = new Tag();
            $tag->slug = $slug;
        }

        $tag->name = $name;
        $tag->type = $type;
        $tag->save();

        if (method_exists($tag, 'trashed') && $tag->trashed()) {
            $tag->restore();
        }

        return $tag;
    }

    private function managedAttributeSlugs(): array
    {
        return [
            // Dưỡng chất / nhóm
            'vitamin-d3',
            'vitamin-k2',
            'vitamin-tong-hop',
            'dha',
            'omega-3',
            'men-vi-sinh',
            'l-reuteri-dsm-17938',
            'sat',
            'canxi',
            'ho-tro-dinh-duong',
            'suc-khoe-xuong',

            // Dạng sản phẩm
            'dang-nho-giot',
            'dang-xit',
            'dang-siro',
            'dang-long',
            'vien-nhai',
            'vien-nang-mem',

            // Quy cách
            'dung-tich-5ml',
            'dung-tich-10ml',
            'dung-tich-15ml',
            'dung-tich-150ml',
            'dung-tich-200ml',
            '10-vien',
            '60-vien',

            // Độ tuổi
            'tu-so-sinh',
            'tu-4-thang',
            'tu-6-thang',
            'tu-7-thang',
            'tu-2-tuoi',
            '6-thang-den-4-tuoi',
            '6-thang-den-12-tuoi',
        ];
    }

    private function products(): array
    {
        return [
            'lineabon-k2-d3-10ml' => [
                'type' => 'Vitamin D3 / K2',
                'name' => 'Thực phẩm bảo vệ sức khỏe LineaBon K2 + D3 10ml',
                'description' => 'LineaBon K2 + D3 dạng nhỏ giọt 10ml bổ sung vitamin D3 và vitamin K2 (MK-7), dùng theo liều lượng phù hợp với từng nhóm tuổi.',
                'origin' => 'Slovenia',
                'manufacturer' => 'ErgoPharma d.o.o',
                'brand' => [
                    'slug' => 'lineabon',
                    'name' => 'LineaBon',
                ],
                'attributes' => [
                    ['slug' => 'vitamin-d3', 'name' => 'Vitamin D3'],
                    ['slug' => 'vitamin-k2', 'name' => 'Vitamin K2'],
                    ['slug' => 'dang-nho-giot', 'name' => 'Dạng nhỏ giọt'],
                    ['slug' => 'dung-tich-10ml', 'name' => '10ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
                'ingredients' => [
                    'Trong 6 giọt: Vitamin D3 400 IU; Vitamin K2 (MK-7) 22,5 mcg.',
                    'Dầu MCT.',
                    'Chiết xuất giàu tocopherol.',
                ],
                'usage' => [
                    'Lắc đều lọ trước khi sử dụng.',
                    'Dốc thẳng lọ và lấy số giọt phù hợp ra thìa hoặc dùng theo hướng dẫn của sản phẩm.',
                    'Trẻ 0-12 tháng: 6 giọt/ngày; trẻ 1-3 tuổi: 6-8 giọt/ngày; trẻ 3-12 tuổi: 8-12 giọt/ngày.',
                    'Có thể pha với nước, cháo, sữa hoặc nước hoa quả dưới 40°C.',
                ],
                'storage' => [
                    'Bảo quản nơi khô mát, tránh ánh sáng mặt trời.',
                    'Bảo quản ở nhiệt độ dưới 25°C và để xa tầm tay trẻ em.',
                ],
                'warning' => [
                    'Không dùng cho người mẫn cảm với bất kỳ thành phần nào của sản phẩm.',
                    'Không dùng cho trẻ thiếu men G6PD theo thông tin của nhà sản xuất.',
                    'Không vượt quá liều lượng khuyến nghị trên nhãn.',
                    'Thực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.',
                ],
                'highlights' => [
                    ['title' => 'Vitamin D3', 'subtitle' => '400 IU trong 6 giọt', 'icon' => 'drop'],
                    ['title' => 'Vitamin K2 MK-7', 'subtitle' => '22,5 mcg trong 6 giọt', 'icon' => 'check'],
                    ['title' => 'Dạng nhỏ giọt', 'subtitle' => 'Dung tích 10ml', 'icon' => 'bottle'],
                    ['title' => '0M+', 'subtitle' => 'Dùng theo hướng dẫn theo tuổi', 'icon' => 'user'],
                ],
                'highlight_message' => 'LineaBon K2 + D3 10ml',
            ],

            'lineabon-d3-k2-spray-10ml' => [
                'type' => 'Vitamin D3 / K2',
                'name' => 'Vitamin D3 K2 LineaBon dạng xịt 10ml',
                'description' => 'LineaBon D3 + K2 dạng xịt 10ml bổ sung vitamin D3 và vitamin K2 (MK-7), với quy cách xịt tiện lợi.',
                'origin' => 'Slovenia',
                'manufacturer' => 'ErgoPharma d.o.o',
                'brand' => [
                    'slug' => 'lineabon',
                    'name' => 'LineaBon',
                ],
                'attributes' => [
                    ['slug' => 'vitamin-d3', 'name' => 'Vitamin D3'],
                    ['slug' => 'vitamin-k2', 'name' => 'Vitamin K2'],
                    ['slug' => 'dang-xit', 'name' => 'Dạng xịt'],
                    ['slug' => 'dung-tich-10ml', 'name' => '10ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
                'ingredients' => [
                    'Trong 0,2ml (3 xịt): Vitamin D3 400 IU.',
                    'Vitamin K2 (MK-7) 22,5 mcg.',
                    'Thành phần nền theo công bố của sản phẩm.',
                ],
                'usage' => [
                    'Lắc đều trước khi sử dụng.',
                    'Xịt đúng số lần theo độ tuổi và hướng dẫn trên nhãn.',
                    'Trẻ 0-12 tháng: 3 xịt/ngày; trẻ 1-3 tuổi: 3-4 xịt/ngày; trẻ 3-12 tuổi: 4-6 xịt/ngày.',
                    'Đậy kín sau khi dùng.',
                ],
                'storage' => [
                    'Bảo quản nơi khô ráo, tránh ánh nắng mặt trời.',
                    'Bảo quản ở nhiệt độ dưới 25°C.',
                ],
                'warning' => [
                    'Không sử dụng nếu dị ứng với bất kỳ thành phần nào của sản phẩm.',
                    'Không vượt quá liều lượng khuyến nghị.',
                    'Đọc kỹ hướng dẫn sử dụng trước khi dùng.',
                    'Thực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.',
                ],
                'highlights' => [
                    ['title' => 'Vitamin D3', 'subtitle' => '400 IU trong 3 xịt', 'icon' => 'drop'],
                    ['title' => 'Vitamin K2 MK-7', 'subtitle' => '22,5 mcg trong 3 xịt', 'icon' => 'check'],
                    ['title' => 'Dạng xịt', 'subtitle' => 'Chai 10ml', 'icon' => 'bottle'],
                    ['title' => '0M+', 'subtitle' => 'Dùng theo hướng dẫn theo tuổi', 'icon' => 'user'],
                ],
                'highlight_message' => 'LineaBon D3 + K2 Spray 10ml',
            ],

            'biogaia-protectis-baby-drops-5ml' => [
                'type' => 'Men vi sinh',
                'name' => 'BioGaia Protectis Baby Drops 5ml',
                'description' => 'BioGaia Protectis Baby Drops là men vi sinh dạng nhỏ giọt chứa chủng Limosilactobacillus reuteri DSM 17938, quy cách 5ml.',
                'origin' => 'Thụy Điển',
                'manufacturer' => 'BioGaia Production AB',
                'brand' => [
                    'slug' => 'biogaia',
                    'name' => 'BioGaia',
                ],
                'attributes' => [
                    ['slug' => 'men-vi-sinh', 'name' => 'Men vi sinh'],
                    ['slug' => 'l-reuteri-dsm-17938', 'name' => 'L. reuteri DSM 17938'],
                    ['slug' => 'dang-nho-giot', 'name' => 'Dạng nhỏ giọt'],
                    ['slug' => 'dung-tich-5ml', 'name' => '5ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
                'ingredients' => [
                    'Limosilactobacillus reuteri DSM 17938.',
                    'Một liều 5 giọt chứa tối thiểu 100 triệu tế bào lợi khuẩn sống.',
                    'Dầu hướng dương và thành phần dầu nền theo công bố của sản phẩm.',
                ],
                'usage' => [
                    'Lắc kỹ khoảng 10 giây trước mỗi lần sử dụng.',
                    'Nghiêng chai nhẹ để lấy giọt ra thìa sạch.',
                    'Liều khuyến nghị của sản phẩm: 5 giọt, 1 lần mỗi ngày.',
                    'Không pha vào thức ăn hoặc đồ uống nóng và không nhỏ trực tiếp từ chai vào miệng.',
                ],
                'storage' => [
                    'Bảo quản nơi khô ráo, ở nhiệt độ phòng theo hướng dẫn của sản phẩm.',
                    'Để xa tầm tay trẻ em.',
                ],
                'warning' => [
                    'Không vượt quá liều lượng khuyến nghị.',
                    'Phiên bản sản phẩm tham chiếu dành cho trẻ đủ tháng; với trẻ sinh non cần tham khảo ý kiến chuyên môn.',
                    'Không thêm vào thức ăn hoặc đồ uống nóng.',
                    'Thực phẩm bổ sung không thay thế chế độ ăn đa dạng và cân bằng.',
                ],
                'highlights' => [
                    ['title' => 'L. reuteri DSM 17938', 'subtitle' => 'Chủng lợi khuẩn', 'icon' => 'microbe'],
                    ['title' => '100 triệu CFU', 'subtitle' => 'Trong liều 5 giọt', 'icon' => 'shield'],
                    ['title' => 'Dạng nhỏ giọt', 'subtitle' => 'Chai 5ml', 'icon' => 'bottle'],
                    ['title' => '0M+', 'subtitle' => 'Trẻ đủ tháng', 'icon' => 'user'],
                ],
                'highlight_message' => 'BioGaia Protectis Baby Drops 5ml',
            ],

            'biogaia-protectis-tablets-10-vien' => [
                'type' => 'Men vi sinh',
                'name' => 'BioGaia Protectis Tablets 2Y+ 10 viên',
                'description' => 'BioGaia Protectis dạng viên nhai chứa L. reuteri DSM 17938, phù hợp cho trẻ trên 2 tuổi và người lớn theo hướng dẫn sản phẩm.',
                'brand' => [
                    'slug' => 'biogaia',
                    'name' => 'BioGaia',
                ],
                'attributes' => [
                    ['slug' => 'men-vi-sinh', 'name' => 'Men vi sinh'],
                    ['slug' => 'l-reuteri-dsm-17938', 'name' => 'L. reuteri DSM 17938'],
                    ['slug' => 'vien-nhai', 'name' => 'Viên nhai'],
                    ['slug' => '10-vien', 'name' => '10 viên'],
                    ['slug' => 'tu-2-tuoi', 'name' => '2Y+'],
                ],
                'ingredients' => [
                    'Mỗi viên chứa 100 triệu tế bào lợi khuẩn sống L. reuteri DSM 17938.',
                    'Isomalt và xylitol.',
                    'Hương liệu và acid citric theo công bố của sản phẩm.',
                ],
                'usage' => [
                    'Kiểm tra bao bì và hạn sử dụng trước khi dùng.',
                    'Có thể nhai viên hoặc uống cùng nước.',
                    'Dùng theo liều lượng ghi trên nhãn; tài liệu BioGaia Việt Nam nêu 1-2 viên/ngày.',
                    'Bảo quản đúng hướng dẫn sau khi sử dụng.',
                ],
                'storage' => [
                    'Bảo quản ở nơi thoáng mát hoặc theo hướng dẫn trên bao bì.',
                    'Tránh ánh sáng mặt trời trực tiếp và nguồn nhiệt.',
                ],
                'warning' => [
                    'Không vượt quá liều lượng khuyến nghị trên nhãn.',
                    'Đọc kỹ thành phần nếu người dùng có tiền sử dị ứng.',
                    'Thực phẩm bổ sung không thay thế chế độ ăn đa dạng và cân bằng.',
                ],
                'highlights' => [
                    ['title' => 'L. reuteri DSM 17938', 'subtitle' => '100 triệu tế bào/viên', 'icon' => 'microbe'],
                    ['title' => 'Viên nhai', 'subtitle' => 'Dạng sử dụng', 'icon' => 'capsule'],
                    ['title' => '10 viên', 'subtitle' => 'Quy cách', 'icon' => 'box'],
                    ['title' => '2Y+', 'subtitle' => 'Theo hướng dẫn sản phẩm', 'icon' => 'user'],
                ],
                'highlight_message' => 'BioGaia Protectis Tablets 2Y+',
            ],

            'wellbaby-multi-vitamin-liquid-150ml' => [
                'type' => 'Vitamin tổng hợp',
                'name' => 'Wellbaby Multi-Vitamin Liquid 150ml',
                'description' => 'Wellbaby Multi-Vitamin Liquid là vitamin và khoáng chất dạng lỏng dành cho trẻ từ 6 tháng đến 4 tuổi, quy cách 150ml.',
                'origin' => 'Anh Quốc',
                'brand' => [
                    'slug' => 'vitabiotics',
                    'name' => 'Vitabiotics',
                ],
                'attributes' => [
                    ['slug' => 'vitamin-tong-hop', 'name' => 'Vitamin tổng hợp'],
                    ['slug' => 'dang-siro', 'name' => 'Dạng siro'],
                    ['slug' => 'dung-tich-150ml', 'name' => '150ml'],
                    ['slug' => '6-thang-den-4-tuoi', 'name' => '6M–4Y'],
                ],
                'ingredients' => [
                    'Mỗi 5ml cung cấp vitamin A, D3, E, C và các vitamin nhóm B.',
                    'Mỗi 5ml có sắt 4mg, kẽm 2,5mg và đồng 150mcg.',
                    'Chiết xuất mạch nha 500mg trong mỗi 5ml.',
                    'Thành phần nền và phụ liệu theo công bố của Vitabiotics.',
                ],
                'usage' => [
                    'Lắc chai trước khi sử dụng.',
                    'Trẻ 6 tháng đến 4 tuổi: dùng 5ml mỗi ngày.',
                    'Pha 5ml vào sữa hoặc nước thường dùng của trẻ và khuấy đều.',
                    'Không dùng trực tiếp từ thìa và không vượt quá lượng khuyến nghị.',
                ],
                'storage' => [
                    'Bảo quản dưới 25°C ở nơi khô ráo, ngoài tầm nhìn và tầm với của trẻ em.',
                    'Sau khi mở, bảo quản trong tủ lạnh và sử dụng trong khoảng 8-10 tuần theo hướng dẫn của hãng.',
                ],
                'warning' => [
                    'Không vượt quá lượng khuyến nghị hàng ngày.',
                    'Sản phẩm có chứa sắt; dùng quá mức có thể gây hại cho trẻ nhỏ.',
                    'Nếu trẻ đang được theo dõi y tế hoặc có dị ứng thực phẩm, tham khảo ý kiến chuyên môn trước khi sử dụng.',
                    'Thực phẩm bổ sung không thay thế chế độ ăn đa dạng và cân bằng.',
                ],
                'highlights' => [
                    ['title' => '14 dưỡng chất', 'subtitle' => 'Vitamin & khoáng chất', 'icon' => 'spark'],
                    ['title' => '5ml/ngày', 'subtitle' => 'Theo hướng dẫn hãng', 'icon' => 'bottle'],
                    ['title' => '150ml', 'subtitle' => 'Quy cách chai', 'icon' => 'box'],
                    ['title' => '6M–4Y', 'subtitle' => 'Độ tuổi phù hợp', 'icon' => 'user'],
                ],
                'highlight_message' => 'Wellbaby Multi-Vitamin Liquid 150ml',
            ],

            'bio-island-dha-for-kids-60-vien' => [
                'type' => 'DHA / Omega-3',
                'name' => 'Bio Island DHA for Kids 60 viên',
                'description' => 'Bio Island DHA for Kids là sản phẩm bổ sung DHA nguồn gốc vi tảo dạng viên nang mềm, quy cách 60 viên.',
                'origin' => 'Úc',
                'brand' => [
                    'slug' => 'bio-island',
                    'name' => 'Bio Island',
                ],
                'attributes' => [
                    ['slug' => 'dha', 'name' => 'DHA'],
                    ['slug' => 'omega-3', 'name' => 'Omega-3'],
                    ['slug' => 'vien-nang-mem', 'name' => 'Viên nang mềm'],
                    ['slug' => '60-vien', 'name' => '60 viên'],
                    ['slug' => 'tu-7-thang', 'name' => '7M+'],
                ],
                'ingredients' => [
                    'Mỗi viên chứa 250mg dầu giàu DHA từ vi tảo Schizochytrium.',
                    'Tương đương DHA 100mg mỗi viên.',
                    'Thành phần viên nang theo công bố của sản phẩm.',
                ],
                'usage' => [
                    'Kiểm tra bao bì và hạn sử dụng trước khi dùng.',
                    'Trẻ 0-6 tháng: chỉ sử dụng khi có hướng dẫn của chuyên gia y tế.',
                    'Trẻ 7 tháng-6 tuổi: 1 viên/ngày; 7-11 tuổi: 2 viên/ngày; từ 12 tuổi: 3 viên/ngày.',
                    'Với trẻ chưa nuốt được viên nang, dùng theo hướng dẫn trên nhãn sản phẩm.',
                ],
                'storage' => [
                    'Bảo quản theo điều kiện ghi trên bao bì.',
                    'Để nơi khô ráo, tránh nhiệt và ánh nắng trực tiếp.',
                ],
                'warning' => [
                    'Không vượt quá liều lượng khuyến nghị.',
                    'Sản phẩm có chứa sulfite theo thông tin của nhà sản xuất.',
                    'Trẻ 0-6 tháng nên tham khảo chuyên gia y tế trước khi sử dụng.',
                    'Sản phẩm bổ sung chỉ hỗ trợ khi khẩu phần ăn chưa đáp ứng đầy đủ.',
                ],
                'highlights' => [
                    ['title' => 'DHA từ vi tảo', 'subtitle' => 'Schizochytrium', 'icon' => 'drop'],
                    ['title' => '100mg DHA', 'subtitle' => 'Tương đương mỗi viên', 'icon' => 'wave'],
                    ['title' => '60 viên', 'subtitle' => 'Viên nang mềm', 'icon' => 'box'],
                    ['title' => '7M+', 'subtitle' => 'Liều dùng thường quy', 'icon' => 'user'],
                ],
                'highlight_message' => 'Bio Island DHA for Kids 60 viên',
            ],

            'fitobimbi-appetito' => [
                'type' => 'Hỗ trợ dinh dưỡng',
                'name' => 'Thực phẩm bảo vệ sức khỏe Fitobimbi Appetito 200ml',
                'description' => 'Fitobimbi Appetito là sản phẩm dạng siro 200ml dành cho trẻ từ 6 tháng đến 12 tuổi theo hướng dẫn của nhãn hàng.',
                'origin' => 'Ý',
                'manufacturer' => 'Pharmalife Research s.r.l',
                'brand' => [
                    'slug' => 'fitobimbi',
                    'name' => 'Fitobimbi',
                ],
                'attributes' => [
                    ['slug' => 'ho-tro-dinh-duong', 'name' => 'Hỗ trợ dinh dưỡng'],
                    ['slug' => 'dang-siro', 'name' => 'Dạng siro'],
                    ['slug' => 'dung-tich-200ml', 'name' => '200ml'],
                    ['slug' => '6-thang-den-12-tuoi', 'name' => '6M–12Y'],
                ],
                'ingredients' => [
                    'Chiết xuất phấn hoa 2g/100ml.',
                    'Chiết xuất mầm lúa mì 2g/100ml.',
                    'Chiết xuất ngọn Centaury và rễ Long đởm vàng.',
                    'Chiết xuất hạt cỏ Cà ri và các thành phần nền theo công bố sản phẩm.',
                ],
                'usage' => [
                    'Lắc kỹ trước khi sử dụng.',
                    'Dùng trực tiếp hoặc pha loãng với nước hay đồ uống phù hợp.',
                    'Trẻ 6 tháng-12 tuổi: dùng theo hướng dẫn trên nhãn, tổng lượng tham chiếu 30-45ml/ngày chia theo nhu cầu.',
                    'Ưu tiên dùng trước bữa ăn theo hướng dẫn của nhãn hàng.',
                ],
                'storage' => [
                    'Bảo quản nơi khô mát, tránh ánh sáng mặt trời.',
                    'Để xa tầm tay trẻ em.',
                ],
                'warning' => [
                    'Không dùng nếu mẫn cảm với bất kỳ thành phần nào của sản phẩm.',
                    'Sản phẩm có dịch chiết thảo dược nên có thể xuất hiện phần bột không hòa tan; cần lắc đều trước khi dùng.',
                    'Không vượt quá hướng dẫn sử dụng trên nhãn.',
                    'Thực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.',
                ],
                'highlights' => [
                    ['title' => 'Dạng siro', 'subtitle' => 'Chai 200ml', 'icon' => 'bottle'],
                    ['title' => 'Từ 6 tháng', 'subtitle' => 'Theo hướng dẫn sản phẩm', 'icon' => 'user'],
                    ['title' => 'Chiết xuất thảo dược', 'subtitle' => 'Theo công bố sản phẩm', 'icon' => 'spark'],
                    ['title' => 'Xuất xứ Ý', 'subtitle' => 'Pharmalife Research', 'icon' => 'check'],
                ],
                'highlight_message' => 'Fitobimbi Appetito 200ml',
            ],

            'sat-huu-co-nho-giot-ferrodue' => [
                'type' => 'Sắt',
                'name' => 'Sắt hữu cơ nhỏ giọt Ferrodue 15ml',
                'description' => 'Ferrodue của Buona là sản phẩm bổ sung sắt dạng nhỏ giọt 15ml, chứa sắt II bisglycinate chelate và hương dâu.',
                'origin' => 'Ý',
                'brand' => [
                    'slug' => 'buona',
                    'name' => 'Buona',
                ],
                'attributes' => [
                    ['slug' => 'sat', 'name' => 'Sắt'],
                    ['slug' => 'dang-nho-giot', 'name' => 'Dạng nhỏ giọt'],
                    ['slug' => 'dung-tich-15ml', 'name' => '15ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
                'ingredients' => [
                    'Trong 10 giọt (0,85ml): Sắt II Bisglycinate Chelate 10mg.',
                    'Fructose và nước tinh khiết.',
                    'Malic acid, citric acid và hương dâu.',
                    'Theo thông tin sản phẩm: không chứa gluten và lactose.',
                ],
                'usage' => [
                    'Sử dụng ống nhỏ giọt đi kèm sản phẩm.',
                    'Trẻ sinh non: 2 giọt/kg thể trọng/ngày từ tháng đầu đời theo hướng dẫn sản phẩm.',
                    'Trẻ 0-12 tháng: 1 giọt/kg thể trọng/ngày; trẻ trên 1 tuổi: 10 giọt/ngày.',
                    'Có thể nhỏ trực tiếp vào miệng hoặc pha với sữa/nước hoa quả theo hướng dẫn sản phẩm.',
                ],
                'storage' => [
                    'Bảo quản nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp.',
                    'Để xa tầm tay trẻ em.',
                    'Sau khi mở nắp, sử dụng trong vòng 3 tháng theo hướng dẫn sản phẩm.',
                ],
                'warning' => [
                    'Không tự ý tăng liều bổ sung sắt.',
                    'Nếu trẻ đang dùng thuốc hoặc sản phẩm bổ sung khác, nên tham khảo ý kiến chuyên môn.',
                    'Sự lắng đọng dưới đáy lọ có thể là hiện tượng tự nhiên theo thông tin sản phẩm.',
                    'Thực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.',
                ],
                'highlights' => [
                    ['title' => 'Sắt bisglycinate', 'subtitle' => '10mg trong 10 giọt', 'icon' => 'drop'],
                    ['title' => '15ml', 'subtitle' => 'Dạng nhỏ giọt', 'icon' => 'bottle'],
                    ['title' => 'Hương dâu', 'subtitle' => 'Theo thông tin sản phẩm', 'icon' => 'spark'],
                    ['title' => '0M+', 'subtitle' => 'Liều theo tuổi/cân nặng', 'icon' => 'user'],
                ],
                'highlight_message' => 'Ferrodue Buona 15ml',
            ],

            'healthy-care-milk-calcium' => [
                'type' => 'Canxi',
                'name' => 'Healthy Care Kids Milk Calcium 60 viên',
                'description' => 'Healthy Care Kids Milk Calcium là sản phẩm bổ sung canxi và vitamin D3 dạng viên nang, quy cách 60 viên.',
                'origin' => 'Úc',
                'manufacturer' => 'Nature’s Care Manufacture Pty Limited',
                'brand' => [
                    'slug' => 'healthy-care',
                    'name' => 'Healthy Care',
                ],
                'attributes' => [
                    ['slug' => 'canxi', 'name' => 'Canxi'],
                    ['slug' => 'vitamin-d3', 'name' => 'Vitamin D3'],
                    ['slug' => 'suc-khoe-xuong', 'name' => 'Sức khỏe xương'],
                    ['slug' => 'vien-nang-mem', 'name' => 'Viên nang mềm'],
                    ['slug' => '60-vien', 'name' => '60 viên'],
                    ['slug' => 'tu-4-thang', 'name' => '4M+'],
                ],
                'ingredients' => [
                    'Mỗi viên chứa Hydroxyapatite 270mg.',
                    'Tương đương Canxi 80mg.',
                    'Colecalciferol (Vitamin D3) 2,5 microgam.',
                ],
                'usage' => [
                    'Dùng cùng thức ăn hoặc theo hướng dẫn của chuyên gia y tế.',
                    'Trẻ 4-6 tháng: 1 viên/ngày; trẻ 7 tháng-1 tuổi: 1-2 viên/ngày.',
                    'Trẻ 1-3 tuổi: 2-3 viên/ngày; trẻ trên 3 tuổi: 3-4 viên/ngày.',
                    'Với trẻ chưa nhai hoặc nuốt được viên, cắt/vặn đuôi viên và bóp phần bên trong ra thìa hoặc trộn với thức ăn.',
                ],
                'storage' => [
                    'Bảo quản dưới 30°C ở nơi khô ráo.',
                    'Tránh nhiệt, ánh nắng trực tiếp và độ ẩm.',
                ],
                'warning' => [
                    'Luôn đọc nhãn và làm theo hướng dẫn sử dụng.',
                    'Không vượt quá liều lượng khuyến nghị.',
                    'Sản phẩm bổ sung không thay thế chế độ ăn cân bằng.',
                    'Kiểm tra thành phần trước khi dùng nếu trẻ có tiền sử dị ứng.',
                ],
                'highlights' => [
                    ['title' => 'Canxi', 'subtitle' => '80mg tương đương/viên', 'icon' => 'spark'],
                    ['title' => 'Vitamin D3', 'subtitle' => '2,5 mcg/viên', 'icon' => 'drop'],
                    ['title' => '60 viên', 'subtitle' => 'Quy cách', 'icon' => 'box'],
                    ['title' => '4M+', 'subtitle' => 'Theo hướng dẫn sản phẩm', 'icon' => 'user'],
                ],
                'highlight_message' => 'Healthy Care Kids Milk Calcium 60 viên',
            ],
        ];
    }
}
