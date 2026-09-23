<?php

namespace App\Services;

use App\Models\ChatBotScenario;
use App\Models\Product;
use Illuminate\Support\Str;

class ChatBotService
{
    public function respond(
        string $message,
        array $history = []
    ): array {
        $text = $this->normalize($message);

        /*
        |--------------------------------------------------------------------------
        | Yêu cầu gặp nhân viên
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'gap nhan vien',
            'gap tu van vien',
            'nhan vien',
            'tu van vien',
            'nguoi that',
            'ho tro vien',
        ])) {
            return [
                'understood' => true,
                'request_staff' => true,
                'message' =>
                    'Mình sẽ kết nối bạn với chuyên viên MommyKids ngay.',
            ];
        }
        /*
|--------------------------------------------------------------------------
| Kịch bản Chat Bot do Admin cấu hình
|--------------------------------------------------------------------------
*/
             $scenarioReply = $this->respondFromScenario($text);

              if ($scenarioReply !== null) {
               return $scenarioReply;
              }

        /*
        |--------------------------------------------------------------------------
        | Nếu chính tin nhắn hiện tại cung cấp tuổi
        |--------------------------------------------------------------------------
        */
        $directAge = $this->extractAgeMonthsFromText($text);

        if ($directAge !== null) {
            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Mình đã ghi nhận bé '
                    . $this->formatAge($directAge)
                    . '. Bạn đang cần sữa, dinh dưỡng, vitamin, '
                    . 'bỉm/tã hay sản phẩm chăm sóc bé?',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Tìm tuổi trong lịch sử hội thoại
        |--------------------------------------------------------------------------
        */
        $ageMonths = $this->findAgeFromHistory(
            $history
        );

        /*
        |--------------------------------------------------------------------------
        | Tư vấn SỮA - lấy sản phẩm thật trong database
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'sua',
            'sua dinh duong',
            'sua bot',
            'sua cong thuc',
            'sua cho be',
            'sua cho con',
        ])) {
            return $this->recommendMilk(
                $ageMonths
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Chào hỏi
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'xin chao',
            'chao',
            'hello',
            'hi',
            'hey',
        ])) {
            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Xin chào bạn 👋 Mình là trợ lý MommyKids. '
                    . 'Bạn cần tư vấn sản phẩm, đơn hàng, '
                    . 'voucher hay vận chuyển?',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Đơn hàng
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'don hang',
            'tra cuu don',
            'kiem tra don',
            'ma don',
            'order',
        ])) {
            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Bạn có thể mở mục “Đơn hàng của tôi” '
                    . 'để xem trạng thái đơn. '
                    . 'Nếu cần xử lý một đơn cụ thể, '
                    . 'hãy gửi mã đơn hoặc yêu cầu gặp nhân viên.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Voucher
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'voucher',
            'ma giam',
            'khuyen mai',
            'giam gia',
            'uu dai',
        ])) {
            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Voucher đã lưu có thể chọn ở bước thanh toán. '
                    . 'MommyKids hỗ trợ voucher đơn hàng '
                    . 'và voucher vận chuyển.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Vận chuyển
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'ship',
            'van chuyen',
            'giao hang',
            'phi giao',
            'phi ship',
            'ghn',
        ])) {
            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Phí vận chuyển được tính theo địa chỉ nhận hàng '
                    . 'và dữ liệu GHN tại bước thanh toán. '
                    . 'Khi đổi địa chỉ, hệ thống sẽ tính lại phí.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Tư vấn sản phẩm chung
        |--------------------------------------------------------------------------
        */
        if ($this->containsAny($text, [
            'san pham',
            'tu van san pham',
            'bim',
            'ta',
            'khan uot',
            'vitamin',
            'binh sua',
            'an dam',
        ])) {
            $ageText = $ageMonths !== null
                ? ' Mình đang nhớ bé '
                    . $this->formatAge($ageMonths)
                    . '.'
                : '';

            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Mình có thể hỗ trợ tìm sản phẩm '
                    . 'theo nhu cầu của bé.'
                    . $ageText
                    . ' Bạn đang muốn tìm loại sản phẩm nào?',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Không hiểu
        |--------------------------------------------------------------------------
        */
        return [
            'understood' => false,
            'request_staff' => false,
            'message' =>
                'Mình chưa hiểu rõ câu hỏi này. '
                . 'Bạn thử diễn đạt lại hoặc chọn '
                . 'một nội dung nhanh bên dưới nhé.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Gợi ý sữa từ database
    |--------------------------------------------------------------------------
    */
    private function recommendMilk(
        ?int $ageMonths
    ): array {
        $baseQuery = Product::query()
            ->active()
            ->where('stock', '>', 0)
            ->where(function ($query) {
                $query
                    ->where(
                        'name',
                        'like',
                        '%Sữa%'
                    )
                    ->orWhereHas(
                        'category',
                        function ($category) {
                            $category->where(
                                'slug',
                                'sua-cho-be'
                            );
                        }
                    );
            });

        $products = collect();

        /*
        |--------------------------------------------------------------------------
        | Có tuổi → ưu tiên sản phẩm đúng Stage
        |--------------------------------------------------------------------------
        */
        if ($ageMonths !== null) {
            $products = (clone $baseQuery)
                ->whereHas(
                    'stages',
                    function ($stage) use (
                        $ageMonths
                    ) {
                        $stage
                            ->where(
                                'stages.is_active',
                                true
                            )
                            ->where(
                                'stages.age_from',
                                '<=',
                                $ageMonths
                            )
                            ->where(
                                'stages.age_to',
                                '>=',
                                $ageMonths
                            );
                    }
                )
                ->orderByDesc('stock')
                ->limit(3)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Nếu chưa có Stage phù hợp → fallback sữa đang bán
        |--------------------------------------------------------------------------
        */
        if ($products->isEmpty()) {
            $products = $baseQuery
                ->orderByDesc('stock')
                ->limit(3)
                ->get();
        }

        if ($products->isEmpty()) {
            return [
                'understood' => true,
                'request_staff' => false,
                'message' =>
                    'Hiện mình chưa tìm thấy sản phẩm sữa '
                    . 'đang còn hàng trong hệ thống. '
                    . 'Bạn có muốn mình kết nối nhân viên tư vấn không?',
            ];
        }

        $lines = $products
            ->map(function (Product $product) {
                return '• '
                    . $product->name
                    . ' — '
                    . number_format(
                        (int) $product->price,
                        0,
                        ',',
                        '.'
                    )
                    . 'đ';
            })
            ->implode("\n");

        $prefix = $ageMonths !== null
            ? 'Với bé '
                . $this->formatAge($ageMonths)
                . ', mình tìm được một số sản phẩm '
                . 'theo dữ liệu giai đoạn trong MommyKids:'
            : 'Mình tìm được một số sản phẩm sữa '
                . 'đang bán tại MommyKids:';

        return [
            'understood' => true,
            'request_staff' => false,
            'message' =>
                $prefix
                . "\n\n"
                . $lines
                . "\n\n"
                . 'Bạn muốn mình tư vấn kỹ sản phẩm nào?',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Tìm tuổi gần nhất trong lịch sử
    |--------------------------------------------------------------------------
    */
    private function findAgeFromHistory(
        array $history
    ): ?int {
        foreach (
            array_reverse($history)
            as $oldMessage
        ) {
            $age = $this->extractAgeMonthsFromText(
                $this->normalize(
                    (string) $oldMessage
                )
            );

            if ($age !== null) {
                return $age;
            }
        }

        return null;
    }

    private function extractAgeMonthsFromText(
        string $text
    ): ?int {
        /*
         * Ví dụ:
         * 2 tháng
         * 12 tháng
         */
        if (
            preg_match(
                '/\b(\d{1,2})\s*thang\b/',
                $text,
                $matches
            )
        ) {
            $months = (int) $matches[1];

            if (
                $months >= 0
                && $months <= 72
            ) {
                return $months;
            }
        }

        /*
         * Ví dụ:
         * 1 tuổi → 12 tháng
         * 2 tuổi → 24 tháng
         */
        if (
            preg_match(
                '/\b(\d{1,2})\s*tuoi\b/',
                $text,
                $matches
            )
        ) {
            $years = (int) $matches[1];

            if (
                $years >= 0
                && $years <= 6
            ) {
                return $years * 12;
            }
        }

        return null;
    }

    private function formatAge(
        int $months
    ): string {
        if (
            $months >= 12
            && $months % 12 === 0
        ) {
            return (int) ($months / 12)
                . ' tuổi';
        }

        return $months . ' tháng';
    }
    private function respondFromScenario(
    string $text
): ?array {
    $scenarios = ChatBotScenario::query()
        ->where('is_active', true)
        ->orderBy('priority')
        ->orderBy('id')
        ->get();

    foreach ($scenarios as $scenario) {
        $keywords = $scenario->keywords ?? [];

        foreach ($keywords as $keyword) {
            $normalizedKeyword = $this->normalize(
                (string) $keyword
            );

             if (
               $normalizedKeyword !== ''
               && $this->matchesKeyword(
                $text,
                  $normalizedKeyword
                 )
                ) {
                $scenario->increment(
                    'matched_count'
                );

                return [
                    'understood' => true,

                    'request_staff' =>
                        (bool) $scenario
                            ->handoff_to_staff,

                    'message' =>
                        (string) $scenario
                            ->response,
                ];
            }
        }
    }

    return null;
}
private function matchesKeyword(
    string $text,
    string $keyword
): bool {
    $text = $this->normalize($text);
    $keyword = $this->normalize($keyword);

    if ($keyword === '') {
        return false;
    }

    $pattern = '/(?<![a-z0-9])'
        . preg_quote($keyword, '/')
        . '(?![a-z0-9])/i';

    return preg_match(
        $pattern,
        $text
    ) === 1;
}

    private function normalize(
        string $value
    ): string {
        return trim(
            Str::lower(
                Str::ascii($value)
            )
        );
    }

    private function containsAny(
        string $text,
        array $keywords
    ): bool {
        foreach ($keywords as $keyword) {
            if (
                str_contains(
                    $text,
                    $this->normalize($keyword)
                )
            ) {
                return true;
            }
        }

        return false;
    }
}