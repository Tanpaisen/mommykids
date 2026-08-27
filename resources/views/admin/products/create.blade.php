@extends('admin.layouts.app')

@section('page_title', 'Thêm sản phẩm')
@section('page_subtitle', 'Tạo sản phẩm mới và gắn danh mục, giai đoạn, thuộc tính')

@section('page_actions')
    <a
        href="{{ route('admin.products.index') }}"
        class="px-4 py-2 rounded-xl
               border border-admin-border
               text-sm text-ink hover:bg-admin-bg transition"
    >
        ← Quay lại
    </a>
@endsection

@section('content')

    <form
        action="{{ route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="grid grid-cols-1 xl:grid-cols-[1.6fr_1fr] gap-6">

            <div class="space-y-6">

                <div class="card">

                    <div class="border-b border-admin-border pb-4 mb-5">

                        <h2 class="font-semibold text-ink">
                            Thông tin cơ bản
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Tên, danh mục, icon, mô tả và trạng thái sản phẩm.
                        </p>

                    </div>


                    <div class="space-y-5">

                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Tên sản phẩm
                                <span class="text-coral">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                required
                                value="{{ old('name') }}"
                                placeholder="Ví dụ: Sữa Aptamil Profutura 900g"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                            @error('name')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div>

                                <label class="block mb-2 text-sm font-semibold">
                                    Danh mục
                                    <span class="text-coral">*</span>
                                </label>

                                <select
                                    name="category_id"
                                    required
                                    class="w-full border border-admin-border
                                           rounded-xl px-4 py-2.5
                                           outline-none focus:border-coral"
                                >

                                    <option value="">
                                        — Chọn danh mục —
                                    </option>

                                    @foreach ($categories as $category)

                                        <option
                                            value="{{ $category->id }}"
                                            @selected((string) old('category_id') === (string) $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>


                            <div>

                                <label class="block mb-2 text-sm font-semibold">
                                    Slug
                                </label>

                                <input
                                    type="text"
                                    name="slug"
                                    value="{{ old('slug') }}"
                                    placeholder="Để trống để tự tạo"
                                    class="w-full border border-admin-border
                                           rounded-xl px-4 py-2.5
                                           outline-none focus:border-coral"
                                >

                            </div>

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Icon sản phẩm
                            </label>

                            <input
                                type="text"
                                name="icon"
                                value="{{ old('icon') }}"
                                placeholder="Ví dụ: 🍼"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       text-2xl outline-none focus:border-coral"
                            >

                            <p class="mt-1.5 text-xs text-ink-soft">
                                Icon được dùng khi sản phẩm chưa có ảnh hoặc ảnh không tải được.
                                Ví dụ: 🍼 👶 🥣 🧴 🧸 🛒
                            </p>

                            @error('icon')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Mô tả
                            </label>

                            <textarea
                                name="description"
                                rows="6"
                                placeholder="Nhập mô tả sản phẩm..."
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >{{ old('description') }}</textarea>

                        </div>


                        <label
                            class="flex items-center justify-between
                                   border border-admin-border rounded-xl
                                   px-4 py-3 cursor-pointer"
                        >

                            <div>

                                <p class="text-sm font-semibold">
                                    Đang bán
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    Cho phép sản phẩm hiển thị ở phía khách hàng.
                                </p>

                            </div>

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', true))
                                class="w-5 h-5 accent-coral"
                            >

                        </label>

                    </div>

                </div>


                <div class="card">

                    <div class="border-b border-admin-border pb-4 mb-5">

                        <h2 class="font-semibold text-ink">
                            Giá & Tồn kho
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Thiết lập giá bán, khuyến mãi và số lượng tồn kho.
                        </p>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Giá bán
                                <span class="text-coral">*</span>
                            </label>

                            <input
                                type="number"
                                name="price"
                                required
                                min="0"
                                value="{{ old('price') }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Giá cũ
                            </label>

                            <input
                                type="number"
                                name="old_price"
                                min="0"
                                value="{{ old('old_price') }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Giảm giá %
                            </label>

                            <input
                                type="number"
                                name="discount_percent"
                                min="0"
                                max="100"
                                value="{{ old('discount_percent') }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Tồn kho
                                <span class="text-coral">*</span>
                            </label>

                            <input
                                type="number"
                                name="stock"
                                required
                                min="0"
                                value="{{ old('stock', 0) }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                        </div>

                    </div>

                </div>


                <div class="card">

                    <div class="border-b border-admin-border pb-4 mb-5">

                        <h2 class="font-semibold text-ink">
                            Hình ảnh
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Upload ảnh đại diện và nhiều ảnh chi tiết.
                        </p>

                    </div>


                    <div class="space-y-5">

                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Ảnh đại diện
                            </label>

                            <input
                                type="file"
                                name="image"
                                accept="image/*"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5 text-sm"
                            >

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Ảnh chi tiết
                            </label>

                            <input
                                type="file"
                                name="images[]"
                                multiple
                                accept="image/*"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5 text-sm"
                            >

                            <p class="mt-1 text-xs text-ink-soft">
                                Có thể chọn tối đa 8 ảnh.
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <div class="space-y-6">

                <div class="card">

                    <h2 class="font-semibold text-ink">
                        Giai đoạn phù hợp
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Chọn một hoặc nhiều giai đoạn.
                    </p>


                    <div class="mt-4 space-y-2 max-h-80 overflow-y-auto">

                        @forelse ($stages as $stage)

                            <label
                                class="flex items-start gap-3
                                       border border-admin-border
                                       rounded-xl px-3 py-3 cursor-pointer
                                       hover:bg-admin-bg"
                            >

                                <input
                                    type="checkbox"
                                    name="stage_ids[]"
                                    value="{{ $stage->id }}"
                                    @checked(in_array($stage->id, old('stage_ids', [])))
                                    class="mt-1 accent-coral"
                                >

                                <div>

                                    <p class="text-sm font-semibold text-ink">
                                        {{ $stage->icon }} {{ $stage->name }}
                                    </p>

                                    <p class="text-xs text-ink-soft mt-1">
                                        {{ $stage->age_from }} - {{ $stage->age_to }} tháng
                                    </p>

                                </div>

                            </label>

                        @empty

                            <p class="text-sm text-ink-soft">
                                Chưa có giai đoạn.
                            </p>

                        @endforelse

                    </div>

                </div>


                <div class="card">

                    <h2 class="font-semibold text-ink">
                        Thuộc tính / Tags
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Chọn các thuộc tính phù hợp với sản phẩm.
                    </p>


                    <div class="mt-4 space-y-2 max-h-96 overflow-y-auto">

                        @forelse ($tags as $tag)

                            <label
                                class="flex items-center justify-between gap-3
                                       border border-admin-border
                                       rounded-xl px-3 py-3 cursor-pointer
                                       hover:bg-admin-bg"
                            >

                                <div class="flex items-center gap-3">

                                    <input
                                        type="checkbox"
                                        name="tag_ids[]"
                                        value="{{ $tag->id }}"
                                        @checked(in_array($tag->id, old('tag_ids', [])))
                                        class="accent-coral"
                                    >

                                    <span class="text-sm font-medium">
                                        {{ $tag->name }}
                                    </span>

                                </div>


                                @if ($tag->type === 'attribute')

                                    <span class="text-xs text-blue-600 bg-blue-50 rounded-full px-2 py-1">
                                        Thuộc tính
                                    </span>

                                @elseif ($tag->type === 'stage')

                                    <span class="text-xs text-purple-600 bg-purple-50 rounded-full px-2 py-1">
                                        Giai đoạn
                                    </span>

                                @else

                                    <span class="text-xs text-amber-600 bg-amber-50 rounded-full px-2 py-1">
                                        Thương hiệu
                                    </span>

                                @endif

                            </label>

                        @empty

                            <p class="text-sm text-ink-soft">
                                Chưa có thuộc tính.
                            </p>

                        @endforelse

                    </div>

                </div>


                <div class="card">

                    <div class="flex flex-col gap-3">

                        <button
                            type="submit"
                            class="btn-primary w-full"
                        >
                            Lưu sản phẩm
                        </button>

                        <a
                            href="{{ route('admin.products.index') }}"
                            class="w-full text-center px-5 py-2.5
                                   rounded-xl border border-admin-border
                                   text-sm text-ink hover:bg-admin-bg"
                        >
                            Hủy
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </form>

@endsection