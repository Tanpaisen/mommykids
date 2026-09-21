<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class EnrichMotherBabyProducts extends Command
{
    protected $signature = 'products:enrich-mother-baby
                            {--dry-run : Chỉ kiểm tra dữ liệu, không ghi vào database}';

    protected $description = 'Bổ sung nội dung chi tiết cho 9 sản phẩm thuộc danh mục Đồ dùng mẹ & bé';

    public function handle(): int
    {
        $products = Product::query()
            ->whereHas('category', fn ($query) => $query->where('slug', 'do-dung-me-be'))
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('Không tìm thấy sản phẩm nào trong category do-dung-me-be.');

            return self::SUCCESS;
        }

        $matched = 0;
        $skipped = 0;

        if ($this->option('dry-run')) {
            $this->info('DRY RUN - Không có dữ liệu nào bị thay đổi.');
            $this->newLine();

            foreach ($products as $product) {
                $data = $this->dataFor($product->slug);

                if ($data) {
                    $matched++;
                    $this->info("MATCH #{$product->id} {$product->name}");
                } else {
                    $skipped++;
                    $this->warn("SKIP  #{$product->id} {$product->name} | slug: {$product->slug}");
                }
            }

            $this->newLine();
            $this->line("Tổng sản phẩm category: {$products->count()}");
            $this->line("Có dữ liệu enrich: {$matched}");
            $this->line("Chưa có dữ liệu enrich: {$skipped}");

            return self::SUCCESS;
        }

        foreach ($products as $product) {
            $data = $this->dataFor($product->slug);

            if (!$data) {
                $skipped++;
                $this->warn("SKIP #{$product->id} {$product->name}");
                continue;
            }

            $product->fill([
                'description' => $data['description'] ?? $product->description,
                'origin' => $data['origin'] ?? $product->origin,
                'manufacturer' => $data['manufacturer'] ?? $product->manufacturer,
                'ingredients' => $data['ingredients'] ?? $product->ingredients,

                'usage_instructions' => isset($data['usage'])
                    ? implode("\n", $data['usage'])
                    : $product->usage_instructions,

                'storage_instructions' => isset($data['storage'])
                    ? implode("\n", $data['storage'])
                    : $product->storage_instructions,

                'warning' => isset($data['warning'])
                    ? implode("\n", $data['warning'])
                    : $product->warning,
            ]);

            $product->save();

            $matched++;
            $this->info("OK #{$product->id} {$product->name}");
        }

        $this->newLine();
        $this->info('Enrich Đồ dùng mẹ & bé hoàn tất.');
        $this->line("Đã cập nhật: {$matched}");
        $this->line("Bỏ qua: {$skipped}");

        return self::SUCCESS;
    }

    private function dataFor(string $slug): ?array
    {
        return match ($slug) {

            /*
             * =========================================================
             * 1. ĐỊU NGỒI MAMAGO AIRY MM01 6IN1
             * =========================================================
             */
            'diu-ngoi-cho-be-6in1-mamago-airy-mm01-xanh-navy' => [
                'description' => 'Địu ngồi Mamago Airy MM01 là sản phẩm hỗ trợ ba mẹ bế và di chuyển cùng bé trong nhiều tình huống hằng ngày. Thiết kế kết hợp phần địu phía trên và bệ ngồi phía dưới giúp phân bổ lực tốt hơn khi sử dụng. Các dây đeo, đai eo và khóa cài có thể điều chỉnh để phù hợp hơn với vóc dáng người đeo. Sản phẩm phù hợp cho các hoạt động như đi dạo, đi mua sắm hoặc di chuyển quãng ngắn cùng bé. Ba mẹ nên lựa chọn tư thế sử dụng phù hợp với độ tuổi, khả năng giữ đầu - cổ của bé và hướng dẫn đi kèm sản phẩm.',

                'usage' => [
                    'Kiểm tra toàn bộ dây vai, đai eo, khóa cài, đường may và phần bệ ngồi trước khi đặt bé vào địu. Không tiếp tục sử dụng nếu phát hiện chi tiết bị lỏng, nứt hoặc hư hỏng.',
                    'Đeo đai eo và điều chỉnh dây vai sao cho sản phẩm ôm chắc cơ thể người lớn nhưng không gây khó chịu. Phần bệ ngồi cần nằm cân bằng trước khi đặt bé.',
                    'Đặt bé vào đúng tư thế phù hợp với độ tuổi và khả năng vận động. Luôn bảo đảm mặt, mũi và đường thở của bé thông thoáng trong suốt quá trình sử dụng.',
                    'Sau khi cài đủ khóa, kiểm tra lại độ chắc chắn của dây và tư thế của bé. Khi di chuyển nên giữ một tay hỗ trợ bé trong các tình huống lên xuống cầu thang, cúi người hoặc thay đổi tư thế.',
                ],

                'storage' => [
                    'Vệ sinh các khu vực tiếp xúc nhiều với cơ thể bé và người đeo sau khi sử dụng.',
                    'Nếu giặt sản phẩm, cần làm khô hoàn toàn phần vải, dây và đệm trước khi cất giữ.',
                    'Bảo quản tại nơi khô ráo, thoáng mát, tránh ánh nắng trực tiếp và nguồn nhiệt cao.',
                    'Không gấp ép phần đệm hoặc bệ ngồi trong thời gian dài để hạn chế biến dạng.',
                ],

                'warning' => [
                    'Luôn kiểm tra khóa cài và dây đeo trước mỗi lần sử dụng; ngưng dùng ngay nếu có dấu hiệu hư hỏng.',
                    'Không để vải hoặc phần thân địu che kín mặt, mũi hay cản trở đường thở của bé.',
                    'Không sử dụng địu khi thực hiện hoạt động có nguy cơ té ngã, vận động mạnh hoặc cần thao tác với vật nóng.',
                    'Tư thế địu cần phù hợp với từng giai đoạn phát triển; ưu tiên hướng dẫn chính thức đi kèm sản phẩm.',
                ],
            ],

            /*
             * =========================================================
             * 2. ĐỊU BABY LAB AC 4 TƯ THẾ
             * =========================================================
             */
            'diu-ngoi-cho-be-4-tu-the-baby-lab-ac' => [
                'description' => 'Địu Baby Lab AC được thiết kế để hỗ trợ nhiều cách bế bé trong quá trình chăm sóc và di chuyển hằng ngày. Hệ thống dây đeo và khóa điều chỉnh giúp ba mẹ căn chỉnh độ ôm theo vóc dáng người sử dụng. Việc thay đổi tư thế cần dựa trên độ tuổi, khả năng giữ đầu - cổ và mức độ thoải mái của bé. Sản phẩm phù hợp khi ba mẹ cần rảnh tay hơn trong lúc đi dạo, mua sắm hoặc chăm sóc bé ở bên ngoài.',

                'usage' => [
                    'Trước khi dùng, kiểm tra dây đeo, khóa cài, đường may và các vị trí chịu lực để bảo đảm không có dấu hiệu bất thường.',
                    'Đeo sản phẩm lên người lớn trước, sau đó điều chỉnh độ dài dây để phần địu nằm đúng vị trí và không bị lệch.',
                    'Đặt bé vào tư thế phù hợp, giữ chắc bé bằng tay cho tới khi tất cả khóa và dây đã được cố định hoàn chỉnh.',
                    'Kiểm tra lại phần đầu, cổ, lưng và chân của bé. Trong khi di chuyển, thường xuyên quan sát để điều chỉnh nếu bé có biểu hiện khó chịu.',
                ],

                'storage' => [
                    'Lau hoặc giặt sạch các vùng dễ bám mồ hôi và bụi bẩn theo hướng dẫn của sản phẩm.',
                    'Để sản phẩm khô hoàn toàn trước khi gấp và cất.',
                    'Bảo quản ở nơi thoáng mát, tránh nơi ẩm hoặc có mùi mạnh.',
                    'Không đặt vật nặng lên dây đai và khóa trong thời gian dài.',
                ],

                'warning' => [
                    'Không dùng khi dây, khóa hoặc đường may có dấu hiệu bung, rách hoặc biến dạng.',
                    'Luôn bảo đảm đường thở của bé thông thoáng và đầu - cổ được hỗ trợ phù hợp.',
                    'Không cúi người đột ngột khi đang địu bé; nếu cần cúi, nên giữ bé bằng tay và hạ thấp đầu gối.',
                    'Không để trẻ ở trong địu mà không có sự giám sát của người lớn.',
                ],
            ],

            /*
             * =========================================================
             * 3. TÚI MOTHER-V 002
             * =========================================================
             */
            'tui-dung-do-me-va-be-mother-v-002' => [
                'description' => 'Túi đựng đồ Mother-V 002 hướng tới nhu cầu mang theo nhiều vật dụng của mẹ và bé trong một không gian gọn gàng. Nhiều ngăn giúp phân chia bỉm, khăn, quần áo, bình sữa, đồ dùng cá nhân và các vật dụng nhỏ để dễ tìm hơn khi cần. Cách bố trí đồ hợp lý giúp hạn chế việc đồ sạch và đồ đã sử dụng bị trộn lẫn. Túi phù hợp cho các chuyến đi ngắn, đi khám, đi chơi hoặc mang theo khi sử dụng xe đẩy.',

                'usage' => [
                    'Phân loại đồ dùng trước khi xếp vào túi: bỉm - khăn, quần áo, đồ ăn/bình sữa và vật dụng cá nhân nên được bố trí riêng.',
                    'Đặt các vật nặng hoặc kích thước lớn ở phần đáy và sát thân túi để túi cân bằng hơn khi mang.',
                    'Đồ có nguy cơ rò rỉ nên đặt trong túi phụ hoặc bao chống tràn trước khi cho vào ngăn chính.',
                    'Đóng đầy đủ khóa kéo và kiểm tra quai xách/quai đeo trước khi di chuyển. Sau mỗi chuyến đi, lấy đồ ẩm hoặc đồ bẩn ra khỏi túi sớm.',
                ],

                'storage' => [
                    'Lấy hết thực phẩm, khăn ướt hoặc đồ còn ẩm ra khỏi túi trước khi cất.',
                    'Lau sạch bên trong và bên ngoài bằng phương pháp phù hợp với chất liệu túi.',
                    'Mở các ngăn cho thoáng và để khô hoàn toàn trước khi đóng khóa.',
                    'Bảo quản nơi khô ráo, tránh ánh nắng mạnh và tránh ép dưới vật nặng trong thời gian dài.',
                ],

                'warning' => [
                    'Không đặt trực tiếp vật sắc nhọn vào túi nếu chưa có bao bảo vệ.',
                    'Không mang tải trọng vượt quá khả năng chịu lực của quai và đường may.',
                    'Không để thực phẩm lỏng, sữa pha sẵn hoặc đồ dễ đổ trong túi quá lâu.',
                    'Kiểm tra quai đeo, móc và khóa kéo định kỳ trước khi mang túi cùng nhiều vật dụng.',
                ],
            ],

            /*
             * =========================================================
             * 4. GHẾ ĂN DẶM MEETBABY HC011
             * =========================================================
             */
            'ghe-ngoi-an-dam-meetbaby-hc011-xanh-vang' => [
                'description' => 'Ghế ngồi ăn dặm Meetbaby HC011 hỗ trợ tạo vị trí ăn riêng và ổn định cho bé trong giai đoạn tập ăn. Ghế giúp ba mẹ dễ tổ chức bữa ăn hơn, đồng thời tạo thói quen ngồi ăn tại một vị trí cố định. Khi sử dụng, ghế cần được đặt trên mặt phẳng chắc chắn, các bộ phận phải được lắp đúng vị trí và bé cần được cố định theo thiết kế của ghế. Việc vệ sinh khay và khu vực ghế sau mỗi bữa ăn giúp hạn chế thức ăn khô bám và mùi khó chịu.',

                'usage' => [
                    'Đặt ghế trên sàn phẳng, kiểm tra chân ghế, các khớp lắp và độ ổn định trước khi cho bé ngồi.',
                    'Đặt bé vào ghế đúng tư thế và sử dụng hệ thống cố định/đai an toàn nếu sản phẩm có trang bị.',
                    'Gắn khay ăn đúng khớp, đặt thức ăn và vật dụng trong tầm với phù hợp; luôn có người lớn ở gần trong suốt bữa ăn.',
                    'Sau khi bé ăn xong, đưa bé ra khỏi ghế trước rồi tháo hoặc lau khay, mặt ghế và các vị trí bám thức ăn.',
                ],

                'storage' => [
                    'Lau sạch khay và mặt ghế ngay sau bữa ăn để hạn chế thức ăn khô bám lâu.',
                    'Để các bộ phận khô hoàn toàn sau khi vệ sinh.',
                    'Nếu ghế có khả năng gấp gọn, khóa đúng cơ cấu trước khi cất giữ.',
                    'Cất ghế tại nơi khô ráo, tránh vị trí trẻ có thể tự kéo hoặc trèo lên.',
                ],

                'warning' => [
                    'Không đặt ghế trên bề mặt dốc, mềm hoặc không ổn định.',
                    'Không để bé đứng trên mặt ghế, trèo lên khay hoặc tự lên xuống khi không có người lớn hỗ trợ.',
                    'Không di chuyển hoặc nhấc ghế khi bé vẫn đang ngồi trên ghế.',
                    'Kiểm tra chốt, khớp nối và các bộ phận cố định định kỳ; ngưng dùng nếu sản phẩm bị nứt hoặc mất ổn định.',
                ],
            ],

            /*
             * =========================================================
             * 5. MÁY XAY FATZBABY FB5001MB 0.3L
             * =========================================================
             */
            'may-xay-thuc-an-dam-fatzbaby-fb5001mb-0-3l' => [
                'description' => 'Máy xay thức ăn dặm FatzBaby FB5001MB có cối dung tích nhỏ, phù hợp với nhu cầu chuẩn bị khẩu phần thức ăn cho bé. Máy hỗ trợ làm nhỏ và xay nhuyễn thực phẩm sau khi đã được sơ chế đúng cách. Cối nhỏ giúp ba mẹ chủ động chuẩn bị lượng vừa đủ cho từng bữa, hạn chế phải xay lượng quá lớn. Khi sử dụng thiết bị điện có lưỡi dao, cần lắp cối và nắp đúng vị trí, không vượt mức thực phẩm cho phép và luôn ngắt nguồn trước khi tháo rửa.',

                'usage' => [
                    'Rửa sạch cối, nắp và các bộ phận tiếp xúc với thực phẩm trước lần dùng đầu tiên; chuẩn bị nguyên liệu đã được sơ chế phù hợp.',
                    'Cắt thực phẩm thành miếng nhỏ, cho lượng vừa phải vào cối và không nén quá chặt để lưỡi dao có khoảng trống hoạt động.',
                    'Lắp cối, lưỡi dao và nắp đúng vị trí rồi vận hành theo từng nhịp phù hợp. Nếu thực phẩm bám thành cối, tắt máy trước khi xử lý.',
                    'Sau khi xay xong, ngắt nguồn điện hoàn toàn rồi mới tháo cối và lưỡi dao. Rửa các bộ phận có thể tháo rời và lau khô thân máy.',
                ],

                'storage' => [
                    'Không để phần thân máy hoặc vị trí điện tiếp xúc trực tiếp với nước.',
                    'Rửa sạch cối, nắp và lưỡi dao ngay sau khi sử dụng để hạn chế thực phẩm khô bám.',
                    'Lau khô hoàn toàn các bộ phận trước khi lắp lại hoặc cất giữ.',
                    'Bảo quản máy ở nơi khô ráo, dây điện được cuộn gọn và ngoài tầm với của trẻ.',
                ],

                'warning' => [
                    'Lưỡi dao sắc; chỉ chạm vào khi thiết bị đã được ngắt nguồn hoàn toàn và thao tác thật cẩn thận.',
                    'Không đưa tay, thìa hoặc vật cứng vào cối khi máy đang hoạt động.',
                    'Không vận hành thiết bị nếu cối, nắp hoặc bộ phận khóa chưa được lắp đúng vị trí.',
                    'Nếu máy nóng bất thường, có mùi khét hoặc tiếng động lạ, cần dừng sử dụng và kiểm tra trước khi tiếp tục.',
                ],
            ],

            /*
             * =========================================================
             * 6. KHAY TRỮ AMORI INOCHI
             * =========================================================
             */
            'khay-tru-do-an-dam-amori-inochi' => [
                'description' => 'Khay trữ đồ ăn dặm Amori Inochi được sử dụng để chia thức ăn thành các khẩu phần nhỏ, giúp ba mẹ chuẩn bị trước đồ ăn cho bé và bảo quản gọn gàng hơn. Các ô riêng biệt thuận tiện khi chia rau củ nghiền, cháo, nước dùng hoặc các phần thức ăn đã sơ chế. Khi bảo quản lạnh hoặc trữ đông, nên để thức ăn nguội phù hợp trước khi cho vào khay và ghi nhớ thời điểm chuẩn bị để quản lý khẩu phần tốt hơn.',

                'origin' => 'Việt Nam',

                'ingredients' => 'Nhựa PP nguyên sinh, hạt màu và phụ gia kháng khuẩn Ag+.',

                'usage' => [
                    'Rửa sạch khay và nắp trước lần sử dụng đầu tiên; để khô trước khi cho thức ăn vào.',
                    'Chia thức ăn đã được chuẩn bị thành từng ô với lượng phù hợp cho một lần dùng, tránh đổ quá đầy sát mép.',
                    'Đậy nắp đúng vị trí trước khi đặt vào ngăn mát hoặc ngăn đông. Có thể ghi ngày chuẩn bị để dễ theo dõi thời gian bảo quản.',
                    'Khi lấy thức ăn ra sử dụng, chỉ lấy lượng cần thiết. Nếu làm nóng bằng lò vi sóng, không đậy kín nắp và cần tuân thủ giới hạn nhiệt của sản phẩm.',
                ],

                'storage' => [
                    'Rửa sạch ngay sau khi dùng, đặc biệt với thực phẩm có dầu hoặc màu đậm.',
                    'Để khay và nắp khô hoàn toàn trước khi xếp chồng hoặc đóng kín.',
                    'Bảo quản nơi sạch, khô; tránh để sát nguồn nhiệt hoặc vật sắc nhọn.',
                    'Khi trữ đông, sắp xếp khay trên mặt phẳng để hạn chế nghiêng đổ trong quá trình đông.',
                ],

                'warning' => [
                    'Không tiếp tục sử dụng nếu khay hoặc nắp bị nứt, cong vênh hoặc biến dạng.',
                    'Không đặt sản phẩm gần lửa hoặc nguồn nhiệt vượt quá giới hạn mà nhà sản xuất cho phép.',
                    'Không đậy kín nắp khi hâm nóng trong lò vi sóng nếu hướng dẫn sản phẩm không cho phép.',
                    'Luôn kiểm tra nhiệt độ thức ăn sau khi hâm và trước khi cho bé ăn.',
                ],
            ],

            /*
             * =========================================================
             * 7. HỘP CHIA SỮA MOYUUM 3 NGĂN
             * =========================================================
             */
            'hop-chia-sua-bot-moyuum-3-ngan-be' => [
                'description' => 'Hộp chia sữa bột Moyuum 3 ngăn giúp ba mẹ chuẩn bị sẵn lượng sữa bột hoặc thực phẩm khô theo từng cữ trước khi ra ngoài. Việc chia thành các ngăn riêng giúp giảm thao tác đong sữa tại nơi công cộng và thuận tiện hơn khi đi chơi, đi khám hoặc đi du lịch. Hộp nên được rửa sạch, làm khô hoàn toàn trước khi cho sữa bột vào vì độ ẩm có thể làm sữa vón cục. Nắp cần được đóng kín trước khi đặt vào túi đồ của bé.',

                'usage' => [
                    'Rửa sạch toàn bộ thân hộp, nắp và các ngăn; để khô hoàn toàn trước khi cho sữa bột hoặc thực phẩm khô vào.',
                    'Đong lượng sữa theo đúng cữ của bé và chia riêng vào từng ngăn. Không trộn thêm nước trong hộp chia sữa.',
                    'Đóng kín từng phần và kiểm tra nắp trước khi cho vào túi mang theo, đặc biệt khi di chuyển nhiều.',
                    'Khi pha sữa, đổ lượng bột từ đúng ngăn vào bình đã chuẩn bị nước theo hướng dẫn của sản phẩm sữa; sau khi dùng cần vệ sinh hộp sớm.',
                ],

                'storage' => [
                    'Luôn để hộp khô hoàn toàn trước khi chứa sữa bột để hạn chế ẩm và vón cục.',
                    'Bảo quản hộp sạch ở nơi khô ráo, tránh ánh nắng trực tiếp và nguồn nhiệt cao.',
                    'Sau khi đi ra ngoài, lấy hết lượng sữa còn dư và vệ sinh hộp thay vì để lâu trong ngăn.',
                    'Cất hộp ở trạng thái khô, nắp mở nhẹ hoặc tách rời nếu cần để tránh giữ hơi ẩm.',
                ],

                'warning' => [
                    'Không dùng hộp ẩm để chứa sữa bột hoặc thức ăn khô.',
                    'Không dùng hộp để bảo quản sữa đã pha nếu nhà sản xuất không có hướng dẫn cho mục đích này.',
                    'Kiểm tra nắp và thân hộp trước mỗi lần sử dụng; ngưng dùng nếu sản phẩm nứt, biến dạng hoặc không còn đóng kín.',
                    'Khi pha sữa cho bé, luôn tuân thủ đúng tỷ lệ nước - bột và hướng dẫn trên sản phẩm sữa.',
                ],
            ],

            /*
             * =========================================================
             * 8. YẾM SILICONE MOYUUM
             * =========================================================
             */
            'yem-an-silicone-moyuum-cao-cap-cam' => [
                'description' => 'Yếm ăn silicone Moyuum hỗ trợ che phần trước quần áo của bé trong bữa ăn và giúp việc vệ sinh sau ăn thuận tiện hơn. Chất liệu silicone mềm cho phép yếm ôm theo cơ thể, trong khi phần phía dưới hỗ trợ hứng một phần thức ăn rơi. Yếm có thể được điều chỉnh tại vùng cổ để phù hợp hơn với bé. Sau mỗi bữa ăn, ba mẹ nên rửa sạch thức ăn bám, đặc biệt tại các nếp gấp và khu vực hứng thức ăn.',

                'ingredients' => 'Silicone.',

                'usage' => [
                    'Rửa sạch và để khô yếm trước khi sử dụng, đặc biệt trước lần dùng đầu tiên.',
                    'Đeo yếm quanh cổ bé và điều chỉnh nút/dây sao cho vừa vặn, không quá chặt và không gây cọ xát.',
                    'Trải phần thân yếm phẳng phía trước, điều chỉnh túi hứng thức ăn mở tự nhiên trong suốt bữa ăn.',
                    'Sau khi ăn, tháo yếm nhẹ nhàng, đổ bỏ thức ăn còn trong túi hứng rồi rửa sạch bằng phương pháp phù hợp.',
                ],

                'storage' => [
                    'Rửa sạch các vết dầu, thức ăn và màu thực phẩm ngay sau khi sử dụng.',
                    'Để yếm khô hoàn toàn trước khi gấp hoặc cất vào túi.',
                    'Bảo quản ở nơi sạch, khô và tránh gần nguồn nhiệt cao.',
                    'Có thể cuộn hoặc gấp nhẹ khi mang theo nhưng tránh ép dưới vật nặng lâu ngày.',
                ],

                'warning' => [
                    'Không để yếm siết chặt vùng cổ; luôn kiểm tra độ vừa vặn trước bữa ăn.',
                    'Không để bé tự chơi hoặc ngủ khi vẫn đang đeo yếm mà không có người lớn giám sát.',
                    'Ngưng sử dụng nếu silicone bị rách, nứt hoặc xuất hiện phần cạnh sắc.',
                    'Không đặt sản phẩm trực tiếp lên bếp, lửa hoặc bề mặt có nhiệt độ quá cao.',
                ],
            ],

            /*
             * =========================================================
             * 9. ĐAI XE MÁY TÂM AN CÓ ĐỠ CỔ
             * =========================================================
             */
            'dai-xe-may-cho-be-tam-an-co-do-co' => [
                'description' => 'Đai xe máy cho bé Tâm An có phần đỡ cổ được thiết kế để hỗ trợ giữ tư thế của bé khi ngồi cùng người lớn trong quá trình di chuyển. Hệ thống dây đai và khóa cài cho phép điều chỉnh độ ôm theo người sử dụng, trong khi phần đỡ phía sau hỗ trợ vùng đầu - cổ theo thiết kế sản phẩm. Đây là sản phẩm hỗ trợ tư thế, vì vậy người lớn vẫn cần chủ động giữ bé, lái xe thận trọng và tuân thủ đầy đủ quy định an toàn giao thông.',

                'usage' => [
                    'Kiểm tra kỹ dây đai, khóa cài, đường may và phần đỡ cổ trước mỗi lần sử dụng; không dùng nếu phát hiện chi tiết bị lỏng hoặc hư hỏng.',
                    'Đeo đai vào người lớn trước và điều chỉnh chiều dài dây sao cho chắc chắn, cân đối, không xoắn dây và không gây cản trở thao tác lái xe.',
                    'Đặt bé đúng vị trí, điều chỉnh phần đỡ cổ và cố định các khóa theo hướng dẫn của sản phẩm. Dây cần đủ chắc nhưng không siết quá chặt cơ thể bé.',
                    'Trước khi xe di chuyển, kiểm tra lại toàn bộ khóa, dây và tư thế của bé. Trong suốt hành trình, người lớn cần chủ động quan sát và tránh các thao tác hoặc tốc độ gây mất ổn định.',
                ],

                'storage' => [
                    'Lau sạch bụi bẩn và mồ hôi sau khi sử dụng; vệ sinh kỹ các vùng thường xuyên tiếp xúc với cơ thể.',
                    'Nếu sản phẩm bị ẩm, cần làm khô hoàn toàn trước khi cất.',
                    'Bảo quản nơi khô ráo, tránh ánh nắng gắt và nguồn nhiệt có thể làm ảnh hưởng đến dây hoặc khóa.',
                    'Khi cất, không để vật nặng đè lâu lên phần đỡ cổ, khóa cài hoặc dây điều chỉnh.',
                ],

                'warning' => [
                    'Sản phẩm chỉ hỗ trợ giữ tư thế và không thay thế việc giám sát, giữ bé hoặc các biện pháp an toàn giao thông phù hợp.',
                    'Không tiếp tục sử dụng nếu dây, khóa, đường may hoặc phần đỡ cổ có dấu hiệu hư hỏng.',
                    'Không để dây đai cản trở người lái, tay lái hoặc các thao tác điều khiển xe.',
                    'Luôn tuân thủ quy định pháp luật giao thông và các hướng dẫn an toàn phù hợp với trẻ em khi di chuyển bằng xe máy.',
                ],
            ],

            default => null,
        };
    }
}
