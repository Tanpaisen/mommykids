<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    /** GET /api/v1/categories */
    public function index()
    {
        return CategoryResource::collection(Category::active()->get());
    }

    /** GET /api/v1/categories/{category} */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }
}
