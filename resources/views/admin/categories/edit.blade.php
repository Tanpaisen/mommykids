@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa danh mục')
@section('page_subtitle', 'Cập nhật danh mục "' . $category->name . '"')

@section('page_actions')
    <a
        href="{{ route('admin.categories.index') }}"
        class="px-4 py-2 rounded-xl border border-admin-border
               text-sm text-ink hover:bg-admin-bg transition"
    >
        ← Quay lại
    </a>
@endsection

@section('content')

    <form
        action="{{ route('admin.categories.update', $category) }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="card">

            <div class="border-b border-admin-border pb-4 mb-5">
                <h2 class="font-semibold text-ink text-base">
                    Thông tin danh mục
                </h2>

                <p class="text-sm text-ink-soft mt-1">
                    Thay đổi thông tin và trạng thái của danh mục.
                </p>
            </div>

            <div class="space-y-5">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-2">
                            Tên danh mục
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $category->name) }}"
                            required
                            class="w-full border border-admin-border rounded-xl px-4 py-2.5
                                   text-sm outline-none focus:border-coral"
                        >

                        @error('name')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-2">
                            Slug
                        </label>

                        <input
                            type="text"
                            name="slug"
                            value="{{ old('slug', $category->slug) }}"
                            class="w-full border border-admin-border rounded-xl px-4 py-2.5
                                   text-sm outline-none focus:border-coral"
                        >

                        @error('slug')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-2">
                            Icon
                        </label>

                        <input
                            type="text"
                            name="icon"
                            value="{{ old('icon', $category->icon) }}"
                            class="w-full border border-admin-border rounded-xl px-4 py-2.5
                                   text-sm outline-none focus:border-coral"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ink mb-2">
                            Thứ tự hiển thị
                        </label>

                        <input
                            type="number"
                            name="sort_order"
                            value="{{ old('sort_order', $category->sort_order) }}"
                            min="0"
                            class="w-full border border-admin-border rounded-xl px-4 py-2.5
                                   text-sm outline-none focus:border-coral"
                        >
                    </div>

                </div>

                <div>
                    <label class="block text-sm font-semibold text-ink mb-2">
                        Ảnh danh mục
                    </label>

                    @if ($category->image)
                        <div class="mb-3">
                            <img
                                src="{{ asset('storage/' . $category->image) }}"
                                alt="{{ $category->name }}"
                                class="w-24 h-24 object-cover rounded-xl border border-admin-border"
                            >
                        </div>
                    @endif

                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        class="w-full border border-admin-border rounded-xl px-4 py-2.5
                               text-sm outline-none focus:border-coral"
                    >

                    <p class="mt-1.5 text-xs text-ink-soft">
                        Chọn ảnh mới nếu muốn thay ảnh hiện tại.
                    </p>

                    @error('image')
                        <p class="mt-1.5 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <label
                    class="flex items-center justify-between border border-admin-border
                           rounded-xl px-4 py-3 cursor-pointer"
                >
                    <div>
                        <p class="font-medium text-sm text-ink">
                            Hiển thị danh mục
                        </p>
                        <p class="text-xs text-ink-soft mt-0.5">
                            Cho phép danh mục xuất hiện trên website.
                        </p>
                    </div>

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="w-5 h-5 accent-coral"
                        @checked(old('is_active', $category->is_active))
                    >
                </label>

            </div>

            <div class="flex justify-end gap-3 border-t border-admin-border mt-6 pt-5">

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-admin-border
                           text-sm text-ink hover:bg-admin-bg transition"
                >
                    Hủy
                </a>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Lưu thay đổi
                </button>

            </div>

        </div>
    </form>

@endsection