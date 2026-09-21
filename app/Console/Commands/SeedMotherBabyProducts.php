<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Command;

class SeedMotherBabyProducts extends Command
{
    protected $signature = 'products:seed-mother-baby {--dry-run : Chỉ hiển thị dữ liệu sẽ tạo, không ghi database}';
    protected $description = 'Tạo dữ liệu sản phẩm mẫu cho danh mục Đồ dùng mẹ & bé';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $category = Category::query()
            ->where('slug', 'do-dung-me-be')
            ->first();

        if (!$category) {
            $this->error('Không tìm thấy category slug do-dung-me-be.');
            return self::FAILURE;
        }

        foreach ($this->products() as $slug => $data) {
            if ($dryRun) {
                $this->line('CHECK ' . $data['name'] . ' - ' . number_format($data['price'], 0, ',', '.') . 'đ');
                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'origin' => $data['origin'] ?? null,
                    'ingredients' => $data['ingredients'] ?? null,
                    'usage_instructions' => $data['usage_instructions'] ?? null,
                    'storage_instructions' => $data['storage_instructions'] ?? null,
                    'warning' => $data['warning'] ?? null,
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'is_active' => true,
                    'is_featured' => false,
                ]
            );

            $tagIds = [];

            if (!empty($data['brand'])) {
                $tagIds[] = $this->tag(
                    $data['brand']['slug'],
                    $data['brand']['name'],
                    'brand'
                )->id;
            }

            foreach ($data['attributes'] ?? [] as $attribute) {
                $tagIds[] = $this->tag(
                    $attribute['slug'],
                    $attribute['name'],
                    'attribute'
                )->id;
            }

            if ($tagIds) {
                $product->tags()->syncWithoutDetaching(array_unique($tagIds));
            }

            $this->info("OK #{$product->id} {$product->name}");
        }

        $this->info($dryRun ? 'Dry-run hoàn tất.' : 'Hoàn tất tạo/cập nhật sản phẩm Đồ dùng mẹ & bé.');
        return self::SUCCESS;
    }

    private function tag(string $slug, string $name, string $type): Tag
    {
        $tag = Tag::withTrashed()->where('slug', $slug)->first();

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
            'diu-ngoi-cho-be-6in1-mamago-airy-mm01-xanh-navy' => [
                'name' => 'Địu ngồi cho bé 6in1 Mamago Airy MM01 (Xanh Navy)',
                'description' => 'Địu ngồi Mamago Airy MM01 thiết kế đa tư thế, hỗ trợ ba mẹ bế và di chuyển cùng bé thuận tiện hơn.',
                'price' => 399000,
                'stock' => 28,
                'brand' => ['slug' => 'mamago', 'name' => 'Mamago'],
                'attributes' => [
                    ['slug' => 'diu-em-be', 'name' => 'Địu em bé'],
                    ['slug' => '6in1', 'name' => '6in1'],
                    ['slug' => 'di-chuyen-cung-be', 'name' => 'Di chuyển cùng bé'],
                ],
            ],
            'diu-ngoi-cho-be-4-tu-the-baby-lab-ac' => [
                'name' => 'Địu ngồi cho bé 4 tư thế Baby Lab AC',
                'description' => 'Địu Baby Lab AC hỗ trợ 4 tư thế bế, phù hợp nhu cầu di chuyển và chăm sóc bé hằng ngày.',
                'price' => 299000,
                'stock' => 24,
                'brand' => ['slug' => 'baby-lab', 'name' => 'Baby Lab'],
                'attributes' => [
                    ['slug' => 'diu-em-be', 'name' => 'Địu em bé'],
                    ['slug' => '4-tu-the', 'name' => '4 tư thế'],
                ],
            ],
            'tui-dung-do-me-va-be-mother-v-002' => [
                'name' => 'Túi đựng đồ cho mẹ và bé Mother-V 002',
                'description' => 'Túi Mother-V 002 có nhiều ngăn và quai đeo tiện dụng, phù hợp mang theo các vật dụng thiết yếu cho bé.',
                'price' => 299000,
                'stock' => 31,
                'brand' => ['slug' => 'mother-v', 'name' => 'Mother-V'],
                'attributes' => [
                    ['slug' => 'tui-dung-do-me-be', 'name' => 'Túi đựng đồ'],
                    ['slug' => 'nhieu-ngan', 'name' => 'Nhiều ngăn'],
                    ['slug' => 'quai-deo-cheo', 'name' => 'Quai đeo chéo'],
                    ['slug' => 'kich-thuoc-38x11-5x32cm', 'name' => '38 x 11,5 x 32 cm'],
                ],
            ],
            'ghe-ngoi-an-dam-meetbaby-hc011-xanh-vang' => [
                'name' => 'Ghế ngồi ăn dặm cho bé Meetbaby HC011 (Xanh vàng)',
                'description' => 'Ghế ăn dặm Meetbaby HC011 hỗ trợ bé ngồi ăn ổn định trong giai đoạn tập ăn.',
                'price' => 790000,
                'stock' => 16,
                'brand' => ['slug' => 'meetbaby', 'name' => 'Meetbaby'],
                'attributes' => [
                    ['slug' => 'ghe-an-dam', 'name' => 'Ghế ăn dặm'],
                    ['slug' => 'do-dung-an-dam', 'name' => 'Đồ dùng ăn dặm'],
                ],
            ],
            'may-xay-thuc-an-dam-fatzbaby-fb5001mb-0-3l' => [
                'name' => 'Máy xay thức ăn dặm đa năng FatzBaby FB5001MB 0,3 lít',
                'description' => 'Máy xay FatzBaby FB5001MB dung tích 0,3 lít, hỗ trợ xay nhuyễn khẩu phần nhỏ cho bé.',
                'price' => 568000,
                'stock' => 18,
                'brand' => ['slug' => 'fatzbaby', 'name' => 'FatzBaby'],
                'attributes' => [
                    ['slug' => 'may-xay-thuc-an', 'name' => 'Máy xay thức ăn'],
                    ['slug' => 'dung-tich-0-3l', 'name' => '0,3 lít'],
                    ['slug' => 'do-dung-an-dam', 'name' => 'Đồ dùng ăn dặm'],
                ],
            ],
            'khay-tru-do-an-dam-amori-inochi' => [
                'name' => 'Khay trữ đồ ăn dặm Amori Inochi',
                'description' => 'Khay trữ đồ ăn dặm Amori Inochi chia nhiều ngăn nhỏ, hỗ trợ chia khẩu phần và bảo quản đông thức ăn cho bé.',
                'origin' => 'Việt Nam',
                'ingredients' => 'Nhựa PP nguyên sinh, hạt màu, phụ gia kháng khuẩn Ag+',
                'usage_instructions' => "Dùng để chia và trữ đông khẩu phần ăn dặm.\nKhông đậy nắp khi sử dụng trong lò vi sóng.",
                'storage_instructions' => 'Bảo quản nơi khô ráo, thoáng mát.',
                'warning' => 'Tránh xa nguồn nhiệt.',
                'price' => 35000,
                'stock' => 45,
                'brand' => ['slug' => 'inochi', 'name' => 'Inochi'],
                'attributes' => [
                    ['slug' => 'khay-tru-thuc-an', 'name' => 'Khay trữ thức ăn'],
                    ['slug' => 'nhua-pp', 'name' => 'Nhựa PP'],
                    ['slug' => 'tu-6-thang', 'name' => '6M+'],
                    ['slug' => '98g', 'name' => '98g'],
                ],
            ],
            'hop-chia-sua-bot-moyuum-3-ngan-be' => [
                'name' => 'Hộp chia sữa bột Moyuum 3 ngăn (Be)',
                'description' => 'Hộp chia sữa Moyuum 3 ngăn giúp chia sẵn từng cữ sữa bột hoặc thức ăn khô khi đưa bé ra ngoài.',
                'price' => 255000,
                'stock' => 34,
                'brand' => ['slug' => 'moyuum', 'name' => 'Moyuum'],
                'attributes' => [
                    ['slug' => 'hop-chia-sua', 'name' => 'Hộp chia sữa'],
                    ['slug' => '3-ngan', 'name' => '3 ngăn'],
                    ['slug' => 'do-dung-an-dam', 'name' => 'Đồ dùng ăn dặm'],
                ],
            ],
            'yem-an-silicone-moyuum-cao-cap-cam' => [
                'name' => 'Yếm ăn silicone Moyuum cao cấp (Cam)',
                'description' => 'Yếm ăn silicone Moyuum hỗ trợ giữ quần áo bé sạch hơn trong bữa ăn, dễ vệ sinh và phù hợp sử dụng hằng ngày.',
                'price' => 369000,
                'stock' => 27,
                'brand' => ['slug' => 'moyuum', 'name' => 'Moyuum'],
                'attributes' => [
                    ['slug' => 'yem-an-dam', 'name' => 'Yếm ăn dặm'],
                    ['slug' => 'silicone', 'name' => 'Silicone'],
                    ['slug' => 'de-ve-sinh', 'name' => 'Dễ vệ sinh'],
                ],
            ],
            'dai-xe-may-cho-be-tam-an-co-do-co' => [
                'name' => 'Đai xe máy cho bé Tâm An (có đỡ cổ)',
                'description' => 'Đai xe máy Tâm An có phần đỡ cổ, hỗ trợ cố định tư thế của bé khi ngồi cùng người lớn trên xe máy.',
                'price' => 109000,
                'stock' => 39,
                'brand' => ['slug' => 'tam-an', 'name' => 'Tâm An'],
                'attributes' => [
                    ['slug' => 'dai-xe-may', 'name' => 'Đai xe máy'],
                    ['slug' => 'co-do-co', 'name' => 'Có đỡ cổ'],
                    ['slug' => 'do-dung-di-chuyen', 'name' => 'Đồ dùng di chuyển'],
                ],
            ],
        ];
    }
}
