<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    /** GET /gio-hang — full cart page */
    public function index()
    {
        return view('client.cart', [
            'items' => $this->cart->items(),
            'total' => $this->cart->total(),
        ]);
    }

    /**
     * POST /api/cart  — called via fetch() from product-card.blade.php
     * body: { product_id, quantity }
     * returns JSON so the header/floating cart badge can update without a full reload.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $item = $this->cart->add($product, $data['quantity'] ?? 1);

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng',
            'cart_item_id' => $item->id,
            'cart_count' => $this->cart->count(),
            'cart_total' => $this->cart->total(),
        ]);
    }

    /** PATCH /api/cart/{cartItem} — body: { quantity } */
    public function update(Request $request, int $cartItem)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0']]);

        $this->cart->updateQuantity($cartItem, $data['quantity']);

        return response()->json([
            'cart_count' => $this->cart->count(),
            'cart_total' => $this->cart->total(),
        ]);
    }

    /** DELETE /api/cart/{cartItem} */
    public function destroy(int $cartItem)
    {
        $this->cart->remove($cartItem);

        return response()->json([
            'cart_count' => $this->cart->count(),
            'cart_total' => $this->cart->total(),
        ]);
    }
}
