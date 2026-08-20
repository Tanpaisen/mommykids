<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /** Scope query to the current visitor: logged-in user_id or guest session_id. */
    protected function scoped()
    {
        return Auth::check()
            ? CartItem::query()->where('user_id', Auth::id())
            : CartItem::query()->where('session_id', Session::getId());
    }

    public function items(): Collection
    {
        return $this->scoped()->with('product.category')->get();
    }

    public function count(): int
    {
        return (int) $this->scoped()->sum('quantity');
    }

    public function total(): int
    {
        return $this->items()->sum(fn (CartItem $item) => $item->quantity * $item->product->price);
    }

    public function add(Product $product, int $quantity = 1): CartItem
    {
        $item = $this->scoped()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            return $item->fresh();
        }

        return CartItem::create([
            'session_id' => Auth::check() ? null : Session::getId(),
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        $item = $this->scoped()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();
            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(int $cartItemId): void
    {
        $this->scoped()->where('id', $cartItemId)->delete();
    }

    /** Merge guest cart into the user's cart right after login. Call from your LoginController/Fortify action. */
    public function mergeGuestCartIntoUser(int $userId, string $sessionId): void
    {
        $guestItems = CartItem::query()->where('session_id', $sessionId)->get();

        foreach ($guestItems as $guestItem) {
            $existing = CartItem::query()
                ->where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $userId, 'session_id' => null]);
            }
        }
    }
}
