@extends('admin.layouts.app')

@section('page_title', 'Hồ sơ khách hàng')
@section('page_subtitle', $customer->name ?? $customer->email)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'orders' }">
    <!-- Nút quay lại -->
    <div>
        <a href="{{ route('admin.customers.index') }}" 
           class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Quay lại danh sách
        </a>
    </div>

    @php
        $displayName = $customer->name ?? $customer->email ?? 'Khách hàng';
        $initial = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'));
        $spent = $customer->total_spent ?? 0;
        $isActive = $customer->is_active ?? true;
        
        $colorIndex = abs(crc32($customer->id ?? 1)) % 5;
        $avatarStyles = [
            'bg-rose-100 text-rose-700',
            'bg-indigo-100 text-indigo-700',
            'bg-emerald-100 text-emerald-700',
            'bg-amber-100 text-amber-700',
            'bg-sky-100 text-sky-700'
        ];
        $avatarClass = $avatarStyles[$colorIndex];
    @endphp

    <!-- Khối tổng quan khách hàng -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Thông tin cá nhân -->
        <div class="lg:col-span-4 bg-white p-6 rounded-xl border border-slate-200/80 shadow-sm flex flex-col items-center text-center justify-center">
            <div class="w-20 h-20 rounded-full {{ $avatarClass }} font-bold text-2xl flex items-center justify-center shadow-inner mb-3">
                {{ $initial }}
            </div>
            <h2 class="text-lg font-bold text-slate-800">{{ $customer->name ?? 'Chưa cập nhật tên' }}</h2>
            <p class="text-xs text-slate-500 font-mono mt-0.5">ID: #{{ $customer->id }}</p>
            
            <div class="w-full border-t border-slate-100 my-4"></div>

            <div class="w-full space-y-2 text-left text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400">Email:</span>
                    <span class="font-medium text-slate-800">{{ $customer->email }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400">Số điện thoại:</span>
                    <span class="font-medium text-slate-800">{{ $customer->phone ?? 'Chưa có SĐT' }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400">Điểm tích lũy:</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200/60">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        {{ number_format($customer->points ?? 0) }} điểm
                    </span>
                </div>
            </div>
        </div>

        <!-- 3 Thẻ thông số -->
        <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Thẻ Đơn hàng -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tổng đơn hàng</span>
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $customer->orders_count ?? $customer->orders->count() }}</div>
                    <span class="text-xs text-slate-400">Đã hoàn thành & đang xử lý</span>
                </div>
            </div>

            <!-- Thẻ Chi tiêu -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tổng chi tiêu</span>
                    <div class="p-2 bg-rose-50 text-rose-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($spent, 0, ',', '.') }}đ</div>
                    <span class="text-xs text-slate-400">Doanh thu tích lũy</span>
                </div>
            </div>

            <!-- Thẻ Trạng thái -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Trạng thái</span>
                    <div class="p-2 {{ $isActive ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>
                <div>
                    @if($isActive)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Hoạt động
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Đang bị khóa
                        </span>
                    @endif
                    <div class="text-xs text-slate-400 mt-2">Quyền truy cập hệ thống</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Chi tiết hoạt động -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        <!-- Thanh Tab -->
        <div class="border-b border-slate-200/80 bg-slate-50/50 px-4 flex gap-6">
            <button @click="activeTab = 'orders'" 
                    :class="activeTab === 'orders' ? 'border-rose-500 text-rose-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-4 px-1 text-sm border-b-2 transition">
                Lịch sử đơn hàng ({{ $customer->orders->count() }})
            </button>
            <button @click="activeTab = 'cart'" 
                    :class="activeTab === 'cart' ? 'border-rose-500 text-rose-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-4 px-1 text-sm border-b-2 transition">
                Giỏ hàng hiện tại ({{ count($customer->cartItems ?? []) }})
            </button>
            <button @click="activeTab = 'wishlist'" 
                    :class="activeTab === 'wishlist' ? 'border-rose-500 text-rose-600 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700'"
                    class="py-4 px-1 text-sm border-b-2 transition">
                Sản phẩm yêu thích ({{ count($customer->wishlist ?? []) }})
            </button>
        </div>

        <div class="p-0">
            <!-- 1. TAB ĐƠN HÀNG -->
            <div x-show="activeTab === 'orders'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/80">
                            <th class="py-3.5 px-4">Mã đơn</th>
                            <th class="py-3.5 px-4">Ngày mua</th>
                            <th class="py-3.5 px-4 text-center">Trạng thái</th>
                            <th class="py-3.5 px-4 text-right">Tổng tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($customer->orders as $order)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-800">#{{ $order->code ?? $order->id }}</td>
                                <td class="py-3.5 px-4 text-slate-500 text-xs">{{ $order->created_at ? $order->created_at->format('H:i d/m/Y') : 'N/A' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-slate-900">
                                    {{ number_format($order->total_amount, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-sm">Chưa có lịch sử đơn hàng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 2. TAB GIỎ HÀNG -->
            <div x-show="activeTab === 'cart'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/80">
                            <th class="py-3.5 px-4">Sản phẩm</th>
                            <th class="py-3.5 px-4 text-center">Số lượng</th>
                            <th class="py-3.5 px-4 text-right">Giá bán</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($customer->cartItems ?? [] as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-medium text-slate-800">{{ $item->product->name ?? 'Sản phẩm đã xóa' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-slate-900">
                                    {{ number_format($item->product->price ?? 0, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-400 text-sm">Giỏ hàng hiện tại đang trống.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 3. TAB YÊU THÍCH -->
            <div x-show="activeTab === 'wishlist'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/80">
                            <th class="py-3.5 px-4">Sản phẩm yêu thích</th>
                            <th class="py-3.5 px-4 text-right">Giá hiện tại</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($customer->wishlist ?? [] as $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-medium text-slate-800">{{ $item->product->name ?? 'Sản phẩm' }}</td>
                                <td class="py-3.5 px-4 text-right font-semibold text-slate-900">
                                    {{ number_format($item->product->price ?? 0, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-8 text-center text-slate-400 text-sm">Chưa có sản phẩm yêu thích nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection