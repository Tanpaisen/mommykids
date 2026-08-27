<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * Cart endpoints for external/stateless clients (mobile app, SPA) authenticated via Sanctum tokens.
 * The server-rendered storefront (Blade) uses the session-based cart endpoints registered in
 * routes/web.php under /api/cart instead — see App\Http\Controllers\CartController.
 */
class CartController extends Controller
{
    public function __construct(protected CartService $cart)
    {
    }

    /** GET /api/v1/cart */
    public function index()
    {
        return response()->json([
            'items' => $this->cart->items()->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'product' => new ProductResource($item->product),
            ]),
            'count' => $this->cart->count(),
            'total' => $this->cart->total(),
        ]);
    }

    /** POST /api/v1/cart  body: { product_id, quantity } */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $item = $this->cart->add($product, $data['quantity'] ?? 1);

        return response()->json([
            'cart_item_id' => $item->id,
            'count' => $this->cart->count(),
            'total' => $this->cart->total(),
        ], 201);
    }

    /** PATCH /api/v1/cart/{cartItem}  body: { quantity } */
    public function update(Request $request, int $cartItem)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0']]);
        $this->cart->updateQuantity($cartItem, $data['quantity']);

        return response()->json([
            'count' => $this->cart->count(),
            'total' => $this->cart->total(),
        ]);
    }

    /** DELETE /api/v1/cart/{cartItem} */
    public function destroy(int $cartItem)
    {
        $this->cart->remove($cartItem);

        return response()->json([
            'count' => $this->cart->count(),
            'total' => $this->cart->total(),
        ]);
    }
}
