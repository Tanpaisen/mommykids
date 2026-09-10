<header class="sticky top-0 z-30 h-16 bg-white border-b border-admin-border flex items-center gap-3 px-4 lg:px-6">
    <button id="admin-sidebar-toggle" class="lg:hidden p-2 -ml-2 text-ink">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="relative hidden md:block w-72">
        <input type="text" placeholder="Tìm đơn hàng, sản phẩm, khách hàng..."
               class="w-full h-10 pl-4 pr-9 rounded-pill border border-admin-border focus:border-coral outline-none text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute right-3 top-3 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
        </svg>
    </div>

    <div class="ml-auto flex items-center gap-2">
        {{-- Pending comments / questions bell --}}
        <a href="{{ route('admin.comments.index') ?? '#' }}" class="relative w-10 h-10 rounded-full hover:bg-admin-bg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h5M21 12c0 4.418-4.03 8-9 8-1.06 0-2.07-.15-3-.43L3 21l1.5-4.5C3.55 15.19 3 13.65 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            @if (($pendingCommentsCount ?? 0) > 0)
                <span class="absolute top-1 right-1.5 w-4 h-4 rounded-full bg-coral text-white text-[10px] font-bold flex items-center justify-center">{{ $pendingCommentsCount }}</span>
            @endif
        </a>

        {{-- Low-stock alert bell --}}
        <a href="{{ route('admin.products.index', ['filter' => 'low_stock']) ?? '#' }}" class="relative w-10 h-10 rounded-full hover:bg-admin-bg flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            @if (($lowStockCount ?? 0) > 0)
                <span class="absolute top-1 right-1.5 w-4 h-4 rounded-full bg-gold text-white text-[10px] font-bold flex items-center justify-center">{{ $lowStockCount }}</span>
            @endif
        </a>

        <div class="w-px h-6 bg-admin-border mx-1"></div>

        {{-- Admin identity + current role --}}
        <div class="flex items-center gap-2 pl-1 pr-3 py-1.5 rounded-pill hover:bg-admin-bg">
            <span class="w-8 h-8 rounded-full bg-coral-light text-coral font-display font-bold flex items-center justify-center text-sm">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </span>
            <div class="hidden sm:block leading-tight">
                <p class="text-sm font-semibold text-ink">{{ auth()->user()->name ?? 'Admin' }}</p>
                <p class="text-xs text-ink-soft">
                    @if(optional(auth()->user())->roles && auth()->user()->roles->count())
                        {{ auth()->user()->roles->pluck('name')->join(', ') }}
                    @else
                        {{ auth()->user()->role ?? 'Chưa gán vai trò' }}
                    @endif
                </p>
            </div>
        </div>

        {{-- Nút Đăng xuất --}}
        <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" 
                    title="Đăng xuất" 
                    class="flex items-center gap-1.5 text-xs font-semibold text-coral bg-coral-light hover:bg-coral hover:text-white border border-coral/20 px-3 py-2 rounded-pill transition-all cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="hidden md:inline">Đăng xuất</span>
            </button>
        </form>
    </div>
</header>