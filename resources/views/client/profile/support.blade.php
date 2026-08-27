@extends('client.layouts.app')

@section('title', 'Hỗ trợ người dùng - MommyKids')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 lg:px-6 py-6">

    {{-- Breadcrumbs --}}
    <div class="flex items-center gap-2 text-xs text-ink-soft mb-6">
        <a href="{{ route('home') }}" class="hover:text-coral">Trang chủ</a>
        <span>/</span>
        <span>Cá nhân</span>
        <span>/</span>
        <span class="text-coral font-medium">Hỗ trợ người dùng</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Sidebar bên trái --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-5 border border-coral-light/60 shadow-sm">
                {{-- Header Tên khách hàng --}}
                <div class="mb-4">
                    <h3 class="font-bold text-ink text-base truncate">
                        Xin chào, {{ $user->name ?? $user->email }}
                    </h3>
                    <span class="inline-block mt-1 px-3 py-1 bg-coral-light/50 text-coral text-xs font-semibold rounded-full">
                        Khách hàng &rsaquo;
                    </span>
                </div>

                {{-- Mã khách hàng thân thiết --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center my-4">
                    <p class="text-xs text-amber-800 font-medium">Mã khách hàng thân thiết</p>
                    <p class="font-mono font-bold text-lg text-amber-900 tracking-wider mt-1">
                        893{{ str_pad($user->id, 10, '0', STR_PAD_LEFT) }}
                    </p>
                </div>

                {{-- Danh sách menu --}}
                <nav class="space-y-1 text-sm font-medium text-ink-soft">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Tài khoản
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Đơn hàng
                    </a>
                    <a href="{{ route('profile.support') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-coral-light/40 text-coral font-bold">
                        <svg class="w-5 h-5 text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Hỗ trợ người dùng
                    </a>
                    <a href="{{ route('profile.policy') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Quy định, chính sách
                    </a>
                </nav>
            </div>
        </div>

        {{-- Nội dung Hỗ trợ bên phải --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl p-6 lg:p-8 border border-coral-light/60 shadow-sm">
                <h1 class="text-2xl font-bold text-ink mb-8">Liên hệ với chúng tôi</h1>

                <div class="space-y-8 text-sm text-ink leading-relaxed">
                    
                    {{-- 1. Hotline --}}
                    <div>
                        <h2 class="font-bold text-base text-ink mb-3">1. Liên hệ Hotline <span class="text-coral">1800 6886</span> (miễn phí)</h2>
                        <ul class="space-y-2 pl-4 text-ink-soft">
                            <li><strong class="text-ink">Nhánh 1:</strong> Đặt hàng, tư vấn sản phẩm, chương trình khuyến mãi từ Đà Nẵng đến các tỉnh phía Bắc</li>
                            <li><strong class="text-ink">Nhánh 2:</strong> Đặt hàng, tư vấn sản phẩm, chương trình khuyến mãi từ Quảng Nam đến các tỉnh phía Nam</li>
                            <li><strong class="text-ink">Nhánh 3:</strong> Giải quyết khiếu nại, tư vấn chính sách dành cho Khách hàng</li>
                            <li><strong class="text-ink">Nhánh 4:</strong> Đăng ký lớp học Tiền sản, lớp học Dinh dưỡng</li>
                        </ul>
                    </div>

                    {{-- 2. Messenger --}}
                    <div>
                        <h2 class="font-bold text-base text-ink mb-3">2. Chat Messenger Facebook</h2>
                        <div class="pl-4">
                            <a href="https://m.me/MommyKids.vn" target="_blank" class="text-blue-600 hover:underline font-medium inline-flex items-center gap-1">
                                m.me/MommyKids.vn
                            </a>
                        </div>
                    </div>

                    {{-- 3. Liên hệ trực tiếp --}}
                    <div>
                        <h2 class="font-bold text-base text-ink mb-3">3. Liên hệ trực tiếp</h2>
                        <div class="pl-4 space-y-5 text-ink-soft">
                            <div>
                                <p class="font-bold text-ink">Văn phòng miền Bắc:</p>
                                <p>Địa chỉ: 120 Trần Duy Hưng, Phường Yên Hòa, Thành phố Hà Nội, Việt Nam.</p>
                                <p>Điện thoại: (024) 7309 1168</p>
                            </div>

                            <div>
                                <p class="font-bold text-ink">Văn phòng miền Nam:</p>
                                <p>Địa chỉ: Tầng 3, số 2A Nguyễn Oanh, Phường Hạnh Thông, TP.Hồ Chí Minh, Việt Nam.</p>
                                <p>Điện thoại: (028) 7308 1168</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection