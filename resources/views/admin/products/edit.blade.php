@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa sản phẩm')

@section('page_subtitle')
    Cập nhật thông tin "{{ $product->name }}"
@endsection

@section('page_actions')
    <a
        href="{{ route('admin.products.index') }}"
        class="px-4 py-2.5 rounded-xl
               border border-admin-border bg-white
               text-sm text-ink hover:bg-admin-bg transition"
    >
        ← Quay lại
    </a>
@endsection


@section('content')

@php
    $selectedStageIds = collect(
        old(
            'stage_ids',
            $product->stages->pluck('id')->all()
        )
    )->map(fn ($id) => (string) $id)->all();

    $selectedTagIds = collect(
        old(
            'tag_ids',
            $product->tags->pluck('id')->all()
        )
    )->map(fn ($id) => (string) $id)->all();
@endphp


<form
    action="{{ route('admin.products.update', $product) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')


    <div class="grid grid-cols-1 xl:grid-cols-[1.55fr_0.95fr] gap-6">

        {{-- =========================================================
            LEFT
        ========================================================== --}}
        <div class="space-y-6">

            {{-- =====================================================
                THÔNG TIN CƠ BẢN
            ====================================================== --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">

                    <h2 class="text-base font-semibold text-ink">
                        Thông tin cơ bản
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Cập nhật thông tin và trạng thái sản phẩm.
                    </p>

                </div>


                <div class="space-y-5">

                    {{-- TÊN --}}
                    <div>

                        <label class="block mb-2 text-sm font-semibold text-ink">
                            Tên sản phẩm
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            required
                            value="{{ old('name', $product->name) }}"
                            class="w-full
                                   border border-admin-border
                                   rounded-xl px-4 py-3
                                   bg-white text-ink
                                   outline-none
                                   focus:border-coral
                                   focus:ring-2
                                   focus:ring-coral/10"
                        >

                        @error('name')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- CATEGORY + SLUG --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- CATEGORY --}}
                        <div>

                            <label class="block mb-2 text-sm font-semibold text-ink">
                                Danh mục
                                <span class="text-coral">*</span>
                            </label>

                            <select
                                name="category_id"
                                required
                                class="w-full
                                       border border-admin-border
                                       rounded-xl px-4 py-3
                                       bg-white text-ink
                                       outline-none
                                       focus:border-coral
                                       focus:ring-2
                                       focus:ring-coral/10"
                            >

                                @foreach ($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        @selected(
                                            (string) old(
                                                'category_id',
                                                $product->category_id
                                            )
                                            ===
                                            (string) $category->id
                                        )
                                    >
                                        {{ $category->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('category_id')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- SLUG --}}
                        <div>

                            <label class="block mb-2 text-sm font-semibold text-ink">
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                value="{{ old('slug', $product->slug) }}"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl px-4 py-3
                                       bg-white text-ink
                                       outline-none
                                       focus:border-coral
                                       focus:ring-2
                                       focus:ring-coral/10"
                            >

                            @error('slug')
                                <p class="mt-1.5 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>


                    {{-- DESCRIPTION --}}
                    <div>

                        <label class="block mb-2 text-sm font-semibold text-ink">
                            Mô tả
                        </label>

                        <textarea
                            name="description"
                            rows="6"
                            placeholder="Nhập mô tả sản phẩm..."
                            class="w-full
                                   border border-admin-border
                                   rounded-xl px-4 py-3
                                   bg-white text-ink
                                   outline-none
                                   resize-y
                                   focus:border-coral
                                   focus:ring-2
                                   focus:ring-coral/10"
                        >{{ old('description', $product->description) }}</textarea>

                        @error('description')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- STATUS --}}
                    <div
                        class="flex items-center justify-between gap-4
                               border border-admin-border
                               rounded-xl px-4 py-4 bg-white"
                    >

                        <div>

                            <p class="text-sm font-semibold text-ink">
                                Trạng thái sản phẩm
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                Cho phép sản phẩm hiển thị phía khách hàng.
                            </p>

                        </div>


                        <label class="flex items-center gap-2 cursor-pointer">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(
                                    old(
                                        'is_active',
                                        $product->is_active
                                    )
                                )
                                class="w-5 h-5 accent-coral"
                            >

                            <span class="text-sm font-medium text-ink">
                                Đang bán
                            </span>

                        </label>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                GIÁ & TỒN KHO
            ====================================================== --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">

                    <h2 class="text-base font-semibold text-ink">
                        Giá & Tồn kho
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Quản lý giá bán, khuyến mãi và số lượng tồn kho.
                    </p>

                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- PRICE --}}
                    <div>

                        <label class="block mb-2 text-sm font-semibold">
                            Giá bán
                            <span class="text-coral">*</span>
                        </label>

                        <div class="relative">

                            <input
                                type="number"
                                name="price"
                                min="0"
                                required
                                value="{{ old('price', $product->price) }}"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl px-4 py-3 pr-12
                                       outline-none
                                       focus:border-coral"
                            >

                            <span
                                class="absolute right-4 top-1/2
                                       -translate-y-1/2
                                       text-sm text-ink-soft"
                            >
                                đ
                            </span>

                        </div>

                        @error('price')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- OLD PRICE --}}
                    <div>

                        <label class="block mb-2 text-sm font-semibold">
                            Giá cũ
                        </label>

                        <div class="relative">

                            <input
                                type="number"
                                name="old_price"
                                min="0"
                                value="{{ old(
                                    'old_price',
                                    $product->old_price
                                ) }}"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl px-4 py-3 pr-12
                                       outline-none
                                       focus:border-coral"
                            >

                            <span
                                class="absolute right-4 top-1/2
                                       -translate-y-1/2
                                       text-sm text-ink-soft"
                            >
                                đ
                            </span>

                        </div>

                    </div>


                    {{-- DISCOUNT --}}
                    <div>

                        <label class="block mb-2 text-sm font-semibold">
                            Giảm giá
                        </label>

                        <div class="relative">

                            <input
                                type="number"
                                name="discount_percent"
                                min="0"
                                max="100"
                                value="{{ old(
                                    'discount_percent',
                                    $product->discount_percent
                                ) }}"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl px-4 py-3 pr-12
                                       outline-none
                                       focus:border-coral"
                            >

                            <span
                                class="absolute right-4 top-1/2
                                       -translate-y-1/2
                                       text-sm text-ink-soft"
                            >
                                %
                            </span>

                        </div>

                    </div>


                    {{-- STOCK --}}
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
                            class="w-full
                                   border border-admin-border
                                   rounded-xl px-4 py-3
                                   outline-none
                                   focus:border-coral"
                        >

                    </div>

                </div>

            </div>


            {{-- =====================================================
                HÌNH ẢNH SẢN PHẨM
            ====================================================== --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4 mb-5">

                    <h2 class="text-base font-semibold text-ink">
                        Hình ảnh sản phẩm
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Quản lý ảnh đại diện và các ảnh chi tiết của sản phẩm.
                    </p>

                </div>


                {{-- =================================================
                    ẢNH ĐẠI DIỆN
                ================================================== --}}
                <div>

                    <label class="block mb-3 text-sm font-semibold text-ink">
                        Ảnh đại diện
                    </label>


                    <div class="flex flex-col sm:flex-row gap-5">

                        {{-- CURRENT IMAGE --}}
                        <div class="shrink-0">

                            @if ($product->image)

                                <div
                                    class="w-40 h-40
                                           rounded-2xl
                                           border border-admin-border
                                           overflow-hidden
                                           bg-white
                                           flex items-center justify-center"
                                >

                                    <img
                                        src="{{ str_starts_with($product->image, 'http')
                                            ? $product->image
                                            : asset('storage/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-contain"
                                    >

                                </div>

                                <p class="text-xs text-ink-soft mt-2 text-center">
                                    Ảnh hiện tại
                                </p>

                            @else

                                <div
                                    class="w-40 h-40
                                           rounded-2xl
                                           border-2 border-dashed
                                           border-admin-border
                                           bg-admin-bg
                                           flex flex-col items-center
                                           justify-center
                                           text-ink-soft"
                                >

                                    <span class="text-4xl">
                                        🖼️
                                    </span>

                                    <span class="text-xs mt-2">
                                        Chưa có ảnh
                                    </span>

                                </div>

                            @endif

                        </div>


                        {{-- UPLOAD --}}
                        <div class="flex-1">

                            <label class="block mb-2 text-sm font-medium text-ink">
                                Chọn ảnh mới
                            </label>

                            <input
                                type="file"
                                name="image"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl px-4 py-3
                                       bg-white text-sm"
                            >

                            <p class="mt-2 text-xs text-ink-soft">
                                Chấp nhận JPG, JPEG, PNG hoặc WEBP.
                                Dung lượng tối đa 4MB.
                            </p>


                            @error('image')
                                <p class="mt-2 text-xs text-red-500">
                                    {{ $message }}
                                </p>
                            @enderror


                            @if ($product->image)

                                <label
                                    class="inline-flex items-center gap-2
                                           mt-4 cursor-pointer
                                           text-sm text-red-500"
                                >

                                    <input
                                        type="checkbox"
                                        name="remove_image"
                                        value="1"
                                        class="accent-red-500"
                                    >

                                    Xóa ảnh đại diện hiện tại

                                </label>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    GALLERY
                ================================================== --}}
                <div class="mt-7 pt-6 border-t border-admin-border">

                    <label class="block mb-3 text-sm font-semibold text-ink">
                        Ảnh chi tiết
                    </label>


                    @if (!empty($product->images))

                        <div
                            class="grid grid-cols-2
                                   sm:grid-cols-3
                                   md:grid-cols-4
                                   gap-4 mb-5"
                        >

                            @foreach ($product->images as $image)

                                <div
                                    class="rounded-xl
                                           border border-admin-border
                                           bg-white p-2"
                                >

                                    <div
                                        class="w-full h-28
                                               rounded-lg overflow-hidden
                                               bg-admin-bg"
                                    >

                                        <img
                                            src="{{ str_starts_with($image, 'http')
                                                ? $image
                                                : asset('storage/' . $image) }}"
                                            alt=""
                                            class="w-full h-full object-cover"
                                        >

                                    </div>


                                    <label
                                        class="flex items-center gap-2
                                               mt-2
                                               text-xs text-red-500
                                               cursor-pointer"
                                    >

                                        <input
                                            type="checkbox"
                                            name="remove_gallery[]"
                                            value="{{ $image }}"
                                            class="accent-red-500"
                                        >

                                        Xóa ảnh này

                                    </label>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div
                            class="rounded-xl
                                   border border-dashed
                                   border-admin-border
                                   bg-admin-bg/40
                                   px-4 py-6
                                   text-center mb-4"
                        >

                            <span class="text-2xl">
                                🖼️
                            </span>

                            <p class="text-sm text-ink-soft mt-2">
                                Chưa có ảnh chi tiết.
                            </p>

                        </div>

                    @endif


                    <label class="block mb-2 text-sm font-medium">
                        Thêm ảnh chi tiết
                    </label>

                    <input
                        type="file"
                        name="images[]"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="w-full
                               border border-admin-border
                               rounded-xl px-4 py-3
                               bg-white text-sm"
                    >

                    <p class="mt-2 text-xs text-ink-soft">
                        Có thể chọn nhiều ảnh cùng lúc, tối đa 8 ảnh.
                    </p>

                    @error('images')
                        <p class="mt-2 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('images.*')
                        <p class="mt-2 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>

        </div>



        {{-- =========================================================
            RIGHT
        ========================================================== --}}
        <div class="space-y-6">

            {{-- =====================================================
                STAGES
            ====================================================== --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4">

                    <h2 class="text-base font-semibold text-ink">
                        Giai đoạn phù hợp
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Chọn một hoặc nhiều giai đoạn phù hợp với sản phẩm.
                    </p>

                </div>


                <div
                    class="mt-4 space-y-2
                           max-h-[380px]
                           overflow-y-auto pr-1"
                >

                    @forelse ($stages as $stage)

                        <label
                            class="flex items-start gap-3
                                   border border-admin-border
                                   rounded-xl px-4 py-3
                                   bg-white
                                   cursor-pointer
                                   hover:border-coral/40
                                   hover:bg-coral-light/20"
                        >

                            <input
                                type="checkbox"
                                name="stage_ids[]"
                                value="{{ $stage->id }}"
                                @checked(
                                    in_array(
                                        (string) $stage->id,
                                        $selectedStageIds,
                                        true
                                    )
                                )
                                class="mt-1
                                       w-4 h-4
                                       accent-coral"
                            >

                            <div>

                                <p class="text-sm font-semibold text-ink">

                                    {{ $stage->icon }}
                                    {{ $stage->name }}

                                </p>

                                <p class="text-xs text-ink-soft mt-1">

                                    {{ $stage->age_from }}
                                    -
                                    {{ $stage->age_to }}
                                    tháng

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


            {{-- =====================================================
                TAGS
            ====================================================== --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4">

                    <h2 class="text-base font-semibold text-ink">
                        Thuộc tính / Tags
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Gắn thuộc tính và thương hiệu cho sản phẩm.
                    </p>

                </div>


                <div
                    class="mt-4 space-y-2
                           max-h-[440px]
                           overflow-y-auto pr-1"
                >

                    @forelse ($tags as $tag)

                        <label
                            class="flex items-center
                                   justify-between gap-4
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   cursor-pointer
                                   hover:border-coral/40
                                   hover:bg-coral-light/20"
                        >

                            <div class="flex items-center gap-3 min-w-0">

                                <input
                                    type="checkbox"
                                    name="tag_ids[]"
                                    value="{{ $tag->id }}"
                                    @checked(
                                        in_array(
                                            (string) $tag->id,
                                            $selectedTagIds,
                                            true
                                        )
                                    )
                                    class="w-4 h-4 accent-coral"
                                >

                                <span class="text-sm font-medium text-ink truncate">
                                    {{ $tag->name }}
                                </span>

                            </div>


                            @if ($tag->type === 'attribute')

                                <span
                                    class="shrink-0
                                           text-[11px]
                                           text-blue-600
                                           bg-blue-50
                                           rounded-full
                                           px-2.5 py-1"
                                >
                                    Thuộc tính
                                </span>

                            @elseif ($tag->type === 'brand')

                                <span
                                    class="shrink-0
                                           text-[11px]
                                           text-amber-600
                                           bg-amber-50
                                           rounded-full
                                           px-2.5 py-1"
                                >
                                    Thương hiệu
                                </span>

                            @elseif ($tag->type === 'stage')

                                <span
                                    class="shrink-0
                                           text-[11px]
                                           text-purple-600
                                           bg-purple-50
                                           rounded-full
                                           px-2.5 py-1"
                                >
                                    Giai đoạn
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


            {{-- =====================================================
                ACTION
            ====================================================== --}}
            <div class="card">

                <div class="flex flex-col gap-3">

                    <button
                        type="submit"
                        class="w-full
                               bg-coral text-white
                               rounded-xl
                               px-5 py-3
                               font-semibold
                               hover:opacity-90
                               transition"
                    >
                        ✓ Lưu thay đổi
                    </button>


                    <a
                        href="{{ route('admin.products.index') }}"
                        class="w-full
                               text-center
                               border border-admin-border
                               rounded-xl
                               px-5 py-3
                               text-ink
                               hover:bg-admin-bg
                               transition"
                    >
                        Hủy
                    </a>

                </div>

            </div>

        </div>

    </div>

</form>

@endsection