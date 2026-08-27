<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->active()
            ->when($keyword !== '', fn ($q) => $q->where('name', 'like', "%{$keyword}%"))
            ->paginate(24)
            ->through(fn ($product) => $product->toCardArray());

        return view('client.search', [
            'keyword' => $keyword,
            'products' => $products,
        ]);
    }
}
