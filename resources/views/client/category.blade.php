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

        $priceOptions = [
            'under_300' => 'Dưới 300.000đ',
            '300_500' => '300.000đ - 500.000đ',
            '500_800' => '500.000đ - 800.000đ',
            'over_800' => 'Trên 800.000đ',
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
            method="GET"
            action="{{ route('category.show', $category->slug) }}"
        >

            {{-- Giữ sort khi áp dụng filter --}}
            <input
                type="hidden"
                name="sort"
                value="{{ $activeSort }}"
            >


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
                                    class="w-4 h-4
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
            @if ($stages->isNotEmpty())

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
                                    class="mt-0.5
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
                 THUỘC TÍNH - TAG TYPE ATTRIBUTE
            ====================================================== --}}
            @if ($attributeTags->isNotEmpty())

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
                                    class="w-4 h-4
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
                 KHOẢNG GIÁ
            ====================================================== --}}
            <div class="p-5 border-b border-coral-light/70">

                <h3 class="font-display font-bold text-sm text-ink mb-3">
                    Khoảng giá
                </h3>

                <div class="space-y-2">

                    @foreach ($priceOptions as $key => $label)

                        <label
                            class="flex items-center gap-3
                                   px-2 py-1.5
                                   rounded-lg
                                   cursor-pointer
                                   hover:bg-cream
                                   transition"
                        >

                            <input
                                type="radio"
                                name="price"
                                value="{{ $key }}"
                                @checked(request('price') === $key)
                                class="w-4 h-4
                                       border-coral-light
                                       text-coral
                                       focus:ring-coral/30"
                            >

                            <span class="text-sm text-ink">
                                {{ $label }}
                            </span>

                        </label>

                    @endforeach

                </div>

            </div>


            {{-- =====================================================
                 ACTION
            ====================================================== --}}
            <div class="p-4 space-y-2">

                <button
                    type="submit"
                    class="w-full
                           px-4 py-2.5
                           rounded-full
                           bg-coral
                           text-white
                           text-sm
                           font-bold
                           hover:opacity-90
                           transition"
                >
                    Áp dụng bộ lọc
                </button>

                <a
                    href="{{ route('category.show', [
                        'category' => $category->slug,
                        'sort' => $activeSort,
                    ]) }}"
                    class="w-full
                           inline-flex
                           items-center
                           justify-center
                           px-4 py-2.5
                           rounded-full
                           border border-coral-light
                           bg-white
                           text-sm
                           font-semibold
                           text-ink-soft
                           hover:text-coral
                           hover:border-coral
                           transition"
                >
                    Xóa bộ lọc
                </a>

            </div>

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
                || request()->filled('price');
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
                    @if (request()->filled('price') && isset($priceOptions[request('price')]))

                        <span
                            class="inline-flex items-center
                                   px-3 py-1.5
                                   rounded-full
                                   bg-white
                                   border border-coral-light
                                   text-xs font-semibold
                                   text-coral"
                        >
                            {{ $priceOptions[request('price')] }}
                        </span>

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
                            <span class="w-9 h-9 shrink-0 rounded-full bg-white border border-coral-light flex items-center justify-center text-coral font-bold">✦</span>
                            <div>
                                <p class="text-xs font-bold text-ink">Đa dạng thương hiệu</p>
                                <p class="text-[11px] text-ink-soft">Nhiều lựa chọn cho ba mẹ</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 shrink-0 rounded-full bg-white border border-coral-light flex items-center justify-center text-coral font-bold">✓</span>
                            <div>
                                <p class="text-xs font-bold text-ink">Chọn theo độ tuổi</p>
                                <p class="text-[11px] text-ink-soft">Dễ tìm theo từng giai đoạn</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 shrink-0 rounded-full bg-white border border-coral-light flex items-center justify-center text-coral font-bold">♡</span>
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

                <div class="relative hidden lg:flex items-center justify-center overflow-hidden bg-coral-light/35" aria-hidden="true">
                    <div class="absolute w-64 h-64 rounded-full bg-white/60 -right-10 -top-10"></div>
                    <div class="absolute w-40 h-40 rounded-full bg-white/50 left-8 bottom-6"></div>
                    <div class="relative z-10 flex items-end gap-4 text-center">
                        <div class="text-7xl">🍼</div>
                        <div class="text-8xl">🥛</div>
                        <div class="text-7xl">🧸</div>
                    </div>
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
                <span class="w-12 h-12 shrink-0 rounded-full bg-coral-light/70 flex items-center justify-center text-xl">✓</span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Hàng chính hãng</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Thông tin sản phẩm và nguồn gốc rõ ràng.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-12 h-12 shrink-0 rounded-full bg-emerald-50 flex items-center justify-center text-xl">🚚</span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Giao hàng nhanh</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Quy trình giao nhận thuận tiện cho ba mẹ.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-12 h-12 shrink-0 rounded-full bg-amber-50 flex items-center justify-center text-xl">🎁</span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Ưu đãi hấp dẫn</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Dễ dàng cập nhật các chương trình khuyến mãi.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="w-12 h-12 shrink-0 rounded-full bg-sky-50 flex items-center justify-center text-xl">↺</span>
                <div>
                    <h3 class="text-sm font-bold text-ink">Hỗ trợ đổi trả</h3>
                    <p class="mt-1 text-xs leading-5 text-ink-soft">Hỗ trợ theo chính sách hiện hành của MommyKids.</p>
                </div>
            </div>
        </div>
    </section>


    @if ($category->slug === 'sua-cho-be')
        <script>
            (() => {
                const button = document.getElementById('milk-guide-toggle');
                const more = document.getElementById('milk-guide-more');
                const label = document.getElementById('milk-guide-toggle-label');
                const icon = document.getElementById('milk-guide-toggle-icon');

                if (!button || !more || !label || !icon) return;

                button.addEventListener('click', () => {
                    const isOpen = button.getAttribute('aria-expanded') === 'true';
                    button.setAttribute('aria-expanded', String(!isOpen));
                    more.classList.toggle('hidden', isOpen);
                    label.textContent = isOpen ? 'Xem thêm' : 'Thu gọn';
                    icon.textContent = isOpen ? '↓' : '↑';
                });
            })();
        </script>
    @endif

@endsection
