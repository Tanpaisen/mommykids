@extends('admin.layouts.app')

@section('page_title', 'Đánh giá sản phẩm')
@section('page_subtitle', 'Quản lý đánh giá của khách hàng')

@section('content')

{{-- =====================================================
    PAGE HEADER
====================================================== --}}
<div
    class="flex flex-col
           sm:flex-row
           sm:items-center
           sm:justify-between
           gap-4
           mb-6"
>
    <div>
        <h2 class="text-xl font-bold text-ink">
            Quản lý đánh giá sản phẩm
        </h2>

        <p class="text-sm text-ink-soft mt-1">
            Xem, tìm kiếm và quản lý tất cả đánh giá của khách hàng.
        </p>
    </div>

    <div
        class="hidden sm:flex
               items-center gap-2
               text-sm text-ink-soft"
    >
        <span>Admin</span>
        <span>›</span>
        <span class="text-ink">
            Đánh giá sản phẩm
        </span>
    </div>
</div>




{{-- =====================================================
    STATISTICS
====================================================== --}}
<div
    class="grid grid-cols-1
           sm:grid-cols-2
           xl:grid-cols-4
           gap-4
           mb-5"
>

    {{-- TOTAL --}}
    <div
        class="bg-white
               rounded-2xl
               border border-admin-border
               shadow-sm
               p-5"
    >
        <div class="flex items-center gap-4">

            <div
                class="w-14 h-14
                       shrink-0
                       rounded-full
                       bg-blue-50
                       text-blue-500
                       flex items-center justify-center"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    class="w-7 h-7"
                >
                    <path
                        d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H10l-5 4v-4.5A2.5 2.5 0 0 1 4 13.5z"
                    />
                    <path d="M8 8h8M8 12h5" />
                </svg>
            </div>

            <div>
                <p class="text-sm text-ink-soft">
                    Tổng đánh giá
                </p>

                <p class="text-2xl font-bold text-ink mt-1">
                    {{ number_format($reviewCount) }}
                </p>

                <p class="text-xs text-green-500 mt-1">
                    Dữ liệu đánh giá thực tế
                </p>
            </div>

        </div>
    </div>


    {{-- AVERAGE --}}
    <div
        class="bg-white
               rounded-2xl
               border border-admin-border
               shadow-sm
               p-5"
    >
        <div class="flex items-center gap-4">

            <div
                class="w-14 h-14
                       shrink-0
                       rounded-full
                       bg-amber-50
                       text-amber-500
                       flex items-center justify-center
                       text-3xl"
            >
                ★
            </div>

            <div>
                <p class="text-sm text-ink-soft">
                    Điểm đánh giá trung bình
                </p>

                <div class="flex items-end gap-1 mt-1">

                    <p class="text-2xl font-bold text-ink">
                        {{ number_format($averageRating, 1) }}
                    </p>

                    <span class="text-sm text-ink-soft mb-0.5">
                        / 5
                    </span>

                </div>

                <p class="text-xs text-ink-soft mt-1">
                    Dựa trên {{ number_format($reviewCount) }} đánh giá
                </p>
            </div>

        </div>
    </div>


    {{-- POSITIVE --}}
    <div
        class="bg-white
               rounded-2xl
               border border-admin-border
               shadow-sm
               p-5"
    >
        <div class="flex items-center gap-4">

            <div
                class="w-14 h-14
                       shrink-0
                       rounded-full
                       bg-green-50
                       text-green-500
                       flex items-center justify-center"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    class="w-7 h-7"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path d="M8.5 10h.01M15.5 10h.01" />
                    <path d="M8 14c1 1.5 2.3 2.2 4 2.2S15 15.5 16 14" />
                </svg>
            </div>

            <div>
                <p class="text-sm text-ink-soft">
                    Đánh giá tích cực (4–5 sao)
                </p>

                <p class="text-2xl font-bold text-ink mt-1">
                    {{ number_format($positiveRatingCount) }}
                    <span class="text-base font-semibold">
                        ({{ $positiveRatingPercent }}%)
                    </span>
                </p>

                <p class="text-xs text-green-500 mt-1">
                    Khách hàng hài lòng
                </p>
            </div>

        </div>
    </div>


    {{-- LOW --}}
    <div
        class="bg-white
               rounded-2xl
               border border-admin-border
               shadow-sm
               p-5"
    >
        <div class="flex items-center gap-4">

            <div
                class="w-14 h-14
                       shrink-0
                       rounded-full
                       bg-red-50
                       text-red-500
                       flex items-center justify-center"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    class="w-7 h-7"
                >
                    <circle cx="12" cy="12" r="9" />
                    <path d="M8.5 10h.01M15.5 10h.01" />
                    <path d="M8 16c1-1.5 2.3-2.2 4-2.2S15 14.5 16 16" />
                </svg>
            </div>

            <div>
                <p class="text-sm text-ink-soft">
                    Đánh giá thấp (1–2 sao)
                </p>

                <p class="text-2xl font-bold text-ink mt-1">
                    {{ number_format($lowRatingCount) }}
                    <span class="text-base font-semibold">
                        ({{ $lowRatingPercent }}%)
                    </span>
                </p>

                <p class="text-xs text-red-500 mt-1">
                    Cần theo dõi
                </p>
            </div>

        </div>
    </div>

</div>


{{-- =====================================================
    FILTER
====================================================== --}}
<div
    class="bg-white
           rounded-2xl
           border border-admin-border
           shadow-sm
           p-5
           mb-5"
>

    <form
        method="GET"
        action="{{ route('admin.reviews.index') }}"
        class="grid grid-cols-1
               md:grid-cols-2
               xl:grid-cols-12
               gap-3"
    >

        {{-- SEARCH --}}
        <div class="md:col-span-2 xl:col-span-5">

            <div class="relative">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tìm theo tên khách hàng, email, sản phẩm hoặc nội dung đánh giá..."
                    class="w-full
                           h-12
                           border border-admin-border
                           rounded-xl
                           bg-white
                           pl-4 pr-11
                           text-sm
                           outline-none
                           focus:border-coral
                           focus:ring-2
                           focus:ring-coral/10"
                >

                <span
                    class="absolute
                           right-4 top-1/2
                           -translate-y-1/2
                           text-ink-soft"
                >
                    🔍
                </span>

            </div>

        </div>


        {{-- RATING --}}
        <div class="xl:col-span-2">

            <select
                name="rating"
                class="w-full
                       h-12
                       border border-admin-border
                       rounded-xl
                       bg-white
                       px-4
                       text-sm
                       outline-none
                       focus:border-coral
                       focus:ring-2
                       focus:ring-coral/10"
            >
                <option value="">
                    Tất cả số sao
                </option>

                @for ($rating = 5; $rating >= 1; $rating--)

                    <option
                        value="{{ $rating }}"
                        @selected(
                            (string) request('rating')
                            ===
                            (string) $rating
                        )
                    >
                        {{ $rating }} sao
                    </option>

                @endfor
            </select>

        </div>


        {{-- PERIOD --}}
        <div class="xl:col-span-2">

            <select
                name="period"
                class="w-full
                       h-12
                       border border-admin-border
                       rounded-xl
                       bg-white
                       px-4
                       text-sm
                       outline-none
                       focus:border-coral
                       focus:ring-2
                       focus:ring-coral/10"
            >
                <option value="">
                    Tất cả thời gian
                </option>

                <option
                    value="today"
                    @selected(request('period') === 'today')
                >
                    Hôm nay
                </option>

                <option
                    value="7days"
                    @selected(request('period') === '7days')
                >
                    7 ngày gần đây
                </option>

                <option
                    value="30days"
                    @selected(request('period') === '30days')
                >
                    30 ngày gần đây
                </option>
            </select>

        </div>


        {{-- ACTIONS --}}
        <div
            class="md:col-span-2
                   xl:col-span-3
                   grid grid-cols-2
                   gap-3"
        >

            <button
                type="submit"
                class="h-12
                       rounded-xl
                       bg-coral
                       text-white
                       text-sm font-semibold
                       hover:opacity-90
                       transition"
            >
                Lọc
            </button>

            <a
                href="{{ route('admin.reviews.index') }}"
                class="h-12
                       rounded-xl
                       bg-admin-bg
                       border border-admin-border
                       text-sm font-medium
                       text-ink
                       flex items-center justify-center
                       hover:border-coral
                       hover:text-coral
                       transition"
            >
                Đặt lại
            </a>

        </div>

    </form>

</div>


{{-- =====================================================
    REVIEW TABLE
====================================================== --}}
<div
    class="bg-white
           rounded-2xl
           border border-admin-border
           shadow-sm
           overflow-hidden"
>

    {{-- TABLE TITLE --}}
    <div
        class="px-5 py-5
               border-b border-admin-border"
    >
        <h3 class="font-semibold text-ink">
            Danh sách đánh giá
            ({{ number_format($reviews->total()) }})
        </h3>
    </div>


    <div class="overflow-x-auto">

        <table class="w-full min-w-[1380px] text-sm">

            <thead
                class="bg-admin-bg
                       text-xs uppercase
                       text-ink-soft"
            >
                <tr>

                    <th class="text-left px-5 py-4 min-w-[220px]">
                        Khách hàng
                    </th>

                    <th class="text-left px-5 py-4 min-w-[300px]">
                        Sản phẩm
                    </th>

                    <th class="text-left px-5 py-4 min-w-[130px]">
                        Số sao
                    </th>

                    <th class="text-left px-5 py-4 min-w-[280px]">
                        Nội dung đánh giá
                    </th>

                    <th class="text-left px-5 py-4 min-w-[150px]">
                        Hình ảnh
                    </th>

                    <th class="text-left px-5 py-4 min-w-[150px]">
                        Ngày đánh giá
                    </th>

                    <th class="text-right px-5 py-4 min-w-[130px]">
                        Thao tác
                    </th>

                </tr>
            </thead>


            <tbody class="divide-y divide-admin-border">

                @forelse ($reviews as $review)

                    @php
                        $productImage = $review->product?->image;

                        $productImageUrl = null;

                        if ($productImage) {
                            $productImageUrl =
                                str_starts_with($productImage, 'http://')
                                || str_starts_with($productImage, 'https://')
                                    ? $productImage
                                    : asset('storage/' . $productImage);
                        }
                    @endphp


                    <tr class="hover:bg-admin-bg/40 transition">

                        {{-- CUSTOMER --}}
                        <td class="px-5 py-4 align-middle">

                            <div class="flex items-center gap-3">

                                <div
                                    class="w-10 h-10
                                           shrink-0
                                           rounded-full
                                           bg-rose-50
                                           text-coral
                                           flex items-center justify-center
                                           font-semibold"
                                >
                                    {{ strtoupper(
                                        mb_substr(
                                            $review->user?->name ?? 'K',
                                            0,
                                            1
                                        )
                                    ) }}
                                </div>


                                <div class="min-w-0">

                                    <p class="font-semibold text-ink">
                                        {{ $review->user?->name ?? 'Khách hàng' }}
                                    </p>

                                    <p
                                        class="text-xs
                                               text-ink-soft
                                               mt-1
                                               max-w-[180px]
                                               truncate"
                                        title="{{ $review->user?->email }}"
                                    >
                                        {{ $review->user?->email ?? '—' }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        {{-- PRODUCT --}}
                        <td class="px-5 py-4 align-middle">

                            @if ($review->product)

                                <div class="flex items-center gap-3">

                                    <div
                                        class="w-14 h-14
                                               shrink-0
                                               rounded-xl
                                               border border-admin-border
                                               bg-white
                                               overflow-hidden
                                               flex items-center justify-center"
                                    >

                                        @if ($productImageUrl)

                                            <img
                                                src="{{ $productImageUrl }}"
                                                alt="{{ $review->product->name }}"
                                                class="w-full h-full object-contain"
                                                loading="lazy"
                                            >

                                        @else

                                            <span class="text-xl">
                                                🛍️
                                            </span>

                                        @endif

                                    </div>


                                    <div class="min-w-0">

                                        <p
                                            class="font-semibold
                                                   text-ink
                                                   leading-5
                                                   max-w-[220px]"
                                        >
                                            {{ $review->product->name }}
                                        </p>

                                        <p class="text-xs text-ink-soft mt-1">
                                            ID #{{ $review->product->id }}
                                        </p>

                                    </div>

                                </div>

                            @else

                                <span class="text-ink-soft">
                                    Sản phẩm không còn tồn tại
                                </span>

                            @endif

                        </td>


                        {{-- RATING --}}
                        <td class="px-5 py-4 align-middle">

                            <div
                                class="flex items-center
                                       gap-0.5
                                       whitespace-nowrap"
                            >
                                @for ($star = 1; $star <= 5; $star++)

                                    <span
                                        class="text-base
                                               {{ $star <= $review->rating
                                                   ? 'text-amber-400'
                                                   : 'text-gray-300' }}"
                                    >
                                        ★
                                    </span>

                                @endfor
                            </div>

                            <p class="text-xs text-ink-soft mt-1">
                                {{ $review->rating }}/5
                            </p>

                        </td>


                        {{-- COMMENT --}}
                        <td class="px-5 py-4 align-middle">

                            @if ($review->comment)

                                <p
                                    class="text-ink
                                           leading-6
                                           max-w-[320px]"
                                >
                                    {{ $review->comment }}
                                </p>

                            @else

                                <span class="text-ink-soft italic">
                                    Không có nội dung
                                </span>

                            @endif

                        </td>


                        {{-- REVIEW IMAGES --}}
                        <td class="px-5 py-4 align-middle">

                            @if (!empty($review->images))

                                <div class="flex flex-wrap gap-2">

                                    @foreach ($review->images as $image)

                                        <a
                                            href="{{ asset('storage/' . $image) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="w-12 h-12
                                                   block
                                                   rounded-lg
                                                   overflow-hidden
                                                   border border-admin-border
                                                   bg-white"
                                        >
                                            <img
                                                src="{{ asset('storage/' . $image) }}"
                                                alt="Ảnh đánh giá"
                                                loading="lazy"
                                                class="w-full h-full object-cover"
                                            >
                                        </a>

                                    @endforeach

                                </div>

                            @else

                                <div
                                    class="w-12 h-12
                                           rounded-lg
                                           bg-admin-bg
                                           border border-admin-border
                                           flex items-center justify-center
                                           text-ink-soft"
                                >
                                    🖼️
                                </div>

                            @endif

                        </td>


                        {{-- DATE --}}
                        <td class="px-5 py-4 align-middle">

                            <p class="font-medium text-ink whitespace-nowrap">
                                {{ $review->created_at?->format('d/m/Y') }}
                            </p>

                            <p class="text-xs text-ink-soft mt-1">
                                {{ $review->created_at?->format('H:i') }}
                            </p>

                            @if (
                                $review->created_at
                                && $review->updated_at
                                && !$review->updated_at->equalTo(
                                    $review->created_at
                                )
                            )

                                <span
                                    class="inline-flex
                                           mt-2
                                           rounded-full
                                           bg-blue-50
                                           text-blue-600
                                           px-2 py-1
                                           text-[11px]
                                           font-medium"
                                >
                                    Đã chỉnh sửa
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="px-5 py-4 align-middle">

                            <div
                                class="flex items-center
                                       justify-end
                                       gap-2"
                            >

                                {{-- VIEW PRODUCT --}}
                                @if ($review->product)

                                    <a
                                        href="{{ route(
                                            'product.show',
                                            [
                                                'product' =>
                                                    $review->product->slug
                                            ]
                                        ) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        title="Xem sản phẩm"
                                        class="w-10 h-10
                                               rounded-xl
                                               bg-admin-bg
                                               border border-admin-border
                                               text-ink
                                               flex items-center justify-center
                                               hover:border-coral
                                               hover:text-coral
                                               transition"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            class="w-5 h-5"
                                        >
                                            <path
                                                d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                            />
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="2.5"
                                            />
                                        </svg>
                                    </a>

                                @endif


                                {{-- DELETE --}}
                                <button
                                    type="button"
                                    title="Xóa đánh giá"
                                    data-action="{{ route(
                                        'admin.reviews.destroy',
                                        $review
                                    ) }}"
                                    data-user="{{ $review->user?->name ?? 'Khách hàng' }}"
                                    data-product="{{ $review->product?->name ?? 'sản phẩm này' }}"
                                    onclick="openDeleteReviewModal(this)"
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-red-50
                                           text-red-500
                                           flex items-center justify-center
                                           hover:bg-red-100
                                           transition"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        class="w-5 h-5"
                                    >
                                        <path d="M4 7h16" />
                                        <path d="M9 7V4h6v3" />
                                        <path d="M7 7l1 13h8l1-13" />
                                        <path d="M10 11v5M14 11v5" />
                                    </svg>
                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            class="py-20 text-center"
                        >

                            <div class="text-4xl">
                                💬
                            </div>

                            <p class="font-semibold mt-3 text-ink">
                                Không tìm thấy đánh giá
                            </p>

                            <p class="text-sm text-ink-soft mt-1">
                                Không có đánh giá phù hợp với bộ lọc hiện tại.
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
    @if ($reviews->total() > 0)

        <div
            class="flex flex-col
                   sm:flex-row
                   sm:items-center
                   sm:justify-between
                   gap-4
                   border-t border-admin-border
                   px-5 py-4"
        >

            <p class="text-sm text-ink-soft">

                Hiển thị

                <strong class="text-ink">
                    {{ $reviews->firstItem() }}
                </strong>

                -

                <strong class="text-ink">
                    {{ $reviews->lastItem() }}
                </strong>

                trong tổng số

                <strong class="text-ink">
                    {{ $reviews->total() }}
                </strong>

                đánh giá

            </p>


            @if ($reviews->hasPages())

                @php
                    $current = $reviews->currentPage();
                    $last = $reviews->lastPage();

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
                    @if ($reviews->onFirstPage())

                        <span
                            class="w-10 h-10
                                   rounded-xl
                                   border border-admin-border
                                   text-gray-300
                                   flex items-center justify-center"
                        >
                            ‹
                        </span>

                    @else

                        <a
                            href="{{ $reviews->previousPageUrl() }}"
                            class="w-10 h-10
                                   rounded-xl
                                   border border-admin-border
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            ‹
                        </a>

                    @endif


                    {{-- FIRST --}}
                    @if ($start > 1)

                        <a
                            href="{{ $reviews->url(1) }}"
                            class="w-10 h-10
                                   rounded-xl
                                   border border-admin-border
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
                                       rounded-xl
                                       bg-coral
                                       text-white
                                       font-semibold
                                       flex items-center justify-center"
                            >
                                {{ $page }}
                            </span>

                        @else

                            <a
                                href="{{ $reviews->url($page) }}"
                                class="w-10 h-10
                                       rounded-xl
                                       border border-admin-border
                                       flex items-center justify-center
                                       hover:border-coral
                                       hover:text-coral
                                       transition"
                            >
                                {{ $page }}
                            </a>

                        @endif

                    @endfor


                    {{-- LAST --}}
                    @if ($end < $last)

                        @if ($end < $last - 1)

                            <span class="px-2 text-ink-soft">
                                ...
                            </span>

                        @endif

                        <a
                            href="{{ $reviews->url($last) }}"
                            class="w-10 h-10
                                   rounded-xl
                                   border border-admin-border
                                   flex items-center justify-center
                                   hover:border-coral
                                   hover:text-coral
                                   transition"
                        >
                            {{ $last }}
                        </a>

                    @endif


                    {{-- NEXT --}}
                    @if ($reviews->hasMorePages())

                        <a
                            href="{{ $reviews->nextPageUrl() }}"
                            class="w-10 h-10
                                   rounded-xl
                                   border border-admin-border
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
                                   rounded-xl
                                   border border-admin-border
                                   text-gray-300
                                   flex items-center justify-center"
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
    DELETE MODAL
====================================================== --}}
<div
    id="deleteReviewModal"
    class="fixed inset-0
           z-50
           hidden
           items-center justify-center
           bg-black/40
           px-4"
    onclick="closeDeleteReviewModalOnBackdrop(event)"
>

    <div
        class="w-full
               max-w-md
               rounded-2xl
               bg-white
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
                       flex items-center justify-center"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    class="w-6 h-6"
                >
                    <path d="M4 7h16" />
                    <path d="M9 7V4h6v3" />
                    <path d="M7 7l1 13h8l1-13" />
                </svg>
            </div>


            <div class="flex-1">

                <h3 class="text-lg font-semibold text-ink">
                    Xóa đánh giá?
                </h3>

                <p class="text-sm text-ink-soft mt-2 leading-6">
                    Bạn có chắc muốn xóa đánh giá của
                    <strong
                        id="deleteReviewUser"
                        class="text-ink"
                    ></strong>
                    cho sản phẩm
                    <strong
                        id="deleteReviewProduct"
                        class="text-ink"
                    ></strong>?
                </p>

                <p class="text-xs text-red-500 mt-2">
                    Đánh giá và toàn bộ ảnh đính kèm sẽ bị xóa.
                    Hành động này không thể hoàn tác.
                </p>

            </div>

        </div>


        <div class="flex justify-end gap-3 mt-6">

            <button
                type="button"
                onclick="closeDeleteReviewModal()"
                class="px-4 py-2.5
                       rounded-xl
                       border border-admin-border
                       text-sm text-ink
                       hover:bg-admin-bg
                       transition"
            >
                Hủy
            </button>


            <form
                id="deleteReviewForm"
                method="POST"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="px-4 py-2.5
                           rounded-xl
                           bg-red-500
                           text-white
                           text-sm font-semibold
                           hover:bg-red-600
                           transition"
                >
                    Xóa đánh giá
                </button>
            </form>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    function openDeleteReviewModal(button) {
        const modal =
            document.getElementById('deleteReviewModal');

        const form =
            document.getElementById('deleteReviewForm');

        const user =
            document.getElementById('deleteReviewUser');

        const product =
            document.getElementById('deleteReviewProduct');

        if (!modal || !form || !user || !product) {
            return;
        }

        form.action = button.dataset.action;

        user.textContent =
            button.dataset.user;

        product.textContent =
            button.dataset.product;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.style.overflow = 'hidden';
    }


    function closeDeleteReviewModal() {
        const modal =
            document.getElementById('deleteReviewModal');

        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.style.overflow = '';
    }


    function closeDeleteReviewModalOnBackdrop(event) {
        if (event.target.id === 'deleteReviewModal') {
            closeDeleteReviewModal();
        }
    }


    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                closeDeleteReviewModal();
            }
        }
    );
</script>

@endpush