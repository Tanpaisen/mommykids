@extends('admin.layouts.app')

@section('title', 'Chi tiết đơn ' . $order->code)

@section('content')
<div class="p-6 max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Đơn hàng {{ $order->code }}</h1>
            <p class="text-sm text-gray-500">{{ $order->created_at->format('H:i — d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:underline">← Quay lại</a>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Thông tin khách --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-2">
            <h2 class="font-semibold text-gray-700 mb-3">👤 Người nhận</h2>
            <p class="text-sm"><span class="text-gray-500">Tên:</span> {{ $order->recipient_name }}</p>
            <p class="text-sm"><span class="text-gray-500">SĐT:</span> {{ $order->recipient_phone }}</p>
            <p class="text-sm"><span class="text-gray-500">Email:</span> {{ $order->recipient_email ?? '—' }}</p>
            <p class="text-sm"><span class="text-gray-500">Địa chỉ:</span>
                {{ $order->address_detail }}, {{ $order->ward_name }},
                {{ $order->district_name }}, {{ $order->province_name }}
            </p>
        </div>

        {{-- Tóm tắt tiền --}}
        <div class="bg-white rounded-xl shadow p-5 space-y-2">
            <h2 class="font-semibold text-gray-700 mb-3">💰 Thanh toán</h2>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Sản phẩm</span><span>{{ number_format($order->subtotal) }}đ</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Phí ship</span><span>{{ number_format($order->shipping_fee) }}đ</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Giảm giá</span><span>-{{ number_format($order->discount) }}đ</span></div>
            <div class="flex justify-between font-bold text-base border-t pt-2"><span>Tổng cộng</span><span class="text-indigo-600">{{ number_format($order->total) }}đ</span></div>
            <p class="text-xs text-gray-400 pt-1">
                {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : strtoupper($order->payment_method) }}
                — {{ $order->payment_status === 'paid' ? '✅ Đã thanh toán' : '⏳ Chưa thanh toán' }}
            </p>
        </div>

        {{-- Cập nhật trạng thái --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="font-semibold text-gray-700 mb-3">📋 Trạng thái đơn</h2>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf @method('PATCH')
                <select name="status" class="border rounded-lg px-3 py-2 text-sm w-full mb-3">
                    @foreach(['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã huỷ','refunded'=>'Đã hoàn tiền'] as $val => $lbl)
                        <option value="{{ $val }}" @selected($order->status === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button class="w-full bg-indigo-600 text-white rounded-lg py-2 text-sm hover:bg-indigo-700">
                    Lưu trạng thái
                </button>
            </form>
        </div>
    </div>

    {{-- Sản phẩm --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">🛒 Sản phẩm đặt mua</h2>
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
                @foreach($order->items as $item)
                <tr>
                    <td class="py-3">
                        <div class="font-medium">{{ $item->product_name }}</div>
                        @if($item->product_sku)<div class="text-gray-400 text-xs">SKU: {{ $item->product_sku }}</div>@endif
                    </td>
                    <td class="py-3 text-right">{{ number_format($item->price) }}đ</td>
                    <td class="py-3 text-right">{{ $item->quantity }}</td>
                    <td class="py-3 text-right font-semibold">{{ number_format($item->subtotal) }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Vận chuyển GHN --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">🚚 Vận chuyển GHN</h2>

        @if($order->shipment?->ghn_order_code)
            {{-- Đã có vận đơn --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5 text-sm">
                <div><span class="text-gray-500 block text-xs">Mã GHN</span>
                    <span class="font-mono font-semibold">{{ $order->shipment->ghn_order_code }}</span></div>
                <div><span class="text-gray-500 block text-xs">Phí ship</span>
                    <span>{{ number_format($order->shipment->shipping_fee) }}đ</span></div>
                <div><span class="text-gray-500 block text-xs">Trạng thái</span>
                    <span class="uppercase font-medium text-blue-600">{{ $order->shipment->status }}</span></div>
                <div><span class="text-gray-500 block text-xs">Dự kiến giao</span>
                    <span>{{ $order->shipment->expected_delivery_at?->format('d/m/Y') ?? '—' }}</span></div>
            </div>

            <div class="flex gap-3 flex-wrap">
                {{-- In vận đơn --}}
                <a href="{{ route('admin.orders.shipment.print', $order) }}" target="_blank"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                    🖨️ In vận đơn
                </a>

                {{-- Tra cứu GHN --}}
                <a href="{{ route('admin.orders.shipment.track', $order) }}"
                   class="border border-indigo-600 text-indigo-600 px-4 py-2 rounded-lg text-sm hover:bg-indigo-50">
                    🔄 Cập nhật trạng thái
                </a>

                {{-- Huỷ vận đơn --}}
                <form method="POST" action="{{ route('admin.orders.shipment.cancel', $order) }}"
                      onsubmit="return confirm('Huỷ vận đơn GHN này?')">
                    @csrf @method('DELETE')
                    <button class="border border-red-400 text-red-500 px-4 py-2 rounded-lg text-sm hover:bg-red-50">
                        ❌ Huỷ vận đơn
                    </button>
                </form>
            </div>

        @else
            {{-- Chưa có vận đơn — form tạo --}}
            <p class="text-sm text-gray-500 mb-4">Chưa tạo vận đơn GHN cho đơn này.</p>

            <form method="POST" action="{{ route('admin.orders.shipment.create', $order) }}"
                  class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @csrf

                <div>
                    <label class="text-xs text-gray-600 block mb-1">Khối lượng (gram)</label>
                    <input type="number" name="weight" value="500" min="1"
                           class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-600 block mb-1">Dài (cm)</label>
                    <input type="number" name="length" value="20" min="1"
                           class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-600 block mb-1">Rộng (cm)</label>
                    <input type="number" name="width" value="15" min="1"
                           class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>
                <div>
                    <label class="text-xs text-gray-600 block mb-1">Cao (cm)</label>
                    <input type="number" name="height" value="10" min="1"
                           class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>

                <div class="col-span-2 md:col-span-4">
                    <label class="text-xs text-gray-600 block mb-1">Ghi chú giao hàng</label>
                    <input type="text" name="note" placeholder="Gọi trước khi giao, hàng dễ vỡ..."
                           class="border rounded-lg px-3 py-2 text-sm w-full">
                </div>

                <div class="col-span-2 md:col-span-4">
                    <button type="submit"
                            class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-green-700">
                        🚚 Tạo vận đơn GHN
                    </button>
                </div>
            </form>
        @endif
    </div>

</div>
@endsection