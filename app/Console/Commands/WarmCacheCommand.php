<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Voucher;
use App\Services\GHNService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class WarmCacheCommand extends Command
{
    /**
     * Mặc định:
     *   php artisan cache:warm
     *
     * Sẽ warm cả cache nội bộ + toàn bộ tuyến GHN.
     *
     * Tuỳ chọn:
     *   php artisan cache:warm --skip-ghn
     *   php artisan cache:warm --force-ghn
     *   php artisan cache:warm --ghn-delay=250
     */
    protected $signature = 'cache:warm
                            {--skip-ghn : Không warm dữ liệu/tuyến GHN}
                            {--force-ghn : Bỏ qua cache đọc và refresh GHN, vẫn giữ cache cũ nếu API lỗi}
                            {--ghn-delay=150 : Khoảng nghỉ giữa các request GHN, tính bằng mili giây}';

    protected $description = 'Đổ sẵn dữ liệu vào cache/Redis để lần truy cập đầu tiên không bị lag';

    public function handle(GHNService $ghn): int
    {
        $this->newLine();
        $this->info('🚀 Bắt đầu warm cache MommyKids...');
        $this->newLine();

        /*
         * 1. DANH MỤC SIDEBAR
         */
        Cache::put(
            'categories_sidebar',
            Category::active()->get(),
            3600
        );

        $this->info('✓ categories_sidebar');

        /*
         * 2. TRANG CHỦ
         */
        $sections = Category::active()
            ->with([
                'products' => fn ($q) =>
                    $q->active()
                        ->latest()
                        ->limit(10),
            ])
            ->get()
            ->filter(
                fn ($category) =>
                    $category->products->isNotEmpty()
            )
            ->map(
                fn ($category) => [
                    'title' => $category->name,
                    'icon' => $category->icon,
                    'url' => route(
                        'category.show',
                        $category->slug
                    ),
                    'products' =>
                        $category->products
                            ->map
                            ->toCardArray(),
                ]
            )
            ->all();

        Cache::put(
            'home_sections',
            $sections,
            600
        );

        $this->info('✓ home_sections');

        /*
         * 3. VOUCHER CÔNG KHAI
         */
        $now = now();

        $vouchers = Voucher::where(
                'status',
                'active'
            )
            ->where(
                'is_public',
                true
            )
            ->where(
                fn ($q) =>
                    $q->whereNull('starts_at')
                        ->orWhere(
                            'starts_at',
                            '<=',
                            $now
                        )
            )
            ->where(
                fn ($q) =>
                    $q->whereNull('expires_at')
                        ->orWhere(
                            'expires_at',
                            '>=',
                            $now
                        )
            )
            ->where(
                fn ($q) =>
                    $q->whereNull('total_quantity')
                        ->orWhere(
                            'total_quantity',
                            '>',
                            0
                        )
            )
            ->latest()
            ->get();

        Cache::put(
            'vouchers_public',
            $vouchers,
            300
        );

        $this->info('✓ vouchers_public');

        /*
         * 4. GHN
         *
         * Nếu không truyền --skip-ghn thì command sẽ:
         *
         * - warm tỉnh/thành;
         * - warm quận/huyện;
         * - kiểm tra available-services theo từng tuyến;
         * - chỉ giữ huyện có thể giao từ shop hiện tại;
         * - warm ward giao được của huyện hợp lệ;
         * - lưu tất cả kết quả vào cache/Redis.
         *
         * Vì phải gọi API GHN theo nhiều huyện nên lần đầu
         * có thể mất một lúc.
         */
        if ($this->option('skip-ghn')) {
            $this->warn('! Bỏ qua GHN vì có --skip-ghn.');
        } else {
            $this->newLine();
            $this->info('🚚 Đang refresh dữ liệu và tuyến GHN (stale-safe)...');

            $forceRefresh = (bool) $this->option('force-ghn');

            $delayMs = max(
                0,
                (int) $this->option('ghn-delay')
            );

            try {
                /*
                 * Warm master provinces trước.
                 *
                 * Key mới dùng trong GHNService:
                 * ghn:master:provinces
                 *
                 * Đồng thời giữ key cũ ghn_provinces
                 * để tương thích với code cũ nếu còn nơi đang dùng.
                 */
                $provinces = $ghn->getProvinces();

                Cache::put(
                    'ghn_provinces',
                    $provinces,
                    86400
                );

                $this->info(
                    '✓ GHN provinces: ' .
                    count($provinces)
                );

                /*
                 * QUAN TRỌNG:
                 * Hàm này mới là phần đẩy sẵn tuyến thật
                 * vào Redis/cache.
                 */
                $stats = $ghn->warmDeliverableRoutes(
                    $forceRefresh,
                    $delayMs
                );

                $this->newLine();

                $this->table(
                    ['GHN cache', 'Số lượng'],
                    [
                        [
                            'Tỉnh đã quét',
                            $stats['provinces_scanned'] ?? 0,
                        ],
                        [
                            'Tỉnh có thể giao',
                            $stats['provinces_deliverable'] ?? 0,
                        ],
                        [
                            'Huyện đã quét',
                            $stats['districts_scanned'] ?? 0,
                        ],
                        [
                            'Huyện có tuyến GHN',
                            $stats['districts_deliverable'] ?? 0,
                        ],
                        [
                            'Ward đã cache',
                            $stats['wards_cached'] ?? 0,
                        ],
                        [
                            'Huyện không có ward hợp lệ',
                            $stats['districts_without_wards'] ?? 0,
                        ],
                        [
                            'Tuyến không có service',
                            $stats['routes_without_service'] ?? 0,
                        ],
                        [
                            'Danh sách huyện cũ được giữ',
                            $stats['stale_district_lists_preserved'] ?? 0,
                        ],
                        [
                            'Danh sách tỉnh cũ được giữ',
                            $stats['stale_province_list_preserved'] ?? 0,
                        ],
                        [
                            'Lỗi API',
                            $stats['errors'] ?? 0,
                        ],
                    ]
                );

                $this->info('✓ GHN deliverable routes');
            } catch (Throwable $e) {
                $this->error(
                    '✗ Không warm được GHN: ' .
                    $e->getMessage()
                );

                $this->newLine();
                $this->warn(
                    'Các cache nội bộ khác vẫn đã được warm.'
                );

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('✅ Warm cache hoàn tất!');
        $this->newLine();

        return self::SUCCESS;
    }
}
