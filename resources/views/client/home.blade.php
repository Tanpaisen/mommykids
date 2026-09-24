@extends('client.layouts.app')

@section(
    'title',
    'MommyKids — Trang chủ'
)


@push('styles')
<style>
    .mk-home {
        width:100%;
        max-width:1330px;

        margin:0 auto;
        padding:1.5rem;
    }

    .mk-home * {
        box-sizing:border-box;
    }

    .mk-home-card {
        border-radius:1.25rem;

        background:#fff;

        box-shadow:
            0 4px 20px
            rgba(43,37,48,.08);
    }


    /* =====================================================
       STAGE
    ===================================================== */
    .mk-stage-grid {
        display:grid;
        grid-template-columns:repeat(5,minmax(0,1fr));

        gap:.75rem;
    }

    .mk-stage-card {
        display:flex;
        align-items:center;

        min-width:0;

        gap:.75rem;
        padding:1rem;

        border-radius:1rem;

        text-decoration:none;
    }

    .mk-stage-icon {
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;

        width:3rem;
        height:3rem;

        border-radius:50%;

        font-size:1.4rem;
    }

    .mk-stage-title {
        margin:0;

        color:#2B2530;

        font-size:.85rem;
        font-weight:700;
        line-height:1.25;
    }

    .mk-stage-desc {
        margin:0;

        color:#6B6470;

        font-size:.75rem;
        line-height:1.3;
    }

    .mk-stage-link {
        font-size:.75rem;
        font-weight:600;
    }


    /* =====================================================
       HERO + CATEGORY
    ===================================================== */
    .mk-home-hero-grid {
        display:grid;
        grid-template-columns:260px minmax(0,1fr);

        gap:1rem;
        margin-bottom:1.5rem;
    }

    .mk-home-category {
        min-height:340px;

        padding:.75rem 0;

        overflow:hidden;
    }

    .mk-home-category-title {
        margin:0;

        padding:0 1rem .65rem;

        color:#6B6470;

        font-size:.7rem;
        font-weight:700;

        letter-spacing:.08em;
        text-transform:uppercase;
    }

    .mk-home-category-list {
        list-style:none;

        margin:0;
        padding:0;
    }

    .mk-home-category-list a {
    display:flex;
    align-items:center;

    gap:.7rem;
    padding:.55rem 1.15rem;

    color:#2B2530;

    font-size:.9rem;
    font-weight:500;
    line-height:1.35;

    text-decoration:none;

    transition:
        background .15s ease,
        color .15s ease;
}

    .mk-category-icon {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    flex:0 0 1.2rem;

    width:1.2rem;

    font-size:1rem;
}

    .mk-home-hero {
        position:relative;

        display:flex;
        align-items:center;

        min-height:340px;

        border-radius:1.25rem;

        overflow:hidden;

        background:
            linear-gradient(
                135deg,
                #FF6F81,
                #F5A623
            );
    }

    .mk-home-hero-inner {
        position:relative;
        z-index:2;

        flex:1;

        padding:2.5rem 3rem;
    }

    .mk-home-hero-tag {
        display:inline-block;

        margin-bottom:.75rem;
        padding:.3rem .8rem;

        border-radius:999px;

        background:rgba(255,255,255,.20);
        color:#fff;

        font-size:.75rem;
        font-weight:600;
    }

    .mk-home-hero h1 {
    margin:0 0 .65rem;

    color:#fff;

    font-family:'Baloo 2',cursive;
    font-size:2.35rem;
    font-weight:800;

    line-height:1.12;
}

    .mk-home-hero p {
    margin:0 0 1.2rem;

    color:rgba(255,255,255,.9);

    font-size:.9rem;
}

    .mk-home-hero-button {
        display:inline-flex;
        align-items:center;

        gap:.4rem;
        padding:.6rem 1.4rem;

        border-radius:999px;

        background:#fff;
        color:#FF6F81;

       font-size:.82rem;
        font-weight:600;

        text-decoration:none;
    }

    .mk-home-dot {
        width:.6rem;
        height:.6rem;

        border-radius:999px;

        background:#fff;

        opacity:.5;
    }

    .mk-home-dot.active {
        width:1.8rem;

        opacity:1;
    }


    /* =====================================================
       TRUST
    ===================================================== */
    .mk-trust-grid {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));

        gap:1rem;
        margin-bottom:2rem;
    }

    .mk-trust-card {
        display:flex;
        align-items:center;

        gap:.75rem;
        padding:1rem;
    }

    .mk-trust-icon {
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;

        width:2.5rem;
        height:2.5rem;

        border-radius:50%;

        font-size:1.2rem;
    }


    /* =====================================================
       QUICK ACTION
    ===================================================== */
    .mk-quick-grid {
        display:grid;
        grid-template-columns:repeat(7,minmax(0,1fr));

        gap:.75rem;

        text-align:center;
    }

    .mk-quick-item {
        display:flex;
        flex-direction:column;
        align-items:center;

        gap:.4rem;

        text-decoration:none;
    }

    .mk-quick-icon {
        display:flex;
        align-items:center;
        justify-content:center;

        width:3rem;
        height:3rem;

        border-radius:1rem;

        font-size:1.3rem;
    }


    /* =====================================================
       SECTIONS
    ===================================================== */
    .mk-section-header {
        display:flex;
        align-items:center;
        justify-content:space-between;

        gap:1rem;
        margin-bottom:1rem;
    }

    .mk-section-title-wrap {
        display:flex;
        align-items:center;

        gap:.6rem;
    }

    .mk-section-title {
        margin:0;

        font-family:'Baloo 2',cursive;
        font-size:1.4rem;
        font-weight:700;
    }

    .mk-section-more {
        flex-shrink:0;

        color:#1DB8A0;

        font-size:.875rem;
        font-weight:600;

        text-decoration:none;
    }

    .mk-tabs {
        display:flex;
        flex-wrap:wrap;

        gap:.5rem;
        margin-bottom:1rem;
    }

    .mk-tab {
        padding:.35rem .9rem;

        border:1px solid #f0eef5;
        border-radius:999px;

        background:#fff;
        color:#2B2530;

        font-size:.8rem;
        font-weight:500;

        text-decoration:none;
    }

    .mk-tab.active {
        border-color:#FF6F81;

        background:#FF6F81;
        color:#fff;

        font-weight:600;
    }

    .mk-product-grid {
        display:grid;
        grid-template-columns:repeat(5,minmax(0,1fr));

        gap:1rem;
    }


    /* =====================================================
       VOUCHER
    ===================================================== */
    .mk-voucher-strip {
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;

        gap:1rem;
        margin-bottom:2rem;
        padding:1.75rem 2rem;

        border-radius:1.25rem;

        background:
            linear-gradient(
                135deg,
                #FF6F81,
                #F5A623
            );
    }


    /* =====================================================
       BLOG / STORE / BRAND
    ===================================================== */
    .mk-bottom-grid {
        display:grid;
        grid-template-columns:1fr 1fr;

        gap:1.5rem;
        margin-bottom:2rem;
    }

    .mk-blog-card {
        display:flex;
        align-items:flex-start;

        gap:.75rem;
        padding:.75rem;

        text-decoration:none;
    }

    .mk-store-card {
        padding:1.25rem;

        background:
            linear-gradient(
                135deg,
                #E1F7F4,
                #fff
            );
    }

    .mk-brand-grid {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));

        gap:.5rem;
    }

    .mk-brand {
        display:flex;
        align-items:center;
        justify-content:center;

        height:3rem;

        padding:.6rem;

        border:1px solid #f0eef5;
        border-radius:.75rem;

        color:#6B6470;

        font-size:.7rem;
        font-weight:700;

        text-align:center;
    }


    /* =====================================================
       ABOUT
    ===================================================== */
    .mk-about-strip {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));

        gap:1.5rem;
        margin-bottom:2rem;
        padding:2rem;

        border-radius:1.25rem;

        background:
            linear-gradient(
                135deg,
                #2B2530,
                #3d3545
            );

        text-align:center;
    }


    /* =====================================================
       RESPONSIVE
    ===================================================== */
    @media (max-width:1050px) {
        .mk-stage-grid {
            grid-template-columns:
                repeat(3,minmax(0,1fr));
        }

        .mk-product-grid {
            grid-template-columns:
                repeat(4,minmax(0,1fr));
        }

        .mk-quick-grid {
            grid-template-columns:
                repeat(4,minmax(0,1fr));
        }
    }

    @media (max-width:850px) {
        .mk-home-hero-grid {
            grid-template-columns:1fr;
        }

        .mk-home-category {
            display:none;
        }

        .mk-trust-grid {
            grid-template-columns:
                repeat(2,minmax(0,1fr));
        }

        .mk-product-grid {
            grid-template-columns:
                repeat(3,minmax(0,1fr));
        }

        .mk-bottom-grid {
            grid-template-columns:1fr;
        }

        .mk-about-strip {
            grid-template-columns:
                repeat(2,minmax(0,1fr));
        }
    }

    @media (max-width:600px) {
        .mk-home {
            padding:1rem;
        }

        .mk-stage-grid {
            grid-template-columns:1fr;
        }

        .mk-product-grid {
            grid-template-columns:
                repeat(2,minmax(0,1fr));

            gap:.75rem;
        }

        .mk-home-hero {
            min-height:300px;
        }

        .mk-home-hero-inner {
            padding:2rem 1.5rem;
        }

        .mk-home-hero h1 {
            font-size:2rem;
        }

        .mk-brand-grid {
            grid-template-columns:
                repeat(2,minmax(0,1fr));
        }
    }
</style>
@endpush


@section('content')

<div class="mk-home">

    {{-- =====================================================
        STAGE BANNERS
    ====================================================== --}}
    <section style="margin-bottom:1.5rem;">

        <div class="mk-stage-grid">

            <a
                href="{{ route('category.show', 'me-bau-sau-sinh') }}?stage[]=2&stage[]=3&stage[]=4"
                class="mk-stage-card"
                style="
                    background:#FFF3DC;
                    border:1px solid #FAC775;
                "
            >
                <div
                    class="mk-stage-icon"
                    style="background:#F5A623;"
                >
                    🤰
                </div>

                <div>
                    <p class="mk-stage-title">
                        Mẹ mang thai
                    </p>

                    <p class="mk-stage-desc">
                        Dinh dưỡng cho mẹ & thai nhi
                    </p>

                    <span
                        class="mk-stage-link"
                        style="color:#1DB8A0;"
                    >
                        Khám phá →
                    </span>
                </div>
            </a>


            <a
                href="{{ route('category.show', 'me-bau-sau-sinh') }}?stage[]=30011"
                class="mk-stage-card"
                style="
                    background:#E1F7F4;
                    border:1px solid #9FE1CB;
                "
            >
                <div
                    class="mk-stage-icon"
                    style="background:#1DB8A0;"
                >
                    👶
                </div>

                <div>
                    <p class="mk-stage-title">
                        Chăm mẹ sau sinh
                    </p>

                    <p class="mk-stage-desc">
                        Phục hồi sức khoẻ, lợi sữa
                    </p>

                    <span
                        class="mk-stage-link"
                        style="color:#1DB8A0;"
                    >
                        Khám phá →
                    </span>
                </div>
            </a>


            <a
                href="{{ route('category.show', 'sua-cho-be') }}?stage[]=5&stage[]=6&stage[]=7"
                class="mk-stage-card"
                style="
                    background:#FFE3E8;
                    border:1px solid #F5C4B3;
                "
            >
                <div
                    class="mk-stage-icon"
                    style="background:#FF6F81;"
                >
                    🍼
                </div>

                <div>
                    <p class="mk-stage-title">
                        Con 0–12 tháng
                    </p>

                    <p class="mk-stage-desc">
                        Sữa, bỉm, đồ sơ sinh
                    </p>

                    <span
                        class="mk-stage-link"
                        style="color:#FF6F81;"
                    >
                        Khám phá →
                    </span>
                </div>
            </a>


            <a
                href="{{ route('category.show', 'sua-cho-be') }}?stage[]=8"
                class="mk-stage-card"
                style="
                    background:#EAF3DE;
                    border:1px solid #C0DD97;
                "
            >
                <div
                    class="mk-stage-icon"
                    style="background:#639922;"
                >
                    🧒
                </div>

                <div>
                    <p class="mk-stage-title">
                        Con 1–2 tuổi
                    </p>

                    <p class="mk-stage-desc">
                        Dinh dưỡng, phát triển trí não
                    </p>

                    <span
                        class="mk-stage-link"
                        style="color:#639922;"
                    >
                        Khám phá →
                    </span>
                </div>
            </a>


            <a
                href="{{ route('category.show', 'sua-cho-be') }}?stage[]=9&stage[]=10"
                class="mk-stage-card"
                style="
                    background:#E6F1FB;
                    border:1px solid #B5D4F4;
                "
            >
                <div
                    class="mk-stage-icon"
                    style="background:#378ADD;"
                >
                    🌟
                </div>

                <div>
                    <p class="mk-stage-title">
                        Con 2–5 tuổi
                    </p>

                    <p class="mk-stage-desc">
                        Tăng cường đề kháng
                    </p>

                    <span
                        class="mk-stage-link"
                        style="color:#378ADD;"
                    >
                        Khám phá →
                    </span>
                </div>
            </a>

        </div>

    </section>


    {{-- =====================================================
        CATEGORY SIDEBAR + HERO
    ====================================================== --}}
    <section class="mk-home-hero-grid">

        {{-- CATEGORY --}}
        <aside
            class="mk-home-card mk-home-category"
        >
            <p class="mk-home-category-title">
                Danh mục
            </p>

            <ul class="mk-home-category-list">

                @foreach ($homeCategories as $category)

                    <li>
                        <a
                            href="{{ route(
                                'category.show',
                                $category->slug
                            ) }}"
                        >
                            <span class="mk-category-icon">

                                @switch($category->slug)

                                    @case('sua-cho-be')
                                        🍼
                                        @break

                                    @case('bim-ta-ve-sinh')
                                        🧷
                                        @break

                                    @case('binh-sua-phu-kien')
                                        🍶
                                        @break

                                    @case('an-dam-dinh-duong')
                                        🥣
                                        @break

                                    @case('vitamin-suc-khoe')
                                        💊
                                        @break

                                    @case('do-dung-me-be')
                                        🧴
                                        @break

                                    @case('do-so-sinh')
                                        👶
                                        @break

                                    @case('do-choi-hoc-tap')
                                        🧸
                                        @break

                                    @case('xe-cho-be')
                                        🚼
                                        @break

                                    @case('me-bau-sau-sinh')
                                        🤰
                                        @break

                                    @default
                                        {{ $category->icon ?? '🛍️' }}

                                @endswitch

                            </span>

                            <span>
                                {{ $category->name }}
                            </span>
                        </a>
                    </li>

                @endforeach

            </ul>


            <div
                style="
                    border-top:1px solid #f0eef5;
                    margin-top:.5rem;
                    padding:.65rem 1rem 0;
                "
            >
                <a
                     href="{{ route('categories.index') }}"
                    style="
                        color:#1DB8A0;

                        font-size:.88rem;
                        font-weight:600;

                        text-decoration:none;
                    "
                >
                    Xem tất cả danh mục →
                </a>
            </div>
        </aside>


        {{-- HERO --}}
        <div class="mk-home-hero">

            <div class="mk-home-hero-inner">

                <span class="mk-home-hero-tag">
                    🎁 Ưu đãi tháng 9
                </span>

                <h1>
                    Sữa thùng
                    <br>
                    giá tốt tháng này
                </h1>

                <p>
                    Chính hãng · Hóa đơn VAT đầy đủ ·
                    Bảo giá tốt nhất
                </p>

                <div
                    style="
                        display:flex;
                        align-items:center;
                        flex-wrap:wrap;

                        gap:.75rem;
                    "
                >
                    <a
                        href="{{ route('products.featured') }}"
                        class="mk-home-hero-button"
                    >
                        Mua ngay
                    </a>

                    <a
                        href="{{ route('vouchers.index') }}"
                        style="
                            display:flex;
                            align-items:center;

                            color:#fff;

                            font-size:.72rem;
                            font-weight:500;

                            text-decoration:none;

                            opacity:.9;
                        "
                    >
                        Xem khuyến mãi →
                    </a>
                </div>
            </div>


            {{-- DOTS --}}
            <div
                style="
                    position:absolute;

                    bottom:1rem;
                    left:50%;

                    transform:translateX(-50%);

                    display:flex;

                    gap:.4rem;
                "
            >
                <span class="mk-home-dot active"></span>
                <span class="mk-home-dot"></span>
                <span class="mk-home-dot"></span>
            </div>

        </div>

    </section>


    {{-- =====================================================
        TRUST BADGES
    ====================================================== --}}
    <section class="mk-trust-grid">

        <div class="mk-home-card mk-trust-card">

            <span
                class="mk-trust-icon"
                style="background:#E1F7F4;"
            >
                ✅
            </span>

            <div>
                <p
                    style="
                        margin:0;

                        font-size:.875rem;
                        font-weight:700;
                    "
                >
                    100% Chính hãng
                </p>

                <p
                    style="
                        margin:0;

                        color:#6B6470;

                        font-size:.75rem;
                    "
                >
                    Có tem phụ, rõ nguồn gốc
                </p>
            </div>
        </div>


        <div class="mk-home-card mk-trust-card">

            <span
                class="mk-trust-icon"
                style="background:#E1F7F4;"
            >
                🚚
            </span>

            <div>
                <p
                    style="
                        margin:0;

                        font-size:.875rem;
                        font-weight:700;
                    "
                >
                    Miễn phí giao hàng
                </p>

                <p
                    style="
                        margin:0;

                        color:#6B6470;

                        font-size:.75rem;
                    "
                >
                    Nội thành HN cho đơn 299k+
                </p>
            </div>
        </div>


        <div class="mk-home-card mk-trust-card">

            <span
                class="mk-trust-icon"
                style="background:#FFE3E8;"
            >
                🔄
            </span>

            <div>
                <p
                    style="
                        margin:0;

                        font-size:.875rem;
                        font-weight:700;
                    "
                >
                    14 ngày đổi trả
                </p>

                <p
                    style="
                        margin:0;

                        color:#6B6470;

                        font-size:.75rem;
                    "
                >
                    Bảo vệ quyền lợi mua sắm
                </p>
            </div>
        </div>


        <div class="mk-home-card mk-trust-card">

            <span
                class="mk-trust-icon"
                style="background:#FFF3DC;"
            >
                🎁
            </span>

            <div>
                <p
                    style="
                        margin:0;

                        font-size:.875rem;
                        font-weight:700;
                    "
                >
                    Giảm giá cực sâu
                </p>

                <p
                    style="
                        margin:0;

                        color:#6B6470;

                        font-size:.75rem;
                    "
                >
                    Ưu đãi hấp dẫn mỗi ngày
                </p>
            </div>
        </div>

    </section>


    {{-- =====================================================
        QUICK ACTIONS
    ====================================================== --}}
    <section style="margin-bottom:2rem;">

        <div
            class="mk-home-card"
            style="padding:1.25rem;"
        >
            <div class="mk-quick-grid">

                @foreach ([
                    [
                        'icon' => '🆕',
                        'label' => 'Hàng mới',
                        'url' => '#',
                        'bg' => '#FFE3E8',
                    ],
                    [
                        'icon' => '🏬',
                        'label' => 'Tìm cửa hàng',
                        'url' => '#mk-stores',
                        'bg' => '#E1F7F4',
                    ],
                    [
                        'icon' => '📞',
                        'label' => 'Hotline',
                        'url' => 'tel:18006886',
                        'bg' => '#FFE3E8',
                    ],
                    [
                        'icon' => '🎁',
                        'label' => 'Đổi quà',
                        'url' => '#',
                        'bg' => '#FFF3DC',
                    ],
                    [
                        'icon' => '🎟️',
                        'label' => 'Voucher',
                        'url' => route('vouchers.index'),
                        'bg' => '#FFE3E8',
                    ],
                    [
                        'icon' => '📱',
                        'label' => 'Mini App',
                        'url' => '#',
                        'bg' => '#E1F7F4',
                    ],
                    [
                        'icon' => '📅',
                        'label' => 'Sự kiện',
                        'url' => '#',
                        'bg' => '#FFF3DC',
                    ],
                ] as $action)

                    <a
                        href="{{ $action['url'] }}"
                        class="mk-quick-item"
                    >
                        <span
                            class="mk-quick-icon"
                            style="
                                background:
                                    {{ $action['bg'] }};
                            "
                        >
                            {{ $action['icon'] }}
                        </span>

                        <span
                            style="
                                color:#2B2530;

                                font-size:.75rem;
                                font-weight:500;
                            "
                        >
                            {{ $action['label'] }}
                        </span>
                    </a>

                @endforeach

            </div>
        </div>

    </section>


    {{-- =====================================================
        FEATURED PRODUCTS
    ====================================================== --}}
    @if ($featuredProducts->isNotEmpty())

        <section style="margin-bottom:2rem;">

            <div class="mk-section-header">

                <div class="mk-section-title-wrap">

                    <span style="font-size:1.4rem;">
                        ⭐
                    </span>

                    <h2 class="mk-section-title">
                        Sản phẩm nổi bật
                    </h2>

                </div>

                <a
                    href="{{ route('products.featured') }}"
                    class="mk-section-more"
                >
                    Xem tất cả →
                </a>

            </div>


            <div class="mk-tabs">

                <span class="mk-tab active">
                    Tất cả
                </span>

                @foreach ($homeCategories->take(4) as $category)

                    <a
                        href="{{ route(
                            'category.show',
                            $category->slug
                        ) }}"
                        class="mk-tab"
                    >
                        {{ $category->name }}
                    </a>

                @endforeach

            </div>


            <div class="mk-product-grid">

                @foreach (
                    $featuredProducts->take(5)
                    as $product
                )

                    <x-product-card
                        :product="$product"
                        :product-id="$product['id']"
                    />

                @endforeach

            </div>

        </section>

    @endif


    {{-- =====================================================
        VOUCHER
    ====================================================== --}}
    <section class="mk-voucher-strip">

        <div>

            <h2
                style="
                    margin:0 0 .3rem;

                    color:#fff;

                    font-family:'Baloo 2',cursive;
                    font-size:1.5rem;
                    font-weight:800;
                "
            >
                Ưu đãi dành cho ba mẹ
            </h2>

            <p
                style="
                    margin:0;

                    color:rgba(255,255,255,.9);

                    font-size:.9rem;
                "
            >
                Nhập mã ngay để nhận ưu đãi
                cho lần mua đầu tiên
            </p>

        </div>


        <div
            style="
                display:flex;
                gap:.75rem;
            "
        >

            <div
                style="
                    padding:.6rem 1rem;

                    border-radius:.75rem;

                    background:
                        rgba(255,255,255,.2);

                    text-align:center;
                "
            >
                <p
                    style="
                        margin:0;

                        color:#fff;

                        font-family:'Baloo 2',cursive;
                        font-size:1.2rem;
                        font-weight:800;
                    "
                >
                    30K
                </p>

                <p
                    style="
                        margin:0;

                        color:rgba(255,255,255,.8);

                        font-size:.7rem;
                    "
                >
                    Voucher
                </p>
            </div>


            <div
                style="
                    padding:.6rem 1rem;

                    border-radius:.75rem;

                    background:
                        rgba(255,255,255,.2);

                    text-align:center;
                "
            >
                <p
                    style="
                        margin:0;

                        color:#fff;

                        font-family:'Baloo 2',cursive;
                        font-size:1.2rem;
                        font-weight:800;
                    "
                >
                    Campaign
                </p>

                <p
                    style="
                        margin:0;

                        color:rgba(255,255,255,.8);

                        font-size:.7rem;
                    "
                >
                    Giá tốt
                </p>
            </div>

        </div>


        <a
            href="{{ route('vouchers.index') }}"
            style="
                flex-shrink:0;

                padding:.7rem 1.8rem;

                border-radius:999px;

                background:#fff;
                color:#FF6F81;

                font-size:.9rem;
                font-weight:700;

                text-decoration:none;
            "
        >
            Nhận ngay
        </a>

    </section>


    {{-- =====================================================
        BLOG + STORE
    ====================================================== --}}
    <section class="mk-bottom-grid">

        {{-- BLOG --}}
        <div>

            <div class="mk-section-header">

                <h2
                    style="
                        margin:0;

                        font-family:'Baloo 2',cursive;
                        font-size:1.3rem;
                        font-weight:700;
                    "
                >
                    📚 Cẩm nang mẹ & bé
                </h2>

                <a
                    href="{{ url('/cam-nang') }}"
                    style="
                        color:#1DB8A0;

                        font-size:.8rem;
                        font-weight:600;

                        text-decoration:none;
                    "
                >
                    Xem tất cả →
                </a>

            </div>


            <div
                style="
                    display:flex;
                    flex-wrap:wrap;

                    gap:.4rem;
                    margin-bottom:.75rem;
                "
            >
                <span
                    style="
                        padding:.2rem .7rem;

                        border-radius:999px;

                        background:#FFE3E8;
                        color:#E8536A;

                        font-size:.75rem;
                        font-weight:600;
                    "
                >
                    Tin tức
                </span>

                <span
                    style="
                        padding:.2rem .7rem;

                        border-radius:999px;

                        background:#E1F7F4;
                        color:#0F6E56;

                        font-size:.75rem;
                        font-weight:600;
                    "
                >
                    Kiến thức mẹ & bé
                </span>

                <span
                    style="
                        padding:.2rem .7rem;

                        border-radius:999px;

                        background:#FFF3DC;
                        color:#854F0B;

                        font-size:.75rem;
                        font-weight:600;
                    "
                >
                    Tuyển dụng
                </span>
            </div>


            <div
                style="
                    display:flex;
                    flex-direction:column;

                    gap:.75rem;
                "
            >

                @foreach ([
                    [
                        'icon' => '📝',
                        'title' =>
                            'Cẩm nang ăn dặm cho bé 6 tháng tuổi — những điều mẹ cần biết',
                        'bg' => '#FFE3E8',
                    ],
                    [
                        'icon' => '🤱',
                        'title' =>
                            'Chọn sữa công thức phù hợp theo từng giai đoạn của bé',
                        'bg' => '#E1F7F4',
                    ],
                    [
                        'icon' => '🦷',
                        'title' =>
                            'Dấu hiệu bé mọc răng và cách chăm sóc đúng cách',
                        'bg' => '#FFF3DC',
                    ],
                ] as $article)

                    <a
                        href="{{ url('/cam-nang') }}"
                        class="mk-home-card mk-blog-card"
                    >
                        <div
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                flex-shrink:0;

                                width:5rem;
                                height:5rem;

                                border-radius:.75rem;

                                background:
                                    {{ $article['bg'] }};

                                font-size:1.8rem;
                            "
                        >
                            {{ $article['icon'] }}
                        </div>


                        <div style="padding:.5rem;">

                            <span
                                style="
                                    padding:.15rem .5rem;

                                    border-radius:999px;

                                    background:#FFE3E8;
                                    color:#E8536A;

                                    font-size:.7rem;
                                    font-weight:600;
                                "
                            >
                                Kiến thức
                            </span>

                            <p
                                style="
                                    margin:.3rem 0 .2rem;

                                    color:#2B2530;

                                    font-size:.875rem;
                                    font-weight:600;
                                    line-height:1.4;
                                "
                            >
                                {{ $article['title'] }}
                            </p>

                            <p
                                style="
                                    margin:0;

                                    color:#6B6470;

                                    font-size:.75rem;
                                "
                            >
                                Đọc bài →
                            </p>

                        </div>
                    </a>

                @endforeach

            </div>

        </div>


        {{-- STORE + BRAND --}}
        <div
            style="
                display:flex;
                flex-direction:column;

                gap:1.25rem;
            "
        >

            <div
                id="mk-stores"
                class="mk-home-card mk-store-card"
            >

                <p
                    style="
                        margin:0 0 .25rem;

                        color:#1DB8A0;

                        font-size:.75rem;
                        font-weight:700;

                        letter-spacing:.06em;
                        text-transform:uppercase;
                    "
                >
                    Hệ thống cửa hàng
                </p>

                <h3
                    style="
                        margin:0 0 .5rem;

                        color:#2B2530;

                        font-family:'Baloo 2',cursive;
                        font-size:1.3rem;
                        font-weight:700;
                    "
                >
                    Tìm MommyKids gần bạn
                </h3>

                <p
                    style="
                        margin:0 0 .75rem;

                        color:#6B6470;

                        font-size:.825rem;
                    "
                >
                    Hệ thống cửa hàng MommyKids
                    phục vụ ba mẹ mỗi ngày
                </p>


                <div
                    style="
                        display:grid;
                        grid-template-columns:1fr 1fr;

                        gap:.5rem;
                        margin-bottom:.75rem;
                    "
                >

                    @foreach ([
                        'Cầu Giấy',
                        'Hà Đông',
                        'Hoàng Mai',
                        'Thanh Xuân',
                    ] as $district)

                        <a
                            href="#"
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;

                                padding:.5rem .75rem;

                                border:1px solid #E1F7F4;
                                border-radius:.75rem;

                                background:#fff;
                                color:#2B2530;

                                font-size:.8rem;

                                text-decoration:none;
                            "
                        >
                            {{ $district }}

                            <span
                                style="
                                    color:#1DB8A0;
                                    font-weight:600;
                                "
                            >
                                Xem →
                            </span>
                        </a>

                    @endforeach

                </div>


                <a
                    href="#"
                    style="
                        display:inline-flex;

                        padding:.6rem 1.4rem;

                        border-radius:999px;

                        background:#1DB8A0;
                        color:#fff;

                        font-size:.825rem;
                        font-weight:600;

                        text-decoration:none;
                    "
                >
                    Xem toàn bộ hệ thống
                </a>

            </div>


            {{-- BRANDS --}}
            <div
                class="mk-home-card"
                style="padding:1.25rem;"
            >

                <p
                    style="
                        margin:0 0 .75rem;

                        color:#6B6470;

                        font-size:.8rem;
                        font-weight:700;
                    "
                >
                    Thương hiệu được mẹ tin dùng
                </p>

                <div class="mk-brand-grid">

                    @foreach ([
                        'Moony',
                        'Meiji',
                        'Bebejoy',
                        'Comotomo',
                        'Grow Plus',
                        'BB Nature',
                        'Aptamil',
                        "Bellamy's",
                    ] as $brand)

                        <div class="mk-brand">
                            {{ $brand }}
                        </div>

                    @endforeach

                </div>

            </div>

        </div>

    </section>


    {{-- =====================================================
        ABOUT STRIP
    ====================================================== --}}
    <section
        id="gioi-thieu"
        class="mk-about-strip"
    >

        <div>

            <p
                style="
                    margin:0;

                    color:#FF6F81;

                    font-family:'Baloo 2',cursive;
                    font-size:2rem;
                    font-weight:800;
                "
            >
                15+
            </p>

            <p
                style="
                    margin:.3rem 0 0;

                    color:rgba(255,255,255,.8);

                    font-size:.825rem;
                "
            >
                Cửa hàng tại Hà Nội
            </p>

        </div>


        <div>

            <p
                style="
                    margin:0;

                    color:#1DB8A0;

                    font-family:'Baloo 2',cursive;
                    font-size:2rem;
                    font-weight:800;
                "
            >
                50K+
            </p>

            <p
                style="
                    margin:.3rem 0 0;

                    color:rgba(255,255,255,.8);

                    font-size:.825rem;
                "
            >
                Mẹ bỉm tin tưởng
            </p>

        </div>


        <div>

            <p
                style="
                    margin:0;

                    color:#F5A623;

                    font-family:'Baloo 2',cursive;
                    font-size:2rem;
                    font-weight:800;
                "
            >
                {{ number_format(
                    $featuredProducts->count()
                ) }}+
            </p>

            <p
                style="
                    margin:.3rem 0 0;

                    color:rgba(255,255,255,.8);

                    font-size:.825rem;
                "
            >
                Sản phẩm nổi bật
            </p>

        </div>


        <div>

            <p
                style="
                    margin:0;

                    color:#FF6F81;

                    font-family:'Baloo 2',cursive;
                    font-size:2rem;
                    font-weight:800;
                "
            >
                ⭐
            </p>

            <p
                style="
                    margin:.3rem 0 0;

                    color:rgba(255,255,255,.8);

                    font-size:.825rem;
                "
            >
                Đánh giá từ khách hàng
            </p>

        </div>

    </section>


    {{-- =====================================================
        CATEGORY PRODUCT SECTIONS
    ====================================================== --}}
    @foreach ($sections as $section)

        <section
            class="mk-home-card"
            style="
                margin-bottom:2rem;
                padding:1.25rem;
            "
        >

            <div class="mk-section-header">

                <div class="mk-section-title-wrap">

                    <span style="font-size:1.4rem;">
                        {{ $section['icon'] ?? '🛍️' }}
                    </span>

                    <h2 class="mk-section-title">
                        {{ $section['title'] }}
                    </h2>

                </div>

                <a
                    href="{{ $section['url'] }}"
                    class="mk-section-more"
                >
                    Xem tất cả →
                </a>

            </div>


            <div class="mk-product-grid">

                @foreach (
                    $section['products']->take(5)
                    as $product
                )

                    <x-product-card
                        :product="$product"
                        :product-id="$product['id']"
                    />

                @endforeach

            </div>

        </section>

    @endforeach

</div>

@endsection