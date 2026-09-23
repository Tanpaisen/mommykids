@extends('admin.layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã huỷ',
        'refunded' => 'Đã hoàn tiền',
    ];

    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-indigo-100 text-indigo-700',
        'shipping' => 'bg-orange-100 text-orange-700',
        'delivered' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-gray-200 text-gray-700',
    ];

    $paymentMethods = [
        'cod' => 'COD',
        'qr' => 'Chuyển khoản',
        'vnpay' => 'VNPAY',
        'momo' => 'MoMo',
        'zalopay' => 'ZaloPay',
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
    ];

    $paymentMethodClasses = [
        'cod' => 'bg-gray-100 text-gray-700',
        'qr' => 'bg-cyan-100 text-cyan-700',
        'vnpay' => 'bg-red-100 text-red-700',
        'momo' => 'bg-pink-100 text-pink-700',
        'zalopay' => 'bg-blue-100 text-blue-700',
        'stripe' => 'bg-violet-100 text-violet-700',
        'paypal' => 'bg-sky-100 text-sky-700',
    ];
@endphp

<div class="p-6">

    {{-- Header --}}
    <div class="flex flex-col gap-2 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Quản lý đơn hàng
        </h1>

        <p class="text-sm text-gray-500">
            Theo dõi đơn hàng, thanh toán và vận chuyển của MommyKids.
        </p>
    </div>


    {{-- =========================================================
    THỐNG KÊ 
    ========================================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">
        {{-- Tổng đơn hàng --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Tổng đơn hàng</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">
                        {{ number_format($stats['total'] ?? 0) }}
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">
                    📦
                </div>
            </div>
        </div>

        {{-- Chờ xác nhận --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Chờ xác nhận</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-2">
                        {{ number_format($stats['pending'] ?? 0) }}
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-yellow-50 flex items-center justify-center text-xl">
                    ⏳
                </div>
            </div>
        </div>

        {{-- Đang giao --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Đang giao</p>
                    <p class="text-2xl font-bold text-orange-600 mt-2">
                        {{ number_format($stats['shipping'] ?? 0) }}
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center text-xl">
                    🚚
                </div>
            </div>
        </div>

        {{-- Doanh thu --}}
        <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500">Doanh thu đã thanh toán</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">
                        {{ number_format($stats['revenue'] ?? 0, 0, ',', '.') }}đ
                    </p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center text-xl">
                    💰
                </div>
            </div>
        </div>
    </div>

    {{-- Thống kê phụ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
        <div class="bg-indigo-50 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-medium text-indigo-700">Đang xử lý</span>
            <strong class="text-indigo-700">{{ number_format($stats['processing'] ?? 0) }}</strong>
        </div>
        <div class="bg-green-50 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-medium text-green-700">Đã giao</span>
            <strong class="text-green-700">{{ number_format($stats['delivered'] ?? 0) }}</strong>
        </div>
        <div class="bg-blue-50 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-medium text-blue-700">Đã thanh toán</span>
            <strong class="text-blue-700">{{ number_format($stats['paid'] ?? 0) }}</strong>
        </div>
        <div class="bg-pink-50 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm font-medium text-pink-700">Đơn có Campaign</span>
            <strong class="text-pink-700">{{ number_format($stats['campaign_orders'] ?? 0) }}</strong>
        </div>
    </div>


    {{-- =========================================================
         BỘ LỌC TÌM KIẾM
    ========================================================== --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 mb-6">
        <form method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                
                {{-- Search --}}
                <div class="xl:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Mã đơn, tên khách, SĐT, email..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400">
                </div>

                {{-- Order status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Trạng thái đơn</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Thanh toán</label>
                    <select name="payment_status" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">Tất cả</option>
                        <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Chưa thanh toán</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>Đã thanh toán</option>
                        <option value="refunded" @selected(request('payment_status') === 'refunded')>Đã hoàn tiền</option>
                    </select>
                </div>

                {{-- Payment method --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Phương thức</label>
                    <select name="payment_method" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-100">
                        <option value="">Tất cả phương thức</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Campaign --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Campaign</label>
                    <select name="campaign" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-pink-100">
                        <option value="">Tất cả đơn</option>
                        <option value="yes" @selected(request('campaign') === 'yes')>Có Campaign</option>
                        <option value="no" @selected(request('campaign') === 'no')>Không Campaign</option>
                    </select>
                </div>

                {{-- From --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Từ ngày</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>

                {{-- To --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Đến ngày</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-100">
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition">
                        Lọc
                    </button>
                    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>


    {{-- =========================================================
         BẢNG ĐƠN HÀNG
    ========================================================== --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-800">Danh sách đơn hàng</h2>
                <p class="text-xs text-gray-400 mt-1">
                    Hiển thị {{ $orders->count() }} / {{ $orders->total() }} đơn hàng
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Mã đơn</th>
                        <th class="px-5 py-3 text-left">Khách hàng</th>
                        <th class="px-5 py-3 text-center">SP</th>
                        <th class="px-5 py-3 text-right">Tổng tiền</th>
                        <th class="px-5 py-3 text-left">Phương thức</th>
                        <th class="px-5 py-3 text-left">Thanh toán</th>
                        <th class="px-5 py-3 text-left">Trạng thái</th>
                        <th class="px-5 py-3 text-left">Vận đơn</th>
                        <th class="px-5 py-3 text-left">Ngày đặt</th>
                        <th class="px-5 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        @php
                            $statusText = $statusLabels[$order->status] ?? $order->status;
                            $statusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-700';
                            $methodText = $paymentMethods[$order->payment_method] ?? strtoupper($order->payment_method);
                            $methodClass = $paymentMethodClasses[$order->payment_method] ?? 'bg-gray-100 text-gray-700';
                        @endphp

                        <tr class="hover:bg-gray-50/70 transition">
                            {{-- Code --}}
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
                                    {{ $order->code }}
                                </a>
                                <div class="text-[11px] text-gray-400 mt-1">{{ $order->id }}</div>
                            </td>

                            {{-- Customer --}}
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-800">{{ $order->recipient_name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $order->recipient_phone }}</div>
                                @if($order->recipient_email)
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $order->recipient_email }}</div>
                                @endif
                            </td>

                            {{-- Items count --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex min-w-8 h-8 px-2 items-center justify-center rounded-lg bg-gray-100 font-semibold text-gray-700">
                                    {{ $order->items_count ?? 0 }}
                                </span>

                                @if((int) ($order->campaign_items_count ?? 0) > 0)
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center rounded-full bg-pink-50 text-pink-700 border border-pink-100 px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap">
                                            🏷 {{ $order->campaign_items_count }} Campaign
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Total --}}
                            <td class="px-5 py-4 text-right">
                                <strong class="text-gray-900 whitespace-nowrap">
                                    {{ number_format($order->total, 0, ',', '.') }}đ
                                </strong>

                                @if((int) ($order->campaign_discount_total ?? 0) > 0)
                                    <div class="text-[11px] text-pink-600 mt-1 whitespace-nowrap">
                                        Campaign tiết kiệm
                                        {{ number_format(
                                            $order->campaign_discount_total,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </div>
                                @endif

                                @if((int) $order->discount > 0)
                                    <div class="text-xs text-green-600 mt-1">
                                        Voucher/điểm
                                        -{{ number_format($order->discount, 0, ',', '.') }}đ
                                    </div>
                                @endif
                            </td>

                            {{-- Payment method --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $methodClass }}">
                                    {{ $methodText }}
                                </span>
                            </td>

                            {{-- Payment status --}}
                            <td class="px-5 py-4">
                                @if($order->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                        ✓ Đã thanh toán
                                    </span>
                                @elseif($order->payment_status === 'refunded')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-200 text-gray-700 text-xs font-semibold">
                                        ↩ Đã hoàn tiền
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-50 text-red-600 text-xs font-semibold">
                                        Chưa thanh toán
                                    </span>
                                @endif
                            </td>

                            {{-- Order status --}}
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </td>

                            {{-- Shipment --}}
                            <td class="px-5 py-4">
                                @if($order->shipment?->ghn_order_code)
                                    <div class="font-mono text-xs font-semibold {{ $order->shipment->status === 'cancel' ? 'text-gray-400 line-through' : 'text-indigo-600' }}">
                                        {{ $order->shipment->ghn_order_code }}
                                    </div>

                                    @if($order->shipment->status === 'cancel')
                                        <div class="text-[11px] text-red-500 mt-1 font-medium">❌ Đã huỷ</div>
                                    @elseif($order->shipment->printed_at)
                                        <div class="text-[11px] text-green-600 mt-1">
                                            Đã in {{ $order->shipment->printed_at->format('d/m H:i') }}
                                        </div>
                                    @else
                                        <div class="text-[11px] text-orange-500 mt-1">⚠️ Chưa in</div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Chưa tạo</span>
                                @endif
                            </td>

                            {{-- Created at --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-700">{{ optional($order->created_at)->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400 mt-1">{{ optional($order->created_at)->format('H:i') }}</div>
                            </td>

                            {{-- Action --}}
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-semibold transition">
                                    Xem chi tiết →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-16 text-center">
                                <div class="text-4xl mb-3">📦</div>
                                <div class="font-semibold text-gray-600">Không tìm thấy đơn hàng</div>
                                <div class="text-sm text-gray-400 mt-1">Thử thay đổi điều kiện tìm kiếm hoặc bộ lọc.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    {{-- Pagination --}}
    @if($orders->hasPages())
        <div class="mt-5">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection