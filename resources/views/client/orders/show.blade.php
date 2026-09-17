@extends('client.layouts.app')

@section('title', 'Chi tiết đơn ' . $order->code . ' - MommyKids')

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
        'cod' => 'Thanh toán khi nhận hàng (COD)',
        'qr' => 'Chuyển khoản ngân hàng',
        'vnpay' => 'VNPAY',
        'momo' => 'MoMo',
        'zalopay' => 'ZaloPay',
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
    ];

    $pointsDiscount = (int) ($order->points_discount ?? 0);
    $voucherDiscount = max(0, (int) $order->discount - $pointsDiscount);

    $statusText = $statusLabels[$order->status] ?? $order->status;
    $statusClass = $statusClasses[$order->status]
        ?? 'bg-gray-100 text-gray-700 border-gray-200';
@endphp

<div class="bg-[#fff8f6] min-h-[70vh] py-8 md:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('profile.orders.index') }}"
                   class="text-sm font-semibold text-rose-600 hover:underline">
                    ← Đơn hàng của tôi
                </a>

                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mt-2">
                    {{ $order->code }}
                </h1>

                <p class="text-sm text-gray-500 mt-1">
                    Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }}
                </p>
            </div>

            <span class="inline-flex w-fit items-center rounded-full border px-3 py-1.5
                         text-sm font-semibold {{ $statusClass }}">
                {{ $statusText }}
            </span>
        </div>
{{-- THÔNG BÁO --}}
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50
                        px-4 py-3 text-sm text-green-700">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50
                        px-4 py-3 text-sm text-red-700">
                ❌ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50
                        px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- YÊU CẦU HUỶ ĐƠN --}}
@if(
    in_array(
        $order->status,
        ['pending', 'confirmed', 'processing'],
        true
    )
)

    {{-- ĐÃ GỬI YÊU CẦU --}}
    @if(
        $cancellationRequest
        && $cancellationRequest->status === 'pending'
    )
        <div class="mb-6 rounded-2xl border border-orange-200
                    bg-orange-50 px-5 py-4">

            <div class="flex items-start gap-3">
                <div class="text-xl">⏳</div>

                <div>
                    <div class="font-bold text-orange-800">
                        Đang chờ xử lý yêu cầu huỷ
                    </div>

                    <div class="text-sm text-orange-700 mt-1">
                        Lý do:
                        {{ $cancellationRequest->reason }}
                    </div>

                    @if($order->payment_status === 'paid')
                        <div class="text-xs text-orange-600 mt-2">
                            Đơn đã thanh toán.
                            Việc hoàn tiền sẽ được xử lý sau khi yêu cầu huỷ được chấp nhận.
                        </div>
                    @endif
                </div>
            </div>

        </div>

    {{-- CHƯA GỬI YÊU CẦU --}}
    @else

        <div class="mb-6 flex justify-end">

            <button
                type="button"
                onclick="
                    document
                        .getElementById('cancel-request-form')
                        .classList
                        .toggle('hidden')
                "
                class="text-sm font-semibold text-red-500
                       hover:text-red-600 hover:underline"
            >
                Yêu cầu huỷ đơn
            </button>

        </div>

        <form
            id="cancel-request-form"
            method="POST"
            action="{{ route(
                'profile.orders.cancel-request',
                $order->code
            ) }}"
            class="hidden mb-6 rounded-2xl border border-red-100
                   bg-white p-5 shadow-sm"
        >
            @csrf

            <h2 class="font-bold text-gray-900">
                Yêu cầu huỷ đơn hàng
            </h2>

            <p class="text-sm text-gray-500 mt-1 mb-4">
                Vui lòng cho cửa hàng biết lý do bạn muốn huỷ đơn.
            </p>

            @if($order->payment_status === 'paid')
                <div class="mb-4 rounded-xl bg-orange-50
                            px-4 py-3 text-sm text-orange-700">
                    ⚠️ Đơn hàng này đã thanh toán.
                    Gửi yêu cầu huỷ không đồng nghĩa với hoàn tiền ngay lập tức.
                </div>
            @endif

            <textarea
                name="reason"
                rows="4"
                required
                minlength="5"
                maxlength="1000"
                placeholder="Ví dụ: Tôi đặt nhầm sản phẩm..."
                class="w-full rounded-xl border border-gray-200
                       px-4 py-3 text-sm
                       focus:border-red-300
                       focus:ring-2 focus:ring-red-100"
            >{{ old('reason') }}</textarea>

            <div class="flex justify-end gap-3 mt-4">

                <button
                    type="button"
                    onclick="
                        document
                            .getElementById('cancel-request-form')
                            .classList
                            .add('hidden')
                    "
                    class="rounded-xl border border-gray-200
                           px-4 py-2 text-sm font-semibold
                           text-gray-600"
                >
                    Không huỷ nữa
                </button>

                <button
                    type="submit"
                    onclick="
                        return confirm(
                            'Bạn chắc chắn muốn gửi yêu cầu huỷ đơn?'
                        )
                    "
                    class="rounded-xl bg-red-500
                           px-5 py-2 text-sm font-semibold
                           text-white hover:bg-red-600"
                >
                    Gửi yêu cầu
                </button>

            </div>

        </form>

    @endif

@endif
        @php
            $steps = [
                'pending' => 'Đã đặt',
                'confirmed' => 'Đã xác nhận',
                'processing' => 'Đang xử lý',
                'shipping' => 'Đang giao',
                'delivered' => 'Đã giao',
            ];

            $statusOrder = array_keys($steps);
            $currentIndex = array_search($order->status, $statusOrder, true);
        @endphp

        @if(!in_array($order->status, ['cancelled', 'refunded'], true))
            <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5 mb-6 overflow-x-auto">
                <div class="min-w-[640px] flex items-start">
                    @foreach($steps as $value => $label)
                        @php
                            $stepIndex = array_search($value, $statusOrder, true);
                            $done = $currentIndex !== false && $stepIndex <= $currentIndex;
                        @endphp

                        <div class="flex-1 flex items-center last:flex-none">
                            <div class="flex flex-col items-center text-center min-w-[90px]">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                                            {{ $done ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                                    {{ $stepIndex + 1 }}
                                </div>

                                <div class="text-xs font-semibold mt-2
                                            {{ $done ? 'text-rose-600' : 'text-gray-400' }}">
                                    {{ $label }}
                                </div>
                            </div>

                            @if(!$loop->last)
                                <div class="h-1 flex-1 rounded-full mx-2 mt-4
                                            {{ $done && $stepIndex < $currentIndex
                                                ? 'bg-rose-400'
                                                : 'bg-gray-100' }}">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <section class="bg-white rounded-2xl border border-rose-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900">Sản phẩm đã đặt</h2>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse($order->items as $item)
                            <div class="px-5 py-4 flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-semibold text-gray-900">{{ $item->product_name }}</div>

                                    @if($item->product_sku)
                                        <div class="text-xs text-gray-400 mt-1">
                                            SKU: {{ $item->product_sku }}
                                        </div>
                                    @endif

                                    <div class="text-sm text-gray-500 mt-2">
                                        {{ number_format($item->price, 0, ',', '.') }}đ × {{ $item->quantity }}
                                    </div>
                                </div>

                                <div class="font-bold text-gray-900 whitespace-nowrap">
                                    {{ number_format($item->subtotal, 0, ',', '.') }}đ
                                </div>
                            </div>
                        @empty
                            <div class="px-5 py-10 text-center text-gray-400">
                                Không có dữ liệu sản phẩm.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5">
                    <h2 class="font-bold text-gray-900 mb-4">Thông tin nhận hàng</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-gray-400 text-xs mb-1">Người nhận</div>
                            <div class="font-semibold text-gray-800">{{ $order->recipient_name }}</div>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs mb-1">Số điện thoại</div>
                            <div class="font-semibold text-gray-800">{{ $order->recipient_phone }}</div>
                        </div>

                        <div class="md:col-span-2">
                            <div class="text-gray-400 text-xs mb-1">Địa chỉ</div>
                            <div class="text-gray-800">
                                {{ $order->address_detail }},
                                {{ $order->ward_name }},
                                {{ $order->district_name }},
                                {{ $order->province_name }}
                            </div>
                        </div>

                        @if($order->note)
                            <div class="md:col-span-2">
                                <div class="text-gray-400 text-xs mb-1">Ghi chú</div>
                                <div class="text-gray-800">{{ $order->note }}</div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5">
                    <h2 class="font-bold text-gray-900 mb-4">Vận chuyển</h2>

                    @if($order->shipment?->ghn_order_code)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <div class="text-xs text-gray-400 mb-1">Mã vận đơn GHN</div>
                                <div class="font-mono font-bold text-gray-800">
                                    {{ $order->shipment->ghn_order_code }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400 mb-1">Trạng thái GHN</div>
                                <div class="font-semibold text-blue-600">
                                    {{ strtoupper((string) $order->shipment->status) }}
                                </div>
                            </div>

                            <div>
                                <div class="text-xs text-gray-400 mb-1">Dự kiến giao</div>
                                <div class="font-semibold text-gray-800">
                                    {{ $order->shipment->expected_delivery_at?->format('d/m/Y') ?? 'Đang cập nhật' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">
                            Đơn hàng chưa có mã vận đơn.
                        </div>
                    @endif
                </section>

                @if($order->voucherUsages->isNotEmpty())
                    <section class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5">
                        <h2 class="font-bold text-gray-900 mb-4">Ưu đãi đã sử dụng</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($order->voucherUsages as $usage)
                                <div class="rounded-xl border border-green-100 bg-green-50/60 p-4">
                                    <div class="text-xs text-gray-500">
                                        {{ $usage->voucher?->type === 'shipping'
                                            ? 'Voucher vận chuyển'
                                            : 'Voucher đơn hàng' }}
                                    </div>

                                    <div class="font-bold text-gray-900 mt-1">
                                        {{ $usage->voucher_name }}
                                    </div>

                                    <div class="font-mono text-xs text-rose-600 mt-1">
                                        {{ $usage->voucher_code }}
                                    </div>

                                    <div class="text-sm font-semibold text-green-700 mt-3">
                                        -{{ number_format($usage->discount_amount, 0, ',', '.') }}đ
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <aside class="space-y-6">
                <section class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5">
                    <h2 class="font-bold text-gray-900 mb-4">Thanh toán</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Sản phẩm</span>
                            <span>{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Phí ship</span>
                            <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
                        </div>

                        @if($voucherDiscount > 0)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">Voucher</span>
                                <span class="font-semibold text-green-600">
                                    -{{ number_format($voucherDiscount, 0, ',', '.') }}đ
                                </span>
                            </div>
                        @endif

                        @if($pointsDiscount > 0)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500">
                                    Điểm
                                    @if((int) ($order->points_used ?? 0) > 0)
                                        ({{ number_format($order->points_used) }})
                                    @endif
                                </span>

                                <span class="font-semibold text-green-600">
                                    -{{ number_format($pointsDiscount, 0, ',', '.') }}đ
                                </span>
                            </div>
                        @endif

                        <div class="border-t border-gray-100 pt-3 flex justify-between gap-4">
                            <span class="font-bold text-gray-900">Tổng cộng</span>
                            <span class="text-xl font-bold text-rose-600">
                                {{ number_format($order->total, 0, ',', '.') }}đ
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100">
                        <div class="text-xs text-gray-400">Phương thức</div>
                        <div class="text-sm font-semibold text-gray-800 mt-1">
                            {{ $paymentLabels[$order->payment_method]
                                ?? strtoupper((string) $order->payment_method) }}
                        </div>

                        <div class="mt-3">
                            @if($order->payment_status === 'paid')
                                <span class="inline-flex rounded-full bg-green-50 px-3 py-1
                                             text-xs font-semibold text-green-700">
                                    ✓ Đã thanh toán
                                </span>
                            @elseif($order->payment_status === 'refunded')
                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1
                                             text-xs font-semibold text-gray-700">
                                    ↩ Đã hoàn tiền
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-orange-50 px-3 py-1
                                             text-xs font-semibold text-orange-600">
                                    Chưa thanh toán
                                </span>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5">
                    <h2 class="font-bold text-gray-900 mb-3">Cần hỗ trợ?</h2>

                    <p class="text-sm text-gray-500">
                        Nếu đơn hàng có vấn đề, bạn có thể liên hệ bộ phận hỗ trợ.
                    </p>

                    <a href="{{ route('profile.support') }}"
                       class="inline-flex mt-4 rounded-xl border border-rose-200 px-4 py-2
                              text-sm font-semibold text-rose-600 hover:bg-rose-50">
                        Liên hệ hỗ trợ
                    </a>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
