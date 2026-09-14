<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load([
            'category',
            'tags',
            'stages',
        ]);

        $brand = $product->tags
            ->firstWhere('type', 'brand');

        $attributes = $product->tags
            ->where('type', 'attribute')
            ->values();

        $related = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map
            ->toCardArray();

        return view('client.product', [
            'product' => $product,
            'brand' => $brand,
            'attributes' => $attributes,
            'related' => $related,
        ]);
    }
}
