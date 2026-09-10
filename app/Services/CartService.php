<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CartService
{
    const COOKIE_NAME = 'cart_uuid';
    const COOKIE_DAYS = 30;

    // ── Lấy hoặc tạo cart hiện tại ──────────────────────────────
    public function getCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id(), 'status' => 'active'],
                ['uuid' => (string) Str::uuid()]
            );
        }

        $uuid = Cookie::get(self::COOKIE_NAME);

        if ($uuid) {
            $cart = Cart::where('uuid', $uuid)
                ->where('status', 'active')
                ->whereNull('user_id')
                ->first();

            if ($cart) return $cart;
        }

        $cart = Cart::create([
            'uuid'    => (string) Str::uuid(),
            'user_id' => null,
            'status'  => 'active',
        ]);

        Cookie::queue(self::COOKIE_NAME, $cart->uuid, 60 * 24 * self::COOKIE_DAYS);

        return $cart;
    }

    // ── Danh sách sản phẩm hợp lệ trong giỏ ────────────────────
    public function items(): Collection
    {
        return $this->getCart()
            ->items()
            ->whereHas('product')
            ->with('product.category')
            ->get();
    }

    // ── Tổng số lượng ───────────────────────────────────────────
    public function count(): int
    {
        return (int) $this->getCart()
            ->items()
            ->whereHas('product')
            ->sum('quantity');
    }

    // ── Tổng tiền ───────────────────────────────────────────────
    public function total(): int
    {
        return (int) $this->items()->sum(
            fn (CartItem $item) => $item->quantity * ($item->product?->price ?? 0)
        );
    }

    // ── Thêm sản phẩm ───────────────────────────────────────────
    public function add(Product $product, int $quantity = 1): void
    {
        if ($product->trashed()) {
            abort(404, 'Sản phẩm không còn tồn tại.');
        }

        $quantity = max(1, $quantity);
        $cart = $this->getCart();

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'price'      => $product->price,
            ]);
        }
    }

    // ── Cập nhật số lượng ───────────────────────────────────────
    public function updateQuantity(string|int $cartItemId, int $quantity): void
    {
        $item = $this->getCart()
            ->items()
            ->whereHas('product')
            ->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();
            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    // ── Xóa item ────────────────────────────────────────────────
    public function remove(string|int $cartItemId): void
    {
        $this->getCart()
            ->items()
            ->where('id', $cartItemId)
            ->delete();
    }

    // ── Merge giỏ guest vào user sau login ──────────────────────
    public function mergeGuestCart(string|int $userId): void
    {
        $uuid = Cookie::get(self::COOKIE_NAME);
        if (!$uuid) return;

        $guestCart = Cart::where('uuid', $uuid)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->first();

        if (!$guestCart) return;

        $userCart = Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            ['uuid' => (string) Str::uuid()]
        );

        foreach ($guestCart->items as $guestItem) {
            $product = Product::find($guestItem->product_id);

            if (!$product) {
                $guestItem->delete();
                continue;
            }

            $userItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($userItem) {
                $userItem->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    // ── Dọn item không hợp lệ ───────────────────────────────────
    public function cleanupInvalidItems(): int
    {
        return $this->getCart()
            ->items()
            ->whereDoesntHave('product')
            ->delete();
    }
}