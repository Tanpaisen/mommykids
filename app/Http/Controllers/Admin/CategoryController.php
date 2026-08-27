<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Danh mục
        |--------------------------------------------------------------------------
        */

        $categoryQuery = Category::query();

        if ($request->filled('category_search')) {
            $search = trim($request->category_search);

            $categoryQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('category_status')) {
            if ($request->category_status === 'active') {
                $categoryQuery->where('is_active', true);
            }

            if ($request->category_status === 'inactive') {
                $categoryQuery->where('is_active', false);
            }
        }

        $categories = $categoryQuery
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(
                10,
                ['*'],
                'category_page'
            )
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Tag
        |--------------------------------------------------------------------------
        */

        $tagQuery = Tag::query();

        if ($request->filled('tag_search')) {
            $search = trim($request->tag_search);

            $tagQuery->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('tag_type')) {
            $tagQuery->where(
                'type',
                $request->tag_type
            );
        }

        $tags = $tagQuery
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(
                10,
                ['*'],
                'tag_page'
            )
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Số lượng thùng rác riêng
        |--------------------------------------------------------------------------
        */

        $categoryTrashCount =
            Category::onlyTrashed()->count();

        $tagTrashCount =
            Tag::onlyTrashed()->count();


        return view(
            'admin.categories.index',
            compact(
                'categories',
                'tags',
                'categoryTrashCount',
                'tagTrashCount'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'admin.categories.create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'icon' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'max:2048',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'is_active' => [
                    'nullable',
                ],
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên danh mục.',

                'image.image' =>
                    'Ảnh tải lên không hợp lệ.',

                'image.max' =>
                    'Ảnh không được vượt quá 2MB.',

                'sort_order.integer' =>
                    'Thứ tự hiển thị phải là số nguyên.',

                'sort_order.min' =>
                    'Thứ tự hiển thị không được nhỏ hơn 0.',
            ]
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra cả danh mục đã xóa mềm.
         */
        if (
            Category::withTrashed()
                ->where(
                    'slug',
                    $validated['slug']
                )
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug danh mục đã tồn tại.',
                ]);
        }

        $validated['sort_order'] =
            $validated['sort_order'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request
                ->file('image')
                ->store(
                    'categories',
                    'public'
                );
        }

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Thêm danh mục thành công.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(Category $category)
    {
        return view(
            'admin.categories.edit',
            compact('category')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Category $category
    ) {
        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'icon' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'image' => [
                    'nullable',
                    'image',
                    'max:2048',
                ],

                'sort_order' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],

                'is_active' => [
                    'nullable',
                ],
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên danh mục.',

                'image.image' =>
                    'Ảnh tải lên không hợp lệ.',

                'image.max' =>
                    'Ảnh không được vượt quá 2MB.',

                'sort_order.integer' =>
                    'Thứ tự hiển thị phải là số nguyên.',

                'sort_order.min' =>
                    'Thứ tự hiển thị không được nhỏ hơn 0.',
            ]
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra slug kể cả record trong thùng rác.
         */
        $slugExists = Category::withTrashed()
            ->where(
                'slug',
                $validated['slug']
            )
            ->where(
                'id',
                '!=',
                $category->id
            )
            ->exists();

        if ($slugExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug danh mục đã tồn tại.',
                ]);
        }

        $validated['sort_order'] =
            $validated['sort_order'] ?? 0;

        $validated['is_active'] =
            $request->boolean('is_active');

        /*
        |--------------------------------------------------------------------------
        | Thay ảnh
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $this->deleteLocalImage(
                $category->image
            );

            $validated['image'] = $request
                ->file('image')
                ->store(
                    'categories',
                    'public'
                );
        }

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Cập nhật danh mục thành công.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Xóa mềm danh mục
    |--------------------------------------------------------------------------
    */

    public function destroy(Category $category)
    {
        /*
         * Không cho xóa danh mục đang có sản phẩm.
         */
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'Không thể chuyển danh mục đang có sản phẩm vào thùng rác.'
                );
        }

        /*
         * Không xóa ảnh khi soft delete.
         */
        $category->update([
            'deleted_by' => auth()->id(),
            'restored_by' => null,
            'restored_at' => null,
        ]);

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with(
                'success',
                'Danh mục đã được chuyển vào thùng rác.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Thùng rác danh mục
    |--------------------------------------------------------------------------
    */

    public function trash(Request $request)
    {
        $query = Category::onlyTrashed();

        if ($request->filled('search')) {
            $search = trim(
                $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q
                        ->where(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'slug',
                            'like',
                            '%' . $search . '%'
                        );
                }
            );
        }

        $categories = $query
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.categories.trash',
            compact('categories')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Khôi phục danh mục
    |--------------------------------------------------------------------------
    */

    public function restore(string $id)
    {
        $category = Category::onlyTrashed()
            ->findOrFail($id);

        /*
         * Kiểm tra slug trước khi restore.
         */
        $slugExists = Category::query()
            ->where(
                'slug',
                $category->slug
            )
            ->exists();

        if ($slugExists) {
            return redirect()
                ->route(
                    'admin.categories.trash'
                )
                ->with(
                    'error',
                    'Không thể khôi phục vì slug danh mục đã được sử dụng.'
                );
        }

        $category->restore();

        $category->update([
            'restored_by' => auth()->id(),
            'restored_at' => now(),
        ]);

        return redirect()
            ->route(
                'admin.categories.trash'
            )
            ->with(
                'success',
                'Khôi phục danh mục thành công.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Xóa vĩnh viễn danh mục
    |--------------------------------------------------------------------------
    */

    public function forceDelete(string $id)
    {
        $category = Category::onlyTrashed()
            ->findOrFail($id);

        /*
         * Kiểm tra lại để đảm bảo không còn sản phẩm.
         */
        if (
            $category
                ->products()
                ->withTrashed()
                ->exists()
        ) {
            return redirect()
                ->route(
                    'admin.categories.trash'
                )
                ->with(
                    'error',
                    'Không thể xóa vĩnh viễn danh mục vẫn còn sản phẩm.'
                );
        }

        /*
         * Lúc này mới xóa ảnh thật.
         */
        $this->deleteLocalImage(
            $category->image
        );

        $category->forceDelete();

        return redirect()
            ->route(
                'admin.categories.trash'
            )
            ->with(
                'success',
                'Đã xóa vĩnh viễn danh mục.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function makeSlug(
        ?string $slug,
        string $name
    ): string {
        return Str::slug(
            filled($slug)
                ? $slug
                : $name
        );
    }

    private function deleteLocalImage(
        ?string $image
    ): void {
        if (!$image) {
            return;
        }

        if (
            Str::startsWith(
                $image,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return;
        }

        Storage::disk('public')
            ->delete($image);
    }
}