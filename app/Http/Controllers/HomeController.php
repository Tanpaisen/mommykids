<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        // Each "section" on the homepage = one category + a few of its active products.
        $sections = Category::active()
            ->with(['products' => fn ($q) => $q->active()->latest()->limit(10)])
            ->get()
            ->filter(fn (Category $cat) => $cat->products->isNotEmpty())
            ->map(fn (Category $cat) => [
                'title' => $cat->name,
                'icon' => $cat->icon,
                'url' => route('category.show', $cat->slug),
                'products' => $cat->products->map->toCardArray(),
            ]);

        return view('client.home', [
            'sections' => $sections,
        ]);
    }
}
