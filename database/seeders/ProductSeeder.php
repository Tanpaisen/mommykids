<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Xóa các sản phẩm mẫu cũ nếu chưa nằm trong giỏ hàng
        |--------------------------------------------------------------------------
        |
        | Không truncate bảng products để tránh phá khóa ngoại.
        | Các sản phẩm kiểu "... - mẫu 1" sẽ được xóa nếu chưa được cart_items dùng.
        |
        */

        Product::query()
            ->where('name', 'like', '% - mẫu %')
            ->get()
            ->each(function (Product $product) {
                $isUsedInCart = DB::table('cart_items')
                    ->where('product_id', $product->id)
                    ->exists();

                if (!$isUsedInCart) {
                    $product->delete();
                }
            });


        /*
        |--------------------------------------------------------------------------
        | 2. Lấy dữ liệu Category / Stage / Tag
        |--------------------------------------------------------------------------
        */

        $categories = Category::all();

        $findCategory = function (array $keywords) use ($categories) {
            foreach ($categories as $category) {
                $haystack = Str::lower(
                    Str::ascii($category->name . ' ' . $category->slug)
                );

                foreach ($keywords as $keyword) {
                    $keyword = Str::lower(Str::ascii($keyword));

                    if (str_contains($haystack, $keyword)) {
                        return $category;
                    }
                }
            }

            return null;
        };


        /*
        |--------------------------------------------------------------------------
        | 3. Danh sách sản phẩm
        |--------------------------------------------------------------------------
        */

        $products = [

            /*
            |--------------------------------------------------------------------------
            | SỮA CHO BÉ
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['sua cho be', 'sua'],
                'name' => 'Sữa Aptamil Profutura Úc số 1 900g (0-6 tháng)',
                'description' => 'Sản phẩm dinh dưỡng công thức Aptamil Profutura dành cho trẻ từ 0 đến 6 tháng tuổi.',
                'price' => 990000,
                'old_price' => 1050000,
                'discount_percent' => 6,
                'stock' => 42,
                'stage_names' => ['Sơ sinh 0-3 tháng', 'Bé 3-6 tháng'],
                'tag_slugs' => ['aptamil'],
            ],

            [
                'category_keywords' => ['sua cho be', 'sua'],
                'name' => 'Sữa Aptamil Profutura Úc số 2 900g (6-12 tháng)',
                'description' => 'Sản phẩm dinh dưỡng công thức Aptamil dành cho bé từ 6 đến 12 tháng tuổi.',
                'price' => 990000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 35,
                'stage_names' => ['Bé 6-12 tháng'],
                'tag_slugs' => ['aptamil'],
            ],

            [
                'category_keywords' => ['sua cho be', 'sua'],
                'name' => 'Sữa Aptamil Úc số 3 900g (12-36 tháng)',
                'description' => 'Sữa công thức Aptamil dành cho trẻ từ 12 đến 36 tháng tuổi.',
                'price' => 840000,
                'old_price' => 890000,
                'discount_percent' => 6,
                'stock' => 28,
                'stage_names' => ['Bé 12-24 tháng', 'Bé 2-3 tuổi'],
                'tag_slugs' => ['aptamil'],
            ],

            [
                'category_keywords' => ['sua cho be', 'sua'],
                'name' => 'Sữa Meiji Infant Formula 800g (0-12 tháng)',
                'description' => 'Sữa Meiji Infant Formula dành cho bé từ sơ sinh đến 12 tháng tuổi.',
                'price' => 575000,
                'old_price' => 610000,
                'discount_percent' => 6,
                'stock' => 54,
                'stage_names' => [
                    'Sơ sinh 0-3 tháng',
                    'Bé 3-6 tháng',
                    'Bé 6-12 tháng',
                ],
                'tag_slugs' => ['meiji'],
            ],

            [
                'category_keywords' => ['sua cho be', 'sua'],
                'name' => 'Sữa Meiji Growing Up Formula 800g (1-3 tuổi)',
                'description' => 'Sữa Meiji Growing Up Formula dành cho trẻ từ 1 đến 3 tuổi.',
                'price' => 480000,
                'old_price' => 520000,
                'discount_percent' => 8,
                'stock' => 46,
                'stage_names' => ['Bé 12-24 tháng', 'Bé 2-3 tuổi'],
                'tag_slugs' => ['meiji'],
            ],

            [
                'category_keywords' => ['sua cho be', 'sua'],
                'name' => 'Sữa NAN Optipro Plus số 1 800g (0-6 tháng)',
                'description' => 'Sản phẩm dinh dưỡng NAN Optipro dành cho bé trong giai đoạn đầu đời.',
                'price' => 595000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 31,
                'stage_names' => ['Sơ sinh 0-3 tháng', 'Bé 3-6 tháng'],
                'tag_slugs' => [],
            ],


            /*
            |--------------------------------------------------------------------------
            | TÃ / BỈM & VỆ SINH
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['ta', 'bim', 've sinh'],
                'name' => 'Tã dán Merries size NB cho bé sơ sinh',
                'description' => 'Tã dán Merries size NB mềm mại, phù hợp với làn da nhạy cảm của trẻ sơ sinh.',
                'price' => 375000,
                'old_price' => 420000,
                'discount_percent' => 11,
                'stock' => 63,
                'stage_names' => ['Sơ sinh 0-3 tháng'],
                'tag_slugs' => ['merries', 'da-nhay-cam', 'danh-cho-so-sinh'],
            ],

            [
                'category_keywords' => ['ta', 'bim', 've sinh'],
                'name' => 'Tã dán Merries size S',
                'description' => 'Tã dán Merries size S với khả năng thấm hút tốt và bề mặt mềm mại.',
                'price' => 395000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 39,
                'stage_names' => ['Sơ sinh 0-3 tháng', 'Bé 3-6 tháng'],
                'tag_slugs' => ['merries', 'da-nhay-cam'],
            ],

            [
                'category_keywords' => ['ta', 'bim', 've sinh'],
                'name' => 'Tã quần Merries size L',
                'description' => 'Tã quần Merries size L phù hợp cho bé vận động nhiều.',
                'price' => 410000,
                'old_price' => 450000,
                'discount_percent' => 9,
                'stock' => 25,
                'stage_names' => ['Bé 6-12 tháng', 'Bé 12-24 tháng'],
                'tag_slugs' => ['merries'],
            ],

            [
                'category_keywords' => ['ta', 'bim', 've sinh'],
                'name' => 'Tã quần Huggies Skin Perfect size XL',
                'description' => 'Tã quần dành cho bé vận động với thiết kế ôm vừa vặn.',
                'price' => 329000,
                'old_price' => 359000,
                'discount_percent' => 8,
                'stock' => 18,
                'stage_names' => ['Bé 12-24 tháng', 'Bé 2-3 tuổi'],
                'tag_slugs' => ['da-nhay-cam'],
            ],


            /*
            |--------------------------------------------------------------------------
            | ĂN DẶM
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['an dam'],
                'name' => 'Bột ăn dặm HiPP Organic vị ngũ cốc',
                'description' => 'Bột ăn dặm ngũ cốc dành cho bé trong giai đoạn bắt đầu làm quen với thức ăn.',
                'price' => 145000,
                'old_price' => 160000,
                'discount_percent' => 9,
                'stock' => 36,
                'stage_names' => ['Bé 6-12 tháng'],
                'tag_slugs' => ['organic', 'danh-cho-be-an-dam'],
            ],

            [
                'category_keywords' => ['an dam'],
                'name' => 'Bánh ăn dặm Gerber vị chuối',
                'description' => 'Bánh ăn dặm mềm, dễ cầm nắm cho bé tập ăn.',
                'price' => 89000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 47,
                'stage_names' => ['Bé 6-12 tháng', 'Bé 12-24 tháng'],
                'tag_slugs' => ['danh-cho-be-an-dam'],
            ],

            [
                'category_keywords' => ['an dam'],
                'name' => 'Cháo tươi Baby vị cá hồi rau củ',
                'description' => 'Cháo ăn dặm tiện lợi với cá hồi và rau củ cho bé.',
                'price' => 39000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 80,
                'stage_names' => ['Bé 6-12 tháng', 'Bé 12-24 tháng'],
                'tag_slugs' => ['khong-chat-bao-quan', 'danh-cho-be-an-dam'],
            ],

            [
                'category_keywords' => ['an dam'],
                'name' => 'Ngũ cốc ăn dặm Nestlé Cerelac',
                'description' => 'Ngũ cốc dành cho trẻ trong thời kỳ ăn dặm.',
                'price' => 79000,
                'old_price' => 89000,
                'discount_percent' => 11,
                'stock' => 51,
                'stage_names' => ['Bé 6-12 tháng'],
                'tag_slugs' => ['danh-cho-be-an-dam'],
            ],

            [
                'category_keywords' => ['an dam'],
                'name' => 'Bánh gạo hữu cơ cho bé vị bí đỏ',
                'description' => 'Bánh gạo hữu cơ dành cho bé ăn dặm, dễ cầm và dễ tan.',
                'price' => 65000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 44,
                'stage_names' => ['Bé 6-12 tháng', 'Bé 12-24 tháng'],
                'tag_slugs' => ['organic', 'danh-cho-be-an-dam'],
            ],


            /*
            |--------------------------------------------------------------------------
            | ĐỒ DÙNG ĂN UỐNG
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['an uong', 'binh sua'],
                'name' => 'Bình sữa Pigeon PPSU Softouch 240ml',
                'description' => 'Bình sữa Pigeon PPSU dung tích 240ml với thiết kế dễ cầm.',
                'price' => 395000,
                'old_price' => 430000,
                'discount_percent' => 8,
                'stock' => 22,
                'stage_names' => ['Sơ sinh 0-3 tháng', 'Bé 3-6 tháng'],
                'tag_slugs' => ['pigeon'],
            ],

            [
                'category_keywords' => ['an uong', 'binh sua'],
                'name' => 'Bình sữa Pigeon PPSU Softouch 160ml',
                'description' => 'Bình sữa dung tích nhỏ phù hợp cho trẻ sơ sinh.',
                'price' => 345000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 27,
                'stage_names' => ['Sơ sinh 0-3 tháng'],
                'tag_slugs' => ['pigeon', 'danh-cho-so-sinh'],
            ],

            [
                'category_keywords' => ['an uong', 'binh sua'],
                'name' => 'Bộ bát thìa ăn dặm silicon cho bé',
                'description' => 'Bộ bát và thìa silicon hỗ trợ bé bắt đầu tự ăn.',
                'price' => 189000,
                'old_price' => 219000,
                'discount_percent' => 14,
                'stock' => 34,
                'stage_names' => ['Bé 6-12 tháng', 'Bé 12-24 tháng'],
                'tag_slugs' => ['danh-cho-be-an-dam'],
            ],

            [
                'category_keywords' => ['an uong', 'binh sua'],
                'name' => 'Cốc tập uống chống đổ 240ml',
                'description' => 'Cốc tập uống thiết kế tay cầm hai bên giúp bé luyện kỹ năng uống độc lập.',
                'price' => 129000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 40,
                'stage_names' => ['Bé 6-12 tháng', 'Bé 12-24 tháng'],
                'tag_slugs' => [],
            ],


            /*
            |--------------------------------------------------------------------------
            | CHĂM SÓC BÉ
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['cham soc be'],
                'name' => 'Sữa tắm gội Cetaphil Baby Gentle Wash 230ml',
                'description' => 'Sữa tắm gội dịu nhẹ dành cho làn da của trẻ nhỏ.',
                'price' => 185000,
                'old_price' => 205000,
                'discount_percent' => 10,
                'stock' => 33,
                'stage_names' => [
                    'Sơ sinh 0-3 tháng',
                    'Bé 3-6 tháng',
                    'Bé 6-12 tháng',
                ],
                'tag_slugs' => ['da-nhay-cam'],
            ],

            [
                'category_keywords' => ['cham soc be'],
                'name' => 'Kem chống hăm Bepanthen 30g',
                'description' => 'Kem chăm sóc vùng da mặc tã dành cho trẻ nhỏ.',
                'price' => 99000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 57,
                'stage_names' => ['Sơ sinh 0-3 tháng', 'Bé 3-6 tháng'],
                'tag_slugs' => ['da-nhay-cam', 'danh-cho-so-sinh'],
            ],

            [
                'category_keywords' => ['cham soc be'],
                'name' => 'Nước giặt quần áo trẻ em D-nee 3L',
                'description' => 'Nước giặt dành cho quần áo trẻ nhỏ với hương thơm dịu nhẹ.',
                'price' => 189000,
                'old_price' => 209000,
                'discount_percent' => 10,
                'stock' => 29,
                'stage_names' => [],
                'tag_slugs' => [],
            ],

            [
                'category_keywords' => ['cham soc be'],
                'name' => 'Khăn ướt không mùi cho bé 100 tờ',
                'description' => 'Khăn ướt không mùi sử dụng trong chăm sóc vệ sinh hằng ngày cho bé.',
                'price' => 49000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 90,
                'stage_names' => ['Sơ sinh 0-3 tháng'],
                'tag_slugs' => ['danh-cho-so-sinh'],
            ],


            /*
            |--------------------------------------------------------------------------
            | CHĂM SÓC MẸ
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['cham soc me', 'me bau', 'sau sinh'],
                'name' => 'Máy hút sữa điện đôi Fatzbaby',
                'description' => 'Máy hút sữa điện đôi hỗ trợ mẹ sau sinh hút sữa thuận tiện tại nhà.',
                'price' => 1290000,
                'old_price' => 1490000,
                'discount_percent' => 13,
                'stock' => 12,
                'stage_names' => [],
                'tag_slugs' => [],
            ],

            [
                'category_keywords' => ['cham soc me', 'me bau', 'sau sinh'],
                'name' => 'Túi trữ sữa mẹ 200ml hộp 30 túi',
                'description' => 'Túi trữ sữa dùng để bảo quản sữa mẹ trong tủ lạnh hoặc tủ đông.',
                'price' => 119000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 61,
                'stage_names' => [],
                'tag_slugs' => [],
            ],

            [
                'category_keywords' => ['cham soc me', 'me bau', 'sau sinh'],
                'name' => 'Miếng lót thấm sữa dùng một lần',
                'description' => 'Miếng lót hỗ trợ mẹ giữ quần áo khô thoáng trong thời kỳ cho con bú.',
                'price' => 95000,
                'old_price' => 110000,
                'discount_percent' => 14,
                'stock' => 45,
                'stage_names' => [],
                'tag_slugs' => [],
            ],


            /*
            |--------------------------------------------------------------------------
            | ĐỒ CHƠI & HỌC TẬP
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['do choi', 'hoc tap'],
                'name' => 'Xúc xắc mềm phát triển giác quan cho bé',
                'description' => 'Đồ chơi xúc xắc hỗ trợ bé nhận biết âm thanh và luyện khả năng cầm nắm.',
                'price' => 89000,
                'old_price' => null,
                'discount_percent' => null,
                'stock' => 38,
                'stage_names' => ['Bé 3-6 tháng', 'Bé 6-12 tháng'],
                'tag_slugs' => [],
            ],

            [
                'category_keywords' => ['do choi', 'hoc tap'],
                'name' => 'Sách vải tương tác cho bé sơ sinh',
                'description' => 'Sách vải nhiều màu sắc giúp bé làm quen hình ảnh và chất liệu.',
                'price' => 129000,
                'old_price' => 149000,
                'discount_percent' => 13,
                'stock' => 32,
                'stage_names' => ['Sơ sinh 0-3 tháng', 'Bé 3-6 tháng'],
                'tag_slugs' => ['danh-cho-so-sinh'],
            ],

            [
                'category_keywords' => ['do choi', 'hoc tap'],
                'name' => 'Bộ xếp hình khối lớn 36 chi tiết',
                'description' => 'Bộ xếp hình hỗ trợ phát triển tư duy, màu sắc và khả năng phối hợp tay mắt.',
                'price' => 259000,
                'old_price' => 299000,
                'discount_percent' => 13,
                'stock' => 21,
                'stage_names' => ['Bé 2-3 tuổi', 'Bé 3-6 tuổi'],
                'tag_slugs' => [],
            ],


            /*
            |--------------------------------------------------------------------------
            | RA NGOÀI & DI CHUYỂN
            |--------------------------------------------------------------------------
            */

            [
                'category_keywords' => ['ra ngoai', 'di chuyen', 'xe day'],
                'name' => 'Xe đẩy trẻ em gấp gọn 2 chiều',
                'description' => 'Xe đẩy gấp gọn phù hợp cho gia đình thường xuyên đưa bé ra ngoài.',
                'price' => 1890000,
                'old_price' => 2150000,
                'discount_percent' => 12,
                'stock' => 8,
                'stage_names' => [
                    'Bé 3-6 tháng',
                    'Bé 6-12 tháng',
                    'Bé 12-24 tháng',
                ],
                'tag_slugs' => [],
            ],

            [
                'category_keywords' => ['ra ngoai', 'di chuyen', 'xe day'],
                'name' => 'Địu em bé đa tư thế có trợ lực',
                'description' => 'Địu hỗ trợ nhiều tư thế giúp bố mẹ thuận tiện khi đưa bé ra ngoài.',
                'price' => 690000,
                'old_price' => 790000,
                'discount_percent' => 13,
                'stock' => 14,
                'stage_names' => ['Bé 3-6 tháng', 'Bé 6-12 tháng'],
                'tag_slugs' => [],
            ],

            [
                'category_keywords' => ['ra ngoai', 'di chuyen', 'xe day'],
                'name' => 'Ghế ngồi ô tô trẻ em tiêu chuẩn an toàn',
                'description' => 'Ghế ngồi ô tô dành cho trẻ nhỏ với dây đai và đệm tựa hỗ trợ.',
                'price' => 2490000,
                'old_price' => 2790000,
                'discount_percent' => 11,
                'stock' => 6,
                'stage_names' => [
                    'Bé 6-12 tháng',
                    'Bé 12-24 tháng',
                    'Bé 2-3 tuổi',
                ],
                'tag_slugs' => [],
            ],
        ];


        /*
        |--------------------------------------------------------------------------
        | 4. Insert / Update
        |--------------------------------------------------------------------------
        */

        foreach ($products as $data) {

            $category = $findCategory(
                $data['category_keywords']
            );

            /*
             * Nếu project chưa có đúng category tương ứng,
             * bỏ qua sản phẩm đó thay vì lỗi toàn Seeder.
             */
            if (!$category) {
                continue;
            }


            $slug = Str::slug($data['name']);


            $product = Product::updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],

                    /*
                     * Không hotlink ảnh từ website khác.
                     * Bạn có thể upload ảnh thật từ Admin sau.
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


            /*
            |--------------------------------------------------------------------------
            | Gắn Stage
            |--------------------------------------------------------------------------
            */

            $stageIds = Stage::query()
                ->whereIn(
                    'name',
                    $data['stage_names']
                )
                ->pluck('id')
                ->all();

            $product->stages()->sync($stageIds);


            /*
            |--------------------------------------------------------------------------
            | Gắn Tag
            |--------------------------------------------------------------------------
            */

            $tagIds = Tag::query()
                ->whereIn(
                    'slug',
                    $data['tag_slugs']
                )
                ->pluck('id')
                ->all();

            $product->tags()->sync($tagIds);
        }
    }
}