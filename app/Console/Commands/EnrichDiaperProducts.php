<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Tag;
use Illuminate\Console\Command;

class EnrichDiaperProducts extends Command
{
    protected $signature = 'products:enrich-diapers
                            {--dry-run : Chỉ xem sản phẩm sẽ được cập nhật, không ghi database}';

    protected $description = 'Bổ sung dữ liệu chi tiết cho sản phẩm thuộc nhóm Bỉm tã & vệ sinh';

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
                : 'Đang enrich dữ liệu Bỉm tã & vệ sinh...'
        );
        $this->newLine();

        foreach ($profiles as $slug => $profile) {
            $product = Product::query()
                ->where('slug', $slug)
                ->whereHas(
                    'category',
                    fn ($query) => $query->where('slug', 'bim-ta-ve-sinh')
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
                    'ingredients' => $this->lines($profile['materials'] ?? []),
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
                $product->tags()->syncWithoutDetaching($tagIds);
            }

            $updated++;
            $this->info("OK    #{$product->id} {$product->name}");
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry-run hoàn tất.');
            $this->line('Chạy thật bằng: php artisan products:enrich-diapers');

            return self::SUCCESS;
        }

        $this->info("Hoàn tất: {$updated} sản phẩm được cập nhật.");

        if ($missing > 0) {
            $this->warn("Có {$missing} sản phẩm trong cấu hình không tìm thấy.");
        }

        $this->newLine();
        $this->line(
            'Lưu ý: những thông số SKU không xác minh chắc chắn '
            . '(ví dụ số miếng của một số gói) được chủ động để trống.'
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
            'ta-dan-merries-size-nb-cho-be-so-sinh' => [
                'description' =>
                    'Merries tã dán size NB dành cho trẻ sơ sinh đến 5 kg. '
                    . 'Sản phẩm sử dụng vật liệu mềm mại, cấu trúc thoáng khí '
                    . 'và bề mặt hỗ trợ hấp thụ, khóa chất lỏng.',
                'origin' => 'Nhật Bản',
                'manufacturer' => 'Kao Corporation',
                'attributes' => [
                    ['slug' => 'ta-dan', 'name' => 'Tã dán'],
                    ['slug' => 'size-nb', 'name' => 'Size NB'],
                    ['slug' => 'duoi-5-kg', 'name' => 'Dưới 5 kg'],
                    ['slug' => '68-mieng', 'name' => '68 miếng'],
                ],
                'materials' => [
                    'Bề mặt mềm mại|Vật liệu tiếp xúc được thiết kế mềm mại cho làn da mỏng manh của bé.',
                    'Cấu trúc thoáng khí|Hỗ trợ giải phóng hơi ẩm và khí nóng, hạn chế cảm giác hầm bí.',
                    'Bề mặt sóng lồi lõm|Hỗ trợ hấp thụ và khóa phân, nước tiểu, hạn chế lan ra xung quanh.',
                    'Lõi thấm hút|Hỗ trợ hấp thụ và giữ chất lỏng bên trong tã.',
                    'Miếng dán mềm mại|Có thể dán lại, thuận tiện khi điều chỉnh độ vừa vặn.',
                    'Vạch báo thay tã|Vạch báo đổi màu giúp nhận biết thời điểm thay tã.',
                ],
                'usage' => [
                    'Mở tã và đặt phần lưng tã dưới cơ thể bé.',
                    'Kéo phần trước của tã lên ngang bụng bé.',
                    'Cố định hai miếng dán hai bên sao cho vừa vặn, không quá chặt.',
                    'Kiểm tra vùng chân và vạch báo; thay tã khi cần thiết.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => $this->defaultDiaperWarnings(),
                'highlights' => $this->diaperHighlights(
                    'Merries – thiết kế hướng đến sự thoải mái cho bé',
                    'Thông tin sản phẩm được tổng hợp từ thông tin chính thức của Merries/Kao.'
                ),
            ],

            'ta-dan-merries-size-s' => [
                'description' =>
                    'Merries tã dán size S dành cho bé khoảng 4–8 kg. '
                    . 'Dòng tã dán Merries sử dụng vật liệu mềm mại, '
                    . 'cấu trúc thoáng khí và bề mặt hỗ trợ thấm hút.',
                'origin' => 'Nhật Bản',
                'manufacturer' => 'Kao Corporation',
                'attributes' => [
                    ['slug' => 'ta-dan', 'name' => 'Tã dán'],
                    ['slug' => 'size-s', 'name' => 'Size S'],
                    ['slug' => '4-8-kg', 'name' => '4 - 8 kg'],
                ],
                'materials' => [
                    'Vật liệu mềm mại|Bề mặt tiếp xúc được thiết kế êm mềm cho làn da bé.',
                    'Cấu trúc 3 lớp thoáng khí|Hỗ trợ giảm hầm bí và giải phóng hơi ẩm.',
                    'Bề mặt sóng lồi lõm|Hỗ trợ hấp thụ và khóa chất lỏng, hạn chế lan ra xung quanh.',
                    'Lõi thấm hút|Hỗ trợ giữ chất lỏng bên trong tã.',
                    'Miếng dán nhiều lần|Cho phép điều chỉnh lại độ vừa vặn khi mặc.',
                    'Vạch báo thay tã|Hỗ trợ nhận biết thời điểm phù hợp để thay tã.',
                ],
                'usage' => [
                    'Mở tã và đặt phần lưng tã dưới cơ thể bé.',
                    'Kéo phần trước của tã lên ngang bụng.',
                    'Cố định hai miếng dán cân đối và vừa vặn.',
                    'Chỉnh vách chống tràn quanh chân và thay tã khi cần.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => $this->defaultDiaperWarnings(),
                'highlights' => $this->diaperHighlights(
                    'Merries – mềm mại và thoáng khí cho bé',
                    'Thông tin được tổng hợp từ catalogue chính thức của Kao.'
                ),
            ],

            'ta-quan-merries-size-l' => [
                'description' =>
                    'Merries tã quần size L dành cho bé khoảng 9–14 kg. '
                    . 'Thiết kế dạng quần hỗ trợ vận động, với lưng thun co giãn, '
                    . 'rãnh thoát khí và lõi thấm hút.',
                'origin' => 'Nhật Bản',
                'manufacturer' => 'Kao Corporation',
                'attributes' => [
                    ['slug' => 'ta-quan', 'name' => 'Tã quần'],
                    ['slug' => 'size-l', 'name' => 'Size L'],
                    ['slug' => '9-14-kg', 'name' => '9 - 14 kg'],
                ],
                'materials' => [
                    'Lõi thấm hút|Hỗ trợ thấm hút trong thời gian dài.',
                    'Thun chân êm mềm 3D|Thiết kế hỗ trợ giảm ma sát tại vùng chân.',
                    'Lưng thun co giãn|Ôm quanh eo và hỗ trợ giữ tã ổn định khi bé vận động.',
                    'Rãnh thoát khí|Giúp hơi ẩm và khí nóng thoát ra ngoài.',
                    'Thiết kế vừa vặn|Hỗ trợ bé vận động thoải mái hơn.',
                    'Băng cuốn sau sử dụng|Hỗ trợ cuộn gọn tã sau khi thay.',
                ],
                'usage' => [
                    'Xác định mặt trước và mặt sau của tã quần.',
                    'Luồn từng chân bé qua hai ống tã.',
                    'Kéo tã lên ngang eo và chỉnh lưng thun cân đối.',
                    'Kiểm tra vùng chân, thay tã khi bẩn hoặc đầy.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => $this->defaultDiaperWarnings(),
                'highlights' => $this->diaperHighlights(
                    'Merries – thoải mái cho bé vận động',
                    'Thông tin được tổng hợp từ catalogue chính thức của Kao.'
                ),
            ],

            'ta-quan-huggies-skin-perfect-size-xl' => [
                'description' =>
                    'Huggies Skin Perfect tã quần size XL dành cho bé khoảng 12–17 kg. '
                    . 'Dòng Skin Perfect sử dụng công nghệ hai vùng thấm riêng biệt, '
                    . 'bề mặt mềm mại và thiết kế dạng quần tiện lợi.',
                'manufacturer' => 'Kimberly-Clark',
                'attributes' => [
                    ['slug' => 'ta-quan', 'name' => 'Tã quần'],
                    ['slug' => 'size-xl', 'name' => 'Size XL'],
                    ['slug' => '12-17-kg', 'name' => '12 - 17 kg'],
                ],
                'materials' => [
                    'Hai vùng thấm Dual Zone|Hai vùng thấm riêng biệt hỗ trợ thấm và khóa chất lỏng.',
                    'Bề mặt mềm mại|Lớp tiếp xúc được thiết kế êm mềm.',
                    'Màng đáy thoát ẩm|Hỗ trợ duy trì cảm giác khô thoáng.',
                    'Lõi thấm hút|Dung tích thấm hút hỗ trợ sử dụng trong thời gian dài.',
                    'Vạch báo tã đầy|Giúp nhận biết thời điểm nên thay tã.',
                    'Băng dính cuộn tã|Giúp cuộn gọn tã sau khi sử dụng.',
                ],
                'usage' => [
                    'Xác định mặt trước và sau của tã quần.',
                    'Luồn hai chân bé vào tã.',
                    'Kéo tã lên ngang eo và chỉnh vừa vặn.',
                    'Kiểm tra vùng chân và thay tã khi vạch báo hoặc tình trạng tã cho thấy cần thay.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => $this->defaultDiaperWarnings(),
                'highlights' => [
                    'items' => [
                        [
                            'title' => 'Hai vùng thấm',
                            'subtitle' => 'Công nghệ Dual Zone',
                            'icon' => 'drop',
                        ],
                        [
                            'title' => 'Khô thoáng',
                            'subtitle' => 'Màng đáy hỗ trợ thoát ẩm',
                            'icon' => 'air',
                        ],
                        [
                            'title' => 'Hạn chế tràn',
                            'subtitle' => 'Hỗ trợ khóa chất lỏng',
                            'icon' => 'shield',
                        ],
                        [
                            'title' => 'Mềm mại',
                            'subtitle' => 'Bề mặt tiếp xúc êm mềm',
                            'icon' => 'heart',
                        ],
                    ],
                    'message' => 'Huggies Skin Perfect – thiết kế hai vùng thấm',
                    'submessage' => 'Nội dung được tổng hợp từ thông tin sản phẩm Huggies.',
                ],
            ],

            'ta-quan-huggies-dry-size-l' => [
                'description' =>
                    'Huggies Dry tã quần size L dành cho bé khoảng 9–14 kg. '
                    . 'Thiết kế dạng quần có lưng thun co giãn, '
                    . 'màng đáy thoát ẩm và lõi siêu thấm hỗ trợ giữ bé khô thoáng.',
                'origin' => 'Việt Nam',
                'manufacturer' => 'Kimberly-Clark',
                'attributes' => [
                    ['slug' => 'ta-quan', 'name' => 'Tã quần'],
                    ['slug' => 'size-l', 'name' => 'Size L'],
                    ['slug' => '9-14-kg', 'name' => '9 - 14 kg'],
                ],
                'materials' => [
                    'Bề mặt thấm hút|Hỗ trợ đưa chất lỏng xuống lõi tã.',
                    'Lõi siêu thấm|Hỗ trợ hấp thụ và khóa chất lỏng.',
                    'Màng đáy thoát ẩm|Giúp hơi ẩm thoát ra ngoài.',
                    'Lưng thun co giãn|Hỗ trợ tã ôm vừa vùng eo.',
                    'Vách chống tràn|Hỗ trợ hạn chế rò rỉ quanh vùng chân.',
                    'Thiết kế dạng quần|Thuận tiện khi mặc và thay cho bé.',
                ],
                'usage' => [
                    'Xác định mặt sau của tã.',
                    'Luồn hai chân bé vào tã như mặc quần.',
                    'Kéo tã lên và chỉnh vùng eo, vùng chân vừa vặn.',
                    'Khi tã bẩn, xé hai bên hông để tháo và thay tã mới.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => $this->defaultDiaperWarnings(),
                'highlights' => $this->diaperHighlights(
                    'Huggies Dry – khô thoáng và tiện lợi',
                    'Nội dung được tổng hợp từ thông tin sản phẩm Huggies Dry.'
                ),
            ],

            'ta-quan-bobby-extra-soft-size-xl' => [
                'description' =>
                    'Bobby Extra Soft Dry tã quần size XL dành cho bé khoảng 12–17 kg. '
                    . 'Sản phẩm có mặt sóng mềm, lõi rãnh thấm, '
                    . 'thun co giãn và màng đáy thoáng khí.',
                'origin' => 'Việt Nam',
                'manufacturer' => 'Công ty Cổ phần Diana Unicharm',
                'attributes' => [
                    ['slug' => 'ta-quan', 'name' => 'Tã quần'],
                    ['slug' => 'size-xl', 'name' => 'Size XL'],
                    ['slug' => '12-17-kg', 'name' => '12 - 17 kg'],
                ],
                'materials' => [
                    'Mặt sóng mềm|Bề mặt dạng sóng hỗ trợ thấm hút và giảm tiếp xúc với chất thải.',
                    'Rãnh thấm|Hỗ trợ dàn đều và khóa chất lỏng trong lõi.',
                    'Thun Soft-Fit|Lưng thun co giãn hỗ trợ ôm vừa vùng eo.',
                    'Màng đáy thoáng khí|Hỗ trợ đẩy hơi nóng ẩm ra ngoài.',
                    'Chỉ thị thay tã|Dải màu hỗ trợ nhận biết thời điểm thay tã.',
                    'Băng cuốn sau sử dụng|Giúp cuộn gọn tã sau khi thay.',
                ],
                'usage' => [
                    'Luồn tã vào từng chân của bé.',
                    'Kéo tã lên ngang eo.',
                    'Điều chỉnh vách chống tràn và mép chun chân nằm đúng vị trí.',
                    'Thay tã khi tã đầy hoặc ngay sau khi bé đi tiêu.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => [
                    'Thay tã đều đặn và thay ngay sau khi bé đi tiêu.',
                    'Ngừng sử dụng nếu xuất hiện dấu hiệu bất thường trên da.',
                    'Để sản phẩm xa tầm tay trẻ nhỏ khi chưa sử dụng.',
                ],
                'highlights' => $this->diaperHighlights(
                    'Bobby Extra Soft – mềm mại và vừa vặn',
                    'Nội dung được tổng hợp từ thông tin sản phẩm Bobby Extra Soft Dry.'
                ),
            ],

            'ta-dan-moony-natural-size-s' => [
                'description' =>
                    'Moony Natural tã dán size S dành cho bé khoảng 4–8 kg. '
                    . 'Dòng Natural có bề mặt mềm, thiết kế thông thoáng '
                    . 'và lưng thun co giãn giúp tã ôm vừa cơ thể bé.',
                'origin' => 'Nhật Bản',
                'manufacturer' => 'Unicharm Corporation',
                'attributes' => [
                    ['slug' => 'ta-dan', 'name' => 'Tã dán'],
                    ['slug' => 'size-s', 'name' => 'Size S'],
                    ['slug' => '4-8-kg', 'name' => '4 - 8 kg'],
                ],
                'materials' => [
                    'Bề mặt mềm|Bề mặt tiếp xúc được thiết kế êm mềm.',
                    'Vải không dệt|Lớp vật liệu không dệt dùng trong cấu tạo tã.',
                    'Bông cellulose|Thành phần trong lõi hấp thụ.',
                    'Hạt siêu thấm|Hỗ trợ hấp thụ và giữ chất lỏng.',
                    'Lưng thun co giãn|Hỗ trợ ôm vừa cơ thể bé.',
                    'Vạch báo thay tã|Hỗ trợ nhận biết thời điểm cần thay tã.',
                ],
                'usage' => [
                    'Mở tã và đặt phần lưng tã dưới cơ thể bé.',
                    'Kéo phần trước của tã lên ngang bụng.',
                    'Cố định hai miếng dán hai bên và chỉnh cân đối.',
                    'Kiểm tra vùng chân và thay tã khi cần.',
                ],
                'storage' => $this->defaultDiaperStorage(),
                'warnings' => $this->defaultDiaperWarnings(),
                'highlights' => $this->diaperHighlights(
                    'Moony Natural – thiết kế mềm mại cho bé',
                    'Thông tin được tổng hợp từ thông tin sản phẩm Moony Natural.'
                ),
            ],

            'khan-uot-mamamy-khong-mui-100-to' => [
                'description' =>
                    'Khăn ướt Mamamy không mùi gói 100 tờ sử dụng vải không dệt '
                    . 'co giãn hai chiều và nước siêu tinh khiết. '
                    . 'Quy cách 100 tờ, kích thước mỗi tờ 15 × 20 cm.',
                'origin' => 'Việt Nam',
                'manufacturer' =>
                    'Công ty Cổ phần Sản xuất và Thương mại Đông Hiệp',
                'attributes' => [
                    ['slug' => 'khan-uot', 'name' => 'Khăn ướt'],
                    ['slug' => 'khong-mui', 'name' => 'Không mùi'],
                    ['slug' => '100-to', 'name' => '100 tờ'],
                    ['slug' => '15x20-cm', 'name' => '15 × 20 cm'],
                ],
                'materials' => [
                    'Vải không dệt 2 chiều|Vật liệu co giãn với hàm lượng rayon cao.',
                    'Nước tinh khiết 99,9%|Nước được xử lý qua hệ thống lọc và tiệt trùng.',
                    'Cấu trúc rút từng tờ|Thiết kế giúp lấy từng tờ thuận tiện.',
                    'Khăn dày|Thiết kế khăn dày, phù hợp thao tác vệ sinh.',
                    'Không mùi|Phiên bản không bổ sung hương thơm.',
                    'Kích thước 15 × 20 cm|Kích thước công bố cho mỗi tờ khăn.',
                ],
                'usage' => [
                    'Mở nắp hoặc miếng dán bảo vệ của gói khăn.',
                    'Rút từng tờ khăn theo nhu cầu sử dụng.',
                    'Lau nhẹ vùng da cần vệ sinh.',
                    'Đóng kín nắp sau khi dùng để hạn chế khăn bị khô.',
                ],
                'storage' => [
                    'Bảo quản nơi khô ráo, thoáng mát.',
                    'Tránh ánh nắng trực tiếp.',
                    'Đóng kín nắp sau khi lấy khăn.',
                ],
                'warnings' => [
                    'Không sử dụng nếu bao bì hư hỏng hoặc sản phẩm có dấu hiệu bất thường.',
                    'Tránh để khăn tiếp xúc trực tiếp với mắt.',
                    'Không ủ ấm cả bịch khăn liên tục ở nhiệt độ cao.',
                ],
                'highlights' => [
                    'items' => [
                        [
                            'title' => '100 tờ',
                            'subtitle' => 'Quy cách gói 100 tờ',
                            'icon' => 'check',
                        ],
                        [
                            'title' => 'Không mùi',
                            'subtitle' => 'Phiên bản không hương thơm',
                            'icon' => 'air',
                        ],
                        [
                            'title' => 'Nước tinh khiết',
                            'subtitle' => '99,9% nước siêu tinh khiết',
                            'icon' => 'drop',
                        ],
                        [
                            'title' => 'Vải không dệt',
                            'subtitle' => 'Co giãn hai chiều',
                            'icon' => 'heart',
                        ],
                    ],
                    'message' => 'Mamamy – khăn ướt không mùi 100 tờ',
                    'submessage' => 'Thông tin được tổng hợp từ trang sản phẩm chính thức của Mamamy.',
                ],
            ],
        ];
    }

    private function defaultDiaperStorage(): array
    {
        return [
            'Bảo quản nơi khô ráo, thoáng mát.',
            'Tránh ánh nắng trực tiếp và nơi có độ ẩm cao.',
            'Giữ sản phẩm sạch và khô trước khi sử dụng.',
        ];
    }

    private function defaultDiaperWarnings(): array
    {
        return [
            'Chọn kích cỡ phù hợp với cân nặng của bé.',
            'Không mặc tã quá chặt quanh bụng và đùi.',
            'Thay tã khi cần thiết để giữ vùng da mặc tã sạch và khô.',
        ];
    }

    private function diaperHighlights(
        string $message,
        string $submessage
    ): array {
        return [
            'items' => [
                [
                    'title' => 'Thấm hút nhanh',
                    'subtitle' => 'Hỗ trợ hấp thụ và khóa chất lỏng',
                    'icon' => 'drop',
                ],
                [
                    'title' => 'Thoáng khí',
                    'subtitle' => 'Hỗ trợ giải phóng hơi ẩm và khí nóng',
                    'icon' => 'air',
                ],
                [
                    'title' => 'Hạn chế tràn',
                    'subtitle' => 'Thiết kế hỗ trợ giữ chất lỏng bên trong',
                    'icon' => 'shield',
                ],
                [
                    'title' => 'Mềm mại',
                    'subtitle' => 'Bề mặt tiếp xúc êm mềm',
                    'icon' => 'heart',
                ],
            ],
            'message' => $message,
            'submessage' => $submessage,
        ];
    }
}
