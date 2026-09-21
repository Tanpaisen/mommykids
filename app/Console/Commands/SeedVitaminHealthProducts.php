<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Command;

class SeedVitaminHealthProducts extends Command
{
    protected $signature = 'products:seed-vitamin-health
                            {--dry-run : Chỉ kiểm tra dữ liệu sẽ tạo, không ghi database}';

    protected $description = 'Tạo dữ liệu sản phẩm mẫu cho danh mục Vitamin & sức khỏe';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $category = Category::query()
            ->where('slug', 'vitamin-suc-khoe')
            ->first();

        if (!$category) {
            $this->error('Không tìm thấy category slug vitamin-suc-khoe.');
            return self::FAILURE;
        }

        $products = $this->products();

        $this->newLine();
        $this->info(
            $dryRun
                ? 'DRY RUN - Không ghi dữ liệu.'
                : 'Đang tạo/cập nhật sản phẩm Vitamin & sức khỏe...'
        );
        $this->newLine();

        foreach ($products as $slug => $data) {
            if ($dryRun) {
                $this->line(sprintf(
                    'CHECK %-55s %sđ',
                    $data['name'],
                    number_format($data['price'], 0, ',', '.')
                ));
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'origin' => $data['origin'] ?? null,
                    'manufacturer' => $data['manufacturer'] ?? null,
                    'ingredients' => $data['ingredients'] ?? null,
                    'usage_instructions' => $data['usage_instructions'] ?? null,
                    'storage_instructions' => $data['storage_instructions'] ?? null,
                    'warning' => $data['warning'] ?? null,
                    'highlights' => $data['highlights'] ?? null,
                    'price' => $data['price'],
                    'old_price' => $data['old_price'] ?? null,
                    'discount_percent' => $data['discount_percent'] ?? null,
                    'stock' => $data['stock'],
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );

            $tagIds = [];

            if (!empty($data['brand'])) {
                $brand = $this->tag(
                    $data['brand']['slug'],
                    $data['brand']['name'],
                    'brand'
                );
                $tagIds[] = $brand->id;
            }

            foreach ($data['attributes'] ?? [] as $attribute) {
                $tag = $this->tag(
                    $attribute['slug'],
                    $attribute['name'],
                    'attribute'
                );
                $tagIds[] = $tag->id;
            }

            if ($tagIds !== []) {
                $product->tags()->syncWithoutDetaching(
                    array_values(array_unique($tagIds))
                );
            }

            $this->info("OK    #{$product->id} {$product->name}");
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry-run hoàn tất.');
            $this->line('Chạy thật: php artisan products:seed-vitamin-health');
            return self::SUCCESS;
        }

        $this->info('Hoàn tất tạo/cập nhật ' . count($products) . ' sản phẩm.');
        $this->warn(
            'Giá là dữ liệu tham khảo tại thời điểm chuẩn bị dataset; '
            . 'stock là số liệu demo nội bộ, không phải tồn kho của KidsPlaza/Con Cưng.'
        );

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

    private function products(): array
    {
        return [
            'lineabon-k2-d3-10ml' => [
                'name' => 'Thực phẩm bảo vệ sức khỏe LineaBon K2 + D3 10ml',
                'description' => 'Sản phẩm dạng nhỏ giọt bổ sung vitamin D3 và vitamin K2, quy cách chai 10ml.',
                'origin' => 'Slovenia',
                'manufacturer' => 'ErgoPharm d.o.o.',
                'ingredients' => "Vitamin D3 (Cholecalciferol)\nVitamin K2 (MK-7)\nDầu MCT và phụ liệu theo công bố sản phẩm",
                'usage_instructions' => "Sử dụng theo đúng liều lượng ghi trên nhãn sản phẩm.\nDùng ống nhỏ giọt để lấy lượng phù hợp theo độ tuổi.",
                'storage_instructions' => "Bảo quản nơi khô ráo, tránh ánh sáng.\nNhiệt độ bảo quản dưới 25°C.",
                'warning' => "Không dùng cho người mẫn cảm với bất kỳ thành phần nào của sản phẩm.\nThực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.",
                'price' => 295000,
                'stock' => 32,
                'brand' => ['slug' => 'lineabon', 'name' => 'Lineabon'],
                'attributes' => [
                    ['slug' => 'vitamin-d3', 'name' => 'Vitamin D3'],
                    ['slug' => 'vitamin-k2', 'name' => 'Vitamin K2'],
                    ['slug' => 'dang-nho-giot', 'name' => 'Dạng nhỏ giọt'],
                    ['slug' => 'dung-tich-10ml', 'name' => '10ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
            ],

            'lineabon-d3-k2-spray-10ml' => [
                'name' => 'Vitamin D3 K2 Lineabon dạng xịt 10ml',
                'description' => 'Sản phẩm Lineabon dạng xịt, bổ sung vitamin D3 và vitamin K2, dung tích 10ml.',
                'ingredients' => "Vitamin D3 (Cholecalciferol)\nVitamin K2 (MK-7)\nDầu MCT và phụ liệu theo công bố sản phẩm",
                'usage_instructions' => "Lắc sản phẩm nếu hướng dẫn trên bao bì yêu cầu.\nXịt đúng số lần theo nhóm tuổi và hướng dẫn của sản phẩm.",
                'storage_instructions' => "Bảo quản nơi khô ráo, tránh ánh sáng.\nNhiệt độ bảo quản dưới 25°C.",
                'warning' => "Không dùng cho người mẫn cảm với bất kỳ thành phần nào của sản phẩm.\nKhông tự ý vượt quá liều khuyến nghị trên nhãn.",
                'price' => 330000,
                'stock' => 24,
                'brand' => ['slug' => 'lineabon', 'name' => 'Lineabon'],
                'attributes' => [
                    ['slug' => 'vitamin-d3', 'name' => 'Vitamin D3'],
                    ['slug' => 'vitamin-k2', 'name' => 'Vitamin K2'],
                    ['slug' => 'dang-xit', 'name' => 'Dạng xịt'],
                    ['slug' => 'dung-tich-10ml', 'name' => '10ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
            ],

            'biogaia-protectis-baby-drops-5ml' => [
                'name' => 'BioGaia Protectis baby drops 5ml',
                'description' => 'Men vi sinh dạng nhỏ giọt dành cho trẻ nhỏ, chứa chủng lợi khuẩn Lactobacillus reuteri DSM 17938.',
                'ingredients' => "Lactobacillus reuteri DSM 17938\nCác thành phần nền theo công bố của sản phẩm",
                'usage_instructions' => "Sử dụng theo liều lượng ghi trên nhãn.\nLắc chai theo hướng dẫn trước khi nhỏ giọt.",
                'storage_instructions' => "Bảo quản theo hướng dẫn trên bao bì.\nĐậy kín nắp sau khi sử dụng.",
                'warning' => "Không sử dụng khi sản phẩm có dấu hiệu bất thường hoặc quá hạn.\nThực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.",
                'price' => 460000,
                'stock' => 28,
                'brand' => ['slug' => 'biogaia', 'name' => 'BioGaia'],
                'attributes' => [
                    ['slug' => 'men-vi-sinh', 'name' => 'Men vi sinh'],
                    ['slug' => 'l-reuteri-dsm-17938', 'name' => 'L. reuteri DSM 17938'],
                    ['slug' => 'dang-nho-giot', 'name' => 'Dạng nhỏ giọt'],
                    ['slug' => 'dung-tich-5ml', 'name' => '5ml'],
                    ['slug' => 'tu-so-sinh', 'name' => '0M+'],
                ],
            ],

            'biogaia-protectis-tablets-10-vien' => [
                'name' => 'BioGaia Protectis Tablets 2Y+ 10 viên',
                'description' => 'Thực phẩm bảo vệ sức khỏe dạng viên nhai chứa lợi khuẩn L. reuteri Protectis, phù hợp từ 2 tuổi.',
                'origin' => 'Thụy Điển',
                'manufacturer' => 'Sanico NV',
                'ingredients' => "Lactobacillus reuteri DSM 17938\nXylitol và các phụ liệu theo công bố sản phẩm",
                'usage_instructions' => "Có thể nhai hoặc uống cùng nước.\nSử dụng theo liều lượng ghi trên nhãn sản phẩm.",
                'storage_instructions' => "Bảo quản nơi khô ráo, dưới 25°C.\nĐể xa tầm tay trẻ em.",
                'warning' => "Không dùng nếu mẫn cảm với thành phần của sản phẩm.\nThực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.",
                'price' => 148000,
                'stock' => 36,
                'brand' => ['slug' => 'biogaia', 'name' => 'BioGaia'],
                'attributes' => [
                    ['slug' => 'men-vi-sinh', 'name' => 'Men vi sinh'],
                    ['slug' => 'vien-nhai', 'name' => 'Viên nhai'],
                    ['slug' => '10-vien', 'name' => '10 viên'],
                    ['slug' => 'tu-2-tuoi', 'name' => '2Y+'],
                ],
            ],

            'wellbaby-multi-vitamin-liquid-150ml' => [
                'name' => 'Wellbaby Multi-Vitamin Liquid 150ml',
                'description' => 'Vitamin tổng hợp dạng siro cho trẻ nhỏ, chứa nhiều vitamin và khoáng chất, quy cách 150ml.',
                'origin' => 'Anh Quốc',
                'manufacturer' => 'Laleham Health and Beauty Limited',
                'ingredients' => "Vitamin C, A, D3, E và vitamin nhóm B\nSắt, kẽm và các khoáng chất theo công bố sản phẩm\nChiết xuất mạch nha",
                'usage_instructions' => "Lắc kỹ trước khi sử dụng.\nDùng lượng phù hợp theo độ tuổi ghi trên nhãn sản phẩm.",
                'storage_instructions' => "Bảo quản dưới 25°C nơi khô thoáng.\nSau khi mở nắp, bảo quản theo hướng dẫn của nhà sản xuất.",
                'warning' => "Không sử dụng quá liều khuyến cáo.\nNếu trẻ đang được theo dõi y tế hoặc có dị ứng thực phẩm, tham khảo ý kiến chuyên môn trước khi sử dụng.\nThực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.",
                'price' => 420000,
                'stock' => 22,
                'brand' => ['slug' => 'vitabiotics', 'name' => 'Vitabiotics'],
                'attributes' => [
                    ['slug' => 'vitamin-tong-hop', 'name' => 'Vitamin tổng hợp'],
                    ['slug' => 'dang-siro', 'name' => 'Dạng siro'],
                    ['slug' => 'dung-tich-150ml', 'name' => '150ml'],
                    ['slug' => 'tu-4-thang', 'name' => '4M+'],
                ],
            ],

            'bio-island-dha-for-kids-60-vien' => [
                'name' => 'Bio Island DHA for Kids 60 viên',
                'description' => 'Thực phẩm bổ sung DHA cho trẻ em dạng viên nang mềm, quy cách 60 viên.',
                'origin' => 'Úc',
                'ingredients' => "DHA có nguồn gốc từ tảo\nCác thành phần viên nang theo công bố sản phẩm",
                'usage_instructions' => "Sử dụng theo độ tuổi và hướng dẫn ghi trên nhãn sản phẩm.",
                'storage_instructions' => "Bảo quản nơi khô ráo, thoáng mát và tránh ánh nắng trực tiếp.",
                'warning' => "Không tự ý vượt quá liều khuyến nghị.\nKiểm tra thông tin dị ứng trên bao bì trước khi sử dụng.",
                'price' => 750000,
                'stock' => 18,
                'brand' => ['slug' => 'bio-island', 'name' => 'Bio Island'],
                'attributes' => [
                    ['slug' => 'dha', 'name' => 'DHA'],
                    ['slug' => 'omega-3', 'name' => 'Omega-3'],
                    ['slug' => 'vien-nang-mem', 'name' => 'Viên nang mềm'],
                    ['slug' => '60-vien', 'name' => '60 viên'],
                ],
            ],

            'fitobimbi-appetito' => [
                'name' => 'Thực phẩm bảo vệ sức khỏe Fitobimbi Appetito',
                'description' => 'Sản phẩm thuộc nhóm vitamin và sức khỏe dành cho trẻ em, dạng dùng tiện lợi.',
                'usage_instructions' => "Sử dụng đúng liều lượng và nhóm tuổi ghi trên nhãn sản phẩm.",
                'storage_instructions' => "Bảo quản theo hướng dẫn trên bao bì và đậy kín sau khi sử dụng.",
                'warning' => "Không dùng nếu mẫn cảm với thành phần của sản phẩm.\nThực phẩm này không phải là thuốc và không có tác dụng thay thế thuốc chữa bệnh.",
                'price' => 340000,
                'stock' => 20,
                'brand' => ['slug' => 'fitobimbi', 'name' => 'Fitobimbi'],
                'attributes' => [
                    ['slug' => 'ho-tro-dinh-duong', 'name' => 'Hỗ trợ dinh dưỡng'],
                    ['slug' => 'dang-long', 'name' => 'Dạng lỏng'],
                ],
            ],

            'sat-huu-co-nho-giot-ferrodue' => [
                'name' => 'Sắt hữu cơ nhỏ giọt Ferrodue',
                'description' => 'Sản phẩm bổ sung sắt dạng nhỏ giọt thuộc nhóm vitamin và sức khỏe cho trẻ nhỏ.',
                'usage_instructions' => "Sử dụng theo đúng liều lượng và độ tuổi ghi trên nhãn sản phẩm.",
                'storage_instructions' => "Bảo quản nơi khô ráo, thoáng mát và theo hướng dẫn của nhà sản xuất.",
                'warning' => "Không tự ý tăng liều bổ sung sắt.\nTham khảo ý kiến chuyên môn nếu trẻ đang dùng sản phẩm bổ sung hoặc thuốc khác.",
                'price' => 235000,
                'stock' => 26,
                'brand' => ['slug' => 'ferrodue', 'name' => 'Ferrodue'],
                'attributes' => [
                    ['slug' => 'sat', 'name' => 'Sắt'],
                    ['slug' => 'dang-nho-giot', 'name' => 'Dạng nhỏ giọt'],
                ],
            ],

            'healthy-care-milk-calcium' => [
                'name' => 'Healthy Care Milk Calcium',
                'description' => 'Thực phẩm bảo vệ sức khỏe thuộc nhóm bổ sung canxi cho trẻ em.',
                'usage_instructions' => "Sử dụng theo độ tuổi và hướng dẫn ghi trên bao bì sản phẩm.",
                'storage_instructions' => "Bảo quản nơi khô ráo, thoáng mát và tránh ánh nắng trực tiếp.",
                'warning' => "Không tự ý vượt quá liều khuyến nghị.\nĐọc kỹ thành phần và hướng dẫn trước khi sử dụng.",
                'price' => 337500,
                'stock' => 19,
                'brand' => ['slug' => 'healthy-care', 'name' => 'Healthy Care'],
                'attributes' => [
                    ['slug' => 'canxi', 'name' => 'Canxi'],
                    ['slug' => 'suc-khoe-xuong', 'name' => 'Sức khỏe xương'],
                ],
            ],
        ];
    }
}
