<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Aptamil Profutura Úc số 1 900g (0-6 tháng)',
                'description' => 'Sản phẩm dinh dưỡng công thức Aptamil Profutura dành cho trẻ từ 0 đến 6 tháng tuổi.',
                'price' => 990000,
                'old_price' => 1050000,
                'discount_percent' => 6,
                'stock' => 42,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Aptamil Profutura Úc số 2 900g (6-12 tháng)',
                'description' => 'Sản phẩm dinh dưỡng công thức Aptamil dành cho trẻ từ 6 đến 12 tháng.',
                'price' => 990000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 35,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Aptamil Úc số 3 900g (12-36 tháng)',
                'description' => 'Sữa công thức Aptamil dành cho trẻ từ 12 đến 36 tháng tuổi.',
                'price' => 840000,
                'old_price' => 890000,
                'discount_percent' => 6,
                'stock' => 28,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Meiji Infant Formula 800g (0-12 tháng)',
                'description' => 'Sữa Meiji Infant Formula dành cho trẻ từ sơ sinh đến 12 tháng.',
                'price' => 575000,
                'old_price' => 610000,
                'discount_percent' => 6,
                'stock' => 54,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Meiji Growing Up Formula 800g (1-3 tuổi)',
                'description' => 'Sữa Meiji Growing Up Formula dành cho trẻ từ 1 đến 3 tuổi.',
                'price' => 480000,
                'old_price' => 520000,
                'discount_percent' => 8,
                'stock' => 46,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa NAN Optipro Plus số 1 800g (0-6 tháng)',
                'description' => 'Sản phẩm dinh dưỡng NAN Optipro dành cho bé trong giai đoạn đầu đời.',
                'price' => 595000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 31,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã dán Merries size NB cho bé sơ sinh',
                'description' => 'Tã dán Merries size NB mềm mại, phù hợp với làn da nhạy cảm của trẻ sơ sinh.',
                'price' => 375000,
                'old_price' => 420000,
                'discount_percent' => 11,
                'stock' => 63,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã dán Merries size S',
                'description' => 'Tã dán Merries size S với khả năng thấm hút tốt.',
                'price' => 395000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 39,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã quần Merries size L',
                'description' => 'Tã quần Merries size L phù hợp cho bé vận động.',
                'price' => 410000,
                'old_price' => 450000,
                'discount_percent' => 9,
                'stock' => 25,
            ],
            [
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Bột ăn dặm HiPP Organic vị ngũ cốc',
                'description' => 'Bột ăn dặm ngũ cốc dành cho bé bắt đầu làm quen với thức ăn.',
                'price' => 145000,
                'old_price' => 160000,
                'discount_percent' => 9,
                'stock' => 36,
            ],
            [
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Bánh ăn dặm Gerber vị chuối',
                'description' => 'Bánh ăn dặm mềm, dễ cầm nắm cho bé.',
                'price' => 89000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 47,
            ],
            [
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Bình sữa Pigeon PPSU Softouch 240ml',
                'description' => 'Bình sữa Pigeon PPSU dung tích 240ml.',
                'price' => 395000,
                'old_price' => 430000,
                'discount_percent' => 8,
                'stock' => 22,
            ],
            [
                'category' => 'Đồ chơi, học tập',
                'name' => 'Sách vải tương tác cho bé sơ sinh',
                'description' => 'Sách vải nhiều màu sắc giúp bé làm quen hình ảnh và chất liệu.',
                'price' => 129000,
                'old_price' => 149000,
                'discount_percent' => 13,
                'stock' => 32,
            ],
            [
                'category' => 'Xe cho bé',
                'name' => 'Xe đẩy trẻ em gấp gọn 2 chiều',
                'description' => 'Xe đẩy gấp gọn phù hợp khi đưa bé ra ngoài.',
                'price' => 1890000,
                'old_price' => 2150000,
                'discount_percent' => 12,
                'stock' => 8,
            ],
        ];

        foreach ($products as $data) {

            $category = Category::where(
                'name',
                $data['category']
            )->first();

            if (!$category) {
                continue;
            }

            $slug = Str::slug($data['name']);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],

                    /*
                     * Ảnh sẽ được upload từ Admin.
                     */
                    'image' => null,
                    'images' => [],

                    'price' => $data['price'],
                    'old_price' => $data['old_price'],
                    'discount_percent' => $data['discount_percent'],
                    'stock' => $data['stock'],
                    'is_active' => true,
                ]
            );
        }
    }
}