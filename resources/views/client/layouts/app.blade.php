<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MommyKids - Mẹ và bé')</title>
    <meta name="description" content="@yield('meta_description', 'MommyKids - Đồ dùng mẹ và bé chính hãng, giá tốt, giao nhanh toàn quốc.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400..800&family=Be+Vietnam+Pro:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
</head>
<body class="bg-cream text-ink font-body">

    {{-- ============ HEADER ============ --}}
    @include('client.partials.header')

    {{-- ============ MOBILE SIDEBAR OVERLAY ============ --}}
    <div id="mk-sidebar-overlay" class="hidden fixed inset-0 bg-ink/40 z-40 lg:hidden"></div>

    <div class="max-w-[1280px] mx-auto px-4 lg:px-6 mt-4 flex gap-5 items-start">

        {{-- ============ SIDEBAR (categories) ============ --}}
        @include('client.partials.sidebar')

        {{-- ============ MAIN CONTENT ============ --}}
        <main class="flex-1 min-w-0 space-y-6 pb-16">
            @yield('content')
        </main>
    </div>

    {{-- ============ FOOTER ============ --}}
    @include('client.partials.footer')

    {{-- Floating cart bubble (mobile signature element) --}}
    <a href="{{ route('cart.index') ?? '#' }}"
       class="lg:hidden fixed bottom-5 right-5 z-40 w-14 h-14 rounded-full bg-coral shadow-pop
              flex items-center justify-center text-white">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span id="mk-cart-count" class="absolute -top-1 -right-1 bg-mint text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">0</span>
    </a>

    @stack('scripts')
</body>
</html>
