<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $products = $category->products()
            ->active()
            ->latest()
            ->paginate(24)
            ->through(fn ($product) => $product->toCardArray());

        return view('client.category', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
