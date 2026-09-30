<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * InventoryService — "Người gác cổng" duy nhất cho mọi biến động kho.
 * 
 * QUY TẮC VÀNG: Tuyệt đối KHÔNG được dùng $product->update(['stock' => ...]) rải rác khắp nơi.
 * Mọi thay đổi tồn kho PHẢI gọi qua service này để:
 *   1. Khoá hàng (lockForUpdate) chống race condition
 *   2. Chống âm kho
 *   3. Ghi log vào stock_movements (chống gian lận, đối soát)
 */
class InventoryService
{
    /**
     * Xuất kho (khách đặt hàng)
     */
    public function export(Product $product, int $quantity, $reference = null, ?string $note = null): bool
    {
        return $this->move($product, -abs($quantity), 'export', $reference, $note);
    }

    /**
     * Nhập kho (nhập hàng về từ nhà cung cấp)
     */
    public function import(Product $product, int $quantity, $reference = null, ?string $note = null): bool
    {
        return $this->move($product, abs($quantity), 'import', $reference, $note);
    }

    /**
     * Khách trả hàng về kho
     */
    public function return(Product $product, int $quantity, $reference = null, ?string $note = null): bool
    {
        return $this->move($product, abs($quantity), 'return', $reference, $note);
    }

    /**
     * Hàng hỏng / thất thoát / mất cắp
     */
    public function damage(Product $product, int $quantity, ?string $note = null): bool
    {
        return $this->move($product, -abs($quantity), 'damage', null, $note);
    }

    /**
     * Admin chỉnh sửa tồn kho thủ công (kiểm kê) — BẮT BUỘC có lý do
     */
    public function adjust(Product $product, int $newStock, string $note): bool
    {
        if (empty(trim($note))) {
            throw new Exception('Vui lòng nhập lý do khi chỉnh sửa tồn kho thủ công.');
        }
        $diff = $newStock - $product->stock;
        if ($diff === 0) {
            return true;
        }
        return $this->move($product, $diff, 'adjust', null, $note);
    }

    /**
     * Core — Mọi biến động kho đều đi qua hàm này.
     * Thực hiện trong 1 Database Transaction:
     *   1. lockForUpdate — khoá dòng hàng, chống 2 người mua cùng lúc vượt kho
     *   2. Kiểm tra stockAfter >= 0 — chống âm kho
     *   3. Cập nhật stock + ghi log stock_movements
     */
    private function move(Product $product, int $quantity, string $type, $reference = null, ?string $note = null): bool
    {
        return DB::transaction(function () use ($product, $quantity, $type, $reference, $note) {
            // Khoá dòng hàng để tránh race condition
            $product = Product::where('id', $product->id)->lockForUpdate()->first();
            if (!$product) {
                throw new Exception('Sản phẩm không tồn tại.');
            }

            $stockBefore = $product->stock;
            $stockAfter  = $stockBefore + $quantity;

            // Chống âm kho
            if ($stockAfter < 0) {
                throw new Exception(
                    "Sản phẩm \"{$product->name}\" không đủ tồn kho. " .
                    "Hiện còn {$stockBefore}, yêu cầu xuất " . abs($quantity) . "."
                );
            }

            // Cập nhật tồn kho mới
            $product->update(['stock' => $stockAfter]);

            // Lấy ra Object (Model) của người đang đăng nhập (Admin hoặc User)
            $causer = Auth::guard('admin')->user() ?? Auth::user();

            // Ghi log biến động
            StockMovement::create([
                'product_id'     => $product->id,
                'type'           => $type,
                'quantity'       => $quantity,
                'stock_before'   => $stockBefore,
                'stock_after'    => $stockAfter,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id'   => $reference?->id,
                'causer_type'    => $causer ? get_class($causer) : null,
                'causer_id'      => $causer?->id,
                'note'           => $note,
            ]);

            // ✅ Xóa cache để Dashboard cập nhật số liệu mới ngay lập tức
            Cache::forget('inventory_total_value');
            Cache::forget('inventory_dead_stock');

            return true;
        });
    }

    /**
     * Kiểm tra số lượng còn lại có đủ để bán không
     */
    public function hasStock(Product $product, int $quantity): bool
    {
        return $product->stock >= $quantity;
    }
}