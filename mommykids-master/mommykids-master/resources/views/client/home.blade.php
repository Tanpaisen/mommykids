@extends('client.layouts.app')

@section('title', 'MommyKids - Đồ dùng mẹ và bé chính hãng')

@section('content')

    {{-- ============ HERO BANNER SLIDER ============ --}}
    <section class="relative rounded-card overflow-hidden shadow-soft">
        <div id="mk-hero-track" class="flex transition-transform duration-700 ease-out">
            <div class="w-full shrink-0 bg-gradient-to-br from-peach to-coral flex items-center justify-between px-6 lg:px-12 py-10 lg:py-16">
                <div class="max-w-md">
                    <p class="font-display font-extrabold text-2xl lg:text-4xl text-white leading-tight">Sữa thùng<br>giá tốt tháng này</p>
                    <p class="mt-3 text-white/90 text-sm lg:text-base">Chính hãng · Hóa đơn VAT đầy đủ · Bảo giá tốt nhất thị trường</p>
                    <a href="#" class="btn-primary bg-white !text-coral hover:!bg-cream mt-5">Mua ngay</a>
                </div>
                <img src="https://via.placeholder.com/260x260?text=Milk" alt="Sữa thùng giá tốt" class="hidden lg:block w-56 h-56 object-contain drop-shadow-xl">
            </div>
            <div class="w-full shrink-0 bg-gradient-to-br from-mint to-mint/70 flex items-center justify-between px-6 lg:px-12 py-10 lg:py-16">
                <div class="max-w-md">
                    <p class="font-display font-extrabold text-2xl lg:text-4xl text-white leading-tight">Voucher tặng<br>bạn mới</p>
                    <p class="mt-3 text-white/90 text-sm lg:text-base">Nhận ngay ưu đãi 30K cho đơn hàng đầu tiên</p>
                    <a href="#" class="btn-primary bg-white !text-mint hover:!bg-cream mt-5">Nhận ngay</a>
                </div>
                <img src="https://via.placeholder.com/260x260?text=Voucher" alt="Voucher tặng bạn mới" class="hidden lg:block w-56 h-56 object-contain drop-shadow-xl">
            </div>
        </div>

        {{-- Dots --}}
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
            <span data-hero-dot class="w-2.5 h-2.5 rounded-full bg-coral"></span>
            <span data-hero-dot class="w-2.5 h-2.5 rounded-full bg-white/60"></span>
        </div>
    </section>

    {{-- ============ QUICK ACTION ICONS ============ --}}
    <section class="card p-4 grid grid-cols-4 lg:grid-cols-7 gap-4">
        @foreach ([
            ['icon' => '🆕', 'label' => 'Hàng mới'],
            ['icon' => '🏬', 'label' => 'Tìm cửa hàng'],
            ['icon' => '📞', 'label' => 'Hotline'],
            ['icon' => '🎁', 'label' => 'Đổi quà'],
            ['icon' => '🎟️', 'label' => 'Voucher'],
            ['icon' => '📱', 'label' => 'Mini App'],
            ['icon' => '📅', 'label' => 'Sự kiện'],
        ] as $action)
            <a href="#" class="flex flex-col items-center gap-2 group">
                <span class="w-12 h-12 rounded-2xl bg-coral-light flex items-center justify-center text-xl group-hover:bg-coral group-hover:text-white transition-colors">{{ $action['icon'] }}</span>
                <span class="text-xs text-ink-soft text-center">{{ $action['label'] }}</span>
            </a>
        @endforeach
    </section>

    {{-- ============ VOUCHER / PROMO STRIP ============ --}}
    <section class="rounded-card overflow-hidden bg-gradient-to-r from-gold-light to-peach-light p-5 lg:p-8 flex items-center justify-between gap-4">
        <div>
            <p class="font-display font-extrabold text-xl lg:text-2xl text-ink">Ưu đãi dành cho ba mẹ</p>
            <p class="text-sm text-ink-soft mt-1">Nhập mã ngay để nhận ưu đãi cho lần mua đầu tiên</p>
        </div>
        <div class="hidden sm:flex gap-3">
            <div class="card px-4 py-3 text-center">
                <p class="font-display font-bold text-coral">30K</p>
                <p class="text-xs text-ink-soft">Voucher</p>
            </div>
            <div class="card px-4 py-3 text-center">
                <p class="font-display font-bold text-coral">-12%</p>
                <p class="text-xs text-ink-soft">Tã & Bỉm</p>
            </div>
            <div class="card px-4 py-3 text-center">
                <p class="font-display font-bold text-coral">-15%</p>
                <p class="text-xs text-ink-soft">Sữa bột</p>
            </div>
        </div>
        <a href="#" class="btn-primary shrink-0">Nhận ngay</a>
    </section>

    {{-- ============ PRODUCT SECTIONS ============ --}}
    {{-- $sections comes from App\Http\Controllers\HomeController@index — one block per category with products --}}
    @forelse ($sections as $section)
        <section class="card p-4 lg:p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">{{ $section['icon'] ?? '🛍️' }}</span>
                    <h2 class="font-display font-bold text-lg lg:text-xl text-ink">{{ $section['title'] }}</h2>
                </div>
                <a href="{{ $section['url'] }}" class="text-sm font-semibold text-coral flex items-center gap-1">
                    Xem tất cả
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($section['products'] as $product)
                    <x-product-card :product="$product" :product-id="$product['id']" />
                @endforeach
            </div>
        </section>
    @empty
        <section class="card p-8 text-center text-ink-soft">
            Chưa có danh mục/sản phẩm nào. Chạy <code class="bg-cream px-1.5 py-0.5 rounded">php artisan db:seed</code> để nạp dữ liệu mẫu.
        </section>
    @endforelse

@endsection
