@extends('client.layouts.app')

@section('title', 'Thông tin tài khoản - MommyKids')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 lg:px-6 py-6">

    {{-- Breadcrumbs --}}
    <div class="flex items-center gap-2 text-xs text-ink-soft mb-6">
        <a href="{{ route('home') }}" class="hover:text-coral">Trang chủ</a>
        <span>/</span>
        <span>Cá nhân</span>
        <span>/</span>
        <span class="text-coral font-medium">Tài khoản</span>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 rounded-xl bg-mint/10 border border-mint text-mint text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

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
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-coral-light/40 text-coral font-bold">
                        <svg class="w-5 h-5 text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                    <a href="{{ route('profile.support') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

        {{-- Khung hiển thị / Cập nhật Thông tin bên phải --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl p-6 border border-coral-light/60 shadow-sm">
                <h1 class="text-2xl font-bold text-ink mb-6">Thông tin tài khoản</h1>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <div class="flex items-center gap-2 text-coral font-bold border-b border-coral-light pb-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin cá nhân
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Họ và tên --}}
                            <div>
                                <label class="block text-xs text-ink-soft mb-1">Họ và tên *</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-coral-light focus:border-coral outline-none text-sm font-medium text-ink">
                            </div>

                            {{-- Số điện thoại --}}
                            <div>
                                <label class="block text-xs text-ink-soft mb-1">Số điện thoại</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Chưa cập nhật"
                                       class="w-full px-4 py-2.5 rounded-xl border border-coral-light focus:border-coral outline-none text-sm font-medium text-ink">
                            </div>

                            {{-- Ngày tháng năm sinh --}}
                            <div>
                                <label class="block text-xs text-ink-soft mb-1">Ngày tháng năm sinh</label>
                                <input type="date" name="birthday" value="{{ old('birthday', $user->birthday) }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-coral-light focus:border-coral outline-none text-sm font-medium text-ink">
                            </div>

                            {{-- Giới tính --}}
                            <div>
                                <label class="block text-xs text-ink-soft mb-1">Giới tính</label>
                                <select name="gender" class="w-full px-4 py-2.5 rounded-xl border border-coral-light focus:border-coral outline-none text-sm font-medium text-ink">
                                    <option value="">Chưa chọn</option>
                                    <option value="nam" {{ old('gender', $user->gender) == 'nam' ? 'selected' : '' }}>Nam</option>
                                    <option value="nu" {{ old('gender', $user->gender) == 'nu' ? 'selected' : '' }}>Nữ</option>
                                    <option value="khac" {{ old('gender', $user->gender) == 'khac' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>

                            {{-- Email --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs text-ink-soft mb-1">Email</label>
                                <input type="email" value="{{ $user->email }}" disabled
                                       class="w-full px-4 py-2.5 rounded-xl border border-coral-light/50 bg-cream/30 text-sm font-medium text-ink-soft cursor-not-allowed">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-coral-light/50 flex justify-end">
                            <button type="submit" class="px-6 py-2.5 bg-coral hover:bg-coral-dark text-white font-bold text-sm rounded-full shadow transition-colors">
                                Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection