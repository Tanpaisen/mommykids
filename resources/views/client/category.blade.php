@extends('client.layouts.app')

@section('title', $category->name . ' - MommyKids')


{{-- =========================================================
     SIDEBAR RIÊNG CHO TRANG DANH MỤC
========================================================== --}}
@section('sidebar')

    @php
        $selectedBrands = $selectedBrands ?? [];
        $selectedAttributes = $selectedAttributes ?? [];
        $selectedStageIds = $selectedStageIds ?? [];

        $activeSort = $sort ?? request('sort', 'default');

        $priceFloor = $priceFloor ?? 0;
        $priceCeiling = $priceCeiling ?? 100000;
        $priceStep = $priceStep ?? 10000;
        $minPrice = $minPrice ?? $priceFloor;
        $maxPrice = $maxPrice ?? $priceCeiling;
        $hasPriceFilter = $hasPriceFilter ?? false;

        /*
         * Sidebar riêng cho category Vitamin & sức khỏe.
         * Vẫn dùng name="attribute[]" để giữ nguyên logic filter hiện tại.
         */
        $isVitaminHealthCategory =
            $category->slug === 'vitamin-suc-khoe';

        $vitaminFilterGroups = [
            [
                'title' => 'Độ tuổi',
                'slugs' => [
                    'tu-so-sinh',
                    'tu-4-thang',
                    'tu-2-tuoi',
                ],
            ],
            [
                'title' => 'Dạng sản phẩm',
                'slugs' => [
                    'dang-nho-giot',
                    'dang-xit',
                    'dang-siro',
                    'vien-nhai',
                    'vien-nang-mem',
                ],
            ],
            [
                'title' => 'Dưỡng chất / Nhóm',
                'slugs' => [
                    'vitamin-d3',
                    'vitamin-k2',
                    'vitamin-tong-hop',
                    'dha',
                    'men-vi-sinh',
                    'sat',
                    'canxi',
                ],
            ],
        ];
    @endphp

    <aside
        id="mk-sidebar"
        class="hidden lg:block
               w-[250px] shrink-0
               bg-white
               rounded-2xl
               border border-coral-light/70
               overflow-hidden
               sticky top-4"
    >

        <form
            id="category-filter-form"
            method="GET"
            action="{{ route('category.show', $category->slug) }}"
        >

            {{-- Giữ sort khi áp dụng filter --}}
            <input
                type="hidden"
                name="sort"
                value="{{ $activeSort }}"
            >

            @php
                $hasSidebarFilters =
                    !empty($selectedBrands)
                    || !empty($selectedAttributes)
                    || !empty($selectedStageIds)
                    || $hasPriceFilter;
            @endphp

            @if ($hasSidebarFilters)
                <div class="border-b border-coral-light/70 bg-coral-light/10">
                    <div class="flex items-center justify-between gap-3 px-5 pt-3.5 pb-2.5">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-ink">
                            <svg class="w-3.5 h-3.5 text-coral" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M7 12h10M10 18h4"/>
                            </svg>
                            Bộ lọc đang chọn
                        </span>

                        <a
                            href="{{ route('category.show', [
                                'category' => $category->slug,
                                'sort' => $activeSort,
                            ]) }}"
                            class="shrink-0 text-xs font-bold text-coral hover:underline"
                        >
                            Xóa tất cả
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-2 px-5 pb-4">
                        @foreach ($selectedBrands as $selectedBrand)
                            @php
                                $selectedBrandTag = $brandTags->firstWhere('slug', $selectedBrand);
                            @endphp

                            @if ($selectedBrandTag)
                                @php
                                    $removeBrandQuery = request()->except('page');
                                    $removeBrandQuery['brand'] = array_values(array_filter(
                                        (array) ($removeBrandQuery['brand'] ?? []),
                                        fn ($value) => (string) $value !== (string) $selectedBrand
                                    ));

                                    if (empty($removeBrandQuery['brand'])) {
                                        unset($removeBrandQuery['brand']);
                                    }
                                @endphp

                                <a
                                    href="{{ route(
                                        'category.show',
                                        array_merge(
                                            ['category' => $category->slug],
                                            $removeBrandQuery
                                        )
                                    ) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full
                                           border border-coral-light bg-white
                                           px-3 py-1.5 text-xs font-semibold text-ink
                                           shadow-sm shadow-coral-light/20
                                           hover:border-coral hover:bg-coral-light/30 hover:text-coral transition"
                                >
                                    <span>{{ $selectedBrandTag->name }}</span>
                                    <span class="text-base leading-none" aria-hidden="true">×</span>
                                </a>
                            @endif
                        @endforeach

                        @foreach ($selectedStageIds as $selectedStageId)
                            @php
                                $selectedStage = $stages->firstWhere('id', (int) $selectedStageId);
                            @endphp

                            @if ($selectedStage)
                                @php
                                    $removeStageQuery = request()->except('page');
                                    $removeStageQuery['stage'] = array_values(array_filter(
                                        (array) ($removeStageQuery['stage'] ?? []),
                                        fn ($value) => (string) $value !== (string) $selectedStageId
                                    ));

                                    if (empty($removeStageQuery['stage'])) {
                                        unset($removeStageQuery['stage']);
                                    }
                                @endphp

                                <a
                                    href="{{ route(
                                        'category.show',
                                        array_merge(
                                            ['category' => $category->slug],
                                            $removeStageQuery
                                        )
                                    ) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full
                                           border border-coral-light bg-white
                                           px-3 py-1.5 text-xs font-semibold text-ink
                                           shadow-sm shadow-coral-light/20
                                           hover:border-coral hover:bg-coral-light/30 hover:text-coral transition"
                                >
                                    <span>{{ $selectedStage->name }}</span>
                                    <span class="text-base leading-none" aria-hidden="true">×</span>
                                </a>
                            @endif
                        @endforeach

                        @foreach ($selectedAttributes as $selectedAttribute)
                            @php
                                $selectedAttributeTag = $attributeTags->firstWhere('slug', $selectedAttribute);
                            @endphp

                            @if ($selectedAttributeTag)
                                @php
                                    $removeAttributeQuery = request()->except('page');
                                    $removeAttributeQuery['attribute'] = array_values(array_filter(
                                        (array) ($removeAttributeQuery['attribute'] ?? []),
                                        fn ($value) => (string) $value !== (string) $selectedAttribute
                                    ));

                                    if (empty($removeAttributeQuery['attribute'])) {
                                        unset($removeAttributeQuery['attribute']);
                                    }
                                @endphp

                                <a
                                    href="{{ route(
                                        'category.show',
                                        array_merge(
                                            ['category' => $category->slug],
                                            $removeAttributeQuery
                                        )
                                    ) }}"
                                    class="inline-flex items-center gap-1.5 rounded-full
                                           border border-coral-light bg-white
                                           px-3 py-1.5 text-xs font-semibold text-ink
                                           shadow-sm shadow-coral-light/20
                                           hover:border-coral hover:bg-coral-light/30 hover:text-coral transition"
                                >
                                    <span>{{ $selectedAttributeTag->name }}</span>
                                    <span class="text-base leading-none" aria-hidden="true">×</span>
                                </a>
                            @endif
                        @endforeach

                        @if ($hasPriceFilter)
                            @php
                                if ($minPrice > $priceFloor && $maxPrice < $priceCeiling) {
                                    $sidebarPriceLabel =
                                        number_format($minPrice, 0, ',', '.') . 'đ - '
                                        . number_format($maxPrice, 0, ',', '.') . 'đ';
                                } elseif ($minPrice > $priceFloor) {
                                    $sidebarPriceLabel = 'Từ ' . number_format($minPrice, 0, ',', '.') . 'đ';
                                } else {
                                    $sidebarPriceLabel = 'Đến ' . number_format($maxPrice, 0, ',', '.') . 'đ';
                                }
                            @endphp

                            <a
                                href="{{ route(
                                    'category.show',
                                    array_merge(
                                        ['category' => $category->slug],
                                        request()->except('page', 'min_price', 'max_price')
                                    )
                                ) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg
                                       border border-coral-light bg-cream/50
                                       px-2.5 py-1.5 text-xs font-semibold text-ink
                                       hover:border-coral hover:text-coral transition"
                            >
                                <span>{{ $sidebarPriceLabel }}</span>
                                <span class="text-base leading-none" aria-hidden="true">×</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endif


            {{-- =====================================================
                 NHÓM SẢN PHẨM
            ====================================================== --}}
            <div class="border-b border-coral-light/70">

                <div class="px-5 py-4 bg-coral-light/60">
                    <h2 class="font-display font-bold text-base text-coral">
                        Nhóm sản phẩm
                    </h2>
                </div>

                <div class="p-4">

                    <div
                        class="px-3 py-2.5
                               rounded-xl
                               bg-cream
                               text-sm
                               font-semibold
                               text-ink"
                    >
                        {{ $category->name }}
                    </div>

                </div>

            </div>


            {{-- =====================================================
                 KHOẢNG GIÁ - SLIDER 2 ĐẦU
            ====================================================== --}}
            <div class="p-5 border-b border-coral-light/70">

                <div class="flex items-center justify-between gap-3">
                    <h3 class="font-display font-bold text-sm text-ink">
                        Khoảng giá
                    </h3>

                    @if ($hasPriceFilter)
                        <a
                            href="{{ route(
                                'category.show',
                                array_merge(
                                    ['category' => $category->slug],
                                    request()->except('page', 'min_price', 'max_price')
                                )
                            ) }}"
                            class="text-[11px] font-semibold text-coral hover:underline"
                        >
                            Đặt lại
                        </a>
                    @endif
                </div>

                <div class="mt-4">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            id="price-min-label"
                            class="inline-flex min-w-0 items-center rounded-lg
                                   border border-coral-light/80 bg-cream/60
                                   px-2.5 py-1.5 text-[11px] font-semibold text-ink"
                        >
                            {{ number_format($minPrice, 0, ',', '.') }}đ
                        </span>

                        <span class="text-[11px] font-medium text-ink-soft/70">đến</span>

                        <span
                            id="price-max-label"
                            class="inline-flex min-w-0 items-center justify-end rounded-lg
                                   border border-coral-light/80 bg-cream/60
                                   px-2.5 py-1.5 text-[11px] font-semibold text-ink text-right"
                        >
                            {{ number_format($maxPrice, 0, ',', '.') }}đ
                        </span>
                    </div>

                    <div
                        id="price-slider"
                        class="relative mt-3 h-8"
                        data-floor="{{ $priceFloor }}"
                        data-ceiling="{{ $priceCeiling }}"
                    >
                        <div
                            class="absolute left-0 right-0 top-1/2 h-1.5
                                   -translate-y-1/2 rounded-full bg-coral-light/70"
                        ></div>

                        <div
                            id="price-range-progress"
                            class="absolute top-1/2 h-1.5
                                   -translate-y-1/2 rounded-full bg-coral"
                        ></div>

                        <input
                            id="price-range-min"
                            type="range"
                            min="{{ $priceFloor }}"
                            max="{{ $priceCeiling }}"
                            step="{{ $priceStep }}"
                            value="{{ $minPrice }}"
                            aria-label="Giá thấp nhất"
                            class="mk-price-range absolute inset-0 w-full"
                        >

                        <input
                            id="price-range-max"
                            type="range"
                            min="{{ $priceFloor }}"
                            max="{{ $priceCeiling }}"
                            step="{{ $priceStep }}"
                            value="{{ $maxPrice }}"
                            aria-label="Giá cao nhất"
                            class="mk-price-range absolute inset-0 w-full"
                        >
                    </div>

                    <input
                        id="min-price-input"
                        type="hidden"
                        name="min_price"
                        value="{{ $minPrice }}"
                        @disabled(!$hasPriceFilter)
                    >

                    <input
                        id="max-price-input"
                        type="hidden"
                        name="max_price"
                        value="{{ $maxPrice }}"
                        @disabled(!$hasPriceFilter)
                    >

                    <p class="mt-2.5 text-[10px] leading-4 text-ink-soft/75">
                        Lọc theo giá bán hiện tại của sản phẩm.
                    </p>
                </div>

            </div>

            {{-- =====================================================
                 THƯƠNG HIỆU - TAG TYPE BRAND
            ====================================================== --}}
            @if ($brandTags->isNotEmpty())

                <div class="p-5 border-b border-coral-light/70">

                    <h3 class="font-display font-bold text-sm text-ink mb-3">
                        Thương hiệu
                    </h3>

                    <div class="space-y-2">

                        @foreach ($brandTags as $tag)

                            <label
                                class="flex items-center gap-3
                                       px-2 py-1.5
                                       rounded-lg
                                       cursor-pointer
                                       hover:bg-cream
                                       transition"
                            >

                                <input
                                    type="checkbox"
                                    name="brand[]"
                                    value="{{ $tag->slug }}"
                                    @checked(in_array($tag->slug, $selectedBrands, true))
                                    class="js-auto-filter
                                           w-4 h-4
                                           rounded
                                           border-coral-light
                                           text-coral
                                           focus:ring-coral/30"
                                >

                                <span class="text-sm text-ink">
                                    {{ $tag->name }}
                                </span>

                            </label>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- =====================================================
                 GIAI ĐOẠN / ĐỘ TUỔI - PRODUCT_STAGE
            ====================================================== --}}
            @if (!$isVitaminHealthCategory && $stages->isNotEmpty())

                <div class="p-5 border-b border-coral-light/70">

                    <h3 class="font-display font-bold text-sm text-ink mb-3">
                        Độ tuổi / Giai đoạn
                    </h3>

                    <div class="space-y-2">

                        @foreach ($stages as $stage)

                            <label
                                class="flex items-start gap-3
                                       px-2 py-1.5
                                       rounded-lg
                                       cursor-pointer
                                       hover:bg-cream
                                       transition"
                            >

                                <input
                                    type="checkbox"
                                    name="stage[]"
                                    value="{{ $stage->id }}"
                                    @checked(in_array((int) $stage->id, $selectedStageIds, true))
                                    class="js-auto-filter
                                           mt-0.5
                                           w-4 h-4
                                           rounded
                                           border-coral-light
                                           text-coral
                                           focus:ring-coral/30"
                                >

                                <span class="min-w-0">

                                    <span class="block text-sm text-ink">
                                        {{ $stage->name }}
                                    </span>

                                    @if (!is_null($stage->age_from) && !is_null($stage->age_to))
                                        <span class="block text-[11px] text-ink-soft mt-0.5">
                                            {{ $stage->age_from }} - {{ $stage->age_to }} tháng
                                        </span>
                                    @endif

                                </span>

                            </label>

                        @endforeach

                    </div>

                </div>

            @endif


            {{-- =====================================================
                 FILTER THUỘC TÍNH
                 Vitamin & sức khỏe: chia 3 nhóm gọn hơn.
                 Category khác: giữ nguyên danh sách thuộc tính cũ.
            ====================================================== --}}
            @if ($isVitaminHealthCategory)

                @foreach ($vitaminFilterGroups as $filterGroup)

                    @php
                        $groupTags = collect($filterGroup['slugs'])
                            ->map(
                                fn ($slug) =>
                                    $attributeTags->firstWhere('slug', $slug)
                            )
                            ->filter()
                            ->values();
                    @endphp

                    @if ($groupTags->isNotEmpty())

                        <div class="p-5 border-b border-coral-light/70">

                            <h3 class="font-display font-bold text-sm text-ink mb-3">
                                {{ $filterGroup['title'] }}
                            </h3>

                            <div class="space-y-2">

                                @foreach ($groupTags as $tag)

                                    <label
                                        class="flex items-center gap-3
                                               px-2 py-1.5
                                               rounded-lg
                                               cursor-pointer
                                               hover:bg-cream
                                               transition"
                                    >

                                        <input
                                            type="checkbox"
                                            name="attribute[]"
                                            value="{{ $tag->slug }}"
                                            @checked(in_array($tag->slug, $selectedAttributes, true))
                                            class="js-auto-filter
                                                   w-4 h-4
                                                   rounded
                                                   border-coral-light
                                                   text-coral
                                                   focus:ring-coral/30"
                                        >

                                        <span class="text-sm text-ink">
                                            {{ $tag->name }}
                                        </span>

                                    </label>

                                @endforeach

                            </div>

                        </div>

                    @endif

                @endforeach

            @elseif ($attributeTags->isNotEmpty())

                <div class="p-5 border-b border-coral-light/70">

                    <h3 class="font-display font-bold text-sm text-ink mb-3">
                        Thuộc tính
                    </h3>

                    <div class="space-y-2">

                        @foreach ($attributeTags as $tag)

                            <label
                                class="flex items-center gap-3
                                       px-2 py-1.5
                                       rounded-lg
                                       cursor-pointer
                                       hover:bg-cream
                                       transition"
                            >

                                <input
                                    type="checkbox"
                                    name="attribute[]"
                                    value="{{ $tag->slug }}"
                                    @checked(in_array($tag->slug, $selectedAttributes, true))
                                    class="js-auto-filter
                                           w-4 h-4
                                           rounded
                                           border-coral-light
                                           text-coral
                                           focus:ring-coral/30"
                                >

                                <span class="text-sm text-ink">
                                    {{ $tag->name }}
                                </span>

                            </label>

                        @endforeach

                    </div>

                </div>

            @endif



        </form>

    </aside>

@endsection


{{-- =========================================================
     MAIN CONTENT
========================================================== --}}
@section('content')

    {{-- =====================================================
         BREADCRUMB
    ====================================================== --}}
    <nav
        class="flex items-center gap-2
               text-sm text-ink-soft"
        aria-label="Breadcrumb"
    >

        <a
            href="{{ route('home') }}"
            class="hover:text-coral transition-colors"
        >
            Trang chủ
        </a>

        <span class="text-ink-soft/50">
            ›
        </span>

        <span class="font-semibold text-ink">
            {{ $category->name }}
        </span>

    </nav>


    {{-- =====================================================
         CATEGORY CARD
    ====================================================== --}}
    <section
        class="bg-white
               rounded-2xl
               border border-coral-light/70
               overflow-hidden"
    >

        {{-- =================================================
             TITLE + SORT
        ================================================== --}}
        <div
            class="flex flex-col
                   xl:flex-row
                   xl:items-center
                   xl:justify-between
                   gap-3
                   px-4 lg:px-5
                   py-3.5
                   border-b border-coral-light/70"
        >

            {{-- TITLE --}}
            <div class="flex items-baseline gap-2 flex-wrap min-w-0">

                <h1
                    class="font-display
                           font-extrabold
                           text-lg lg:text-xl
                           text-coral"
                >
                    {{ $category->name }}
                </h1>

                <span class="text-sm text-ink-soft">
                    ({{ $products->total() }} sản phẩm)
                </span>

            </div>


            {{-- SORT --}}
            <div class="flex flex-wrap items-center gap-x-1 gap-y-2">

                @php
                    $sortItems = [
                        'default' => 'Phù hợp',
                        'newest' => 'Hàng mới',
                        'price_asc' => 'Giá thấp - cao',
                        'price_desc' => 'Giá cao - thấp',
                    ];
                @endphp

                @foreach ($sortItems as $key => $label)

                    @php
                        $sortUrl = route(
                            'category.show',
                            array_merge(
                                ['category' => $category->slug],
                                request()->except('page', 'sort'),
                                ['sort' => $key]
                            )
                        );
                    @endphp

                    <a
                        href="{{ $sortUrl }}"
                        class="inline-flex
                               items-center
                               justify-center
                               min-h-[34px]
                               px-3.5
                               rounded-full
                               text-sm
                               font-medium
                               whitespace-nowrap
                               transition-all
                               {{
                                   $activeSort === $key
                                       ? 'bg-coral-light text-coral font-semibold'
                                       : 'text-ink hover:text-coral hover:bg-cream'
                               }}"
                    >
                        {{ $label }}
                    </a>

                @endforeach

            </div>

        </div>


        {{-- =================================================
             ACTIVE FILTER CHIPS
        ================================================== --}}
        @php
            $hasFilters =
                !empty($selectedBrands)
                || !empty($selectedAttributes)
                || !empty($selectedStageIds)
                || $hasPriceFilter;
        @endphp

        @if ($hasFilters)

            <div
                class="px-4 lg:px-5
                       py-3
                       border-b border-coral-light/60
                       bg-cream/40"
            >

                <div class="flex items-center gap-2 flex-wrap">

                    <span class="text-xs font-medium text-ink-soft mr-1">
                        Đang lọc:
                    </span>


                    {{-- BRAND --}}
                    @foreach ($brandTags->whereIn('slug', $selectedBrands) as $tag)

                        <span
                            class="inline-flex items-center
                                   px-3 py-1.5
                                   rounded-full
                                   bg-white
                                   border border-coral-light
                                   text-xs font-semibold
                                   text-coral"
                        >
                            {{ $tag->name }}
                        </span>

                    @endforeach


                    {{-- STAGE --}}
                    @foreach ($stages->whereIn('id', $selectedStageIds) as $stage)

                        <span
                            class="inline-flex items-center
                                   px-3 py-1.5
                                   rounded-full
                                   bg-white
                                   border border-coral-light
                                   text-xs font-semibold
                                   text-coral"
                        >
                            {{ $stage->name }}
                        </span>

                    @endforeach


                    {{-- ATTRIBUTE --}}
                    @foreach ($attributeTags->whereIn('slug', $selectedAttributes) as $tag)

                        <span
                            class="inline-flex items-center
                                   px-3 py-1.5
                                   rounded-full
                                   bg-white
                                   border border-coral-light
                                   text-xs font-semibold
                                   text-coral"
                        >
                            {{ $tag->name }}
                        </span>

                    @endforeach


                    {{-- PRICE --}}
                    @if ($hasPriceFilter)
                        @php
                            if ($minPrice > $priceFloor && $maxPrice < $priceCeiling) {
                                $priceFilterLabel =
                                    number_format($minPrice, 0, ',', '.') . 'đ - '
                                    . number_format($maxPrice, 0, ',', '.') . 'đ';
                            } elseif ($minPrice > $priceFloor) {
                                $priceFilterLabel = 'Từ ' . number_format($minPrice, 0, ',', '.') . 'đ';
                            } else {
                                $priceFilterLabel = 'Đến ' . number_format($maxPrice, 0, ',', '.') . 'đ';
                            }
                        @endphp

                        <a
                            href="{{ route(
                                'category.show',
                                array_merge(
                                    ['category' => $category->slug],
                                    request()->except('page', 'min_price', 'max_price')
                                )
                            ) }}"
                            class="inline-flex items-center gap-1.5
                                   px-3 py-1.5 rounded-full bg-white
                                   border border-coral-light text-xs font-semibold text-coral
                                   hover:bg-coral-light/30 transition"
                        >
                            <span>{{ $priceFilterLabel }}</span>
                            <span aria-hidden="true">×</span>
                        </a>
                    @endif


                    <a
                        href="{{ route('category.show', [
                            'category' => $category->slug,
                            'sort' => $activeSort,
                        ]) }}"
                        class="ml-1
                               text-xs
                               font-semibold
                               text-coral
                               hover:underline"
                    >
                        Xóa tất cả
                    </a>

                </div>

            </div>

        @endif


        {{-- =================================================
             PRODUCT AREA
        ================================================== --}}
        <div class="p-4 lg:p-5">

            @if ($products->isEmpty())

                <div class="py-16 text-center">

                    <div
                        class="mx-auto
                               w-16 h-16
                               rounded-2xl
                               bg-coral-light
                               flex items-center
                               justify-center
                               text-3xl"
                    >
                        📦
                    </div>

                    <h2
                        class="mt-4
                               font-display
                               font-bold
                               text-lg
                               text-ink"
                    >
                        Chưa tìm thấy sản phẩm
                    </h2>

                    <p class="mt-1 text-sm text-ink-soft">
                        Hãy thử thay đổi hoặc xóa bớt bộ lọc.
                    </p>

                    @if ($hasFilters)

                        <a
                            href="{{ route('category.show', [
                                'category' => $category->slug,
                                'sort' => $activeSort,
                            ]) }}"
                            class="inline-flex
                                   mt-5
                                   px-4 py-2.5
                                   rounded-full
                                   bg-coral
                                   text-white
                                   text-sm
                                   font-semibold"
                        >
                            Xóa bộ lọc
                        </a>

                    @endif

                </div>

            @else

                <div
                    class="grid
                           grid-cols-2
                           sm:grid-cols-3
                           lg:grid-cols-4
                           xl:grid-cols-5
                           gap-4"
                >

                    @foreach ($products as $product)

                        <x-product-card
                            :product="$product"
                            :product-id="$product['id']"
                        />

                    @endforeach

                </div>


                @if ($products->hasPages())

                    <div
                        class="mt-8
                               pt-6
                               border-t border-coral-light/60"
                    >
                        {{ $products->links() }}
                    </div>

                @endif

            @endif

        </div>

    </section>


    {{-- GIỚI THIỆU SỮA CHO BÉ --}}
    @if ($category->slug === 'sua-cho-be')
        <section class="mt-5 overflow-hidden rounded-2xl border border-coral-light/70 bg-gradient-to-r from-white via-cream/60 to-coral-light/50">
            <div class="grid grid-cols-1 lg:grid-cols-[1.2fr_.8fr] min-h-[250px]">
                <div class="p-5 sm:p-6 lg:p-7">
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-coral">
                        Cẩm nang MommyKids
                    </p>

                    <h2 class="mt-1 font-display text-xl sm:text-2xl font-extrabold text-ink">
                        Giới thiệu Sữa cho bé
                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-ink-soft">
                        Sữa cho bé là nhóm sản phẩm dinh dưỡng được lựa chọn theo từng giai đoạn phát triển.
                        Tại MommyKids, ba mẹ có thể tìm sản phẩm từ nhiều thương hiệu phổ biến và lọc nhanh
                        theo thương hiệu, độ tuổi cũng như khoảng giá phù hợp.
                    </p>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 shrink-0 rounded-full bg-white border border-coral-light flex items-center justify-center text-coral">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M7 4v6M17 4v6M6 13h5v5H6zM14 13h4M14 17h4"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold text-ink">Đa dạng thương hiệu</p>
                                <p class="text-[11px] text-ink-soft">Nhiều lựa chọn cho ba mẹ</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 shrink-0 rounded-full bg-white border border-coral-light flex items-center justify-center text-coral">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 1.5"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold text-ink">Chọn theo độ tuổi</p>
                                <p class="text-[11px] text-ink-soft">Dễ tìm theo từng giai đoạn</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 shrink-0 rounded-full bg-white border border-coral-light flex items-center justify-center text-coral">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20s-7-4.35-7-10a4 4 0 0 1 7-2.65A4 4 0 0 1 19 10c0 5.65-7 10-7 10Z"/>
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-bold text-ink">Dễ dàng lựa chọn</p>
                                <p class="text-[11px] text-ink-soft">Bộ lọc rõ ràng, tiện lợi</p>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        id="milk-guide-toggle"
                        class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-coral text-white text-sm font-bold hover:opacity-90 transition"
                        aria-expanded="false"
                        aria-controls="milk-guide-more"
                    >
                        <span id="milk-guide-toggle-label">Xem thêm</span>
                        <span id="milk-guide-toggle-icon" aria-hidden="true">↓</span>
                    </button>
                </div>

                <div
                    class="relative hidden lg:flex
                           items-center justify-center
                           overflow-hidden
                           bg-coral-light/30
                           p-5"
                >
                    <img
                        src="{{ asset('images/categories/suaaa.jpg') }}"
                        alt="Sữa công thức cho bé"
                        class="w-full h-full
                               object-cover
                               object-center
                               rounded-2xl
                               shadow-sm"
                    >
                </div>
            </div>

            <div id="milk-guide-more" class="hidden border-t border-coral-light/70 bg-white px-5 sm:px-6 lg:px-7 py-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <article>
                        <h3 class="font-display font-bold text-ink">1. Chọn theo độ tuổi</h3>
                        <p class="mt-2 text-sm leading-6 text-ink-soft">
                            Ba mẹ nên ưu tiên đúng độ tuổi được nhà sản xuất khuyến nghị trên từng sản phẩm.
                        </p>
                    </article>

                    <article>
                        <h3 class="font-display font-bold text-ink">2. Chọn theo thương hiệu</h3>
                        <p class="mt-2 text-sm leading-6 text-ink-soft">
                            Có thể lọc nhanh Aptamil, Meiji, NAN, Friso, Enfamil, Similac, Morinaga, ColosBaby...
                        </p>
                    </article>

                    <article>
                        <h3 class="font-display font-bold text-ink">3. So sánh giá và nhu cầu</h3>
                        <p class="mt-2 text-sm leading-6 text-ink-soft">
                            Kết hợp thương hiệu, độ tuổi, khoảng giá và sắp xếp để thu hẹp lựa chọn.
                        </p>
                    </article>
                </div>
            </div>
        </section>
    @endif


    {{-- CAM KẾT / DỊCH VỤ --}}
    <section class="mt-5 rounded-2xl border border-coral-light/70 bg-white px-4 sm:px-5 py-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-full bg-coral-light/55 text-coral flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.7-2.8 8.2-7 10-4.2-1.8-7-5.3-7-10V6l7-3Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-5"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Hàng chính hãng</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Thông tin sản phẩm và nguồn gốc rõ ràng.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-full bg-coral-light/55 text-coral flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h11v10H3zM14 9h4l3 3v4h-7z"/>
                        <circle cx="7" cy="18" r="2"/>
                        <circle cx="18" cy="18" r="2"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Giao hàng nhanh</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Quy trình giao nhận thuận tiện cho ba mẹ.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-full bg-coral-light/55 text-coral flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h16v11H4zM3 6h18v3H3zM12 6v14"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6c-2.5 0-4-1-4-2.2C8 2.8 9 2 10.3 2 12 2 12 4 12 6Zm0 0c2.5 0 4-1 4-2.2C16 2.8 15 2 13.7 2 12 2 12 4 12 6Z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Ưu đãi hấp dẫn</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Dễ dàng cập nhật các chương trình khuyến mãi.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-11 h-11 shrink-0 rounded-full bg-coral-light/55 text-coral flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v5h5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.5 12A7 7 0 1 0 8 6.5L4 10"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Hỗ trợ đổi trả</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Hỗ trợ theo chính sách hiện hành của MommyKids.</p>
                </div>
            </div>
        </div>
    </section>

@endsection
