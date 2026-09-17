<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Command;

class EnrichBottleProducts extends Command
{
    protected $signature = 'products:enrich-bottles
                            {--dry-run : Chỉ kiểm tra, không ghi database}';

    protected $description = 'Bổ sung dữ liệu cho nhóm Bình sữa & phụ kiện';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $profiles = $this->profiles();

        $updated = 0;
        $missing = 0;

        $this->newLine();

        $this->info(
            $dryRun
                ? 'DRY RUN - Không có dữ liệu nào bị thay đổi.'
                : 'Đang enrich dữ liệu Bình sữa & phụ kiện...'
        );

        $this->newLine();

        foreach ($profiles as $slug => $profile) {
            $product = Product::query()
                ->where('slug', $slug)
                ->whereHas(
                    'category',
                    fn ($query) => $query->where('slug', 'binh-sua-phu-kien')
                )
                ->first();

            if (!$product) {
                $this->warn("SKIP  {$slug} - không tìm thấy đúng product/category.");
                $missing++;
                continue;
            }

            if ($dryRun) {
                $this->line("CHECK #{$product->id} {$product->name}");
                continue;
            }

            /*
             * Chỉ update field có dữ liệu trong profile.
             * Field chưa xác minh được sẽ không bị ghi đè bằng dữ liệu đoán.
             */
            $fields = array_filter(
                [
                    'description' => $profile['description'] ?? null,
                    'origin' => $profile['origin'] ?? null,
                    'manufacturer' => $profile['manufacturer'] ?? null,
                    'ingredients' => $this->lines($profile['structure'] ?? []),
                    'usage_instructions' => $this->lines($profile['usage'] ?? []),
                    'storage_instructions' => $this->lines($profile['storage'] ?? []),
                    'warning' => $this->lines($profile['warnings'] ?? []),
                    'highlights' => $profile['highlights'] ?? null,
                ],
                static fn ($value) => $value !== null && $value !== ''
            );

            $product->update($fields);

            $tagIds = [];

            foreach ($profile['attributes'] ?? [] as $attribute) {
                $tag = $this->attributeTag(
                    $attribute['slug'],
                    $attribute['name']
                );

                $tagIds[] = $tag->id;
            }

            if ($tagIds !== []) {
                /*
                 * Không làm mất brand/tag hiện có.
                 * Chạy lại command không tạo pivot trùng.
                 */
                $product->tags()->syncWithoutDetaching($tagIds);
            }

            $updated++;

            $this->info("OK    #{$product->id} {$product->name}");
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry-run hoàn tất.');
            $this->line('Chạy thật bằng: php artisan products:enrich-bottles');

            return self::SUCCESS;
        }

        $this->info("Hoàn tất: {$updated} sản phẩm được cập nhật.");

        if ($missing > 0) {
            $this->warn("Có {$missing} sản phẩm trong cấu hình không tìm thấy.");
        }

        $this->newLine();

        $this->line(
            'Các trường không đủ thông tin xác minh được chủ động để trống '
            . 'thay vì tự đoán.'
        );

        return self::SUCCESS;
    }

    private function attributeTag(string $slug, string $name): Tag
    {
        $tag = Tag::withTrashed()
            ->where('slug', $slug)
            ->first();

        if (!$tag) {
            $tag = new Tag();
            $tag->slug = $slug;
        }

        $tag->name = $name;
        $tag->type = 'attribute';
        $tag->save();

        if (method_exists($tag, 'trashed') && $tag->trashed()) {
            $tag->restore();
        }

        return $tag;
    }

    private function lines(array $items): ?string
    {
        $items = array_values(
            array_filter(
                array_map(
                    static fn ($item) => trim((string) $item),
                    $items
                )
            )
        );

        return $items === []
            ? null
            : implode(PHP_EOL, $items);
    }

    private function profiles(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | #28 - PIGEON PPSU SOFTOUCH 240ML
            |--------------------------------------------------------------------------
            */
            'binh-sua-pigeon-ppsu-softouch-240ml' => [
                'description' =>
                    'Bình sữa Pigeon PPSU SoftTouch dung tích 240ml sử dụng thân bình '
                    . 'PPSU và núm ti silicone SoftTouch. Thiết kế cổ rộng hỗ trợ '
                    . 'pha sữa, vệ sinh và lắp ráp thuận tiện.',

                'manufacturer' => 'Pigeon Corporation',

                'attributes' => [
                    ['slug' => 'binh-sua', 'name' => 'Bình sữa'],
                    ['slug' => '240ml', 'name' => '240ml'],
                    ['slug' => 'ppsu', 'name' => 'PPSU'],
                    ['slug' => 'num-ti-softtouch-m', 'name' => 'Núm ti SoftTouch M'],
                    ['slug' => 'tu-3-thang', 'name' => 'Từ 3 tháng'],
                ],

                'structure' => [
                    'Thân bình PPSU|Vật liệu PPSU nhẹ, bền và chịu nhiệt tốt.',
                    'Núm ti SoftTouch|Núm ti silicone mềm và linh hoạt.',
                    'Hệ thống thông khí|Hỗ trợ dòng sữa ổn định trong quá trình bú.',
                    'Thiết kế cổ rộng|Thuận tiện khi pha sữa, tháo lắp và vệ sinh.',
                ],

                /*
                 * 4 dòng đầu = Hướng dẫn sử dụng.
                 * Các dòng sau = Vệ sinh & tiệt trùng trong bottle.blade.php.
                 */
                'usage' => [
                    'Rửa sạch tay, bình, núm ti và các bộ phận trước khi sử dụng.',
                    'Lắp núm ti, vòng cổ và thân bình đúng vị trí.',
                    'Cho sữa vào bình ở nhiệt độ phù hợp và đóng kín.',
                    'Kiểm tra nhiệt độ và dòng chảy trước khi cho bé bú.',
                    'Tháo rời các bộ phận và rửa sạch sau mỗi lần sử dụng.',
                    'Có thể tiệt trùng bằng phương pháp phù hợp với hướng dẫn của sản phẩm.',
                ],

                'storage' => $this->defaultBottleStorage(),

                'warnings' => $this->defaultBottleWarnings(),

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => 'PPSU bền nhẹ', 'subtitle' => 'Thân bình PPSU', 'icon' => 'shield'],
                        ['title' => 'Núm ti mềm', 'subtitle' => 'SoftTouch silicone', 'icon' => 'nipple'],
                        ['title' => 'Thông khí', 'subtitle' => 'Hỗ trợ dòng sữa ổn định', 'icon' => 'air'],
                        ['title' => 'Cổ rộng', 'subtitle' => 'Dễ pha và vệ sinh', 'icon' => 'wash'],
                    ],
                    'Pigeon SoftTouch – thiết kế hỗ trợ bú tự nhiên',
                    'Thông tin chính được tổng hợp theo dòng Pigeon SoftTouch.'
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | #29 - PIGEON PPSU SOFTOUCH 160ML
            |--------------------------------------------------------------------------
            */
            'binh-sua-pigeon-ppsu-softouch-160ml' => [
                'description' =>
                    'Bình sữa Pigeon PPSU SoftTouch dung tích 160ml có thân bình PPSU '
                    . 'và núm ti silicone SoftTouch, phù hợp cho giai đoạn sơ sinh. '
                    . 'Thiết kế cổ rộng giúp thao tác pha sữa và vệ sinh thuận tiện.',

                'manufacturer' => 'Pigeon Corporation',

                'attributes' => [
                    ['slug' => 'binh-sua', 'name' => 'Bình sữa'],
                    ['slug' => '160ml', 'name' => '160ml'],
                    ['slug' => 'ppsu', 'name' => 'PPSU'],
                    ['slug' => 'num-ti-softtouch-ss', 'name' => 'Núm ti SoftTouch SS'],
                    ['slug' => 'tu-0-thang', 'name' => 'Từ 0 tháng'],
                ],

                'structure' => [
                    'Thân bình PPSU|Vật liệu PPSU nhẹ, bền và chịu nhiệt tốt.',
                    'Núm ti SoftTouch|Núm ti silicone mềm, phù hợp với bé nhỏ.',
                    'Hệ thống thông khí|Hỗ trợ quá trình bú ổn định.',
                    'Thiết kế cổ rộng|Thuận tiện cho pha sữa và làm sạch.',
                ],

                'usage' => [
                    'Rửa sạch tay, bình, núm ti và các bộ phận trước khi sử dụng.',
                    'Lắp núm ti và vòng cổ chắc chắn vào thân bình.',
                    'Cho lượng sữa phù hợp vào bình và đóng kín.',
                    'Kiểm tra nhiệt độ và dòng chảy trước khi cho bé bú.',
                    'Tháo rời toàn bộ các bộ phận sau khi sử dụng.',
                    'Rửa sạch và tiệt trùng theo phương pháp phù hợp.',
                ],

                'storage' => $this->defaultBottleStorage(),
                'warnings' => $this->defaultBottleWarnings(),

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => '160ml', 'subtitle' => 'Dung tích gọn nhẹ', 'icon' => 'bottle'],
                        ['title' => 'PPSU', 'subtitle' => 'Bền và chịu nhiệt', 'icon' => 'shield'],
                        ['title' => 'SoftTouch', 'subtitle' => 'Núm ti silicone mềm', 'icon' => 'nipple'],
                        ['title' => 'Dễ vệ sinh', 'subtitle' => 'Thiết kế cổ rộng', 'icon' => 'wash'],
                    ],
                    'Pigeon SoftTouch – dành cho giai đoạn đầu của bé',
                    'Thông tin chính được tổng hợp theo dòng Pigeon SoftTouch.'
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | #30 - PHILIPS AVENT NATURAL RESPONSE 260ML
            |--------------------------------------------------------------------------
            */
            'binh-sua-philips-avent-natural-response-260ml' => [
                'description' =>
                    'Bình sữa Philips Avent Natural Response 260ml sử dụng thân bình '
                    . 'polypropylene không BPA và núm ti silicone mềm. Núm Natural Response '
                    . 'chỉ nhả sữa khi bé chủ động bú và có van chống đầy hơi tích hợp.',

                'origin' => 'Indonesia',
                'manufacturer' => 'Philips Avent',

                'attributes' => [
                    ['slug' => 'binh-sua', 'name' => 'Bình sữa'],
                    ['slug' => '260ml', 'name' => '260ml'],
                    ['slug' => 'nhua-pp-polypropylene', 'name' => 'Nhựa PP (Polypropylene)'],
                    ['slug' => 'num-ti-natural-response', 'name' => 'Núm ti Natural Response'],
                    ['slug' => '3-6-thang', 'name' => '3 - 6 tháng'],
                ],

                'structure' => [
                    'Thân bình PP|Polypropylene không BPA, nhẹ và dễ cầm.',
                    'Núm Natural Response|Núm silicone mềm, sữa chảy khi bé chủ động bú.',
                    'Van chống đầy hơi|Thiết kế giúp hạn chế không khí đi vào bụng bé.',
                    'Cổ bình rộng|Hỗ trợ pha sữa, lắp ráp và vệ sinh dễ dàng.',
                ],

                'usage' => [
                    'Rửa sạch và tiệt trùng bình cùng núm ti trước khi dùng.',
                    'Lắp núm ti Natural Response và vòng cổ vào bình.',
                    'Cho sữa vào bình ở nhiệt độ phù hợp.',
                    'Kiểm tra nhiệt độ, sau đó cho bé bú theo nhịp tự nhiên.',
                    'Tháo rời các bộ phận và rửa sạch sau mỗi lần dùng.',
                    'Có thể vệ sinh bằng máy rửa chén nếu thực hiện đúng hướng dẫn của nhà sản xuất.',
                ],

                'storage' => $this->defaultBottleStorage(),

                'warnings' => [
                    'Luôn kiểm tra nhiệt độ thức ăn trước khi cho bé bú.',
                    'Kiểm tra núm ti trước mỗi lần sử dụng và thay khi có dấu hiệu hư hỏng.',
                    'Không để trẻ sử dụng sản phẩm khi không có sự giám sát phù hợp.',
                ],

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => 'Natural Response', 'subtitle' => 'Sữa chảy khi bé chủ động bú', 'icon' => 'nipple'],
                        ['title' => 'Chống đầy hơi', 'subtitle' => 'Van anti-colic tích hợp', 'icon' => 'air'],
                        ['title' => 'Không BPA', 'subtitle' => 'Thân PP và núm silicone', 'icon' => 'shield'],
                        ['title' => 'Dễ vệ sinh', 'subtitle' => 'Cổ rộng, ít bộ phận', 'icon' => 'wash'],
                    ],
                    'Philips Avent Natural Response – theo nhịp bú tự nhiên của bé',
                    'Thông tin dựa trên thông số của dòng Natural Response 260ml.'
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | #31 - COMOTOMO SILICONE 250ML
            |--------------------------------------------------------------------------
            */
            'binh-sua-comotomo-silicon-250ml' => [
                'description' =>
                    'Bình sữa Comotomo Silicone 250ml có thân bình silicone mềm, '
                    . 'núm ti silicone dáng rộng và hai van chống đầy hơi tích hợp. '
                    . 'Miệng bình rộng giúp việc tháo lắp và vệ sinh thuận tiện.',

                'origin' => 'Hàn Quốc',
                'manufacturer' => 'Comotomo',

                'attributes' => [
                    ['slug' => 'binh-sua', 'name' => 'Bình sữa'],
                    ['slug' => '250ml', 'name' => '250ml'],
                    ['slug' => 'silicone', 'name' => 'Silicone'],
                    ['slug' => 'num-ti-medium-flow', 'name' => 'Núm ti Medium Flow'],
                    ['slug' => '3-6-thang', 'name' => '3 - 6 tháng'],
                ],

                'structure' => [
                    'Thân bình silicone|Silicone dùng cho tiếp xúc thực phẩm, mềm và dễ cầm.',
                    'Núm ti silicone|Thiết kế rộng, mềm và linh hoạt.',
                    'Hai van chống đầy hơi|Hỗ trợ dòng sữa ổn định và giảm lượng khí nuốt vào.',
                    'Miệng bình rộng|Có thể vệ sinh bên trong thuận tiện.',
                ],

                'usage' => [
                    'Rửa sạch bình, núm ti, vòng cổ và nắp trước khi sử dụng.',
                    'Lắp núm ti và vòng cổ đúng khớp với thân bình.',
                    'Cho sữa vào bình và đóng kín trước khi sử dụng.',
                    'Kiểm tra nhiệt độ và dòng chảy trước khi cho bé bú.',
                    'Tháo rời toàn bộ bộ phận và vệ sinh sau mỗi lần sử dụng.',
                    'Có thể tiệt trùng bằng nước sôi hoặc thiết bị tiệt trùng theo hướng dẫn của Comotomo.',
                ],

                'storage' => $this->defaultBottleStorage(),

                'warnings' => [
                    'Kiểm tra núm ti và thân bình trước mỗi lần sử dụng.',
                    'Ngừng sử dụng bộ phận có dấu hiệu rách, nứt hoặc hư hỏng.',
                    'Luôn kiểm tra nhiệt độ sữa trước khi cho bé bú.',
                ],

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => 'Silicone mềm', 'subtitle' => 'Thân bình mềm, dễ cầm', 'icon' => 'heart'],
                        ['title' => 'Medium Flow', 'subtitle' => 'Núm ti cho giai đoạn 3 - 6 tháng', 'icon' => 'nipple'],
                        ['title' => 'Chống đầy hơi', 'subtitle' => 'Hai van thông khí', 'icon' => 'air'],
                        ['title' => 'Miệng rộng', 'subtitle' => 'Dễ vệ sinh', 'icon' => 'wash'],
                    ],
                    'Comotomo – bình silicone mềm với thiết kế miệng rộng',
                    'Thông tin được tổng hợp từ thông số chính thức của Comotomo.'
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | #32 - BỘ BÁT THÌA ĂN DẶM SILICONE
            |--------------------------------------------------------------------------
            */
            'bo-bat-thia-an-dam-silicon-cho-be' => [
                'description' =>
                    'Bộ bát thìa ăn dặm silicone cho bé gồm dụng cụ phục vụ bữa ăn '
                    . 'với vật liệu silicone mềm. Thông tin thương hiệu, xuất xứ '
                    . 'và nhà sản xuất sẽ hiển thị khi dữ liệu sản phẩm được bổ sung.',

                'attributes' => [
                    ['slug' => 'bo-bat-thia', 'name' => 'Bộ bát thìa'],
                    ['slug' => 'silicone', 'name' => 'Silicone'],
                    ['slug' => 'dung-cu-an-dam', 'name' => 'Dụng cụ ăn dặm'],
                    ['slug' => 'bat-va-thia', 'name' => 'Bát + thìa'],
                    ['slug' => 'giai-doan-an-dam', 'name' => 'Giai đoạn ăn dặm'],
                ],

                'structure' => [
                    'Bát ăn dặm|Dùng để chứa khẩu phần ăn của bé.',
                    'Thìa ăn dặm|Thiết kế nhỏ gọn phù hợp thao tác cho bé ăn.',
                    'Vật liệu silicone|Chất liệu mềm, thuận tiện khi cầm nắm.',
                    'Thiết kế đồng bộ|Bát và thìa sử dụng cùng một bộ sản phẩm.',
                ],

                'usage' => [
                    'Rửa sạch sản phẩm trước lần sử dụng đầu tiên.',
                    'Cho lượng thức ăn phù hợp vào bát.',
                    'Dùng thìa để cho bé ăn dưới sự giám sát của người lớn.',
                    'Rửa sạch ngay sau khi kết thúc bữa ăn.',
                    'Rửa bằng nước và chất tẩy rửa phù hợp với dụng cụ ăn uống.',
                    'Để sản phẩm khô hoàn toàn trước khi cất giữ.',
                ],

                'storage' => $this->defaultAccessoryStorage(),

                'warnings' => [
                    'Luôn có người lớn giám sát khi bé sử dụng.',
                    'Kiểm tra sản phẩm trước mỗi lần dùng.',
                    'Ngừng sử dụng nếu sản phẩm rách hoặc hư hỏng.',
                ],

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => 'Silicone mềm', 'subtitle' => 'Vật liệu mềm', 'icon' => 'heart'],
                        ['title' => 'Bộ bát thìa', 'subtitle' => 'Dùng cho bữa ăn dặm', 'icon' => 'check'],
                        ['title' => 'Dễ làm sạch', 'subtitle' => 'Thuận tiện vệ sinh', 'icon' => 'wash'],
                        ['title' => 'Gọn nhẹ', 'subtitle' => 'Thuận tiện sử dụng', 'icon' => 'check'],
                    ],
                    'Bộ dụng cụ ăn dặm silicone',
                    'Các thông tin chưa có trong dữ liệu gốc sẽ không được tự suy đoán.'
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | #33 - CỐC TẬP UỐNG CHỐNG ĐỔ 240ML
            |--------------------------------------------------------------------------
            */
            'coc-tap-uong-chong-do-240ml' => [
                'description' =>
                    'Cốc tập uống chống đổ dung tích 240ml được thiết kế để hỗ trợ '
                    . 'giai đoạn bé làm quen với việc uống bằng cốc. '
                    . 'Thông tin vật liệu và thương hiệu sẽ được bổ sung khi có dữ liệu xác minh.',

                'attributes' => [
                    ['slug' => 'coc-tap-uong', 'name' => 'Cốc tập uống'],
                    ['slug' => '240ml', 'name' => '240ml'],
                    ['slug' => 'chong-do', 'name' => 'Chống đổ'],
                    ['slug' => 'giai-doan-tap-uong', 'name' => 'Giai đoạn tập uống'],
                ],

                'structure' => [
                    'Thân cốc 240ml|Dung tích công bố 240ml.',
                    'Thiết kế chống đổ|Hỗ trợ hạn chế tràn khi sử dụng đúng cách.',
                    'Bộ phận uống|Thiết kế phục vụ giai đoạn tập uống.',
                    'Cấu tạo tháo lắp|Hỗ trợ việc vệ sinh sau sử dụng.',
                ],

                'usage' => [
                    'Rửa sạch các bộ phận trước khi sử dụng.',
                    'Lắp các bộ phận đúng vị trí.',
                    'Cho lượng nước phù hợp vào cốc và đóng kín.',
                    'Cho bé tập uống dưới sự giám sát của người lớn.',
                    'Tháo rời và rửa sạch sau mỗi lần sử dụng.',
                    'Để khô hoàn toàn trước khi lắp lại hoặc cất giữ.',
                ],

                'storage' => $this->defaultAccessoryStorage(),

                'warnings' => [
                    'Luôn giám sát bé trong quá trình sử dụng.',
                    'Không dùng nếu sản phẩm có dấu hiệu nứt hoặc hư hỏng.',
                    'Kiểm tra nhiệt độ đồ uống trước khi cho bé sử dụng.',
                ],

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => '240ml', 'subtitle' => 'Dung tích cốc', 'icon' => 'bottle'],
                        ['title' => 'Chống đổ', 'subtitle' => 'Hỗ trợ hạn chế tràn', 'icon' => 'shield'],
                        ['title' => 'Tập uống', 'subtitle' => 'Hỗ trợ bé làm quen với cốc', 'icon' => 'check'],
                        ['title' => 'Dễ vệ sinh', 'subtitle' => 'Có thể tháo lắp', 'icon' => 'wash'],
                    ],
                    'Cốc tập uống chống đổ 240ml',
                    'Thông tin vật liệu và nhà sản xuất đang chờ dữ liệu xác minh.'
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | #34 - MÁY TIỆT TRÙNG BÌNH SỮA UV MINI
            |--------------------------------------------------------------------------
            */
            'may-tiet-trung-binh-sua-uv-mini' => [
                'description' =>
                    'Máy tiệt trùng bình sữa UV mini là thiết bị dùng công nghệ UV '
                    . 'để hỗ trợ xử lý các vật dụng phù hợp với hướng dẫn của sản phẩm. '
                    . 'Thông số công suất, dung tích và nhà sản xuất cần được bổ sung '
                    . 'từ đúng model thiết bị trước khi hiển thị.',

                'attributes' => [
                    ['slug' => 'may-tiet-trung', 'name' => 'Máy tiệt trùng'],
                    ['slug' => 'uv', 'name' => 'UV'],
                    ['slug' => 'mini', 'name' => 'Mini'],
                ],

                'structure' => [
                    'Khoang tiệt trùng|Không gian đặt vật dụng cần xử lý.',
                    'Nguồn UV|Bộ phận phát tia UV theo thiết kế của thiết bị.',
                    'Bảng điều khiển|Dùng để khởi động hoặc chọn chế độ nếu model hỗ trợ.',
                    'Vỏ máy|Bảo vệ các bộ phận bên trong thiết bị.',
                ],

                'usage' => [
                    'Làm sạch vật dụng trước khi đặt vào máy.',
                    'Sắp xếp vật dụng trong khoang theo hướng dẫn của thiết bị.',
                    'Đóng máy và chọn chế độ phù hợp.',
                    'Chờ chu trình hoàn tất trước khi lấy vật dụng ra.',
                    'Giữ khoang máy sạch và khô theo hướng dẫn sử dụng.',
                    'Không tự ý tháo nguồn UV hoặc linh kiện điện để vệ sinh.',
                ],

                'storage' => [
                    'Đặt thiết bị tại nơi khô ráo và bằng phẳng.',
                    'Tránh nước, độ ẩm cao và nguồn nhiệt trực tiếp.',
                    'Ngắt nguồn điện khi không sử dụng trong thời gian dài.',
                ],

                'warnings' => [
                    'Không nhìn trực tiếp vào nguồn UV khi thiết bị đang hoạt động.',
                    'Không sử dụng máy nếu dây điện, nắp hoặc bộ phận an toàn bị hỏng.',
                    'Thực hiện đúng hướng dẫn của model thiết bị thực tế.',
                ],

                'highlights' => $this->bottleHighlights(
                    [
                        ['title' => 'Công nghệ UV', 'subtitle' => 'Nguồn UV tích hợp', 'icon' => 'shield'],
                        ['title' => 'Thiết kế mini', 'subtitle' => 'Kích thước gọn', 'icon' => 'check'],
                        ['title' => 'Khoang xử lý', 'subtitle' => 'Dành cho vật dụng phù hợp', 'icon' => 'bottle'],
                        ['title' => 'Dễ thao tác', 'subtitle' => 'Điều khiển theo chế độ máy', 'icon' => 'check'],
                    ],
                    'Máy tiệt trùng UV mini',
                    'Các thông số kỹ thuật chưa có trong tên sản phẩm được giữ ở trạng thái cập nhật.'
                ),
            ],
        ];
    }

    private function defaultBottleStorage(): array
    {
        return [
            'Bảo quản sản phẩm ở nơi khô ráo, thoáng mát.',
            'Tránh ánh nắng trực tiếp và nguồn nhiệt khi không sử dụng.',
            'Để các bộ phận sạch và khô trước khi lắp hoặc cất giữ.',
        ];
    }

    private function defaultAccessoryStorage(): array
    {
        return [
            'Bảo quản nơi khô ráo, sạch sẽ.',
            'Để sản phẩm khô hoàn toàn sau khi vệ sinh.',
            'Tránh tiếp xúc với nguồn nhiệt hoặc vật sắc nhọn khi không phù hợp.',
        ];
    }

    private function defaultBottleWarnings(): array
    {
        return [
            'Luôn kiểm tra nhiệt độ sữa trước khi cho bé bú.',
            'Kiểm tra bình và núm ti trước mỗi lần sử dụng.',
            'Thay thế bộ phận khi có dấu hiệu nứt, rách hoặc hư hỏng.',
        ];
    }

    private function bottleHighlights(
        array $items,
        string $message,
        string $submessage
    ): array {
        return [
            'items' => $items,
            'message' => $message,
            'submessage' => $submessage,
        ];
    }
    
}
