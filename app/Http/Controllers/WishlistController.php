<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $items = WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->with('product')
            ->latest()
            ->paginate(20);

        return view('client.wishlist.index', compact('items'));
    }

    public function store(
        Request $request,
        Product $product
    ): JsonResponse|RedirectResponse {
        WishlistItem::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        $count = WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'wishlisted' => true,
                'count' => $count,
                'message' => 'Đã thêm sản phẩm vào yêu thích.',
            ]);
        }

        return back()->with(
            'success',
            'Đã thêm sản phẩm vào yêu thích.'
        );
    }

    public function destroy(
        Request $request,
        Product $product
    ): JsonResponse|RedirectResponse {
        WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        $count = WishlistItem::query()
            ->where('user_id', $request->user()->id)
            ->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'wishlisted' => false,
                'count' => $count,
                'message' => 'Đã bỏ sản phẩm khỏi yêu thích.',
            ]);
        }

        return back()->with(
            'success',
            'Đã bỏ sản phẩm khỏi yêu thích.'
        );
    }
}
