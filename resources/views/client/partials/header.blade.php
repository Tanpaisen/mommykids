<header class="sticky top-0 z-50 bg-surface/95 backdrop-blur border-b border-coral-light">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-6 py-3 flex items-center gap-3 lg:gap-6">

        {{-- Mobile sidebar toggle --}}
        <button id="mk-sidebar-toggle" class="lg:hidden p-2 -ml-2 text-ink" aria-label="Mở danh mục">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Logo --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2 shrink-0">
            <span class="w-10 h-10 rounded-blob bg-coral flex items-center justify-center text-white font-display font-bold text-lg">M</span>
            <span class="hidden sm:block font-display font-extrabold text-xl text-ink">Mommy<span class="text-coral">Kids</span></span>
        </a>

        {{-- Location picker (desktop) --}}
        <button class="hidden lg:flex items-center gap-1 text-sm text-ink-soft px-3 py-2 rounded-pill border border-coral-light hover:border-coral shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Giao tới: <span class="font-semibold text-ink">{{ $currentCity ?? 'Hà Nội' }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Search bar --}}
        <form action="{{ route('search') ?? '#' }}" method="GET" class="flex-1 flex items-center">
            <div class="relative w-full">
                <input type="text" name="q"
                       placeholder="Ba mẹ cần tìm gì cho bé hôm nay?"
                       class="w-full h-11 pl-4 pr-11 rounded-pill border-2 border-coral-light focus:border-coral
                              outline-none text-sm placeholder:text-ink-soft/70">
                <button type="submit" class="absolute right-1.5 top-1.5 w-8 h-8 rounded-full bg-coral text-white
                                             flex items-center justify-center hover:bg-coral-dark">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
                    </svg>
                </button>
            </div>
        </form>

        {{-- Cart & notifications (desktop) --}}
        <div class="hidden lg:flex items-center gap-2 shrink-0">
            <a href="{{ route('notifications.index') ?? '#' }}"
               class="relative flex items-center gap-2 px-3 py-2 rounded-pill hover:bg-coral-light text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                Thông báo
            </a>
            <a href="{{ route('cart.index') ?? '#' }}"
               class="relative flex items-center gap-2 px-4 py-2 rounded-pill bg-coral-light hover:bg-coral hover:text-white text-sm font-semibold text-coral transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Giỏ hàng
                <span class="mk-cart-count absolute -top-1 -right-1 bg-mint text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount ?? 0 }}</span>
            </a>
        </div>
    </div>

    {{-- Utility strip: quick links --}}
    <div class="hidden lg:block bg-coral-light/60">
        <div class="max-w-[1280px] mx-auto px-6 py-1.5 flex items-center gap-6 text-xs font-medium text-ink-soft">
            <a href="#" class="hover:text-coral">Hàng mới</a>
            <a href="#" class="hover:text-coral">Voucher</a>
            <a href="#" class="hover:text-coral">Sự kiện</a>
            <a href="#" class="hover:text-coral">Hotline: 1800 6886</a>
            <span class="ml-auto"></span>
            @auth
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile.edit') ?? '#' }}" class="hover:text-coral font-medium">
                        Xin chào, {{ auth()->user()->name }}
                    </a>
                    <span class="text-ink-soft/40">|</span>
                    <form method="POST" action="{{ route('logout') ?? '#' }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-coral font-semibold text-coral cursor-pointer">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            @else
                <button type="button" onclick="openLoginModal()" class="hover:text-coral font-semibold text-coral cursor-pointer">
                    Đăng nhập / Đăng ký
                </button>
            @endauth
        </div>
    </div>
</header>