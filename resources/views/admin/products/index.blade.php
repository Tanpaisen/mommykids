@extends('admin.layouts.app')

@section('page_title', 'Sản phẩm')
@section('page_subtitle', 'Quản lý sản phẩm, giá, tồn kho, lượt bán và đánh giá')


@section('content')

{{-- =====================================================
    HEADER ACTIONS
====================================================== --}}
<div
    class="flex flex-col sm:flex-row
           sm:items-center sm:justify-between
           gap-4 mb-5"
>

    <div>
        <h2 class="text-lg font-semibold text-ink">
            Danh sách sản phẩm
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Quản lý các sản phẩm đang hoạt động trong hệ thống.
        </p>
    </div>


    <div class="flex flex-wrap items-center gap-3">

        @can('products.manage')
            {{-- THÙNG RÁC SẢN PHẨM --}}
            <a
                href="{{ route('admin.products.trash') }}"
                class="inline-flex items-center gap-2
                    h-11 px-4
                    rounded-xl
                    border border-admin-border
                    bg-white
                    text-sm font-medium text-ink
                    hover:border-red-300
                    hover:text-red-500
                    transition"
            >
                <span class="text-base">
                    🗑️
                </span>

                <span>
                    Thùng rác
                </span>

                @if (($trashCount ?? 0) > 0)
                    <span
                        class="inline-flex
                            min-w-5 h-5
                            items-center justify-center
                            rounded-full
                            bg-red-500
                            px-1.5
                            text-[11px]
                            font-semibold
                            text-white"
                    >
                        {{ $trashCount }}
                    </span>
                @endif
            </a>


            {{-- THÊM SẢN PHẨM --}}
            <a
                href="{{ route('admin.products.create') }}"
                class="inline-flex items-center justify-center gap-2
                    h-11 px-5
                    rounded-xl
                    bg-coral
                    text-white
                    text-sm font-semibold
                    hover:opacity-90
                    transition"
            >
                <span>+</span>

                <span>
                    Thêm sản phẩm
                </span>
            </a>

            {{-- IMPORT SẢN PHẨM --}}
            <a href="{{ route('admin.products.import.form') }}" 
                class="px-4 py-2.5 rounded-xl border border-admin-border bg-white text-sm text-ink hover:bg-admin-bg transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import CSV
                </a>
        @endcan
    </div>

</div>



{{-- =====================================================
    FILTER
====================================================== --}}
<div class="card mb-5">

    <form
        method="GET"
        action="{{ route('admin.products.index') }}"
        class="grid grid-cols-1
               md:grid-cols-2
               xl:grid-cols-[2fr_1fr_1fr_1fr_auto_auto]
               gap-3"
    >

        {{-- SEARCH --}}
        <input
            type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Tìm theo tên hoặc slug sản phẩm..."
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >


        {{-- CATEGORY --}}
        <select
            name="category_id"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >
            <option value="">
                Tất cả danh mục
            </option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected(
                        (string) request('category_id')
                        ===
                        (string) $category->id
                    )
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>


        {{-- STATUS --}}
        <select
            name="status"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >
            <option value="">
                Tất cả trạng thái
            </option>

            <option
                value="active"
                @selected(request('status') === 'active')
            >
                Đang bán
            </option>

            <option
                value="inactive"
                @selected(request('status') === 'inactive')
            >
                Đã ẩn
            </option>

            <option
                value="featured"
                @selected(request('status') === 'featured')
            >
                ⭐ Sản phẩm nổi bật
            </option>
        </select>


        {{-- SORT --}}
        <select
            name="sort"
            class="border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   outline-none
                   focus:border-coral
                   focus:ring-2
                   focus:ring-coral/10"
        >
            <option
                value=""
                @selected(!request()->filled('sort'))
            >
                Sắp xếp mặc định
            </option>

            <option
                value="newest"
                @selected(request('sort') === 'newest')
            >
                Mới nhất
            </option>

            <option
                value="best_selling"
                @selected(request('sort') === 'best_selling')
            >
                Bán chạy nhất
            </option>

            <option
                value="rating_desc"
                @selected(request('sort') === 'rating_desc')
            >
                Đánh giá cao nhất
            </option>

            <option
                value="price_asc"
                @selected(request('sort') === 'price_asc')
            >
                Giá tăng dần
            </option>

            <option
                value="price_desc"
                @selected(request('sort') === 'price_desc')
            >
                Giá giảm dần
            </option>
        </select>


        {{-- LOW STOCK --}}
        <label
            class="flex items-center justify-center gap-2
                   border border-admin-border
                   rounded-xl px-4 py-3
                   bg-white
                   cursor-pointer
                   whitespace-nowrap"
        >
            <input
                type="checkbox"
                name="low_stock"
                value="1"
                @checked(request()->boolean('low_stock'))
                class="accent-coral"
            >

            <span>
                Tồn kho thấp
            </span>
        </label>


        {{-- FILTER BUTTON --}}
        <button
            type="submit"
            class="bg-coral
                   text-white
                   rounded-xl
                   px-6 py-3
                   font-semibold
                   hover:opacity-90
                   transition"
        >
            Lọc
        </button>

    </form>


    {{-- RESET FILTER --}}
    @if (
        request()->filled('search')
        || request()->filled('category_id')
        || request()->filled('status')
        || request()->filled('sort')
        || request()->boolean('low_stock')
    )

        <div class="mt-3">

            <a
                href="{{ route('admin.products.index') }}"
                class="inline-flex items-center gap-1
                       text-sm text-ink-soft
                       hover:text-coral
                       transition"
            >
                ↻ Làm mới bộ lọc
            </a>

        </div>

    @endif

</div>



{{-- =====================================================
    TABLE
====================================================== --}}
<div class="card overflow-hidden">

    <div class="overflow-x-auto xl:overflow-x-visible">

        <table class="w-full table-fixed text-[13px]">

            <thead
                class="bg-admin-bg
                       text-xs uppercase
                       text-ink-soft"
            >
                <tr>

                    <th class="w-[3%] text-left px-1.5 py-3">
                        STT
                    </th>

                    <th class="w-[30%] text-left px-2.5 py-3">
                        Sản phẩm
                    </th>

                    <th class="w-[10%] text-left px-2.5 py-3">
                        Danh mục
                    </th>

                    <th class="w-[11%] text-left px-2.5 py-3">
                        Giá
                    </th>

                    <th class="w-[7%] text-center px-2 py-3">
                        Tồn kho
                    </th>

                    <th class="w-[7%] text-center px-2 py-3">
                        Đã bán
                    </th>

                    <th class="w-[12%] text-left px-2.5 py-3">
                        Đánh giá
                    </th>

                    <th class="w-[10%] text-left px-2.5 py-3">
                        Trạng thái
                    </th>

                    <th class="w-[10%] text-right px-2.5 py-3">
                        Thao tác
                    </th>

                </tr>
            </thead>


            <tbody class="divide-y divide-admin-border">

                @forelse ($products as $product)

                    <tr class="hover:bg-admin-bg/40 transition">

                        {{-- STT --}}
                        <td class="px-1.5 py-3 text-ink-soft">
                            {{ $products->firstItem() + $loop->index }}
                        </td>


                        {{-- PRODUCT --}}
                        <td class="px-2.5 py-3">

                            <div class="flex items-center gap-3 min-w-0">

                                {{-- IMAGE --}}
                                <div
                                    class="w-14 h-14
                                           shrink-0
                                           rounded-xl
                                           border border-admin-border
                                           overflow-hidden
                                           bg-white
                                           flex items-center justify-center"
                                >

                                    @if ($product->image)

                                        <img
                                            src="{{ str_starts_with($product->image, 'http')
                                                ? $product->image
                                                : asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name }}"
                                            class="w-full h-full object-contain"
                                            onerror="
                                                this.style.display='none';
                                                this.nextElementSibling.style.display='flex';
                                            "
                                        >

                                        <span
                                            style="display:none;"
                                            class="w-full h-full
                                                   items-center justify-center
                                                   text-2xl text-ink-soft"
                                        >
                                            🖼️
                                        </span>

                                    @else

                                        <span class="text-2xl text-ink-soft">
                                            🖼️
                                        </span>

                                    @endif

                                </div>


                                {{-- INFO --}}
                                <div class="min-w-0">

                                    <p class="font-semibold text-ink leading-5 break-words">
                                        {{ $product->name }}
                                    </p>

                                    <p
                                        class="text-[11px] text-ink-soft
                                               truncate
                                               max-w-full
                                               mt-0.5"
                                        title="{{ $product->slug }}"
                                    >
                                        {{ $product->slug }}
                                    </p>

                                    <p class="text-[11px] text-ink-soft mt-0.5">
                                        ID #{{ $product->id }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        {{-- CATEGORY --}}
                        <td class="px-2.5 py-3 align-middle">

                            <span
                                class="inline-flex max-w-full
                                       bg-admin-bg
                                       rounded-lg
                                       px-2 py-1
                                       text-[11px] leading-4 text-center whitespace-normal"
                            >
                                {{ $product->category?->name ?? 'Chưa phân loại' }}
                            </span>

                        </td>


                        {{-- PRICE --}}
                        <td class="px-2.5 py-3 align-middle">

                            <p class="font-semibold text-coral whitespace-nowrap">
                                {{ number_format(
                                    $product->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </p>


                            @if ($product->old_price)

                                <p
                                    class="text-[11px] text-ink-soft
                                           line-through
                                           mt-0.5 whitespace-nowrap"
                                >
                                    {{ number_format(
                                        $product->old_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </p>

                            @endif


                            @if ($product->discount_percent)

                                <span
                                    class="inline-flex
                                           mt-1
                                           bg-red-50
                                           text-red-500
                                           rounded-full
                                           px-2 py-0.5
                                           text-xs"
                                >
                                    -{{ $product->discount_percent }}%
                                </span>

                            @endif

                        </td>


                        {{-- STOCK --}}
                        <td class="px-2 py-3 text-center align-middle">

                            <span
                                class="inline-flex
                                       min-w-9 h-8
                                       items-center justify-center
                                       rounded-lg px-2
                                       {{ $product->stock <= 10
                                            ? 'bg-red-50 text-red-500'
                                            : 'bg-admin-bg text-ink' }}
                                       font-semibold"
                            >
                                {{ $product->stock }}
                            </span>

                        </td>


                        {{-- SOLD COUNT --}}
                        <td class="px-2 py-3 text-center align-middle">

                            <span
                                class="inline-flex
                                       min-w-10 h-8
                                       items-center justify-center
                                       gap-1
                                       rounded-lg px-2.5
                                       bg-coral/10
                                       text-coral
                                       font-semibold"
                                title="Lượt bán từ các đơn đã giao thành công"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    class="w-3.5 h-3.5"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 6h2l2 9h10l2-6H6"
                                    />
                                    <circle cx="9" cy="19" r="1" />
                                    <circle cx="17" cy="19" r="1" />
                                </svg>

                                {{ number_format(
                                    (int) ($product->sold_count ?? 0),
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </span>

                        </td>


                        {{-- REVIEW STATS --}}
                        <td class="px-2.5 py-3 align-middle">

                            @php
                                $adminReviewCount =
                                    (int) ($product->reviews_count ?? 0);

                                $adminRating = round(
                                    (float) (
                                        $product->reviews_avg_rating ?? 0
                                    ),
                                    1
                                );

                                $adminFilledStars =
                                    (int) round($adminRating);
                            @endphp

                            @if ($adminReviewCount > 0)

                                <div
                                    class="flex items-center gap-1 flex-wrap"
                                    title="{{ number_format(
                                        $adminRating,
                                        1,
                                        ',',
                                        '.'
                                    ) }}/5 từ {{ $adminReviewCount }} đánh giá"
                                >

                                    <span
                                        class="inline-flex
                                               items-center
                                               gap-[1px]
                                               whitespace-nowrap"
                                    >
                                        @for ($star = 1; $star <= 5; $star++)

                                            <span
                                                class="text-[11px] leading-none
                                                       {{
                                                           $star <= $adminFilledStars
                                                               ? 'text-amber-400'
                                                               : 'text-gray-300'
                                                       }}"
                                            >
                                                ★
                                            </span>

                                        @endfor
                                    </span>

                                    <span
                                        class="text-[11px]
                                               font-semibold
                                               text-ink"
                                    >
                                        {{ number_format(
                                            $adminRating,
                                            1,
                                            ',',
                                            '.'
                                        ) }}
                                    </span>

                                    <span class="text-[11px] text-ink-soft">
                                        ({{ $adminReviewCount }})
                                    </span>

                                </div>

                            @else

                                <span
                                    class="inline-flex
                                           items-center
                                           rounded-full
                                           bg-gray-50
                                           px-2 py-1
                                           text-[11px]
                                           text-ink-soft"
                                >
                                    Chưa có đánh giá
                                </span>

                            @endif

                        </td>


                        {{-- STATUS --}}
                        <td class="px-2.5 py-3 align-middle">

                            <div class="flex flex-wrap items-center gap-1">

                                @if ($product->is_active)

                                    <span
                                        class="inline-flex
                                               items-center gap-2
                                               rounded-full
                                               bg-green-50
                                               text-green-600
                                               px-2 py-1
                                               text-[11px]
                                               font-semibold"
                                    >

                                        <span
                                            class="w-2 h-2
                                                   rounded-full
                                                   bg-green-500"
                                        ></span>

                                        Đang bán

                                    </span>

                                @else

                                    <span
                                        class="inline-flex
                                               items-center gap-2
                                               rounded-full
                                               bg-gray-100
                                               text-gray-500
                                               px-2 py-1
                                               text-[11px]
                                               font-semibold"
                                    >

                                        <span
                                            class="w-2 h-2
                                                   rounded-full
                                                   bg-gray-400"
                                        ></span>

                                        Đã ẩn

                                    </span>

                                @endif


                                @if ($product->is_featured)

                                    <span
                                        class="inline-flex
                                               items-center gap-1.5
                                               rounded-full
                                               bg-amber-50
                                               text-amber-600
                                               px-2 py-1
                                               text-[11px]
                                               font-semibold"
                                    >
                                        ⭐ Nổi bật
                                    </span>

                                @endif

                            </div>

                        </td>


                        {{-- ACTION --}}
                        <td class="px-2.5 py-3 align-middle">

                            <div class="flex flex-wrap justify-end gap-1.5">
                                @can('products.manage')
                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route(
                                            'admin.products.edit',
                                            $product
                                        ) }}"
                                        class="px-2.5 py-1.5
                                            border border-admin-border
                                            rounded-lg
                                            text-ink
                                            hover:border-coral
                                            hover:text-coral
                                            transition text-xs font-semibold whitespace-nowrap"
                                    >
                                        Sửa
                                    </a>


                                    {{-- SOFT DELETE --}}
                                    <button
                                        type="button"
                                        data-action="{{ route(
                                            'admin.products.destroy',
                                            $product
                                        ) }}"
                                        data-name="{{ $product->name }}"
                                        onclick="openDeleteProductModal(this)"
                                        class="px-2.5 py-1.5
                                            bg-red-50
                                            text-red-500
                                            rounded-lg
                                            hover:bg-red-100
                                            transition text-xs font-semibold whitespace-nowrap"
                                    >
                                        Xóa
                                    </button>
                                @else
                                    {{-- CHỈ XEM --}}
                                    <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition text-xs font-semibold">
                                        Xem chi tiết
                                    </a>
                                @endcan
                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="9"
                            class="py-20 text-center"
                        >

                            <div class="text-4xl">
                                🛍️
                            </div>

                            <p class="font-semibold mt-3 text-ink">
                                Không tìm thấy sản phẩm
                            </p>

                            <p class="text-sm text-ink-soft mt-1">
                                Không có sản phẩm phù hợp với bộ lọc hiện tại.
                            </p>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>



    {{-- =====================================================
        PAGINATION
    ====================================================== --}}
    @if ($products->total() > 0)

        <div
            class="flex flex-col sm:flex-row
                   sm:items-center
                   sm:justify-between
                   gap-4
                   border-t border-admin-border
                   px-5 py-4"
        >

            <p class="text-sm text-ink-soft">

                Hiển thị

                <strong class="text-ink">
                    {{ $products->firstItem() }}
                </strong>

                -

                <strong class="text-ink">
                    {{ $products->lastItem() }}
                </strong>

                trong

                <strong class="text-ink">
                    {{ $products->total() }}
                </strong>

                sản phẩm

            </p>


            @if ($products->hasPages())

                @php
                    $current = $products->currentPage();
                    $last = $products->lastPage();

                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);

                    if ($current <= 3) {
                        $end = min(5, $last);
                    }

                    if ($current >= $last - 2) {
                        $start = max(1, $last - 4);
                    }
                @endphp


                <div class="flex items-center gap-1">

                    {{-- PREVIOUS --}}
                    @if ($products->onFirstPage())

                        <span
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   text-gray-300"
                        >
                            ‹
                        </span>

                    @else

                        <a
                            href="{{ $products->previousPageUrl() }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- FIRST PAGE --}}
                    @if ($start > 1)

                        <a
                            href="{{ $products->url(1) }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            1
                        </a>

                        @if ($start > 2)

                            <span class="px-2 text-ink-soft">
                                ...
                            </span>

                        @endif

                    @endif


                    {{-- PAGE WINDOW --}}
                    @for ($page = $start; $page <= $end; $page++)

                        @if ($page === $current)

                            <span
                                class="w-10 h-10
                                       bg-coral
                                       text-white
                                       rounded-xl
                                       flex items-center justify-center
                                       font-semibold"
                            >
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $products->url($page) }}"
                                class="w-10 h-10
                                       border border-admin-border
                                       rounded-xl
                                       flex items-center justify-center
                                       hover:border-coral
                                       hover:text-coral
                                       transition"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    {{-- LAST PAGE --}}
                    @if ($end < $last)

                        @if ($end < $last - 1)

                            <span class="px-2 text-ink-soft">
                                ...
                            </span>

                        @endif


                        <a
                            href="{{ $products->url($last) }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            {{ $last }}
                        </a>

                    @endif


                    {{-- NEXT --}}
                    @if ($products->hasMorePages())

                        <a
                            href="{{ $products->nextPageUrl() }}"
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            ›
                        </a>

                    @else

                        <span
                            class="w-10 h-10
                                   border border-admin-border
                                   rounded-xl
                                   flex items-center justify-center
                                   text-gray-300"
                        >
                            ›
                        </span>

                    @endif

                </div>

            @endif

        </div>

    @endif

</div>



{{-- =====================================================
    SOFT DELETE MODAL
====================================================== --}}
<div
    id="deleteProductModal"
    class="fixed inset-0 z-50
           hidden items-center justify-center
           bg-black/40 px-4"
    onclick="closeDeleteProductModalOnBackdrop(event)"
>

    <div
        class="w-full max-w-md
               bg-white
               rounded-2xl
               shadow-2xl
               p-6"
        onclick="event.stopPropagation()"
    >

        <div class="flex items-start gap-4">

            <div
                class="w-12 h-12
                       shrink-0
                       rounded-full
                       bg-red-50
                       text-red-500
                       flex items-center justify-center
                       text-xl"
            >
                🗑️
            </div>


            <div class="flex-1">

                <h3 class="text-lg font-semibold text-ink">
                    Chuyển vào thùng rác?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">

                    Bạn có chắc muốn chuyển

                    <strong
                        id="deleteProductName"
                        class="text-ink"
                    ></strong>

                    vào thùng rác?

                </p>

                <p class="text-xs text-ink-soft mt-2">
                    Sản phẩm sẽ không còn hiển thị ở danh sách
                    và phía khách hàng, nhưng vẫn có thể khôi phục sau này.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeDeleteProductModal()"
                class="border border-admin-border
                       rounded-xl
                       px-4 py-2.5
                       text-sm text-ink
                       hover:bg-admin-bg
                       transition"
            >
                Hủy
            </button>


            <form
                id="deleteProductForm"
                method="POST"
            >

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="bg-red-500
                           text-white
                           rounded-xl
                           px-4 py-2.5
                           text-sm font-semibold
                           hover:bg-red-600
                           transition"
                >
                    Chuyển vào thùng rác
                </button>

            </form>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script>
    function openDeleteProductModal(button) {
        const modal = document.getElementById(
            'deleteProductModal'
        );

        const form = document.getElementById(
            'deleteProductForm'
        );

        const name = document.getElementById(
            'deleteProductName'
        );

        if (!modal || !form || !name) {
            return;
        }

        form.action = button.dataset.action;
        name.textContent = button.dataset.name;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function closeDeleteProductModal() {
        const modal = document.getElementById(
            'deleteProductModal'
        );

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    function closeDeleteProductModalOnBackdrop(event) {
        if (
            event.target.id === 'deleteProductModal'
        ) {
            closeDeleteProductModal();
        }
    }


    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeDeleteProductModal();
            }
        }
    );
</script>

@endpush