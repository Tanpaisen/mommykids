<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Scope query theo người dùng hiện tại:
     * - Đã đăng nhập: user_id
     * - Khách: session_id
     */
    protected function scoped()
    {
        return Auth::check()
            ? CartItem::query()->where('user_id', Auth::id())
            : CartItem::query()->where('session_id', Session::getId());
    }

    /**
     * Lấy danh sách sản phẩm hợp lệ trong giỏ hàng.
     *
     * whereHas('product') sẽ loại CartItem có Product:
     * - đã bị soft delete
     * - không còn tồn tại
     */
    public function items(): Collection
    {
        return $this->scoped()
            ->whereHas('product')
            ->with([
                'product.category',
            ])
            ->get();
    }

    /**
     * Tổng số lượng sản phẩm trong giỏ.
     *
     * Không tính CartItem có sản phẩm đã bị xóa mềm.
     */
    public function count(): int
    {
        return (int) $this->scoped()
            ->whereHas('product')
            ->sum('quantity');
    }

    /**
     * Tổng tiền giỏ hàng.
     */
    public function total(): int
    {
        return (int) $this->items()->sum(
            function (CartItem $item) {
                $price = $item->product?->price ?? 0;

                return $item->quantity * $price;
            }
        );
    }

    /**
     * Thêm sản phẩm vào giỏ.
     */
    public function add(
        Product $product,
        int $quantity = 1
    ): CartItem {
        /*
         * Không cho thêm sản phẩm đã bị soft delete.
         * Thông thường route model binding đã chặn,
         * nhưng giữ kiểm tra này để an toàn.
         */
        if ($product->trashed()) {
            abort(
                404,
                'Sản phẩm không còn tồn tại.'
            );
        }

        /*
         * Không cho quantity âm hoặc bằng 0.
         */
        $quantity = max(1, $quantity);

        $item = $this->scoped()
            ->where(
                'product_id',
                $product->id
            )
            ->first();

        if ($item) {
            $item->increment(
                'quantity',
                $quantity
            );

            return $item->fresh();
        }

        return CartItem::create([
            'session_id' => Auth::check()
                ? null
                : Session::getId(),

            'user_id' => Auth::id(),

            'product_id' => $product->id,

            'quantity' => $quantity,
        ]);
    }

    /**
     * Cập nhật số lượng.
     */
    public function updateQuantity(
        int $cartItemId,
        int $quantity
    ): void {
        $item = $this->scoped()
            ->whereHas('product')
            ->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update([
            'quantity' => $quantity,
        ]);
    }

    /**
     * Xóa một CartItem khỏi giỏ.
     */
    public function remove(int $cartItemId): void
    {
        $this->scoped()
            ->where(
                'id',
                $cartItemId
            )
            ->delete();
    }

    /**
     * Merge giỏ hàng guest vào user sau login.
     */
    public function mergeGuestCartIntoUser(
        int $userId,
        string $sessionId
    ): void {
        $guestItems = CartItem::query()
            ->where(
                'session_id',
                $sessionId
            )
            ->get();

        foreach ($guestItems as $guestItem) {

            /*
             * Kiểm tra Product còn tồn tại hay không.
             *
             * Product::find() không lấy product đã soft delete.
             */
            $product = Product::find(
                $guestItem->product_id
            );

            /*
             * Nếu sản phẩm đã bị xóa mềm hoặc không tồn tại,
             * bỏ CartItem đó luôn.
             */
            if (!$product) {
                $guestItem->delete();

                continue;
            }

            $existing = CartItem::query()
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'product_id',
                    $guestItem->product_id
                )
                ->first();

            if ($existing) {
                $existing->increment(
                    'quantity',
                    $guestItem->quantity
                );

                $guestItem->delete();
            } else {
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                ]);
            }
        }
    }

    /**
     * Dọn các CartItem bị mồ côi do product đã bị soft delete
     * hoặc bị xóa khỏi database.
     */
    public function cleanupInvalidItems(): int
    {
        return $this->scoped()
            ->whereDoesntHave('product')
            ->delete();
    }
}