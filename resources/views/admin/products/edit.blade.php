@extends('admin.layouts.app')

@section('page_title', 'Chỉnh sửa sản phẩm')

@section('page_subtitle')
    Cập nhật thông tin sản phẩm "{{ $product->name }}"
@endsection

@section('page_actions')
    <a
        href="{{ route('admin.products.index') }}"
        class="inline-flex items-center justify-center gap-2
               px-4 py-2.5 rounded-xl
               border border-admin-border
               bg-white text-sm font-medium text-ink
               hover:bg-admin-bg transition"
    >
        ← Quay lại
    </a>
@endsection


@section('content')

<form
    action="{{ route('admin.products.update', $product) }}"
    method="POST"
>
    @csrf
    @method('PUT')


    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.55fr)_minmax(360px,0.95fr)] gap-6">

        {{-- =========================================================
            CỘT TRÁI
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

                    {{-- TÊN SẢN PHẨM --}}
                    <div>

                        <label
                            for="productName"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Tên sản phẩm
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            id="productName"
                            type="text"
                            name="name"
                            value="{{ old('name', $product->name) }}"
                            required
                            autocomplete="off"
                            class="w-full
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   text-ink
                                   outline-none
                                   transition
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


                    {{-- DANH MỤC + SLUG --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- DANH MỤC --}}
                        <div>

                            <label
                                for="categoryId"
                                class="block mb-2 text-sm font-semibold text-ink"
                            >
                                Danh mục
                                <span class="text-coral">*</span>
                            </label>

                            <select
                                id="categoryId"
                                name="category_id"
                                required
                                class="w-full
                                       border border-admin-border
                                       rounded-xl
                                       px-4 py-3
                                       bg-white
                                       text-ink
                                       outline-none
                                       transition
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
                                            ) === (string) $category->id
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

                            <label
                                for="productSlug"
                                class="block mb-2 text-sm font-semibold text-ink"
                            >
                                Slug
                            </label>

                            <input
                                id="productSlug"
                                type="text"
                                name="slug"
                                value="{{ old('slug', $product->slug) }}"
                                autocomplete="off"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl
                                       px-4 py-3
                                       bg-white
                                       text-ink
                                       outline-none
                                       transition
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


                    {{-- ICON --}}
                    <div>

                        <label
                            for="productIcon"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Icon
                        </label>

                        <input
                            id="productIcon"
                            type="text"
                            name="icon"
                            value="{{ old('icon', $product->icon) }}"
                            placeholder="Ví dụ: 🍼"
                            maxlength="50"
                            class="w-full
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   text-xl text-ink
                                   outline-none
                                   transition
                                   focus:border-coral
                                   focus:ring-2
                                   focus:ring-coral/10"
                        >

                        @error('icon')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- MÔ TẢ --}}
                    <div>

                        <label
                            for="productDescription"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Mô tả
                        </label>

                        <textarea
                            id="productDescription"
                            name="description"
                            rows="6"
                            placeholder="Nhập mô tả sản phẩm..."
                            class="w-full
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   text-ink
                                   outline-none
                                   resize-y
                                   transition
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


                    {{-- TRẠNG THÁI --}}
                    <div
                        class="flex items-center justify-between gap-4
                               border border-admin-border
                               rounded-2xl
                               px-4 py-4
                               bg-white"
                    >

                        <div>

                            <p class="text-sm font-semibold text-ink">
                                Trạng thái sản phẩm
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                Cho phép sản phẩm hiển thị phía khách hàng.
                            </p>

                        </div>


                        <label
                            class="flex items-center gap-2
                                   cursor-pointer
                                   shrink-0"
                        >

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

                    {{-- GIÁ BÁN --}}
                    <div>

                        <label
                            for="productPrice"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Giá bán
                            <span class="text-coral">*</span>
                        </label>

                        <div class="relative">

                            <input
                                id="productPrice"
                                type="number"
                                name="price"
                                min="0"
                                required
                                value="{{ old('price', $product->price) }}"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl
                                       px-4 py-3 pr-12
                                       bg-white
                                       outline-none
                                       focus:border-coral
                                       focus:ring-2
                                       focus:ring-coral/10"
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


                    {{-- GIÁ CŨ --}}
                    <div>

                        <label
                            for="productOldPrice"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Giá cũ
                        </label>

                        <div class="relative">

                            <input
                                id="productOldPrice"
                                type="number"
                                name="old_price"
                                min="0"
                                value="{{ old('old_price', $product->old_price) }}"
                                placeholder="Không bắt buộc"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl
                                       px-4 py-3 pr-12
                                       bg-white
                                       outline-none
                                       focus:border-coral
                                       focus:ring-2
                                       focus:ring-coral/10"
                            >

                            <span
                                class="absolute right-4 top-1/2
                                       -translate-y-1/2
                                       text-sm text-ink-soft"
                            >
                                đ
                            </span>

                        </div>

                        @error('old_price')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- GIẢM GIÁ --}}
                    <div>

                        <label
                            for="productDiscount"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Giảm giá
                        </label>

                        <div class="relative">

                            <input
                                id="productDiscount"
                                type="number"
                                name="discount_percent"
                                min="0"
                                max="100"
                                value="{{ old(
                                    'discount_percent',
                                    $product->discount_percent
                                ) }}"
                                placeholder="0"
                                class="w-full
                                       border border-admin-border
                                       rounded-xl
                                       px-4 py-3 pr-12
                                       bg-white
                                       outline-none
                                       focus:border-coral
                                       focus:ring-2
                                       focus:ring-coral/10"
                            >

                            <span
                                class="absolute right-4 top-1/2
                                       -translate-y-1/2
                                       text-sm text-ink-soft"
                            >
                                %
                            </span>

                        </div>

                        @error('discount_percent')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- TỒN KHO --}}
                    <div>

                        <label
                            for="productStock"
                            class="block mb-2 text-sm font-semibold text-ink"
                        >
                            Tồn kho
                            <span class="text-coral">*</span>
                        </label>

                        <input
                            id="productStock"
                            type="number"
                            name="stock"
                            min="0"
                            required
                            value="{{ old('stock', $product->stock) }}"
                            class="w-full
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   outline-none
                                   focus:border-coral
                                   focus:ring-2
                                   focus:ring-coral/10"
                        >

                        @error('stock')
                            <p class="mt-1.5 text-xs text-red-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </div>

        </div>



        {{-- =========================================================
            CỘT PHẢI
        ========================================================== --}}
        <div class="space-y-6">

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


            {{-- =====================================================
                GIAI ĐOẠN
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
                    class="mt-4
                           space-y-2
                           max-h-[360px]
                           overflow-y-auto
                           pr-1"
                >

                    @forelse ($stages as $stage)

                        <label
                            class="flex items-start gap-3
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   cursor-pointer
                                   transition
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
                                       accent-coral
                                       shrink-0"
                            >


                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-ink">

                                    @if (!empty($stage->icon))
                                        <span class="mr-1">
                                            {{ $stage->icon }}
                                        </span>
                                    @endif

                                    {{ $stage->name }}

                                </p>


                                @if (
                                    $stage->age_from !== null ||
                                    $stage->age_to !== null
                                )

                                    <p class="text-xs text-ink-soft mt-1">

                                        @if (
                                            $stage->age_from !== null &&
                                            $stage->age_to !== null
                                        )

                                            {{ $stage->age_from }}
                                            -
                                            {{ $stage->age_to }}
                                            tháng

                                        @elseif ($stage->age_from !== null)

                                            Từ {{ $stage->age_from }} tháng

                                        @else

                                            Đến {{ $stage->age_to }} tháng

                                        @endif

                                    </p>

                                @endif

                            </div>

                        </label>

                    @empty

                        <div
                            class="rounded-xl
                                   border border-dashed border-admin-border
                                   px-4 py-8
                                   text-center"
                        >

                            <div class="text-2xl mb-2">
                                👶
                            </div>

                            <p class="text-sm font-medium text-ink">
                                Chưa có giai đoạn
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- =====================================================
                THUỘC TÍNH / TAG
            ====================================================== --}}
            <div class="card">

                <div class="border-b border-admin-border pb-4">

                    <h2 class="text-base font-semibold text-ink">
                        Thuộc tính / Tags
                    </h2>

                    <p class="text-sm text-ink-soft mt-1">
                        Gắn các thuộc tính phù hợp với sản phẩm.
                    </p>

                </div>


                <div
                    class="mt-4
                           space-y-2
                           max-h-[420px]
                           overflow-y-auto
                           pr-1"
                >

                    @forelse ($tags as $tag)

                        <label
                            class="flex items-center justify-between gap-4
                                   border border-admin-border
                                   rounded-xl
                                   px-4 py-3
                                   bg-white
                                   cursor-pointer
                                   transition
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
                                    class="w-4 h-4
                                           accent-coral
                                           shrink-0"
                                >

                                <span class="text-sm font-medium text-ink truncate">
                                    {{ $tag->name }}
                                </span>

                            </div>


                            @if ($tag->type === 'attribute')

                                <span
                                    class="shrink-0
                                           text-[11px]
                                           font-medium
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
                                           font-medium
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
                                           font-medium
                                           text-purple-600
                                           bg-purple-50
                                           rounded-full
                                           px-2.5 py-1"
                                >
                                    Giai đoạn
                                </span>

                            @else

                                <span
                                    class="shrink-0
                                           text-[11px]
                                           font-medium
                                           text-gray-600
                                           bg-gray-100
                                           rounded-full
                                           px-2.5 py-1"
                                >
                                    {{ ucfirst($tag->type) }}
                                </span>

                            @endif

                        </label>

                    @empty

                        <div
                            class="rounded-xl
                                   border border-dashed border-admin-border
                                   px-4 py-8
                                   text-center"
                        >

                            <div class="text-2xl mb-2">
                                🏷️
                            </div>

                            <p class="text-sm font-medium text-ink">
                                Chưa có thuộc tính
                            </p>

                        </div>

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
                               inline-flex items-center justify-center gap-2
                               px-5 py-3
                               rounded-xl
                               bg-coral
                               text-white
                               text-sm font-semibold
                               shadow-sm
                               transition
                               hover:opacity-90"
                    >
                        ✓ Lưu thay đổi
                    </button>


                    <a
                        href="{{ route('admin.products.index') }}"
                        class="w-full
                               inline-flex items-center justify-center
                               px-5 py-3
                               rounded-xl
                               border border-admin-border
                               bg-white
                               text-sm font-medium text-ink
                               transition
                               hover:bg-admin-bg"
                    >
                        Hủy
                    </a>

                </div>

            </div>

        </div>

    </div>

</form>

@endsection