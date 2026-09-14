<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // 🔴 Lấy từ Redis, hết hạn 10 phút
        $sections = Cache::remember('home_sections', 600, function () {
            return Category::active()
                ->with(['products' => fn ($q) => $q->active()->latest()->limit(10)])
                ->get()
                ->filter(fn (Category $cat) => $cat->products->isNotEmpty())
                ->map(fn (Category $cat) => [
                    'title' => $cat->name,
                    'icon' => $cat->icon,
                    'url' => route('category.show', $cat->slug),
                    'products' => $cat->products->map->toCardArray(),
                ]);
        });

        return view('client.home', compact('sections'));
    }
}