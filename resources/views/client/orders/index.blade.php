@extends('client.layouts.app')

@section('title', 'Đơn hàng của tôi - MommyKids')

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
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
        'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'shipping' => 'bg-orange-50 text-orange-700 border-orange-200',
        'delivered' => 'bg-green-50 text-green-700 border-green-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        'refunded' => 'bg-gray-100 text-gray-700 border-gray-200',
    ];

    $paymentLabels = [
        'cod' => 'COD',
        'qr' => 'Chuyển khoản',
        'vnpay' => 'VNPAY',
        'momo' => 'MoMo',
        'zalopay' => 'ZaloPay',
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
    ];
@endphp

<div class="bg-[#fff8f6] min-h-[70vh] py-8 md:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-7">
            <div>
                <div class="text-sm font-semibold text-rose-500 mb-1">Tài khoản của tôi</div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Đơn hàng của tôi</h1>
                <p class="text-sm text-gray-500 mt-2">
                    Theo dõi trạng thái đặt hàng, thanh toán và vận chuyển.
                </p>
            </div>

            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center justify-center rounded-xl border border-rose-200
                      bg-white px-4 py-2.5 text-sm font-semibold text-rose-600
                      hover:bg-rose-50 transition">
                ← Hồ sơ của tôi
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 mb-6">
            <a href="{{ route('profile.orders.index') }}"
               class="bg-white border border-rose-100 rounded-2xl p-4 shadow-sm hover:shadow transition">
                <div class="text-xs text-gray-500">Tất cả</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] ?? 0 }}</div>
            </a>

            <a href="{{ route('profile.orders.index', ['status' => 'pending']) }}"
               class="bg-white border border-amber-100 rounded-2xl p-4 shadow-sm hover:shadow transition">
                <div class="text-xs text-gray-500">Chờ xác nhận</div>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] ?? 0 }}</div>
            </a>

            <a href="{{ route('profile.orders.index', ['status' => 'processing']) }}"
               class="bg-white border border-indigo-100 rounded-2xl p-4 shadow-sm hover:shadow transition">
                <div class="text-xs text-gray-500">Đang xử lý</div>
                <div class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['processing'] ?? 0 }}</div>
            </a>

            <a href="{{ route('profile.orders.index', ['status' => 'shipping']) }}"
               class="bg-white border border-orange-100 rounded-2xl p-4 shadow-sm hover:shadow transition">
                <div class="text-xs text-gray-500">Đang giao</div>
                <div class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['shipping'] ?? 0 }}</div>
            </a>

            <a href="{{ route('profile.orders.index', ['status' => 'delivered']) }}"
               class="bg-white border border-green-100 rounded-2xl p-4 shadow-sm hover:shadow transition col-span-2 lg:col-span-1">
                <div class="text-xs text-gray-500">Đã giao</div>
                <div class="text-2xl font-bold text-green-600 mt-1">{{ $stats['delivered'] ?? 0 }}</div>
            </a>
        </div>

        <form method="GET"
              action="{{ route('profile.orders.index') }}"
              class="bg-white rounded-2xl border border-rose-100 shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Tìm theo mã đơn, tên hoặc số điện thoại..."
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm
                                  focus:border-rose-400 focus:ring-2 focus:ring-rose-100">
                </div>

                <select name="status"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm bg-white">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 rounded-xl bg-rose-500 px-4 py-2.5
                                   text-sm font-semibold text-white hover:bg-rose-600 transition">
                        Lọc
                    </button>

                    <a href="{{ route('profile.orders.index') }}"
                       class="rounded-xl border border-gray-200 px-4 py-2.5
                              text-sm font-semibold text-gray-600 hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        <div class="space-y-4">
            @forelse($orders as $order)
                @php
                    $statusText = $statusLabels[$order->status] ?? $order->status;
                    $statusClass = $statusClasses[$order->status]
                        ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    $paymentText = $paymentLabels[$order->payment_method]
                        ?? strtoupper((string) $order->payment_method);
                @endphp

                <article class="bg-white rounded-2xl border border-rose-100 shadow-sm overflow-hidden">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between
                                gap-3 px-5 py-4 border-b border-gray-100">
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('profile.orders.show', $order->code) }}"
                               class="font-bold text-gray-900 hover:text-rose-600">
                                {{ $order->code }}
                            </a>

                            <span class="inline-flex items-center rounded-full border px-2.5 py-1
                                         text-xs font-semibold {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>

                        <div class="text-xs text-gray-500">
                            Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 px-5 py-5">
                        <div>
                            <div class="text-xs text-gray-400 mb-1">Người nhận</div>
                            <div class="font-semibold text-sm text-gray-800">{{ $order->recipient_name }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $order->recipient_phone }}</div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-400 mb-1">Sản phẩm</div>
                            <div class="font-semibold text-sm text-gray-800">
                                {{ $order->items_count }} mặt hàng
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-400 mb-1">Thanh toán</div>
                            <div class="font-semibold text-sm text-gray-800">{{ $paymentText }}</div>

                            @if($order->payment_status === 'paid')
                                <div class="text-xs text-green-600 mt-1">✓ Đã thanh toán</div>
                            @elseif($order->payment_status === 'refunded')
                                <div class="text-xs text-gray-600 mt-1">↩ Đã hoàn tiền</div>
                            @else
                                <div class="text-xs text-orange-500 mt-1">Chưa thanh toán</div>
                            @endif
                        </div>

                        <div>
                            <div class="text-xs text-gray-400 mb-1">Vận chuyển</div>
                            @if($order->shipment?->ghn_order_code)
                                <div class="font-mono text-sm font-semibold text-gray-800">
                                    {{ $order->shipment->ghn_order_code }}
                                </div>
                                <div class="text-xs text-blue-600 mt-1">
                                    {{ strtoupper((string) $order->shipment->status) }}
                                </div>
                            @else
                                <div class="text-sm text-gray-500">Chưa có vận đơn</div>
                            @endif
                        </div>

                        <div class="lg:text-right">
                            <div class="text-xs text-gray-400 mb-1">Tổng tiền</div>
                            <div class="text-lg font-bold text-rose-600">
                                {{ number_format($order->total, 0, ',', '.') }}đ
                            </div>

                            <a href="{{ route('profile.orders.show', $order->code) }}"
                               class="inline-flex mt-3 text-sm font-semibold text-rose-600 hover:underline">
                                Xem chi tiết →
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl border border-rose-100 shadow-sm px-6 py-14 text-center">
                    <div class="text-4xl mb-3">📦</div>
                    <h2 class="font-bold text-gray-800">Chưa có đơn hàng phù hợp</h2>
                    <p class="text-sm text-gray-500 mt-2">
                        Các đơn hàng của tài khoản sẽ xuất hiện tại đây.
                    </p>

                    <a href="{{ route('home') }}"
                       class="inline-flex mt-5 rounded-xl bg-rose-500 px-5 py-2.5
                              text-sm font-semibold text-white hover:bg-rose-600">
                        Tiếp tục mua sắm
                    </a>
                </div>
            @endforelse
        </div>

        @if($orders->hasPages())
            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
