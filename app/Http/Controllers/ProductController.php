<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load('category');

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(6)
            ->get()
            ->map->toCardArray();

        return view('client.product', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}
