<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/v1/products
     * Query params: category (slug), q (keyword search), per_page
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with('category')
            ->active()
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->query('category')));
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('q') . '%');
            })
            ->latest()
            ->paginate($request->integer('per_page', 24));

        return ProductResource::collection($products);
    }

    /** GET /api/v1/products/{product} */
    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }
}
