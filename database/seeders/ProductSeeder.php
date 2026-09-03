<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [

            /*
            |--------------------------------------------------------------------------
            | SỮA CHO BÉ
            |--------------------------------------------------------------------------
            */

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
                'description' => 'Sản phẩm dinh dưỡng công thức Aptamil Profutura dành cho trẻ từ 6 đến 12 tháng.',
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
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Friso Gold Pro số 3 800g (1-3 tuổi)',
                'description' => 'Sữa dinh dưỡng Friso Gold Pro dành cho trẻ từ 1 đến 3 tuổi.',
                'price' => 625000,
                'old_price' => 675000,
                'discount_percent' => 7,
                'stock' => 38,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Enfamil A+ NeuroPro số 2 830g',
                'description' => 'Sản phẩm dinh dưỡng Enfamil A+ NeuroPro dành cho trẻ trong giai đoạn phát triển.',
                'price' => 735000,
                'old_price' => 790000,
                'discount_percent' => 7,
                'stock' => 29,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa Similac 5G số 2 900g',
                'description' => 'Sữa Similac 5G bổ sung dưỡng chất cho trẻ trong giai đoạn phát triển.',
                'price' => 645000,
                'old_price' => 690000,
                'discount_percent' => 7,
                'stock' => 34,
            ],
            [
                'category' => 'Sữa cho bé',
                'name' => 'Sữa ColosBaby Gold 0+ 800g',
                'description' => 'Sữa ColosBaby Gold dành cho trẻ nhỏ, bổ sung dinh dưỡng thiết yếu.',
                'price' => 520000,
                'old_price' => 565000,
                'discount_percent' => 8,
                'stock' => 41,
            ],
              
                [
    'category' => 'Sữa cho bé',
    'name' => 'Sữa Morinaga Hagukumi số 1 800g (0-6 tháng)',
    'description' => 'Sản phẩm dinh dưỡng công thức Morinaga Hagukumi số 1 dành cho trẻ từ 0 đến 6 tháng tuổi.',
    'price' => 570000,
    'old_price' => 620000,
    'discount_percent' => 8,
    'stock' => 30,
],
[
    'category' => 'Sữa cho bé',
    'name' => 'Sữa Morinaga Chilmil số 2 800g (6-36 tháng)',
    'description' => 'Sản phẩm dinh dưỡng công thức Morinaga Chilmil số 2 dành cho trẻ từ 6 đến 36 tháng tuổi.',
    'price' => 505000,
    'old_price' => 560000,
    'discount_percent' => 10,
    'stock' => 27,
],

            /*
            |--------------------------------------------------------------------------
            | BỈM TÃ & VỆ SINH
            |--------------------------------------------------------------------------
            */

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
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã quần Huggies Skin Perfect size XL',
                'description' => 'Tã quần Huggies Skin Perfect mềm mại, giúp bé thoải mái vận động.',
                'price' => 359000,
                'old_price' => 399000,
                'discount_percent' => 10,
                'stock' => 44,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã quần Huggies Dry size L',
                'description' => 'Tã quần Huggies Dry size L với khả năng thấm hút nhanh.',
                'price' => 329000,
                'old_price' => 365000,
                'discount_percent' => 10,
                'stock' => 51,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã quần Bobby Extra Soft size XL',
                'description' => 'Tã quần Bobby Extra Soft mềm mại và thoáng khí cho bé.',
                'price' => 315000,
                'old_price' => 349000,
                'discount_percent' => 10,
                'stock' => 37,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Tã dán Moony Natural size S',
                'description' => 'Tã dán Moony Natural thiết kế mềm mại dành cho làn da nhạy cảm.',
                'price' => 399000,
                'old_price' => 449000,
                'discount_percent' => 11,
                'stock' => 32,
            ],
            [
                'category' => 'Bỉm tã & vệ sinh',
                'name' => 'Khăn ướt Mamamy không mùi 100 tờ',
                'description' => 'Khăn ướt không mùi, phù hợp vệ sinh hằng ngày cho trẻ nhỏ.',
                'price' => 45000,
                'old_price' => 55000,
                'discount_percent' => 18,
                'stock' => 90,
            ],

            /*
            |--------------------------------------------------------------------------
            | ĂN DẶM - DINH DƯỠNG
            |--------------------------------------------------------------------------
            */

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
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Cháo tươi Baby vị cá hồi rau củ',
                'description' => 'Cháo tươi cho bé với cá hồi và rau củ, tiện lợi khi sử dụng.',
                'price' => 39000,
                'old_price' => 45000,
                'discount_percent' => 13,
                'stock' => 70,
            ],
            [
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Ngũ cốc ăn dặm Nestlé Cerelac',
                'description' => 'Ngũ cốc ăn dặm Cerelac bổ sung dưỡng chất cho bé trong giai đoạn ăn dặm.',
                'price' => 82000,
                'old_price' => 95000,
                'discount_percent' => 14,
                'stock' => 58,
            ],
            [
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Bánh gạo hữu cơ cho bé vị bí đỏ',
                'description' => 'Bánh gạo hữu cơ vị bí đỏ dễ cầm nắm, phù hợp cho trẻ nhỏ.',
                'price' => 59000,
                'old_price' => 69000,
                'discount_percent' => 14,
                'stock' => 52,
            ],
            [
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Bột ăn dặm Ridielac Gold vị gạo sữa',
                'description' => 'Bột ăn dặm Ridielac Gold vị gạo sữa thơm ngon cho bé.',
                'price' => 76000,
                'old_price' => 85000,
                'discount_percent' => 11,
                'stock' => 64,
            ],
            [
                'category' => 'Ăn dặm, dinh dưỡng',
                'name' => 'Bánh ăn dặm Pigeon vị rau củ',
                'description' => 'Bánh ăn dặm Pigeon vị rau củ giúp bé tập nhai.',
                'price' => 72000,
                'old_price' => 80000,
                'discount_percent' => 10,
                'stock' => 45,
            ],

            /*
            |--------------------------------------------------------------------------
            | BÌNH SỮA & PHỤ KIỆN
            |--------------------------------------------------------------------------
            */

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
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Bình sữa Pigeon PPSU Softouch 160ml',
                'description' => 'Bình sữa Pigeon PPSU 160ml phù hợp cho trẻ nhỏ.',
                'price' => 365000,
                'old_price' => 399000,
                'discount_percent' => 9,
                'stock' => 27,
            ],
            [
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Bình sữa Philips Avent Natural Response 260ml',
                'description' => 'Bình sữa Philips Avent Natural Response dung tích 260ml.',
                'price' => 345000,
                'old_price' => 385000,
                'discount_percent' => 10,
                'stock' => 33,
            ],
            [
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Bình sữa Comotomo Silicon 250ml',
                'description' => 'Bình sữa Comotomo thân silicon mềm mại, dung tích 250ml.',
                'price' => 429000,
                'old_price' => 469000,
                'discount_percent' => 9,
                'stock' => 24,
            ],
            [
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Bộ bát thìa ăn dặm silicon cho bé',
                'description' => 'Bộ bát và thìa silicon hỗ trợ bé trong giai đoạn tập ăn.',
                'price' => 189000,
                'old_price' => 219000,
                'discount_percent' => 14,
                'stock' => 40,
            ],
            [
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Cốc tập uống chống đổ 240ml',
                'description' => 'Cốc tập uống chống đổ giúp bé làm quen với việc uống nước.',
                'price' => 159000,
                'old_price' => 179000,
                'discount_percent' => 11,
                'stock' => 36,
            ],
            [
                'category' => 'Bình sữa & phụ kiện',
                'name' => 'Máy tiệt trùng bình sữa UV mini',
                'description' => 'Máy tiệt trùng hỗ trợ vệ sinh bình sữa và phụ kiện cho bé.',
                'price' => 1290000,
                'old_price' => 1490000,
                'discount_percent' => 13,
                'stock' => 12,
            ],

            /*
            |--------------------------------------------------------------------------
            | ĐỒ CHƠI - HỌC TẬP
            |--------------------------------------------------------------------------
            */

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
                'category' => 'Đồ chơi, học tập',
                'name' => 'Xúc xắc mềm phát triển giác quan cho bé',
                'description' => 'Đồ chơi xúc xắc mềm giúp bé phát triển thính giác và khả năng cầm nắm.',
                'price' => 99000,
                'old_price' => 119000,
                'discount_percent' => 17,
                'stock' => 43,
            ],
            [
                'category' => 'Đồ chơi, học tập',
                'name' => 'Bộ xếp hình khối lớn 36 chi tiết',
                'description' => 'Bộ xếp hình khối lớn giúp bé luyện tư duy và khả năng phối hợp tay mắt.',
                'price' => 249000,
                'old_price' => 289000,
                'discount_percent' => 14,
                'stock' => 28,
            ],
            [
                'category' => 'Đồ chơi, học tập',
                'name' => 'Thảm chơi phát triển giác quan cho bé',
                'description' => 'Thảm chơi nhiều màu sắc và họa tiết hỗ trợ phát triển giác quan.',
                'price' => 459000,
                'old_price' => 529000,
                'discount_percent' => 13,
                'stock' => 19,
            ],
            [
                'category' => 'Đồ chơi, học tập',
                'name' => 'Đàn piano mini phát nhạc cho bé',
                'description' => 'Đồ chơi đàn piano mini với âm thanh vui nhộn dành cho trẻ nhỏ.',
                'price' => 269000,
                'old_price' => 319000,
                'discount_percent' => 16,
                'stock' => 25,
            ],

            /*
            |--------------------------------------------------------------------------
            | XE CHO BÉ
            |--------------------------------------------------------------------------
            */

            [
                'category' => 'Xe cho bé',
                'name' => 'Xe đẩy trẻ em gấp gọn 2 chiều',
                'description' => 'Xe đẩy gấp gọn phù hợp khi đưa bé ra ngoài.',
                'price' => 1890000,
                'old_price' => 2150000,
                'discount_percent' => 12,
                'stock' => 8,
            ],
            [
                'category' => 'Xe cho bé',
                'name' => 'Xe đẩy em bé siêu nhẹ gấp gọn du lịch',
                'description' => 'Xe đẩy nhẹ, gấp gọn nhanh, phù hợp cho gia đình thường xuyên di chuyển.',
                'price' => 1490000,
                'old_price' => 1690000,
                'discount_percent' => 12,
                'stock' => 11,
            ],
            [
                'category' => 'Xe cho bé',
                'name' => 'Xe chòi chân 4 bánh cho bé',
                'description' => 'Xe chòi chân 4 bánh giúp bé rèn luyện khả năng vận động và giữ thăng bằng.',
                'price' => 590000,
                'old_price' => 690000,
                'discount_percent' => 14,
                'stock' => 17,
            ],
            [
                'category' => 'Xe cho bé',
                'name' => 'Xe đạp 3 bánh có cần đẩy cho bé',
                'description' => 'Xe đạp ba bánh có cần đẩy, phù hợp cho bé tập làm quen với xe.',
                'price' => 890000,
                'old_price' => 990000,
                'discount_percent' => 10,
                'stock' => 14,
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | CẤU HÌNH BỘ LỌC CHO SẢN PHẨM SỮA
        |--------------------------------------------------------------------------
        |
        | Dữ liệu tuổi được khai báo tường minh theo SKU/tên sản phẩm.
        | Không đoán Organic / Không đường / Da nhạy cảm...
        |
        */

        $milkFilterMap = [
            'Sữa Aptamil Profutura Úc số 1 900g (0-6 tháng)' => [
                'brand' => 'Aptamil',
                'age_from' => 0,
                'age_to' => 6,
                'attributes' => ['Dạng bột', 'Sữa số 1'],
            ],
            'Sữa Aptamil Profutura Úc số 2 900g (6-12 tháng)' => [
                'brand' => 'Aptamil',
                'age_from' => 6,
                'age_to' => 12,
                'attributes' => ['Dạng bột', 'Sữa số 2'],
            ],
            'Sữa Aptamil Úc số 3 900g (12-36 tháng)' => [
                'brand' => 'Aptamil',
                'age_from' => 12,
                'age_to' => 36,
                'attributes' => ['Dạng bột', 'Sữa số 3'],
            ],
            'Sữa Meiji Infant Formula 800g (0-12 tháng)' => [
                'brand' => 'Meiji',
                'age_from' => 0,
                'age_to' => 12,
                'attributes' => ['Dạng bột'],
            ],
            'Sữa Meiji Growing Up Formula 800g (1-3 tuổi)' => [
                'brand' => 'Meiji',
                'age_from' => 12,
                'age_to' => 36,
                'attributes' => ['Dạng bột'],
            ],
            'Sữa NAN Optipro Plus số 1 800g (0-6 tháng)' => [
                'brand' => 'NAN',
                'age_from' => 0,
                'age_to' => 6,
                'attributes' => ['Dạng bột', 'Sữa số 1'],
            ],
            'Sữa Friso Gold Pro số 3 800g (1-3 tuổi)' => [
                'brand' => 'Friso',
                'age_from' => 12,
                'age_to' => 36,
                'attributes' => ['Dạng bột', 'Sữa số 3'],
            ],
            'Sữa Enfamil A+ NeuroPro số 2 830g' => [
                'brand' => 'Enfamil',
                'age_from' => 6,
                'age_to' => 12,
                'attributes' => ['Dạng bột', 'Sữa số 2'],
            ],
            'Sữa Similac 5G số 2 900g' => [
                'brand' => 'Similac',
                'age_from' => 6,
                'age_to' => 12,
                'attributes' => ['Dạng bột', 'Sữa số 2'],
            ],
            'Sữa ColosBaby Gold 0+ 800g' => [
                'brand' => 'ColosBaby',
                'age_from' => 0,
                'age_to' => 12,
                'attributes' => ['Dạng bột', 'Dòng 0+'],
            ],
            'Sữa Morinaga Hagukumi số 1 800g (0-6 tháng)' => [
                'brand' => 'Morinaga',
                'age_from' => 0,
                'age_to' => 6,
                'attributes' => ['Dạng bột', 'Sữa số 1'],
            ],
            'Sữa Morinaga Chilmil số 2 800g (6-36 tháng)' => [
                'brand' => 'Morinaga',
                'age_from' => 6,
                'age_to' => 36,
                'attributes' => ['Dạng bột', 'Sữa số 2'],
            ],
        ];

        /*
         * Tạo / cập nhật các brand tag cần cho nhóm sữa.
         */
        $brandTags = collect();

        foreach (
            collect($milkFilterMap)
                ->pluck('brand')
                ->unique()
                ->values()
            as $brandName
        ) {
            $slug = Str::slug($brandName);

            $tag = Tag::withTrashed()
                ->where('slug', $slug)
                ->first();

            if (!$tag) {
                $tag = Tag::create([
                    'name' => $brandName,
                    'slug' => $slug,
                    'type' => 'brand',
                ]);
            } else {
                if ($tag->trashed()) {
                    $tag->restore();
                }

                $tag->update([
                    'name' => $brandName,
                    'type' => 'brand',
                ]);
            }

            $brandTags->put($brandName, $tag);
        }

        /*
         * Tạo / cập nhật các Attribute Tag dùng cho nhóm sữa.
         *
         * Chỉ dùng các thuộc tính nhìn thấy trực tiếp từ dữ liệu sản phẩm,
         * tránh tự gắn Organic / Không đường / Không chất bảo quản khi
         * chưa có dữ liệu xác minh cho từng SKU.
         */
        $attributeNames = collect($milkFilterMap)
            ->pluck('attributes')
            ->flatten()
            ->filter()
            ->unique()
            ->values();

        $attributeTags = collect();

        foreach ($attributeNames as $attributeName) {
            $slug = Str::slug($attributeName);

            $tag = Tag::withTrashed()
                ->where('slug', $slug)
                ->first();

            if (!$tag) {
                $tag = Tag::create([
                    'name' => $attributeName,
                    'slug' => $slug,
                    'type' => 'attribute',
                ]);
            } else {
                if ($tag->trashed()) {
                    $tag->restore();
                }

                $tag->update([
                    'name' => $attributeName,
                    'type' => 'attribute',
                ]);
            }

            $attributeTags->put($attributeName, $tag);
        }


        /*
         * Bật Stage "Bé 6-12 tháng" nếu đang bị tắt.
         */
        Stage::query()
            ->where('age_from', 6)
            ->where('age_to', 12)
            ->where(function ($query) {
                $query
                    ->where('name', 'like', '%Bé%')
                    ->orWhere('name', 'like', '%bé%');
            })
            ->update([
                'is_active' => true,
            ]);

        /*
         * Chỉ lấy Stage dành cho trẻ, bỏ các Stage thai kỳ.
         */
        $babyStages = Stage::query()
            ->where(function ($query) {
                $query
                    ->where('name', 'like', '%Bé%')
                    ->orWhere('name', 'like', '%bé%')
                    ->orWhere('name', 'like', '%Sơ sinh%')
                    ->orWhere('name', 'like', '%sơ sinh%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();


        foreach ($products as $data) {

            $category = Category::where('name', $data['category'])
                ->first();

            if (!$category) {
                $this->command?->warn(
                    "Không tìm thấy danh mục: {$data['category']} - bỏ qua {$data['name']}"
                );

                continue;
            }

            $slug = Str::slug($data['name']);

            /*
            |--------------------------------------------------------------------------
            | Kiểm tra sản phẩm đã tồn tại chưa
            |--------------------------------------------------------------------------
            */

            $product = Product::withTrashed()
                ->where('slug', $slug)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | SẢN PHẨM ĐÃ TỒN TẠI
            |--------------------------------------------------------------------------
            |
            | Chỉ cập nhật dữ liệu nghiệp vụ.
            | KHÔNG đụng image / images để không mất URL Cloudinary.
            |
            */

            if ($product) {

                if ($product->trashed()) {
                    $product->restore();
                }

                $product->update([
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'old_price' => $data['old_price'],
                    'discount_percent' => $data['discount_percent'],
                    'stock' => $data['stock'],
                    'is_active' => true,
                ]);

                $this->command?->line(
                    "Đã cập nhật: {$data['name']}"
                );

            } else {

                /*
                |--------------------------------------------------------------------------
                | SẢN PHẨM MỚI
                |--------------------------------------------------------------------------
                */

                $product = Product::create([
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'],

                    /*
                     * Sản phẩm mới chưa có ảnh.
                     * Upload qua Admin để đưa lên Cloudinary.
                     */
                    'image' => null,
                    'images' => [],

                    'price' => $data['price'],
                    'old_price' => $data['old_price'],
                    'discount_percent' => $data['discount_percent'],
                    'stock' => $data['stock'],
                    'is_active' => true,
                ]);

                $this->command?->info(
                    "Đã thêm mới: {$data['name']}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | GẮN BRAND + STAGE CHO SẢN PHẨM SỮA
            |--------------------------------------------------------------------------
            */

            if (
                $data['category'] === 'Sữa cho bé'
                && isset($milkFilterMap[$data['name']])
            ) {
                $filterData =
                    $milkFilterMap[$data['name']];

                $this->syncMilkFilters(
                    $product,
                    $filterData,
                    $brandTags,
                    $attributeTags,
                    $babyStages
                );
            }
        }

        $this->command?->info(
            'Đã đồng bộ Brand + Attribute + Stage cho 12 sản phẩm Sữa cho bé.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Đồng bộ bộ lọc cho một sản phẩm sữa
    |--------------------------------------------------------------------------
    */

    private function syncMilkFilters(
        Product $product,
        array $filterData,
        $brandTags,
        $attributeTags,
        $babyStages
    ): void {
        /*
        |--------------------------------------------------------------------------
        | BRAND
        |--------------------------------------------------------------------------
        |
        | Chỉ thay các tag loại brand.
        | Giữ nguyên attribute tag mà Admin đã gắn thủ công.
        |
        */

        $allBrandTagIds = Tag::query()
            ->where('type', 'brand')
            ->pluck('id')
            ->all();

        if (!empty($allBrandTagIds)) {
            $product->tags()->detach(
                $allBrandTagIds
            );
        }

        $brandName = $filterData['brand'];

        if ($brandTags->has($brandName)) {
            $product->tags()->syncWithoutDetaching([
                $brandTags->get($brandName)->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ATTRIBUTE
        |--------------------------------------------------------------------------
        |
        | Chỉ thay các tag attribute được quản lý bởi Seeder này.
        | Các attribute khác mà bạn gắn thủ công trong Admin vẫn được giữ.
        |
        */

        $managedAttributeIds = $attributeTags
            ->pluck('id')
            ->all();

        if (!empty($managedAttributeIds)) {
            $product->tags()->detach(
                $managedAttributeIds
            );
        }

        foreach (
            $filterData['attributes'] ?? []
            as $attributeName
        ) {
            if ($attributeTags->has($attributeName)) {
                $product->tags()->syncWithoutDetaching([
                    $attributeTags
                        ->get($attributeName)
                        ->id,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | STAGE
        |--------------------------------------------------------------------------
        |
        | Chọn mọi Stage trẻ em có khoảng tuổi giao với tuổi sản phẩm.
        |
        | Ví dụ:
        | 0-6   => 0-3, 3-6
        | 6-12  => 6-12
        | 0-12  => 0-3, 3-6, 6-12
        | 12-36 => 12-24, 24-36
        | 6-36  => 6-12, 12-24, 24-36
        |
        */

        $ageFrom = (int) $filterData['age_from'];
        $ageTo = (int) $filterData['age_to'];

        $stageIds = $babyStages
            ->filter(function ($stage) use (
                $ageFrom,
                $ageTo
            ) {
                if (
                    is_null($stage->age_from)
                    || is_null($stage->age_to)
                ) {
                    return false;
                }

                return
                    $stage->age_from < $ageTo
                    && $stage->age_to > $ageFrom;
            })
            ->pluck('id')
            ->values()
            ->all();

        $product->stages()->sync(
            $stageIds
        );
    }
}