<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class InventoryController extends Controller
{

    /**
     * Dashboard tồn kho: cảnh báo hết hàng, tổng vốn kho, hàng tồn đọng
     */
    public function index(Request $request)
    {
        // Sản phẩm sắp hết hàng (cần nhập gấp)
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_alert')
            ->where('is_active', true)
            ->orderBy('stock')
            ->paginate(10, ['*'], 'low_stock_page');

        // Tổng vốn đang đọng trong kho
        $totalInventoryValue = Cache::remember('inventory_total_value', now()->addMinutes(60), function () {
            return Product::sum(\DB::raw('stock * cost_price'));
        });

        // Hàng tồn đọng (Dead Stock): còn hàng nhưng 90 ngày không có giao dịch xuất
        $deadStock = Cache::remember('inventory_dead_stock', now()->addHours(12), function () {
            return Product::where('stock', '>', 0)
                ->whereDoesntHave('stockMovements', function ($q) {
                    $q->where('type', 'export')
                      ->where('created_at', '>=', now()->subDays(90));
                })
                ->orderByDesc('stock')
                ->limit(10)
                ->get();
        });

        return view('admin.inventory.index', compact(
            'lowStockProducts', 'totalInventoryValue', 'deadStock'
        ));
    }

    /**
     * Lịch sử biến động kho (xuất/nhập/trả/hỏng/chỉnh sửa)
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'user'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $movements = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.inventory.movements', compact('movements', 'products'));
    }

    /**
     * Form nhập hàng
     */
    public function createImport()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'stock', 'cost_price']);
        return view('admin.inventory.import', compact('products'));
    }

    /**
     * Xử lý nhập hàng
     */
    public function storeImport(Request $request, InventoryService $inventory)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'cost_price' => 'nullable|integer|min:0',
            'note'       => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);

        try {
            // Nếu cập nhật giá vốn mới khi nhập hàng
            if ($request->filled('cost_price')) {
                $product->update(['cost_price' => $request->cost_price]);
            }

            $inventory->import(
                $product,
                $request->quantity,
                null,
                $request->note ?: 'Nhập hàng thủ công'
            );

            return redirect()->route('admin.inventory.movements')
                ->with('success', "Đã nhập +{$request->quantity} {$product->name} vào kho.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Form điều chỉnh tồn kho (kiểm kê) — BẮT BUỘC có lý do
     */
    public function createAdjust()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'stock']);
        return view('admin.inventory.adjust', compact('products'));
    }

    /**
     * Xử lý điều chỉnh tồn kho
     */
    public function storeAdjust(Request $request, InventoryService $inventory)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'new_stock'  => 'required|integer|min:0',
            'note'       => 'required|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);

        try {
            $inventory->adjust($product, $request->new_stock, $request->note);

            return redirect()->route('admin.inventory.movements')
                ->with('success', "Đã điều chỉnh tồn kho \"{$product->name}\" thành {$request->new_stock}.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
