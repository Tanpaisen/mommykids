<style>
    .mk-header-link {
        display: inline-block;
        flex-shrink: 0;

        color: #2B2530;

        font-size: .78rem;
        font-weight: 500;
        line-height: 1.15;

        text-decoration: none;
        white-space: nowrap;

        transition: color .15s ease;
    }

    .mk-header-link:hover {
        color: #FF6F81;
    }

    .mk-header-main {
        display: flex;
        align-items: center;

        width: 100%;
        max-width: 1360px;

        min-height: 72px;

        margin: 0 auto;
        padding: .5rem 1.5rem;

        gap: .85rem;
    }

    .mk-header-logo {
        display: flex;
        align-items: center;
        flex-shrink: 0;

        gap: .4rem;

        text-decoration: none;
    }

    .mk-header-nav {
        display: flex;
        align-items: center;
        flex-shrink: 0;

        gap: .9rem;
    }

    .mk-header-search {
        position: relative;

        flex: 1 1 190px;

        min-width: 150px;
        max-width: 300px;

        margin-left: .15rem;
    }

    .mk-header-right {
        display: flex;
        align-items: center;
        flex-shrink: 0;

        gap: .6rem;
    }

    .mk-dropdown-link {
        display: block;

        padding: .45rem 1rem;

        color: #2B2530;

        font-size: .82rem;
        line-height: 1.35;

        text-decoration: none;
        white-space: nowrap;
    }

    .mk-dropdown-coral:hover {
        background: #FFE3E8;
    }

    .mk-dropdown-teal:hover {
        background: #E1F7F4;
    }

    @media (max-width: 1250px) {
        .mk-header-main {
            gap: .65rem;
        }

        .mk-header-nav {
            gap: .65rem;
        }

        .mk-header-link {
            font-size: .74rem;
        }

        .mk-header-search {
            max-width: 240px;
        }
    }

    @media (max-width: 1199px) {
        .mk-header-nav {
            display: none;
        }

        .mk-header-search {
            max-width: none;
        }
    }

    .mk-header-wishlist {
        position: relative;

        display: flex;
        align-items: center;
        justify-content: center;

        width: 34px;
        height: 34px;

        border: 1px solid #FFE3E8;
        border-radius: 50%;

        background: #fff;
        color: #FF6F81;

        text-decoration: none;

        transition:
            background .15s ease,
            color .15s ease,
            transform .15s ease;
    }

    .mk-header-wishlist:hover {
        background: #FFE3E8;
        transform: translateY(-1px);
    }

    .mk-header-wishlist-count {
        position: absolute;
        top: -5px;
        right: -5px;

        display: flex;
        align-items: center;
        justify-content: center;

        min-width: 17px;
        height: 17px;

        padding: 0 4px;

        border-radius: 999px;

        background: #1DB8A0;
        color: #fff;

        font-size: 9px;
        font-weight: 700;
        line-height: 1;
    }

    @media (max-width: 1023px) {
        .mk-header-main {
            min-height: 64px;

            padding-left: 1rem;
            padding-right: 1rem;
        }

        .mk-header-right {
            display: none;
        }
    }
</style>


{{-- =========================================================
    ANNOUNCEMENT BAR
========================================================= --}}
<div
    style="
        background:#1DB8A0;
        color:#fff;
        text-align:center;
        font-size:.8rem;
        padding:.5rem 1rem;
        font-weight:600;
        line-height:1.35;
    "
>
    🚚 Miễn phí giao hàng nội thành Hà Nội cho đơn từ 299k

    <span class="hidden sm:inline">
        &nbsp;•&nbsp;
        🔄 Đổi trả thoải mái 14 ngày
        &nbsp;•&nbsp;
        ⭐ Tích điểm – Hoàn xu mọi đơn hàng
    </span>
</div>


{{-- =========================================================
    HEADER
========================================================= --}}
<header
    style="
        background:#fff;
        border-bottom:1px solid #f0eef5;
        position:sticky;
        top:0;
        z-index:50;
    "
>
    <div class="mk-header-main">

        {{-- MOBILE MENU --}}
        <button
            id="mk-sidebar-toggle"
            type="button"
            class="lg:hidden"
            aria-label="Mở danh mục"
            style="
                flex-shrink:0;
                border:0;
                background:none;
                cursor:pointer;
                padding:.25rem;
            "
        >
            <svg
                width="22"
                height="22"
                viewBox="0 0 24 24"
                fill="none"
                stroke="#2B2530"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>


        {{-- LOGO --}}
        <a
            href="{{ route('home') }}"
            class="mk-header-logo"
        >
            <span
                style="
                    width:2rem;
                    height:2rem;

                    border-radius:50%;

                    background:#FF6F81;
                    color:#fff;

                    display:flex;
                    align-items:center;
                    justify-content:center;

                    font-family:'Baloo 2',cursive;
                    font-size:.9rem;
                    font-weight:800;
                "
            >
                M
            </span>

            <span
                class="hidden sm:inline"
                style="
                    color:#2B2530;

                    font-family:'Baloo 2',cursive;
                    font-size:1.15rem;
                    font-weight:800;

                    white-space:nowrap;
                "
            >
                Mommy<span style="color:#FF6F81;">Kids</span>
            </span>
        </a>


        {{-- =================================================
            DESKTOP NAV
        ================================================== --}}
        <nav class="mk-header-nav">

            <a href="{{ route('pages.about') }}" class="mk-header-link">
                Về chúng tôi
            </a>

            <a href="{{ route('handbook.show') }}" class="mk-header-link">
                Cẩm nang
            </a>

            <a
                href="{{ route('vouchers.index') }}"
                class="mk-header-link"
            >
                Khuyến mãi
            </a>

            <a
                href="{{ route('home') }}#mk-stores"
                class="mk-header-link"
            >
                Cửa hàng
            </a>

            <a
                href="#mk-footer"
                class="mk-header-link"
            >
                Liên hệ
            </a>


            {{-- DÀNH CHO MẸ --}}
            <div
                style="position:relative;"
                onmouseenter="
                    document.getElementById('mk-dd-me')
                        .style.display='block'
                "
                onmouseleave="
                    document.getElementById('mk-dd-me')
                        .style.display='none'
                "
            >
                <button
                    type="button"
                    style="
                        display:flex;
                        align-items:center;

                        gap:.25rem;

                        padding:0;

                        border:none;
                        background:none;

                        color:#FF6F81;

                        font-family:'Be Vietnam Pro',sans-serif;
                        font-size:.78rem;
                        font-weight:600;

                        white-space:nowrap;
                        cursor:pointer;
                    "
                >
                    Dành cho Mẹ

                    <svg
                        width="11"
                        height="11"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#FF6F81"
                        stroke-width="2.5"
                    >
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>


                <div
                    id="mk-dd-me"
                    style="
                        display:none;

                        position:absolute;
                        top:100%;
                        left:0;

                        min-width:190px;

                        margin-top:.3rem;
                        padding:.5rem 0;

                        border:1px solid #f0eef5;
                        border-radius:.75rem;

                        background:#fff;

                        box-shadow:
                            0 8px 24px
                            rgba(0,0,0,.10);

                        z-index:100;
                    "
                >
                    <a
                        href="{{ route('category.show', 'me-bau-sau-sinh') }}"
                        class="mk-dropdown-link mk-dropdown-coral"
                    >
                        🤰 Mẹ mang thai
                    </a>

                    <a
                        href="{{ route('category.show', 'me-bau-sau-sinh') }}"
                        class="mk-dropdown-link mk-dropdown-coral"
                    >
                        🤱 Chăm sóc sau sinh
                    </a>

                    <a
                        href="{{ route('category.show', 'do-dung-me-be') }}"
                        class="mk-dropdown-link mk-dropdown-coral"
                    >
                        💄 Đồ dùng cho mẹ
                    </a>

                    <a
                        href="{{ route('category.show', 'vitamin-suc-khoe') }}"
                        class="mk-dropdown-link mk-dropdown-coral"
                    >
                        💊 Vitamin & sức khỏe
                    </a>
                </div>
            </div>


            {{-- DÀNH CHO BÉ --}}
            <div
                style="position:relative;"
                onmouseenter="
                    document.getElementById('mk-dd-be')
                        .style.display='block'
                "
                onmouseleave="
                    document.getElementById('mk-dd-be')
                        .style.display='none'
                "
            >
                <button
                    type="button"
                    style="
                        display:flex;
                        align-items:center;

                        gap:.25rem;

                        padding:0;

                        border:none;
                        background:none;

                        color:#1DB8A0;

                        font-family:'Be Vietnam Pro',sans-serif;
                        font-size:.78rem;
                        font-weight:600;

                        white-space:nowrap;
                        cursor:pointer;
                    "
                >
                    Dành cho Bé

                    <svg
                        width="11"
                        height="11"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#1DB8A0"
                        stroke-width="2.5"
                    >
                        <path d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>


                <div
                    id="mk-dd-be"
                    style="
                        display:none;

                        position:absolute;
                        top:100%;
                        left:0;

                        min-width:190px;

                        margin-top:.3rem;
                        padding:.5rem 0;

                        border:1px solid #f0eef5;
                        border-radius:.75rem;

                        background:#fff;

                        box-shadow:
                            0 8px 24px
                            rgba(0,0,0,.10);

                        z-index:100;
                    "
                >
                    <a
                        href="{{ route('category.show', 'do-so-sinh') }}"
                        class="mk-dropdown-link mk-dropdown-teal"
                    >
                        👶 Đồ sơ sinh
                    </a>

                    <a
                        href="{{ route('category.show', 'an-dam-dinh-duong') }}"
                        class="mk-dropdown-link mk-dropdown-teal"
                    >
                        🥣 Ăn dặm & dinh dưỡng
                    </a>

                    <a
                        href="{{ route('category.show', 'xe-cho-be') }}"
                        class="mk-dropdown-link mk-dropdown-teal"
                    >
                        🚼 Xe cho bé
                    </a>

                    <a
                        href="{{ route('category.show', 'do-choi-hoc-tap') }}"
                        class="mk-dropdown-link mk-dropdown-teal"
                    >
                        🧸 Đồ chơi & học tập
                    </a>
                </div>
            </div>


            <a
                href="{{ route('deals.index') }}"
                style="
                    flex-shrink:0;

                    color:#F5A623;

                    font-size:.78rem;
                    font-weight:700;

                    text-decoration:none;
                    white-space:nowrap;
                "
            >
                🔥 Deal hot
            </a>

        </nav>


        {{-- =================================================
            SEARCH
        ================================================== --}}
        <form
            action="{{ route('search') }}"
            method="GET"
            class="mk-header-search"
        >
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Tìm sản phẩm..."
                style="
                    width:100%;
                    height:36px;

                    padding:.35rem 2.55rem .35rem .9rem;

                    border:1.5px solid #FFE3E8;
                    border-radius:999px;

                    outline:none;

                    color:#2B2530;

                    font-family:'Be Vietnam Pro',sans-serif;
                    font-size:.8rem;
                "
            >

            <button
                type="submit"
                aria-label="Tìm kiếm"
                style="
                    position:absolute;
                    right:.3rem;
                    top:50%;
                    transform:translateY(-50%);

                    width:28px;
                    height:28px;

                    border:none;
                    border-radius:50%;

                    background:#FF6F81;

                    display:flex;
                    align-items:center;
                    justify-content:center;

                    cursor:pointer;
                "
            >
                <svg
                    width="12"
                    height="12"
                    fill="none"
                    stroke="#fff"
                    stroke-width="2.5"
                    viewBox="0 0 24 24"
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
            </button>
        </form>


        {{-- =================================================
            RIGHT ACTIONS
        ================================================== --}}
        @php
            $wishlistCount = 0;

            if (auth()->check()) {
                $wishlistCount = \App\Models\WishlistItem::query()
                    ->where('user_id', auth()->id())
                    ->count();
            }
        @endphp

        <div class="mk-header-right">

            {{-- LOCATION --}}
            <button
                type="button"
                style="
                    display:flex;
                    align-items:center;

                    gap:.25rem;

                    padding:0;

                    border:none;
                    background:none;

                    color:#6B6470;

                    font-family:'Be Vietnam Pro',sans-serif;
                    font-size:.76rem;

                    white-space:nowrap;
                    cursor:pointer;
                "
            >
                <svg
                    width="12"
                    height="12"
                    fill="none"
                    stroke="#1DB8A0"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"
                    />
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>

                {{ $currentCity ?? 'Hà Nội' }} ▾
            </button>


            {{-- NOTIFICATION --}}
            @auth
                <a
                    href="{{ route('notifications.index') }}"
                    title="Thông báo"
                    style="
                        width:28px;
                        height:28px;

                        display:flex;
                        align-items:center;
                        justify-content:center;

                        color:#2B2530;
                        text-decoration:none;
                    "
                >
                    <svg
                        width="18"
                        height="18"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>
                </a>
            @endauth


            {{-- WISHLIST --}}
            @auth
                <a
                    href="{{ route('wishlist.index') }}"
                    class="mk-header-wishlist"
                    title="Sản phẩm yêu thích"
                    aria-label="Sản phẩm yêu thích"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 21s-7-4.35-9.33-8.42C.91 9.5 2.06 5.5 5.72 4.32A5.2 5.2 0 0 1 12 6.09a5.2 5.2 0 0 1 6.28-1.77c3.66 1.18 4.81 5.18 3.05 8.26C19 16.65 12 21 12 21Z"
                        />
                    </svg>

                    <span
                        class="mk-header-wishlist-count"
                        data-wishlist-count
                    >
                        {{ $wishlistCount }}
                    </span>
                </a>
            @else
                <button
                    type="button"
                    onclick="openLoginModal()"
                    class="mk-header-wishlist"
                    title="Đăng nhập để xem sản phẩm yêu thích"
                    aria-label="Đăng nhập để xem sản phẩm yêu thích"
                    style="cursor:pointer;"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 21s-7-4.35-9.33-8.42C.91 9.5 2.06 5.5 5.72 4.32A5.2 5.2 0 0 1 12 6.09a5.2 5.2 0 0 1 6.28-1.77c3.66 1.18 4.81 5.18 3.05 8.26C19 16.65 12 21 12 21Z"
                        />
                    </svg>
                </button>
            @endauth


            {{-- CART --}}
            <a
                href="{{ route('cart.index') }}"
                style="
                    position:relative;

                    display:flex;
                    align-items:center;

                    gap:.35rem;

                    padding:.38rem .85rem;

                    border-radius:999px;

                    background:#FFE3E8;
                    color:#FF6F81;

                    font-size:.78rem;
                    font-weight:600;

                    text-decoration:none;
                    white-space:nowrap;
                "
            >
                <svg
                    width="14"
                    height="14"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                    />
                </svg>

                Giỏ hàng

                <span
                    class="mk-cart-count"
                    style="
                        position:absolute;
                        top:-.3rem;
                        right:-.2rem;

                        min-width:1.05rem;
                        height:1.05rem;

                        padding:0 .2rem;

                        border-radius:999px;

                        background:#1DB8A0;
                        color:#fff;

                        display:flex;
                        align-items:center;
                        justify-content:center;

                        font-size:.6rem;
                        font-weight:700;
                    "
                >
                    {{ $cartCount ?? 0 }}
                </span>
            </a>


            {{-- LOGIN / ACCOUNT --}}
            @auth

                <a
                    href="{{ route('profile.edit') }}"
                    title="{{ auth()->user()->name }}"
                    style="
                        color:#FF6F81;

                        font-size:.78rem;
                        font-weight:600;

                        text-decoration:none;
                        white-space:nowrap;
                    "
                >
                    Tài khoản
                </a>

            @else

                <button
                    type="button"
                    onclick="openLoginModal()"
                    style="
                        padding:0;

                        border:none;
                        background:none;

                        color:#FF6F81;

                        font-family:'Be Vietnam Pro',sans-serif;
                        font-size:.78rem;
                        font-weight:600;

                        cursor:pointer;
                        white-space:nowrap;
                    "
                >
                    Đăng nhập
                </button>

            @endauth

        </div>

    </div>
</header>