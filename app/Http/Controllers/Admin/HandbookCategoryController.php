<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HandbookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HandbookCategoryController extends Controller
{
    public function index()
    {
        $categories = HandbookCategory::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->get();

        $allCategories = HandbookCategory::orderBy('name', 'asc')->get();

        return view('admin.cam-nang.index', compact('categories', 'allCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'parent_id'   => 'nullable|exists:handbook_categories,id',
            'sort_order'  => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $count = 1;
        while (HandbookCategory::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        HandbookCategory::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'parent_id'   => $validated['parent_id'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Thêm danh mục cẩm nang thành công!');
    }

    public function destroy($id)
    {
        $category = HandbookCategory::findOrFail($id);
        $category->delete();

        return back()->with('success', 'Đã xóa danh mục thành công!');
    }
}