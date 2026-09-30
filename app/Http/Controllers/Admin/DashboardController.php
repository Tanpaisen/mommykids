<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ========== TRẠNG THÁI ĐÃ NHẬN TIỀN ==========
        $paidStatuses = ['paid', 'delivered'];

        // ========== DOANH THU HÔM NAY — ĐỦ 3 CHIỀU ==========
        $todayStats = Order::whereIn('status', $paidStatuses)
            ->whereDate('created_at', now())
            ->selectRaw('
                CAST(SUM(subtotal) AS UNSIGNED) as subtotal,      -- Tiền hàng TRƯỚC giảm
                CAST(SUM(discount) AS UNSIGNED) as discount,      -- Tiền đã khuyến mãi
                CAST(SUM(total) AS UNSIGNED) as total,            -- Tiền THỰC NHẬN
                CAST(SUM(CASE WHEN payment_method = "qr" THEN total ELSE 0 END) AS UNSIGNED) as qr,
                CAST(SUM(CASE WHEN payment_method = "vnpay" THEN total ELSE 0 END) AS UNSIGNED) as vnpay,
                CAST(SUM(CASE WHEN payment_method = "zalopay" THEN total ELSE 0 END) AS UNSIGNED) as zalopay,
                CAST(SUM(CASE WHEN payment_method = "cod" THEN total ELSE 0 END) AS UNSIGNED) as cod,
                CAST(SUM(CASE WHEN payment_method = "paypal" THEN total ELSE 0 END) AS UNSIGNED) as paypal,
                CAST(SUM(CASE WHEN payment_method = "stripe" THEN total ELSE 0 END) AS UNSIGNED) as stripe,
                CAST(SUM(CASE WHEN payment_method = "bank" THEN total ELSE 0 END) AS UNSIGNED) as bank
            ')->first();

        $revenue['today_subtotal']   = $todayStats->subtotal ?? 0;
        $revenue['today_discount']   = $todayStats->discount ?? 0;
        $revenue['today']            = $todayStats->total ?? 0;
        $revenue['today_qr']         = $todayStats->qr ?? 0;
        $revenue['today_vnpay']      = $todayStats->vnpay ?? 0;
        $revenue['today_zalopay']    = $todayStats->zalopay ?? 0;
        $revenue['today_cod']        = $todayStats->cod ?? 0;
        $revenue['today_paypal']     = $todayStats->paypal ?? 0;
        $revenue['today_stripe']     = $todayStats->stripe ?? 0;
        $revenue['today_bank']       = $todayStats->bank ?? 0;

        // TÍNH VỐN HÔM NAY
        $todayCost = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('orders.status', $paidStatuses)
            ->whereDate('orders.created_at', now())
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        $revenue['today_cost']   = (int) $todayCost;
        $revenue['today_profit'] = $revenue['today'] - $revenue['today_cost'];

        // ========== DOANH THU TUẦN NÀY — ĐỦ 3 CHIỀU ==========
        $weekStart = now()->startOfWeek();
        $weekStats = Order::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$weekStart, now()])
            ->selectRaw('
                CAST(SUM(subtotal) AS UNSIGNED) as subtotal,
                CAST(SUM(discount) AS UNSIGNED) as discount,
                CAST(SUM(total) AS UNSIGNED) as total,
                CAST(SUM(CASE WHEN payment_method = "qr" THEN total ELSE 0 END) AS UNSIGNED) as qr,
                CAST(SUM(CASE WHEN payment_method = "vnpay" THEN total ELSE 0 END) AS UNSIGNED) as vnpay,
                CAST(SUM(CASE WHEN payment_method = "zalopay" THEN total ELSE 0 END) AS UNSIGNED) as zalopay,
                CAST(SUM(CASE WHEN payment_method = "cod" THEN total ELSE 0 END) AS UNSIGNED) as cod,
                CAST(SUM(CASE WHEN payment_method = "paypal" THEN total ELSE 0 END) AS UNSIGNED) as paypal,
                CAST(SUM(CASE WHEN payment_method = "stripe" THEN total ELSE 0 END) AS UNSIGNED) as stripe,
                CAST(SUM(CASE WHEN payment_method = "bank" THEN total ELSE 0 END) AS UNSIGNED) as bank
            ')->first();

        $revenue['week_subtotal']    = $weekStats->subtotal ?? 0;
        $revenue['week_discount']    = $weekStats->discount ?? 0;
        $revenue['week']             = $weekStats->total ?? 0;
        $revenue['week_qr']          = $weekStats->qr ?? 0;
        $revenue['week_vnpay']       = $weekStats->vnpay ?? 0;
        $revenue['week_zalopay']     = $weekStats->zalopay ?? 0;
        $revenue['week_cod']         = $weekStats->cod ?? 0;
        $revenue['week_paypal']      = $weekStats->paypal ?? 0;
        $revenue['week_stripe']      = $weekStats->stripe ?? 0;
        $revenue['week_bank']        = $weekStats->bank ?? 0;

        // TÍNH VỐN TUẦN NÀY
        $weekCost = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('orders.status', $paidStatuses)
            ->whereBetween('orders.created_at', [$weekStart, now()])
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        $revenue['week_cost']   = (int) $weekCost;
        $revenue['week_profit'] = $revenue['week'] - $revenue['week_cost'];

       // ========== DOANH THU THÁNG NÀY ==========
        $monthStart = now()->startOfMonth();
        $monthStats = Order::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$monthStart, now()])
            ->selectRaw('
                CAST(SUM(total) AS UNSIGNED) as total,
                CAST(SUM(subtotal) AS UNSIGNED) as subtotal, 
                CAST(SUM(discount) AS UNSIGNED) as discount
            ')->first();

        $revenue['month']          = $monthStats->total ?? 0;
        $revenue['month_subtotal'] = $monthStats->subtotal ?? 0;
        $revenue['month_discount'] = $monthStats->discount ?? 0;

        // TÍNH VỐN THÁNG NÀY
        $monthCost = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('orders.status', $paidStatuses)
            ->whereBetween('orders.created_at', [$monthStart, now()])
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        $revenue['month_cost']   = (int) $monthCost;
        $revenue['month_profit'] = $revenue['month'] - $revenue['month_cost'];
        
        // ========== DOANH THU TOÀN HỆ THỐNG (ALL-TIME) ==========
        $allTimeStats = Order::whereIn('status', $paidStatuses)
            ->selectRaw('
                CAST(SUM(total) AS UNSIGNED) as total,
                CAST(SUM(subtotal) AS UNSIGNED) as subtotal, 
                CAST(SUM(discount) AS UNSIGNED) as discount
            ')->first();

        // Tính tổng tiền vốn (Giá vốn * Số lượng) của các đơn hàng thành công
        $totalCostAllTime = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('orders.status', $paidStatuses)
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        // Gán dữ liệu vào mảng trả về
        $revenue['all_time']          = $allTimeStats->total ?? 0;
        $revenue['all_time_subtotal'] = $allTimeStats->subtotal ?? 0;
        $revenue['all_time_discount'] = $allTimeStats->discount ?? 0;
        
        // Thêm 2 biến mới: Vốn và Lợi nhuận
        $revenue['all_time_cost']     = (int) $totalCostAllTime;
        $revenue['all_time_profit']   = $revenue['all_time'] - $revenue['all_time_cost'];

        // ========== SỐ ĐƠN HÀNG ==========
        $orders['today'] = Order::whereIn('status', $paidStatuses)
            ->whereDate('created_at', now())
            ->count();
        $orders['week'] = Order::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$weekStart, now()])
            ->count();

        // ========== SẢN PHẨM SẮP HẾT HÀNG ==========
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'low_stock_alert')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get(['id', 'name', 'stock', 'low_stock_alert']);

        // ========== TOP BÁN CHẠY ==========
        $topProducts = OrderItem::select(
            'product_id',
            DB::raw('SUM(quantity) as sold')
        )
        ->whereHas('order', fn($q) => $q->whereIn('status', $paidStatuses))
        ->groupBy('product_id')
        ->orderByDesc('sold')
        ->limit(3)
        ->with('product:id,name')
        ->get()
        ->map(fn($item) => [
            'name' => $item->product?->name ?? 'Sản phẩm đã xóa',
            'sold' => $item->sold,
        ]);

        // ========== BÌNH LUẬN & BÀI VIẾT ==========
        $pendingComments = collect();
        $topArticles = collect([
            ['title' => 'Cẩm nang ăn dặm cho bé 6 tháng tuổi', 'views' => 3820],
            ['title' => 'Chọn sữa công thức phù hợp theo từng giai đoạn', 'views' => 2977],
            ['title' => 'Dấu hiệu bé mọc răng và cách chăm sóc', 'views' => 2140],
        ]);

        return view('admin.dashboard', compact(
            'revenue',
            'orders',
            'lowStockProducts',
            'pendingComments',
            'topArticles',
            'topProducts'
        ));
    }
}