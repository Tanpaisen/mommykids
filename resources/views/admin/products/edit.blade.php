@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa sản phẩm')
@section('page_subtitle', 'Cập nhật thông tin "' . $product->name . '"')

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
        action="{{ route('admin.products.update', $product) }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @csrf
        @method('PUT')


        <div class="grid grid-cols-1 xl:grid-cols-[1.6fr_1fr] gap-6">

            {{-- LEFT --}}
            <div class="space-y-6">

                {{-- THÔNG TIN --}}
                <div class="card">

                    <div class="border-b border-admin-border pb-4 mb-5">

                        <h2 class="font-semibold text-ink">
                            Thông tin cơ bản
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Cập nhật nội dung và trạng thái sản phẩm.
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
                                value="{{ old('name', $product->name) }}"
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

                                    @foreach ($categories as $category)

                                        <option
                                            value="{{ $category->id }}"
                                            @selected(
                                                (string) old('category_id', $product->category_id)
                                                ===
                                                (string) $category->id
                                            )
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
                                    value="{{ old('slug', $product->slug) }}"
                                    class="w-full border border-admin-border
                                           rounded-xl px-4 py-2.5
                                           outline-none focus:border-coral"
                                >

                                @error('slug')
                                    <p class="mt-1.5 text-xs text-red-500">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        <div>

                            <label class="block mb-2 text-sm font-semibold">
                                Mô tả
                            </label>

                            <textarea
                                name="description"
                                rows="6"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-3
                                       outline-none focus:border-coral"
                            >{{ old('description', $product->description) }}</textarea>

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
                                    Cho phép sản phẩm hiển thị phía khách hàng.
                                </p>

                            </div>

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $product->is_active))
                                class="w-5 h-5 accent-coral"
                            >

                        </label>

                    </div>

                </div>


                {{-- PRICE --}}
                <div class="card">

                    <div class="border-b border-admin-border pb-4 mb-5">

                        <h2 class="font-semibold text-ink">
                            Giá & Tồn kho
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Cập nhật giá và số lượng còn lại.
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
                                min="0"
                                required
                                value="{{ old('price', $product->price) }}"
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
                                value="{{ old('old_price', $product->old_price) }}"
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
                                value="{{ old('discount_percent', $product->discount_percent) }}"
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
                                min="0"
                                required
                                value="{{ old('stock', $product->stock) }}"
                                class="w-full border border-admin-border
                                       rounded-xl px-4 py-2.5
                                       outline-none focus:border-coral"
                            >

                        </div>

                    </div>

                </div>


                {{-- IMAGE --}}
                <div class="card">

                    <div class="border-b border-admin-border pb-4 mb-5">

                        <h2 class="font-semibold text-ink">
                            Hình ảnh
                        </h2>

                        <p class="text-sm text-ink-soft mt-1">
                            Thay ảnh đại diện hoặc thêm ảnh chi tiết.
                        </p>

                    </div>


                    {{-- Main --}}
                    <div>

                        <label class="block mb-2 text-sm font-semibold">
                            Ảnh đại diện hiện tại
                        </label>

                        @if ($product->image)

                            <img
                                src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image) }}"
                                alt="{{ $product->name }}"
                                class="w-32 h-32 object-cover
                                       rounded-xl border border-admin-border mb-3"
                            >

                        @else

                            <p class="text-sm text-ink-soft mb-3">
                                Chưa có ảnh đại diện.
                            </p>

                        @endif


                        <input
                            type="file"
                            name="image"
                            accept="image/*"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-2.5 text-sm"
                        >

                    </div>


                    {{-- Gallery --}}
                    <div class="mt-6">

                        <label class="block mb-3 text-sm font-semibold">
                            Ảnh chi tiết hiện tại
                        </label>


                        @if (!empty($product->images))

                            <div class="flex flex-wrap gap-3 mb-4">

                                @foreach ($product->images as $image)

                                    <img
                                        src="{{ asset('storage/' . $image) }}"
                                        alt="{{ $product->name }}"
                                        class="w-20 h-20 object-cover
                                               rounded-xl border border-admin-border"
                                    >

                                @endforeach

                            </div>

                        @else

                            <p class="text-sm text-ink-soft mb-3">
                                Chưa có ảnh chi tiết.
                            </p>

                        @endif


                        <input
                            type="file"
                            name="images[]"
                            multiple
                            accept="image/*"
                            class="w-full border border-admin-border
                                   rounded-xl px-4 py-2.5 text-sm"
                        >

                        <p class="mt-1 text-xs text-ink-soft">
                            Ảnh mới sẽ được thêm vào gallery hiện tại.
                        </p>

                    </div>

                </div>

            </div>



            {{-- RIGHT --}}
            <div class="space-y-6">

                {{-- STAGES --}}
                <div class="card">

                    <h2 class="font-semibold text-ink">
                        Giai đoạn phù hợp
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Chọn các giai đoạn phù hợp.
                    </p>


                    @php
                        $selectedStageIds = old(
                            'stage_ids',
                            $product->stages->pluck('id')->all()
                        );
                    @endphp


                    <div class="mt-4 space-y-2 max-h-80 overflow-y-auto">

                        @foreach ($stages as $stage)

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
                                    @checked(in_array($stage->id, $selectedStageIds))
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

                        @endforeach

                    </div>

                </div>


                {{-- TAGS --}}
                <div class="card">

                    <h2 class="font-semibold text-ink">
                        Thuộc tính / Tags
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Gắn các thuộc tính phù hợp.
                    </p>


                    @php
                        $selectedTagIds = old(
                            'tag_ids',
                            $product->tags->pluck('id')->all()
                        );
                    @endphp


                    <div class="mt-4 space-y-2 max-h-96 overflow-y-auto">

                        @foreach ($tags as $tag)

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
                                        @checked(in_array($tag->id, $selectedTagIds))
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

                        @endforeach

                    </div>

                </div>


                {{-- ACTION --}}
                <div class="card">

                    <div class="flex flex-col gap-3">

                        <button
                            type="submit"
                            class="btn-primary w-full"
                        >
                            Lưu thay đổi
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