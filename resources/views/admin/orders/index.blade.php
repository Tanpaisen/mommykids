@extends('admin.layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Đơn hàng</h1>
    </div>

    {{-- Bộ lọc --}}
    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Tìm mã đơn, tên, SĐT..."
               class="border rounded-lg px-3 py-2 text-sm w-64">

        <select name="status" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">Tất cả trạng thái</option>
            @foreach(['pending'=>'Chờ xác nhận','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','shipping'=>'Đang giao','delivered'=>'Đã giao','cancelled'=>'Đã huỷ'] as $val => $label)
                <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
            @endforeach
        </select>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm">Lọc</button>
        <a href="{{ route('admin.orders.index') }}" class="border px-4 py-2 rounded-lg text-sm">Reset</a>
    </form>

    {{-- Bảng --}}
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Mã đơn</th>
                    <th class="px-4 py-3 text-left">Khách hàng</th>
                    <th class="px-4 py-3 text-left">Tổng tiền</th>
                    <th class="px-4 py-3 text-left">Vận đơn GHN</th>
                    <th class="px-4 py-3 text-left">Trạng thái</th>
                    <th class="px-4 py-3 text-left">Ngày tạo</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                @php $label = $order->statusLabel(); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-semibold text-indigo-600">{{ $order->code }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $order->recipient_name }}</div>
                        <div class="text-gray-400 text-xs">{{ $order->recipient_phone }}</div>
                    </td>
                    <td class="px-4 py-3 font-semibold">{{ number_format($order->total) }}đ</td>
                    <td class="px-4 py-3">
                        @if($order->shipment?->ghn_order_code)
                            <span class="font-mono text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded">
                                {{ $order->shipment->ghn_order_code }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">Chưa tạo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            bg-{{ $label['color'] }}-100 text-{{ $label['color'] }}-700">
                            {{ $label['text'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="text-indigo-600 hover:underline text-sm">Chi tiết →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">Không có đơn hàng nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection