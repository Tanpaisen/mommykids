<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedNewbornProducts extends Command
{
    protected $signature = 'products:seed-newborn {--dry-run}';

    protected $description = 'Tạo dữ liệu sản phẩm mẫu cho danh mục Đồ sơ sinh';

    public function handle(): int
    {
        $category = Category::query()
            ->where('slug', 'do-so-sinh')
            ->first();

        if (!$category) {
            $this->error('Không tìm thấy danh mục có slug: do-so-sinh');
            return self::FAILURE;
        }

        $products = $this->products();

        $this->info('Danh mục: ' . $category->name . ' (#' . $category->id . ')');
        $this->info('Số sản phẩm chuẩn bị tạo/cập nhật: ' . count($products));

        if ($this->option('dry-run')) {
            foreach ($products as $index => $product) {
                $this->line(sprintf(
                    '%02d. %s | %s | %sđ',
                    $index + 1,
                    $product['name'],
                    $product['manufacturer'],
                    number_format($product['price'], 0, ',', '.')
                ));
            }

            $this->newLine();
            $this->comment('DRY-RUN: chưa ghi dữ liệu vào database.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($category, $products) {
            foreach ($products as $product) {
                $newSlug = Str::slug($product['name']);

                $model = Product::query()
                    ->where('slug', $product['legacy_slug'])
                    ->orWhere('slug', $newSlug)
                    ->first();

                if (!$model) {
                    $model = new Product();
                }

                $model->slug = $newSlug;
                $model->category_id = $category->id;
                $model->name = $product['name'];
                $model->description = $product['description'];
                $model->origin = $product['origin'];
                $model->manufacturer = $product['manufacturer'];
                $model->ingredients = $product['ingredients'];
                $model->usage_instructions = $product['usage_instructions'];
                $model->storage_instructions = $product['storage_instructions'];
                $model->warning = $product['warning'];
                $model->highlights = $product['highlights'];
                $model->price = $product['price'];
                $model->old_price = $product['old_price'];
                $model->discount_percent = $product['discount_percent'];
                $model->stock = $product['stock'];
                $model->is_active = true;
                $model->is_featured = $product['is_featured'];
                $model->save();
            }
        });

        $this->newLine();
        $this->info('Đã tạo/cập nhật ' . count($products) . ' sản phẩm Đồ sơ sinh.');
        $this->comment('Lưu ý: command không ghi đè image/images để bạn có thể bổ sung ảnh thật sau.');

        return self::SUCCESS;
    }

    private function products(): array
    {
        return [
            [
                'legacy_slug' => 'set-do-so-sinh-kidsplaza-otis-nd24h-be',
                'name' => 'Set đồ sơ sinh Otis ND24H (Be)',
                'manufacturer' => 'Otis',
                'origin' => 'Việt Nam',
                'ingredients' => '95% cotton, 5% spandex',
                'price' => 559000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 18,
                'is_featured' => true,
                'description' => 'Set đồ dành cho bé 0-3 tháng, gồm body, bộ quần áo dài tay, bao tay chân và mũ. Chất liệu cotton pha spandex tạo độ mềm mại và co giãn vừa phải, phù hợp để chuẩn bị giỏ đồ sơ sinh hoặc làm quà tặng.',
                'highlights' => "Set 4 nhóm đồ dùng thiết yếu\nPhù hợp bé 0-3 tháng\nChất liệu cotton mềm và thoáng\nGam màu be dễ phối",
                'usage_instructions' => "Giặt sản phẩm trước lần sử dụng đầu tiên.\nƯu tiên giặt nhẹ với nước mát hoặc nước ấm dưới 40°C.\nCài hoặc mở khuy nhẹ tay khi thay đồ cho bé.\nChọn đúng size theo cân nặng và chiều dài cơ thể bé.",
                'storage_instructions' => "Phơi nơi thông thoáng, tránh nắng gắt kéo dài.\nChỉ cất khi sản phẩm đã khô hoàn toàn.\nGấp riêng quần áo sơ sinh trong ngăn sạch, khô.",
                'warning' => "Không dùng chất tẩy mạnh.\nKhông ngâm chung với quần áo dễ ra màu.\nKiểm tra khuy, đường may và độ vừa vặn trước khi mặc cho bé.",
            ],
            [
                'legacy_slug' => 'bodysuit-dai-tay-animo-hola-fw26-vd0726062-kem',
                'name' => 'Bodysuit dài tay Animo Hola FW26 VD0726062 (Kem)',
                'manufacturer' => 'Animo',
                'origin' => 'Việt Nam',
                'ingredients' => '100% cotton',
                'price' => 197100,
                'old_price' => 219000,
                'discount_percent' => 10,
                'stock' => 24,
                'is_featured' => true,
                'description' => 'Bodysuit liền thân dài tay dành cho bé trong giai đoạn 0-12 tháng. Thiết kế gọn gàng giúp phần bụng và lưng được che phủ tốt khi bé vận động, đồng thời chất cotton phù hợp cho nhu cầu mặc hằng ngày.',
                'highlights' => "Bodysuit liền thân dài tay\nDải size 0-12 tháng\n100% cotton\nMàu kem trung tính",
                'usage_instructions' => "Giặt trước lần mặc đầu tiên.\nGiặt ở nhiệt độ không quá 30°C.\nLộn trái sản phẩm khi giặt và ủi.\nChọn size vừa người, không mặc quá chật.",
                'storage_instructions' => "Phơi nơi thoáng mát.\nTránh để trang phục ẩm lâu.\nGấp hoặc treo khi sản phẩm đã khô hoàn toàn.",
                'warning' => "Không ngâm chung với đồ dễ ra màu.\nHạn chế chất giặt tẩy mạnh.\nKiểm tra cúc bấm trước khi mặc cho bé.",
            ],
            [
                'legacy_slug' => 'body-so-sinh-dui-kidsplaza-hn21h-vang',
                'name' => 'Body sơ sinh đùi HN21H (Vàng)',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải cotton mềm mại',
                'price' => 199000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 16,
                'is_featured' => false,
                'description' => 'Body đùi dành cho bé nhỏ với thiết kế liền thân, phù hợp cho sinh hoạt hằng ngày và thời tiết ấm. Kiểu dáng gọn giúp ba mẹ thay đồ nhanh hơn và hạn chế áo bị xô lên khi bé vận động.',
                'highlights' => "Thiết kế body đùi\nPhù hợp 0-12 tháng theo size\nMềm và thoáng\nMàu vàng tươi sáng",
                'usage_instructions' => "Giặt trước khi mặc lần đầu.\nMở toàn bộ khuy trước khi mặc hoặc cởi cho bé.\nChọn size theo cân nặng và chiều cao.\nGiặt nhẹ sau mỗi lần sử dụng.",
                'storage_instructions' => "Phơi nơi khô thoáng.\nTránh nắng gắt kéo dài.\nCất riêng cùng quần áo sạch của bé.",
                'warning' => "Không dùng chất tẩy mạnh.\nKhông chọn size quá chật.\nKiểm tra đường may và khuy trước khi sử dụng.",
            ],
            [
                'legacy_slug' => 'set-bao-tay-chan-kidsplaza-otis-nd25h-hong',
                'name' => 'Set bao tay chân Otis ND25H (Hồng)',
                'manufacturer' => 'Otis',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải cotton pha co giãn',
                'price' => 69000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 36,
                'is_featured' => false,
                'description' => 'Set bao tay và bao chân dành cho bé sơ sinh, hỗ trợ giữ ấm nhẹ và hạn chế móng tay bé cào vào da trong những tuần đầu. Thiết kế gọn, dễ kết hợp cùng quần áo sơ sinh.',
                'highlights' => "Set bao tay và bao chân\nDùng cho bé sơ sinh\nMềm mại, dễ phối đồ\nGam hồng nhẹ nhàng",
                'usage_instructions' => "Giặt sạch trước lần dùng đầu tiên.\nĐeo vừa cổ tay và cổ chân, không siết chặt.\nThay bộ mới khi bị ẩm hoặc bẩn.",
                'storage_instructions' => "Phơi khô hoàn toàn trước khi cất.\nCất theo cặp để tránh thất lạc.\nBảo quản nơi sạch, khô.",
                'warning' => "Không dùng nếu phần bo quá chặt.\nKiểm tra chỉ thừa ở mặt trong.\nKhông để bao tay chân ẩm trên người bé quá lâu.",
            ],
            [
                'legacy_slug' => 'mu-so-sinh-kidsplaza-otis-nd23t-trang-be',
                'name' => 'Mũ sơ sinh Otis ND23T (Trắng be)',
                'manufacturer' => 'Otis',
                'origin' => 'Việt Nam',
                'ingredients' => '95% cotton, 5% spandex',
                'price' => 59000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 32,
                'is_featured' => false,
                'description' => 'Mũ sơ sinh dáng ôm nhẹ, phù hợp cho bé trong giai đoạn đầu đời. Cotton pha spandex giúp mũ mềm, có độ co giãn vừa phải và dễ đội khi đưa bé ra ngoài hoặc khi thời tiết mát.',
                'highlights' => "Dành cho bé sơ sinh\n95% cotton\nCo giãn nhẹ\nMàu trắng be dễ phối",
                'usage_instructions' => "Đội mũ sao cho phần bo nằm thoải mái quanh đầu.\nTháo mũ khi bé ra nhiều mồ hôi.\nGiặt nhẹ sau khi bẩn hoặc ẩm.",
                'storage_instructions' => "Phơi nơi thông thoáng.\nKhông vắt xoắn mạnh.\nCất khi mũ khô hoàn toàn.",
                'warning' => "Không chọn mũ quá chật.\nKhông che kín mặt hoặc đường thở của bé.\nTheo dõi nhiệt độ cơ thể khi đội mũ trong phòng kín.",
            ],
            [
                'legacy_slug' => 'yem-so-sinh-kidsplaza-vuong',
                'name' => 'Yếm sơ sinh cotton vuông',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => '100% cotton',
                'price' => 19000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 50,
                'is_featured' => false,
                'description' => 'Yếm cotton dạng vuông dùng khi cho bé bú hoặc trong sinh hoạt hằng ngày. Bề mặt mềm giúp thấm sữa và nước bọt, góp phần giữ vùng cổ và phần áo phía trước khô ráo hơn.',
                'highlights' => "100% cotton\nDùng từ sơ sinh\nThấm hút tốt\nDễ giặt và nhanh khô",
                'usage_instructions' => "Buộc hoặc cài vừa quanh cổ bé.\nDùng để hứng sữa, nước bọt hoặc khi cho bé ăn.\nThay yếm ngay khi ẩm nhiều.",
                'storage_instructions' => "Giặt sạch sau mỗi lần bẩn.\nPhơi khô hoàn toàn.\nCất trong ngăn đồ sạch của bé.",
                'warning' => "Không buộc quá chặt quanh cổ.\nKhông để dây yếm quấn quanh người bé.\nKhông để bé đeo yếm khi ngủ mà không có người lớn giám sát.",
            ],
            [
                'legacy_slug' => 'khan-sua-4-lop-kidsplaza-ks02',
                'name' => 'Khăn sữa 4 lớp KS02',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải cotton nhiều lớp',
                'price' => 42000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 48,
                'is_featured' => true,
                'description' => 'Khăn sữa 4 lớp dùng để lau miệng, lau sữa, thấm mồ hôi hoặc hỗ trợ vệ sinh nhẹ cho bé. Cấu trúc nhiều lớp tăng khả năng thấm hút trong khi vẫn giữ kích thước gọn để mang theo.',
                'highlights' => "Thiết kế 4 lớp\nMềm và thấm hút\nĐa mục đích\nPhù hợp giỏ đồ sơ sinh",
                'usage_instructions' => "Giặt trước khi dùng lần đầu.\nDùng riêng khăn sạch cho vùng mặt và miệng.\nThay khăn khác khi khăn đã ẩm hoặc bẩn.",
                'storage_instructions' => "Giặt và phơi khô sau khi dùng.\nBảo quản nơi khô thoáng.\nXếp riêng khăn sạch với khăn đã sử dụng.",
                'warning' => "Không dùng khăn còn ẩm lâu ngày.\nKhông dùng chất tẩy quá mạnh.\nThay khăn khi bề mặt đã xơ hoặc xuống cấp.",
            ],
            [
                'legacy_slug' => 'set-5-khan-sua-muslin-2-lop-o-baby-tm26-25x25',
                'name' => 'Set 5 khăn sữa muslin 2 lớp O!Baby TM26 25x25',
                'manufacturer' => 'O!Baby',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải muslin 2 lớp',
                'price' => 69000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 42,
                'is_featured' => false,
                'description' => 'Set gồm 5 khăn muslin kích thước 25x25 cm, phù hợp cho việc lau mặt, lau miệng, lau sữa hoặc mang theo trong túi đồ của bé. Kích thước nhỏ giúp ba mẹ dễ thay khăn sạch nhiều lần trong ngày.',
                'highlights' => "Set 5 khăn\nMuslin 2 lớp\nKích thước 25x25 cm\nDễ mang theo",
                'usage_instructions' => "Giặt sạch trước lần dùng đầu tiên.\nPhân loại khăn theo mục đích sử dụng nếu cần.\nThay khăn mới khi khăn đã ướt hoặc bẩn.",
                'storage_instructions' => "Phơi nơi có không khí lưu thông.\nGấp gọn sau khi khô.\nCất trong túi hoặc ngăn sạch.",
                'warning' => "Không sử dụng khăn có mùi ẩm mốc.\nKhông chà mạnh lên vùng da nhạy cảm.\nNgưng dùng nếu khăn bị xơ cứng rõ rệt.",
            ],
            [
                'legacy_slug' => 'tam-lot-so-sinh-kidsplaza-3-lop-tm21',
                'name' => 'Tấm lót sơ sinh 3 lớp TM21',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => 'Bề mặt lưới 3 lớp, màng đáy PE, bột giấy',
                'price' => 35000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 45,
                'is_featured' => true,
                'description' => 'Tấm lót sơ sinh dùng hỗ trợ giữ bề mặt nằm của bé sạch và khô hơn trong quá trình thay tã hoặc chăm sóc hằng ngày. Thiết kế nhiều lớp giúp hấp thụ chất lỏng và hạn chế thấm xuống phía dưới.',
                'highlights' => "Cấu trúc 3 lớp\nCó màng đáy PE\nDùng khi thay tã\nGọn nhẹ, tiện mang theo",
                'usage_instructions' => "Trải tấm lót trên bề mặt phẳng trước khi đặt bé.\nĐặt mặt thấm hút hướng lên trên.\nThay tấm mới sau khi đã bẩn hoặc thấm nhiều.",
                'storage_instructions' => "Bảo quản nơi khô thoáng.\nGiữ phần còn lại trong bao bì sạch.\nTránh nơi có độ ẩm cao.",
                'warning' => "Không bỏ sản phẩm đã dùng vào bồn cầu.\nKhông tái sử dụng tấm dùng một lần.\nLuôn giám sát bé khi thay tã trên bề mặt cao.",
            ],
            [
                'legacy_slug' => 'goi-xo-so-sinh-kidsplaza',
                'name' => 'Gối xô sơ sinh',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải xô mềm, ruột gối mềm',
                'price' => 58000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 25,
                'is_featured' => false,
                'description' => 'Gối xô kích thước nhỏ dành cho nhu cầu chăm sóc bé sơ sinh. Vỏ xô tạo cảm giác mềm và thoáng; sản phẩm nên được sử dụng theo hướng dẫn chăm sóc giấc ngủ an toàn và trong điều kiện có người lớn giám sát.',
                'highlights' => "Dành cho bé sơ sinh\nVỏ xô mềm\nKích thước nhỏ gọn\nDễ vệ sinh",
                'usage_instructions' => "Đặt gối trên bề mặt phẳng theo đúng mục đích sử dụng.\nGiữ mặt gối sạch và khô.\nVệ sinh định kỳ theo hướng dẫn của sản phẩm.",
                'storage_instructions' => "Phơi thật khô sau khi vệ sinh.\nBảo quản nơi sạch, tránh ẩm.\nKhông cất khi ruột gối còn ẩm.",
                'warning' => "Không dùng gối hoặc vật mềm để che mặt bé.\nKhông để bé ngủ không giám sát cùng nhiều vật mềm xung quanh.\nNgưng dùng nếu gối biến dạng hoặc rách.",
            ],
            [
                'legacy_slug' => 'u-ken-so-sinh-kidsplaza-tm24-be-dam-0-3m',
                'name' => 'Ủ kén sơ sinh TM24 (Be đậm) 0-3M',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải mềm dùng cho đồ sơ sinh',
                'price' => 139000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 21,
                'is_featured' => true,
                'description' => 'Ủ kén dành cho bé 0-3 tháng, hỗ trợ quấn gọn cơ thể trong thời gian nghỉ ngơi hoặc khi ba mẹ bế bé. Thiết kế dạng kén giúp thao tác quấn nhanh hơn so với chăn rời.',
                'highlights' => "Dành cho 0-3 tháng\nThiết kế dạng kén\nMềm và gọn\nThuận tiện khi chăm bé",
                'usage_instructions' => "Đặt bé vào giữa sản phẩm theo hướng dẫn.\nQuấn vừa phải, không siết vùng ngực và hông.\nĐảm bảo phần mặt và đường thở luôn thông thoáng.",
                'storage_instructions' => "Giặt nhẹ và phơi khô hoàn toàn.\nGấp gọn khi không sử dụng.\nBảo quản nơi thoáng sạch.",
                'warning' => "Không quấn quá chặt.\nKhông để lớp vải che mũi hoặc miệng bé.\nNgưng quấn khi bé đã có dấu hiệu tự lật theo khuyến nghị chăm sóc an toàn.",
            ],
            [
                'legacy_slug' => 'chan-u-2-lop-co-mu-theu-kidsplaza-tm21-hong',
                'name' => 'Chăn ủ 2 lớp có mũ thêu TM21 (Hồng)',
                'manufacturer' => 'Đang cập nhật',
                'origin' => 'Việt Nam',
                'ingredients' => 'Vải mềm 2 lớp',
                'price' => 159000,
                'old_price' => null,
                'discount_percent' => 0,
                'stock' => 20,
                'is_featured' => false,
                'description' => 'Chăn ủ hai lớp có mũ, phù hợp khi bế bé sau tắm, khi di chuyển hoặc trong thời tiết mát. Thiết kế liền mũ giúp che phần đầu thuận tiện hơn khi cần giữ ấm nhẹ cho bé.',
                'highlights' => "Thiết kế 2 lớp\nCó mũ liền chăn\nDùng từ giai đoạn sơ sinh\nPhù hợp khi bế và di chuyển",
                'usage_instructions' => "Trải chăn trên bề mặt phẳng trước khi đặt bé.\nQuấn vừa cơ thể và giữ mặt bé thông thoáng.\nĐiều chỉnh độ ấm theo nhiệt độ môi trường.",
                'storage_instructions' => "Giặt nhẹ theo hướng dẫn.\nPhơi khô hoàn toàn trước khi gấp.\nCất ở nơi sạch và khô.",
                'warning' => "Không phủ chăn lên mặt bé.\nKhông quấn quá dày trong môi trường nóng.\nLuôn theo dõi bé khi sử dụng chăn ủ.",
            ],
        ];
    }
}