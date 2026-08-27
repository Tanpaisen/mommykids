<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tags,slug'],
            'type' => ['required', 'in:attribute,stage,brand'],
        ], [
            'name.required' => 'Vui lòng nhập tên thuộc tính.',
            'slug.unique' => 'Slug này đã tồn tại.',
            'type.required' => 'Vui lòng chọn loại thuộc tính.',
            'type.in' => 'Loại thuộc tính không hợp lệ.',
        ]);

        $validated['slug'] = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        Tag::create($validated);

        return redirect()
            ->route('admin.categories.index', ['tab' => 'tags'])
            ->with('success', 'Thêm thuộc tính thành công.');
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:tags,slug,' . $tag->id
            ],
            'type' => ['required', 'in:attribute,stage,brand'],
        ], [
            'name.required' => 'Vui lòng nhập tên thuộc tính.',
            'slug.unique' => 'Slug này đã tồn tại.',
            'type.required' => 'Vui lòng chọn loại thuộc tính.',
            'type.in' => 'Loại thuộc tính không hợp lệ.',
        ]);

        $validated['slug'] = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $tag->update($validated);

        return redirect()
            ->route('admin.categories.index', ['tab' => 'tags'])
            ->with('success', 'Cập nhật thuộc tính thành công.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()
            ->route('admin.categories.index', ['tab' => 'tags'])
            ->with('success', 'Xóa thuộc tính thành công.');
    }
}