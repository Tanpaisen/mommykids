<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
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

                'type' => [
                    'required',
                    'in:attribute,stage,brand',
                ],
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên thuộc tính.',

                'type.required' =>
                    'Vui lòng chọn loại thuộc tính.',

                'type.in' =>
                    'Loại thuộc tính không hợp lệ.',
            ]
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra cả tag trong thùng rác.
         */
        if (
            Tag::withTrashed()
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
                        'Slug này đã tồn tại.',
                ]);
        }

        Tag::create($validated);

        return redirect()
            ->route(
                'admin.categories.index',
                [
                    'tab' => 'tags',
                ]
            )
            ->with(
                'success',
                'Thêm thuộc tính thành công.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Tag $tag
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

                'type' => [
                    'required',
                    'in:attribute,stage,brand',
                ],
            ],
            [
                'name.required' =>
                    'Vui lòng nhập tên thuộc tính.',

                'type.required' =>
                    'Vui lòng chọn loại thuộc tính.',

                'type.in' =>
                    'Loại thuộc tính không hợp lệ.',
            ]
        );

        $validated['slug'] = $this->makeSlug(
            $validated['slug'] ?? null,
            $validated['name']
        );

        /*
         * Kiểm tra slug kể cả tag đã xóa mềm.
         */
        $slugExists = Tag::withTrashed()
            ->where(
                'slug',
                $validated['slug']
            )
            ->where(
                'id',
                '!=',
                $tag->id
            )
            ->exists();

        if ($slugExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' =>
                        'Slug này đã tồn tại.',
                ]);
        }

        $tag->update($validated);

        return redirect()
            ->route(
                'admin.categories.index',
                [
                    'tab' => 'tags',
                ]
            )
            ->with(
                'success',
                'Cập nhật thuộc tính thành công.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Xóa mềm
    |--------------------------------------------------------------------------
    */

    public function destroy(Tag $tag)
    {
        $tag->update([
            'deleted_by' => auth()->id(),
            'restored_by' => null,
            'restored_at' => null,
        ]);

        /*
         * Không detach product_tag.
         * Khi restore thì liên kết sản phẩm vẫn còn.
         */
        $tag->delete();

        return redirect()
            ->route(
                'admin.categories.index',
                [
                    'tab' => 'tags',
                ]
            )
            ->with(
                'success',
                'Thuộc tính đã được chuyển vào thùng rác.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Thùng rác thuộc tính
    |--------------------------------------------------------------------------
    */

    public function trash(Request $request)
    {
        $query = Tag::onlyTrashed();

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

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        $tags = $query
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.tags.trash',
            compact('tags')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Khôi phục
    |--------------------------------------------------------------------------
    */

    public function restore(string $id)
    {
        $tag = Tag::onlyTrashed()
            ->findOrFail($id);

        /*
         * Kiểm tra slug trước khi restore.
         */
        $slugExists = Tag::query()
            ->where(
                'slug',
                $tag->slug
            )
            ->exists();

        if ($slugExists) {
            return redirect()
                ->route(
                    'admin.tags.trash'
                )
                ->with(
                    'error',
                    'Không thể khôi phục vì slug thuộc tính đã được sử dụng.'
                );
        }

        $tag->restore();

        $tag->update([
            'restored_by' => auth()->id(),
            'restored_at' => now(),
        ]);

        return redirect()
            ->route('admin.tags.trash')
            ->with(
                'success',
                'Khôi phục thuộc tính thành công.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Xóa vĩnh viễn
    |--------------------------------------------------------------------------
    */

    public function forceDelete(string $id)
    {
        $tag = Tag::onlyTrashed()
            ->findOrFail($id);

        /*
         * Chỉ xóa pivot khi force delete.
         */
        $tag->products()->detach();

        $tag->forceDelete();

        return redirect()
            ->route('admin.tags.trash')
            ->with(
                'success',
                'Đã xóa vĩnh viễn thuộc tính.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Helper
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
}