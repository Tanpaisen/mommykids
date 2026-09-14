<?php
namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\GHNService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WarmCacheCommand extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Đổ sẵn dữ liệu vào cache để truy cập đầu tiên không lag';

    public function handle(GHNService $ghn)
    {
        // 1. Danh mục sidebar
        Cache::put('categories_sidebar', Category::active()->get(), 3600);
        $this->info('✓ categories_sidebar');

        // 2. Trang chủ (sections)
        $sections = Category::active()
            ->with(['products' => fn ($q) => $q->active()->latest()->limit(10)])
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty())
            ->map(fn ($c) => [
                'title' => $c->name, 'icon' => $c->icon,
                'url' => route('category.show', $c->slug),
                'products' => $c->products->map->toCardArray(),
            ])->all();
        Cache::put('home_sections', $sections, 600);
        $this->info('✓ home_sections');

        // 3. Voucher công khai (trang lag 6.44s!)
        $now = now();
        $vouchers = Voucher::where('status', 'active')
            ->where('is_public', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
            ->where(fn ($q) => $q->whereNull('total_quantity')->orWhere('total_quantity', '>', 0))
            ->latest()->get();
        Cache::put('vouchers_public', $vouchers, 300);
        $this->info('✓ vouchers_public');

        // 4. Tỉnh/thành GHN (tránh gọi API ngoài)
        Cache::put('ghn_provinces', $ghn->getProvinces(), 86400);
        $this->info('✓ ghn_provinces');

        $this->newLine()->info('✅ Warm cache hoàn tất!');
    }
}