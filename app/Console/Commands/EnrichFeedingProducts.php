<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Command;

class EnrichFeedingProducts extends Command
{
    protected $signature = 'products:enrich-feeding
                            {--dry-run : Chỉ kiểm tra, không ghi database}';

    protected $description = 'Bổ sung dữ liệu cho nhóm Ăn dặm, dinh dưỡng';

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
                : 'Đang enrich dữ liệu Ăn dặm, dinh dưỡng...'
        );
        $this->newLine();

        foreach ($profiles as $slug => $profile) {
            $product = Product::query()
                ->where('slug', $slug)
                ->whereHas(
                    'category',
                    fn ($query) => $query->where('slug', 'an-dam-dinh-duong')
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

            $fields = array_filter(
                [
                    'description' => $profile['description'] ?? null,
                    'origin' => $profile['origin'] ?? null,
                    'manufacturer' => $profile['manufacturer'] ?? null,
                    'ingredients' => $this->lines($profile['ingredients'] ?? []),
                    'usage_instructions' => $this->lines($profile['usage'] ?? []),
                    'storage_instructions' => $this->lines($profile['storage'] ?? []),
                    'warning' => $this->lines($profile['warnings'] ?? []),
                    'highlights' => $profile['highlights'] ?? null,
                ],
                static fn ($value) => $value !== null && $value !== ''
            );

            $product->update($fields);

            $tagIds = [];

            if (!empty($profile['brand'])) {
                $brand = $this->tag(
                    $profile['brand']['slug'],
                    $profile['brand']['name'],
                    'brand'
                );
                $tagIds[] = $brand->id;
            }

            foreach ($profile['attributes'] ?? [] as $attribute) {
                $tag = $this->tag(
                    $attribute['slug'],
                    $attribute['name'],
                    'attribute'
                );
                $tagIds[] = $tag->id;
            }

            if ($tagIds !== []) {
                $product->tags()->syncWithoutDetaching(array_values(array_unique($tagIds)));
            }

            $updated++;
            $this->info("OK    #{$product->id} {$product->name}");
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry-run hoàn tất.');
            $this->line('Chạy thật bằng: php artisan products:enrich-feeding');

            return self::SUCCESS;
        }

        $this->info("Hoàn tất: {$updated} sản phẩm được cập nhật.");

        if ($missing > 0) {
            $this->warn("Có {$missing} sản phẩm trong cấu hình không tìm thấy.");
        }

        $this->line(
            'Thông số không xác định được đúng biến thể (khối lượng, xuất xứ...) '
            . 'được chủ động để trống thay vì tự đoán.'
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

        return $items === [] ? null : implode(PHP_EOL, $items);
    }

    private function highlights(array $items, string $message, string $submessage): array
    {
        return [
            'items' => $items,
            'message' => $message,
            'submessage' => $submessage,
        ];
    }

    private function cerealStorage(): array
    {
        return [
            'Đậy kín bao bì sau khi mở và bảo quản nơi khô ráo, thoáng mát.',
            'Tránh ánh nắng trực tiếp, nơi có nhiệt độ hoặc độ ẩm cao.',
            'Tuân thủ thời gian sử dụng sau khi mở được ghi trên bao bì.',
        ];
    }

    private function snackStorage(): array
    {
        return [
            'Đóng kín bao bì sau khi mở để hạn chế bánh hút ẩm.',
            'Bảo quản nơi khô ráo, thoáng mát và tránh ánh nắng trực tiếp.',
            'Sử dụng trong thời gian khuyến nghị trên bao bì sau khi mở.',
        ];
    }

    private function cerealWarnings(): array
    {
        return [
            'Kiểm tra thành phần và thông tin dị ứng trên bao bì trước khi sử dụng.',
            'Pha đúng tỷ lệ, nhiệt độ và lượng dùng theo hướng dẫn của đúng sản phẩm.',
            'Không sử dụng nếu bao bì rách, phồng, ẩm hoặc sản phẩm có dấu hiệu bất thường.',
        ];
    }

    private function snackWarnings(): array
    {
        return [
            'Chỉ cho bé dùng khi ngồi thẳng và luôn có người lớn giám sát.',
            'Chọn lượng và kích thước miếng phù hợp khả năng nhai của bé.',
            'Kiểm tra thành phần và cảnh báo dị ứng trên bao bì trước khi sử dụng.',
        ];
    }

    private function profiles(): array
    {
        return [
            'bot-an-dam-hipp-organic-vi-ngu-coc' => [
                'brand' => ['slug' => 'hipp', 'name' => 'HiPP'],
                'description' =>
                    'Bột ăn dặm HiPP Organic vị ngũ cốc thuộc nhóm thực phẩm ăn dặm '
                    . 'từ ngũ cốc, phù hợp để xây dựng bữa ăn mềm và linh hoạt cho bé. '
                    . 'Khi sử dụng cần pha/chế biến theo đúng hướng dẫn của biến thể trên bao bì.',
                'manufacturer' => 'HiPP',
                'attributes' => [
                    ['slug' => 'bot-an-dam', 'name' => 'Bột ăn dặm'],
                    ['slug' => 'vi-ngu-coc', 'name' => 'Vị ngũ cốc'],
                    ['slug' => 'organic', 'name' => 'Organic'],
                    ['slug' => 'giai-doan-an-dam', 'name' => 'Giai đoạn ăn dặm'],
                ],
                'ingredients' => [
                    'Ngũ cốc hữu cơ|Sản phẩm thuộc nhóm bột ngũ cốc HiPP Organic.',
                    'Vitamin B1|Dòng ngũ cốc HiPP Organic có bổ sung vitamin B1 theo thông tin của HiPP.',
                    'Chất xơ từ ngũ cốc|Thành phần nền từ ngũ cốc hỗ trợ tạo kết cấu bột ăn dặm.',
                    'Không tự suy diễn biến thể|Thành phần chi tiết cần đối chiếu đúng hộp sản phẩm đang bán.',
                ],
                'usage' => [
                    'Rửa tay và chuẩn bị bát, thìa sạch trước khi pha.',
                    'Đong lượng bột và chất lỏng theo đúng tỷ lệ ghi trên bao bì.',
                    'Khuấy đều đến khi bột mịn và đạt độ đặc phù hợp.',
                    'Kiểm tra nhiệt độ trước khi cho bé ăn.',
                ],
                'storage' => $this->cerealStorage(),
                'warnings' => $this->cerealWarnings(),
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Ngũ cốc Organic', 'subtitle' => 'Nhóm ngũ cốc hữu cơ', 'icon' => 'grain'],
                        ['title' => 'Dễ chế biến', 'subtitle' => 'Linh hoạt cho bữa ăn dặm', 'icon' => 'bowl'],
                        ['title' => 'Vitamin B1', 'subtitle' => 'Theo dòng ngũ cốc HiPP Organic', 'icon' => 'check'],
                        ['title' => 'Giai đoạn ăn dặm', 'subtitle' => 'Pha theo hướng dẫn bao bì', 'icon' => 'baby'],
                    ],
                    'HiPP Organic – nền ngũ cốc cho bữa ăn dặm',
                    'Thành phần và cách pha cụ thể cần đối chiếu đúng biến thể trên bao bì.'
                ),
            ],

            'banh-an-dam-gerber-vi-chuoi' => [
                'brand' => ['slug' => 'gerber', 'name' => 'Gerber'],
                'description' =>
                    'Bánh ăn dặm Gerber vị chuối là dạng snack cho bé tập tự cầm ăn. '
                    . 'Dòng Gerber Banana Puffs chính hãng được thiết kế kích thước nhỏ, '
                    . 'dễ cầm và phù hợp cho bé đã làm quen với thức ăn rắn.',
                'manufacturer' => 'Gerber',
                'attributes' => [
                    ['slug' => 'banh-an-dam', 'name' => 'Bánh ăn dặm'],
                    ['slug' => 'vi-chuoi', 'name' => 'Vị chuối'],
                    ['slug' => 'tu-8-thang', 'name' => 'Từ 8 tháng'],
                    ['slug' => 'tu-cam-an', 'name' => 'Tự cầm ăn'],
                ],
                'ingredients' => [
                    'Ngũ cốc|Dòng Banana Puffs sử dụng nền ngũ cốc.',
                    'Hương chuối|Tạo hương vị chuối đặc trưng của sản phẩm.',
                    'Vitamin & khoáng chất|Gerber Banana Puffs có bổ sung vi chất theo công bố của hãng.',
                    'Lưu ý dị ứng|Đối chiếu đúng bảng thành phần trên bao bì vì công thức có thể thay đổi theo thị trường.',
                ],
                'usage' => [
                    'Rửa sạch tay của bé trước khi ăn.',
                    'Cho bé ngồi thẳng, ổn định khi sử dụng bánh.',
                    'Cho từng lượng nhỏ phù hợp khả năng cầm và nhai.',
                    'Luôn quan sát bé trong suốt thời gian ăn.',
                    'Đậy kín sau khi mở để giữ độ giòn và hạn chế hút ẩm.',
                ],
                'storage' => $this->snackStorage(),
                'warnings' => $this->snackWarnings(),
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Vị chuối', 'subtitle' => 'Hương vị trái cây', 'icon' => 'leaf'],
                        ['title' => 'Tự cầm ăn', 'subtitle' => 'Kích thước phù hợp tập bốc', 'icon' => 'hand'],
                        ['title' => 'Từ 8 tháng', 'subtitle' => 'Theo Gerber Banana Puffs', 'icon' => 'baby'],
                        ['title' => 'Bữa phụ tiện lợi', 'subtitle' => 'Dễ chia khẩu phần', 'icon' => 'bag'],
                    ],
                    'Gerber Banana – snack hỗ trợ bé tập tự ăn',
                    'Cho bé ăn khi ngồi thẳng và luôn có người lớn giám sát.'
                ),
            ],

            'chao-tuoi-baby-vi-ca-hoi-rau-cu' => [
                'description' =>
                    'Cháo tươi Baby vị cá hồi rau củ là bữa ăn dặm tiện lợi với hương vị '
                    . 'cá hồi và rau củ. Sản phẩm phù hợp khi cần chuẩn bị bữa ăn nhanh; '
                    . 'cách làm nóng và thời gian dùng sau khi mở cần theo hướng dẫn trên bao bì.',
                'attributes' => [
                    ['slug' => 'chao-tuoi', 'name' => 'Cháo tươi'],
                    ['slug' => 'ca-hoi-rau-cu', 'name' => 'Cá hồi rau củ'],
                    ['slug' => 'tien-loi', 'name' => 'Tiện lợi'],
                    ['slug' => 'giai-doan-an-dam', 'name' => 'Giai đoạn ăn dặm'],
                ],
                'ingredients' => [
                    'Nền cháo|Dạng cháo mềm dành cho bữa ăn dặm.',
                    'Cá hồi|Thành phần/hương vị chính được thể hiện trong tên sản phẩm.',
                    'Rau củ|Nhóm nguyên liệu chính được thể hiện trong tên sản phẩm.',
                    'Thành phần đầy đủ|Cần đối chiếu nhãn đúng gói sản phẩm để biết tỷ lệ và phụ liệu cụ thể.',
                ],
                'usage' => [
                    'Kiểm tra bao bì còn nguyên vẹn và hạn sử dụng trước khi dùng.',
                    'Lấy lượng cháo vừa đủ cho một bữa ăn.',
                    'Làm ấm theo đúng hướng dẫn trên bao bì nếu muốn dùng nóng.',
                    'Khuấy đều và kiểm tra nhiệt độ trước khi cho bé ăn.',
                ],
                'storage' => [
                    'Bảo quản theo điều kiện ghi trên bao bì của sản phẩm.',
                    'Sau khi mở, sử dụng hoặc bảo quản phần còn lại đúng thời gian nhà sản xuất khuyến nghị.',
                    'Không dùng sản phẩm khi bao bì phồng, rò rỉ hoặc có dấu hiệu bất thường.',
                ],
                'warnings' => [
                    'Kiểm tra thành phần và cảnh báo dị ứng, đặc biệt với cá và các nguyên liệu đi kèm.',
                    'Luôn kiểm tra nhiệt độ sau khi làm nóng trước khi cho bé ăn.',
                    'Không sử dụng nếu bao bì hoặc sản phẩm có dấu hiệu bất thường.',
                ],
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Cá hồi & rau củ', 'subtitle' => 'Hương vị bữa ăn', 'icon' => 'leaf'],
                        ['title' => 'Dạng cháo mềm', 'subtitle' => 'Phù hợp bữa ăn dặm', 'icon' => 'bowl'],
                        ['title' => 'Chuẩn bị nhanh', 'subtitle' => 'Tiện lợi khi sử dụng', 'icon' => 'spoon'],
                        ['title' => 'Khẩu phần linh hoạt', 'subtitle' => 'Dùng theo nhu cầu của bé', 'icon' => 'portion'],
                    ],
                    'Cháo tươi cá hồi rau củ – bữa ăn dặm tiện lợi',
                    'Thông tin thành phần đầy đủ và cách làm nóng cần đối chiếu trực tiếp trên bao bì.'
                ),
            ],

            'ngu-coc-an-dam-nestle-cerelac' => [
                'brand' => ['slug' => 'nestle-cerelac', 'name' => 'Nestlé Cerelac'],
                'description' =>
                    'Nestlé Cerelac là dòng ngũ cốc ăn dặm dành cho trẻ trong giai đoạn '
                    . 'chuyển sang thức ăn bổ sung. Cerelac có nhiều biến thể hương vị và '
                    . 'công thức, vì vậy tỷ lệ pha và thành phần cần đối chiếu đúng hộp đang sử dụng.',
                'manufacturer' => 'Nestlé',
                'attributes' => [
                    ['slug' => 'ngu-coc-an-dam', 'name' => 'Ngũ cốc ăn dặm'],
                    ['slug' => 'giai-doan-an-dam', 'name' => 'Giai đoạn ăn dặm'],
                    ['slug' => 'bo-sung-vi-chat', 'name' => 'Bổ sung vi chất'],
                ],
                'ingredients' => [
                    'Ngũ cốc|Nền nguyên liệu chính của dòng Cerelac.',
                    'Sắt & kẽm|Dòng Cerelac infant cereals được Nestlé công bố có bổ sung các vi chất này.',
                    'Vitamin A & C|Thuộc nhóm vi chất được bổ sung trong dòng Cerelac infant cereals.',
                    'Theo từng biến thể|Thành phần cụ thể khác nhau theo hương vị và thị trường.',
                ],
                'usage' => [
                    'Rửa tay và chuẩn bị bát, thìa sạch trước khi pha.',
                    'Pha đúng lượng bột và nước theo tỷ lệ ghi trên hộp sản phẩm.',
                    'Khuấy đều đến khi đạt độ mịn và độ đặc phù hợp.',
                    'Kiểm tra nhiệt độ trước khi cho bé ăn.',
                ],
                'storage' => $this->cerealStorage(),
                'warnings' => $this->cerealWarnings(),
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Ngũ cốc ăn dặm', 'subtitle' => 'Dòng Cerelac', 'icon' => 'grain'],
                        ['title' => 'Bổ sung vi chất', 'subtitle' => 'Sắt, kẽm, vitamin A/C theo dòng', 'icon' => 'check'],
                        ['title' => 'Dễ chuẩn bị', 'subtitle' => 'Pha theo hướng dẫn của từng hộp', 'icon' => 'bowl'],
                        ['title' => 'Giai đoạn ăn dặm', 'subtitle' => 'Thực phẩm bổ sung', 'icon' => 'baby'],
                    ],
                    'Nestlé Cerelac – ngũ cốc cho giai đoạn ăn dặm',
                    'Cerelac có nhiều biến thể; luôn dùng đúng hướng dẫn của hộp sản phẩm đang sử dụng.'
                ),
            ],

            'banh-gao-huu-co-cho-be-vi-bi-do' => [
                'description' =>
                    'Bánh gạo hữu cơ cho bé vị bí đỏ là dạng bánh ăn dặm tiện lợi, '
                    . 'kết hợp nền gạo hữu cơ với hương vị bí đỏ. Phù hợp dùng như bữa phụ '
                    . 'khi bé đã sẵn sàng tập cầm nắm và nhai thức ăn.',
                'attributes' => [
                    ['slug' => 'banh-gao', 'name' => 'Bánh gạo'],
                    ['slug' => 'huu-co', 'name' => 'Hữu cơ'],
                    ['slug' => 'vi-bi-do', 'name' => 'Vị bí đỏ'],
                    ['slug' => 'tap-nhai', 'name' => 'Tập nhai'],
                ],
                'ingredients' => [
                    'Gạo hữu cơ|Thành phần nền được thể hiện trong tên sản phẩm.',
                    'Bí đỏ|Hương vị nổi bật được thể hiện trong tên sản phẩm.',
                    'Dạng bánh gạo|Thiết kế phù hợp sử dụng như bữa phụ ăn dặm.',
                    'Công thức chi tiết|Đối chiếu bao bì để biết tỷ lệ và các thành phần bổ sung.',
                ],
                'usage' => [
                    'Rửa sạch tay của bé trước khi ăn.',
                    'Cho bé ngồi thẳng và ổn định trong suốt thời gian ăn.',
                    'Chia lượng bánh phù hợp khả năng cầm nắm và nhai.',
                    'Luôn quan sát bé, không để bé vừa ăn vừa nằm hoặc di chuyển.',
                ],
                'storage' => $this->snackStorage(),
                'warnings' => $this->snackWarnings(),
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Gạo hữu cơ', 'subtitle' => 'Nền bánh gạo', 'icon' => 'grain'],
                        ['title' => 'Vị bí đỏ', 'subtitle' => 'Hương vị rau củ', 'icon' => 'leaf'],
                        ['title' => 'Dễ cầm nắm', 'subtitle' => 'Phù hợp tập tự ăn', 'icon' => 'hand'],
                        ['title' => 'Bữa phụ', 'subtitle' => 'Chia lượng phù hợp', 'icon' => 'bag'],
                    ],
                    'Bánh gạo hữu cơ vị bí đỏ – lựa chọn bữa phụ ăn dặm',
                    'Độ tuổi và thành phần đầy đủ cần đối chiếu đúng bao bì của sản phẩm đang bán.'
                ),
            ],

            'bot-an-dam-ridielac-gold-vi-gao-sua' => [
                'brand' => ['slug' => 'ridielac-gold', 'name' => 'RiDielac Gold'],
                'description' =>
                    'Bột ăn dặm RiDielac Gold vị gạo sữa của Vinamilk dành cho giai đoạn '
                    . 'ăn dặm, sử dụng nền bột gạo và sữa. Dòng gạo sữa hiện được Vinamilk '
                    . 'công bố với công thức cân đối các nhóm chất và bổ sung vitamin, khoáng chất.',
                'manufacturer' => 'Vinamilk',
                'attributes' => [
                    ['slug' => 'bot-an-dam', 'name' => 'Bột ăn dặm'],
                    ['slug' => 'gao-sua', 'name' => 'Gạo sữa'],
                    ['slug' => 'tu-6-24-thang', 'name' => 'Từ 6 - 24 tháng'],
                    ['slug' => 'bo-sung-vi-chat', 'name' => 'Bổ sung vi chất'],
                ],
                'ingredients' => [
                    'Bột gạo & sữa bột|Hai thành phần nền chính được Vinamilk công bố cho vị Gạo Sữa.',
                    'Chất xơ hòa tan inulin|Có trong công thức RiDielac Gold Gạo Sữa.',
                    'Vitamin & khoáng chất|Công thức có bổ sung nhiều vitamin và khoáng chất.',
                    'DHA, lutein & lợi khuẩn|Có trong công thức công bố của RiDielac Gold Gạo Sữa.',
                ],
                'usage' => [
                    'Rửa tay và chuẩn bị bát, thìa sạch trước khi pha.',
                    'Pha đúng lượng bột và nước theo tỷ lệ ghi trên bao bì RiDielac Gold.',
                    'Khuấy đều đến khi bột mịn và đạt độ đặc phù hợp.',
                    'Kiểm tra nhiệt độ trước khi cho bé ăn.',
                ],
                'storage' => $this->cerealStorage(),
                'warnings' => $this->cerealWarnings(),
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Gạo & sữa', 'subtitle' => 'Vị ngọt dễ làm quen', 'icon' => 'grain'],
                        ['title' => '6 - 24 tháng', 'subtitle' => 'Theo dòng RiDielac Gold Gạo Sữa', 'icon' => 'baby'],
                        ['title' => 'Vitamin & khoáng chất', 'subtitle' => 'Theo công thức Vinamilk', 'icon' => 'check'],
                        ['title' => 'Dễ pha', 'subtitle' => 'Chuẩn bị theo hướng dẫn bao bì', 'icon' => 'bowl'],
                    ],
                    'RiDielac Gold Gạo Sữa – công thức cho giai đoạn ăn dặm',
                    'Tỷ lệ pha và khẩu phần cần làm theo đúng hướng dẫn trên hộp sản phẩm.'
                ),
            ],

            'banh-an-dam-pigeon-vi-rau-cu' => [
                'brand' => ['slug' => 'pigeon', 'name' => 'Pigeon'],
                'description' =>
                    'Bánh ăn dặm Pigeon vị rau củ là snack cho bé trong giai đoạn tập nhai '
                    . 'và tự cầm ăn. Các dòng snack rau củ Pigeon cho bé thường được thiết kế '
                    . 'thành miếng nhỏ, dùng trực tiếp và cần có người lớn giám sát khi bé ăn.',
                'manufacturer' => 'Pigeon',
                'attributes' => [
                    ['slug' => 'banh-an-dam', 'name' => 'Bánh ăn dặm'],
                    ['slug' => 'vi-rau-cu', 'name' => 'Vị rau củ'],
                    ['slug' => 'tu-7-thang', 'name' => 'Từ 7 tháng'],
                    ['slug' => 'tap-nhai', 'name' => 'Tập nhai'],
                ],
                'ingredients' => [
                    'Nền ngũ cốc / gạo|Các dòng snack rau củ Pigeon sử dụng nền ngũ cốc hoặc gạo.',
                    'Bột rau củ|Tạo hương vị rau củ của sản phẩm.',
                    'Dạng snack nhỏ|Phù hợp giai đoạn bé tập cầm và nhai.',
                    'Thành phần chính xác|Đối chiếu đúng biến thể rau củ trên bao bì vì công thức có thể khác nhau.',
                ],
                'usage' => [
                    'Rửa sạch tay của bé trước khi ăn.',
                    'Cho bé ngồi thẳng, không cho ăn khi đang nằm hoặc di chuyển.',
                    'Cho từng lượng nhỏ phù hợp khả năng nhai của bé.',
                    'Luôn có người lớn quan sát trong suốt thời gian bé ăn.',
                    'Đóng kín phần còn lại ngay sau khi mở.',
                ],
                'storage' => $this->snackStorage(),
                'warnings' => $this->snackWarnings(),
                'highlights' => $this->highlights(
                    [
                        ['title' => 'Vị rau củ', 'subtitle' => 'Hương vị nhẹ', 'icon' => 'leaf'],
                        ['title' => 'Từ 7 tháng', 'subtitle' => 'Theo dòng snack rau củ Pigeon', 'icon' => 'baby'],
                        ['title' => 'Tập nhai', 'subtitle' => 'Dạng snack cầm tay', 'icon' => 'hand'],
                        ['title' => 'Dùng trực tiếp', 'subtitle' => 'Tiện cho bữa phụ', 'icon' => 'bag'],
                    ],
                    'Pigeon rau củ – snack cho giai đoạn bé tập nhai',
                    'Luôn để bé ngồi thẳng và có người lớn giám sát khi sử dụng.'
                ),
            ],
        ];
    }
}
