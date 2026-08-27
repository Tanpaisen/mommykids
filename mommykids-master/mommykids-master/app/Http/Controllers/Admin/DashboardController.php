<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        // Replace each block below with real queries once Orders/Comments/Articles models exist.

        $revenue = [
            'today' => 12_450_000,
            'today_qr' => 7_200_000,
            'today_vnpay' => 5_250_000,
            'week' => 68_900_000,
            'week_qr' => 39_100_000,
            'week_vnpay' => 29_800_000,
        ];

        $orders = [
            'today' => 24,
            'week' => 143,
        ];

        $lowStockProducts = Product::query()
            ->where('stock', '<', 10)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        // Placeholder shape until the Comments Center (Module 3) is built.
        $pendingComments = collect([
            ['type' => 'Sản phẩm', 'target' => 'Sữa Grow Plus+ 1.85kg', 'author' => 'Chị Hương', 'excerpt' => 'Bé nhà em 8 tháng dùng được sữa này không ạ?', 'minutes_ago' => 12],
            ['type' => 'Bài viết', 'target' => 'Chăm sóc rốn cho trẻ sơ sinh', 'author' => 'Chị Lan', 'excerpt' => 'Rốn bé còn hơi ướt sau 2 tuần có sao không shop?', 'minutes_ago' => 47],
            ['type' => 'Sản phẩm', 'target' => 'Bỉm BB Nature Care size L', 'author' => 'Chị Ngọc', 'excerpt' => 'Bỉm này có bị hằn không ạ?', 'minutes_ago' => 95],
        ]);

        $topArticles = collect([
            ['title' => 'Cẩm nang ăn dặm cho bé 6 tháng tuổi', 'views' => 3820],
            ['title' => 'Chọn sữa công thức phù hợp theo từng giai đoạn', 'views' => 2977],
            ['title' => 'Dấu hiệu bé mọc răng và cách chăm sóc', 'views' => 2140],
        ]);

        $topProducts = collect([
            ['name' => 'Sữa Grow Plus+ đỏ 1.85kg', 'sold' => 412],
            ['name' => 'Bỉm quần Bebejoy Premium XL', 'sold' => 356],
            ['name' => 'Combo bình sữa Comotomo 250ml', 'sold' => 289],
        ]);

        return view('admin.dashboard', compact(
            'revenue', 'orders', 'lowStockProducts', 'pendingComments', 'topArticles', 'topProducts'
        ));
    }
}
