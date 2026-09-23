@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn ' . $order->code)

@section('content')
<div class="p-6 max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Đơn hàng {{ $order->code }}</h1>
            <p class="text-sm text-gray-500">
                {{ $order->created_at->format('H:i — d/m/Y') }}
            </p>
        </div>

        <a href="{{ route('admin.orders.index') }}"
           class="text-sm text-gray-500 hover:underline">
            ← Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Người nhận / Thanh toán / Trạng thái --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Người nhận --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-2">
            <h2 class="font-semibold text-gray-700 mb-3">👤 Người nhận</h2>

            <p class="text-sm"><span class="text-gray-500">Tên:</span> {{ $order->recipient_name }}</p>
            <p class="text-sm"><span class="text-gray-500">SĐT:</span> {{ $order->recipient_phone }}</p>
            <p class="text-sm"><span class="text-gray-500">Email:</span> {{ $order->recipient_email ?? '—' }}</p>

            <p class="text-sm">
                <span class="text-gray-500">Địa chỉ:</span>
                {{ $order->address_detail }},
                {{ $order->ward_name }},
                {{ $order->district_name }},
                {{ $order->province_name }}
            </p>
        </div>

        {{-- Tóm tắt tiền --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-2">
            <h2 class="font-semibold text-gray-700 mb-3">
                💰 Thanh toán
            </h2>

            @php
                $pointsDiscount = (int) ($order->points_discount ?? 0);
                $voucherDiscount = max(0, (int) $order->discount - $pointsDiscount);

                /*
                 * campaign_discount_amount đã là tổng mức giảm Campaign
                 * của từng OrderItem (đã nhân số lượng).
                 *
                 * Đây chỉ là thông tin hiển thị vì $order->subtotal
                 * đã là subtotal SAU Campaign. Không trừ thêm lần nữa.
                 */
                $campaignDiscount = (int) $order->items->sum(
                    fn ($item) => (int) ($item->campaign_discount_amount ?? 0)
                );

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

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Sản phẩm</span>
                <span>{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
            </div>

            @if($campaignDiscount > 0)
                <div class="rounded-lg border border-pink-100 bg-pink-50 px-3 py-2">
                    <div class="flex justify-between gap-3 text-sm">
                        <span class="text-pink-700 font-medium">
                            🏷 Tiết kiệm từ Campaign
                        </span>
                        <span class="text-pink-700 font-bold">
                            {{ number_format($campaignDiscount, 0, ',', '.') }}đ
                        </span>
                    </div>
                    <div class="text-[11px] text-pink-500 mt-1">
                        Khoản giảm này đã được tính trực tiếp vào đơn giá sản phẩm.
                    </div>
                </div>
            @endif

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Phí ship</span>
                <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</span>
            </div>

            @if($voucherDiscount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Voucher</span>
                    <span class="text-green-600 font-medium">
                        -{{ number_format($voucherDiscount, 0, ',', '.') }}đ
                    </span>
                </div>
            @endif

            @if($pointsDiscount > 0)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">
                        Điểm đã dùng
                        @if((int) ($order->points_used ?? 0) > 0)
                            ({{ number_format($order->points_used) }} điểm)
                        @endif
                    </span>
                    <span class="text-green-600 font-medium">
                        -{{ number_format($pointsDiscount, 0, ',', '.') }}đ
                    </span>
                </div>
            @endif

            <div class="flex justify-between font-bold text-base border-t pt-3 mt-2">
                <span>Tổng cộng</span>
                <span class="text-indigo-600">
                    {{ number_format($order->total, 0, ',', '.') }}đ
                </span>
            </div>

            <div class="pt-2 text-xs text-gray-500">
                <span class="font-semibold">
                    {{ $paymentLabels[$order->payment_method] ?? strtoupper($order->payment_method) }}
                </span>
                <span class="mx-1">—</span>
                @if($order->payment_status === 'paid')
                    <span class="text-green-600">✅ Đã thanh toán</span>
                @elseif($order->payment_status === 'refunded')
                    <span class="text-gray-600">↩ Đã hoàn tiền</span>
                @else
                    <span class="text-orange-500">⏳ Chưa thanh toán</span>
                @endif
            </div>
        </div>

        {{-- Trạng thái đơn --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-700 mb-3">📋 Trạng thái đơn</h2>

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

                $allowedTransitions = [
                    'pending' => [
                        'confirmed' => 'Xác nhận đơn',
                        'cancelled' => 'Huỷ đơn',
                    ],
                    'confirmed' => [
                        'processing' => 'Chuyển sang xử lý',
                        'cancelled' => 'Huỷ đơn',
                    ],
                    'processing' => [
                        'cancelled' => 'Huỷ đơn',
                    ],
                    'shipping' => app()->environment(['local', 'testing'])
                      ? ['delivered' => 'Mô phỏng đã giao']
                      : ($order->shipment?->ghn_order_code ? [] : ['delivered' => 'Xác nhận đã giao']),
                    'delivered' => [],
                    'cancelled' => [],
                    'refunded' => [],
                ];

                $nextStatuses = $allowedTransitions[$order->status] ?? [];
            @endphp

            <div class="mb-4">
                <span class="text-xs text-gray-500 block mb-1">Trạng thái hiện tại</span>
                <span class="inline-flex px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-semibold">
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </span>
            </div>

            @if(count($nextStatuses))
                @can('orders.manage')
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <label class="text-xs text-gray-500 block mb-2">Chuyển trạng thái</label>
                        <select name="status" required class="border rounded-lg px-3 py-2 text-sm w-full mb-3">
                            <option value="">-- Chọn trạng thái --</option>
                            @foreach($nextStatuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm hover:bg-indigo-700">
                            Cập nhật trạng thái
                        </button>
                    </form>
                @else
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-500">
                        🔒 Bạn không có quyền thay đổi trạng thái đơn hàng này.
                    </div>
                @endcan
            @else
                @if($order->status === 'delivered')
                    <div class="bg-green-50 text-green-700 rounded-lg px-3 py-2 text-sm">✅ Đơn hàng đã hoàn tất.</div>
                @elseif($order->status === 'cancelled')
                    <div class="bg-red-50 text-red-600 rounded-lg px-3 py-2 text-sm">❌ Đơn hàng đã bị huỷ.</div>
                @elseif($order->status === 'refunded')
                    <div class="bg-gray-100 text-gray-600 rounded-lg px-3 py-2 text-sm">↩ Đơn hàng đã hoàn tiền.</div>
                @elseif($order->status === 'shipping' && $order->shipment?->ghn_order_code)
                    <div class="bg-blue-50 text-blue-700 rounded-lg px-3 py-2 text-sm">
                        🚚 Đơn đang được GHN vận chuyển.<br>Trạng thái sẽ được cập nhật khi tra cứu GHN.
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Voucher đã áp dụng --}}
    @if($order->voucherUsages->isNotEmpty())
        <div class="bg-white rounded-xl shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-700">🎟 Voucher đã áp dụng</h2>
                <span class="text-xs text-gray-400">{{ $order->voucherUsages->count() }} voucher</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($order->voucherUsages as $usage)
                    @php
                        $voucherType = $usage->voucher?->type;
                        $typeLabel = match ($voucherType) {
                            'shipping' => 'Voucher vận chuyển',
                            'order' => 'Voucher đơn hàng',
                            default => 'Voucher',
                        };

                        $statusLabel = match ($usage->status) {
                            'reserved' => 'Đang giữ',
                            'applied' => 'Đã áp dụng',
                            'completed' => 'Hoàn tất',
                            'cancelled' => 'Đã huỷ',
                            default => $usage->status,
                        };

                        $statusClass = match ($usage->status) {
                            'applied', 'completed' => 'bg-green-100 text-green-700',
                            'reserved' => 'bg-yellow-100 text-yellow-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="text-xs text-gray-500">{{ $typeLabel }}</div>
                                <div class="font-bold text-gray-800 mt-1">{{ $usage->voucher_name }}</div>
                                <div class="font-mono text-xs text-indigo-600 mt-1">{{ $usage->voucher_code }}</div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Giá trị giảm</span>
                            <span class="font-bold text-green-600">-{{ number_format($usage->discount_amount, 0, ',', '.') }}đ</span>
                        </div>

                        @if($usage->discount_type === 'free_shipping')
                            <div class="text-xs text-blue-600 mt-2">🚚 Miễn phí vận chuyển</div>
                        @endif

                        @if($usage->applied_at)
                            <div class="text-xs text-gray-400 mt-2">Áp dụng: {{ $usage->applied_at->format('H:i d/m/Y') }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Sản phẩm --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">🛒 Sản phẩm đặt mua</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-500 border-b text-xs uppercase">
                    <tr>
                        <th class="pb-2 text-left">Sản phẩm</th>
                        <th class="pb-2 text-right">Đơn giá</th>
                        <th class="pb-2 text-right">SL</th>
                        <th class="pb-2 text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($order->items as $item)
                        @php
                            $itemQuantity = max(1, (int) $item->quantity);
                            $campaignLineDiscount = max(
                                0,
                                (int) ($item->campaign_discount_amount ?? 0)
                            );
                            $hasCampaign = !empty($item->campaign_id)
                                && $campaignLineDiscount > 0;

                            $unitCampaignDiscount = $hasCampaign
                                ? (int) round(
                                    $campaignLineDiscount / $itemQuantity
                                )
                                : 0;

                            $baseUnitPrice = (int) $item->price
                                + $unitCampaignDiscount;

                            $campaignPercent = (
                                $hasCampaign
                                && $baseUnitPrice > 0
                            )
                                ? (int) round(
                                    $unitCampaignDiscount
                                    * 100
                                    / $baseUnitPrice
                                )
                                : 0;
                        @endphp

                        <tr>
                            <td class="py-3">
                                <div class="font-medium">
                                    {{ $item->product_name }}
                                </div>

                                @if($item->product_sku)
                                    <div class="text-gray-400 text-xs">
                                        SKU: {{ $item->product_sku }}
                                    </div>
                                @endif

                                @if($hasCampaign)
                                    <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-pink-50 text-pink-700 text-[11px] font-semibold border border-pink-100">
                                            🏷 Campaign #{{ $item->campaign_id }}
                                            @if($campaignPercent > 0)
                                                · -{{ $campaignPercent }}%
                                            @endif
                                        </span>

                                        @if($item->campaign_type)
                                            <span class="text-[11px] text-gray-400">
                                                {{ $item->campaign_type }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-[11px] text-green-600 mt-1">
                                        Tiết kiệm:
                                        {{ number_format(
                                            $campaignLineDiscount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                <div class="font-medium">
                                    {{ number_format(
                                        $item->price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </div>

                                @if($hasCampaign)
                                    <div class="text-xs text-gray-400 line-through mt-1">
                                        {{ number_format(
                                            $baseUnitPrice,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 text-right">
                                {{ $item->quantity }}
                            </td>

                            <td class="py-3 text-right font-semibold">
                                <div>
                                    {{ number_format(
                                        $item->subtotal,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </div>

                                @if($hasCampaign)
                                    <div class="text-xs text-gray-400 line-through font-normal mt-1">
                                        {{ number_format(
                                            (int) $item->subtotal
                                                + $campaignLineDiscount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}đ
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400">Không có sản phẩm trong đơn hàng.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Vận chuyển GHN --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">🚚 Vận chuyển GHN</h2>

        @if($order->shipment?->ghn_order_code)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5 text-sm">
                <div>
                    <span class="text-gray-500 block text-xs">Mã GHN</span>
                    <span class="font-mono font-semibold">{{ $order->shipment->ghn_order_code }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Phí ship GHN</span>
                    <span>{{ number_format($order->shipment->shipping_fee) }}đ</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Trạng thái</span>
                    @php
                        $ghnColors = [
                            'ready_to_pick' => 'yellow',
                            'picking'       => 'blue',
                            'delivering'    => 'indigo',
                            'delivered'     => 'green',
                            'cancel'        => 'red',
                        ];
                        $ghnColor = $ghnColors[$order->shipment->status] ?? 'blue';
                    @endphp
                    <span class="uppercase font-medium text-{{ $ghnColor }}-600">{{ $order->shipment->status }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs">Dự kiến giao</span>
                    <span>{{ $order->shipment->expected_delivery_at?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>

            @if($order->shipment->status === 'cancel')
                {{-- Vận đơn đã huỷ — cho tạo lại --}}
                @can('orders.manage')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <p class="text-red-600 text-sm font-medium">⚠️ Vận đơn GHN đã bị huỷ.</p>
                        <p class="text-gray-500 text-xs mt-1">Bạn có thể tạo vận đơn mới cho đơn hàng này ngay bên dưới.</p>
                    </div>

                    <form method="POST" action="{{ route('admin.orders.shipment.create', $order) }}" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @csrf
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Khối lượng (gram)</label>
                            <input type="number" name="weight" value="{{ $order->shipment->weight ?? 500 }}" min="1" class="border rounded-lg px-3 py-2 text-sm w-full">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Dài (cm)</label>
                            <input type="number" name="length" value="{{ $order->shipment->length ?? 20 }}" min="1" class="border rounded-lg px-3 py-2 text-sm w-full">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Rộng (cm)</label>
                            <input type="number" name="width" value="{{ $order->shipment->width ?? 15 }}" min="1" class="border rounded-lg px-3 py-2 text-sm w-full">
                        </div>
                        <div>
                            <label class="text-xs text-gray-600 block mb-1">Cao (cm)</label>
                            <input type="number" name="height" value="{{ $order->shipment->height ?? 10 }}" min="1" class="border rounded-lg px-3 py-2 text-sm w-full">
                        </div>
                        <div class="col-span-2 md:col-span-4">
                            <label class="text-xs text-gray-600 block mb-1">Ghi chú</label>
                            <input type="text" name="note" placeholder="Gọi trước khi giao..." class="border rounded-lg px-3 py-2 text-sm w-full">
                        </div>
                        <div class="col-span-2 md:col-span-4">
                            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-green-700">
                                🚚 Tạo vận đơn mới
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-sm text-red-500">⚠️ Vận đơn GHN đã bị huỷ. Bạn cần liên hệ Quản lý để tạo vận đơn mới.</div>
                @endcan
            @else
                {{-- Vận đơn bình thường -> Cho in, tra cứu, hủy --}}
                <div class="mb-5">
                    <span class="text-gray-500 block text-xs">In vận đơn</span>
                    <span id="print-status" class="text-xs @if($order->shipment->printed_at) text-green-600 @else text-gray-400 @endif">
                        @if($order->shipment->printed_at)
                            ✅ {{ $order->shipment->printed_at->format('H:i d/m/Y') }}
                        @else
                            Chưa in
                        @endif
                    </span>
                </div>

                <div class="flex gap-3 flex-wrap">
                    @can('orders.manage')
                        <a href="{{ route('admin.orders.shipment.print', $order) }}" target="_blank" onclick="markPrinted()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                            🖨️ In vận đơn
                        </a>

                        <form method="POST" action="{{ route('admin.orders.shipment.track', $order) }}">
                            @csrf
                            <button type="submit" class="border border-indigo-600 text-indigo-600 px-4 py-2 rounded-lg text-sm hover:bg-indigo-50">
                                🔄 Tra cứu GHN
                            </button>
                        </form>

                        @if(in_array($order->shipment->status, ['pending', 'ready_to_pick']))
                            <form method="POST" action="{{ route('admin.orders.shipment.cancel', $order) }}" onsubmit="return confirm('Huỷ vận đơn GHN này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="border border-red-400 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50">
                                    ❌ Huỷ vận đơn
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            @endif
        @else
            <p class="text-sm text-gray-500 mb-4">Chưa tạo vận đơn GHN cho đơn này.</p>
            @can('orders.manage')
                <form method="POST" action="{{ route('admin.orders.shipment.create', $order) }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @csrf
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Khối lượng (gram)</label>
                        <input type="number" name="weight" value="500" min="1" required class="border rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Dài (cm)</label>
                        <input type="number" name="length" value="20" min="1" required class="border rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Rộng (cm)</label>
                        <input type="number" name="width" value="15" min="1" required class="border rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div>
                        <label class="text-xs text-gray-600 block mb-1">Cao (cm)</label>
                        <input type="number" name="height" value="10" min="1" required class="border rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div class="sm:col-span-2 md:col-span-4">
                        <label class="text-xs text-gray-600 block mb-1">Ghi chú giao hàng</label>
                        <input type="text" name="note" placeholder="Gọi trước khi giao, hàng dễ vỡ..." class="border rounded-lg px-3 py-2 text-sm w-full">
                    </div>
                    <div class="sm:col-span-2 md:col-span-4">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-green-700">
                            🚚 Tạo vận đơn GHN
                        </button>
                    </div>
                </form>
            @else
                <div class="p-3 bg-gray-50 text-sm text-gray-500 rounded-lg">🔒 Tính năng tạo vận đơn chỉ dành cho cấp Quản lý.</div>
            @endcan
        @endif
    </div>

</div>

@push('scripts')
<script>
function markPrinted() {
    const status = document.getElementById('print-status');
    if (!status) return;

    const now = new Date();
    const pad = n => n.toString().padStart(2, '0');
    const formatted = `${pad(now.getHours())}:${pad(now.getMinutes())} ${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()}`;

    status.textContent = '✅ ' + formatted;
    status.className = 'text-xs text-green-600';
}
</script>
@endpush

@endsection