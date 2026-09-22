@extends('admin.layouts.app')
@section('content')
<div class="px-6 py-8 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản lý Kho</h2>
            <p class="text-sm text-gray-500 mt-1">Tổng quan tồn kho, cảnh báo hết hàng & vốn kho</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.inventory.import.create') }}" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium">+ Nhập hàng</a>
            <a href="{{ route('admin.inventory.adjust.create') }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium">Điều chỉnh kho</a>
            <a href="{{ route('admin.inventory.movements') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">Lịch sử kho</a>
        </div>
    </div>

    <!-- Thẻ chỉ số -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <p class="text-sm text-gray-500 mb-2">Tổng vốn tồn kho</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalInventoryValue) }} đ</p>
            <p class="text-xs text-gray-400 mt-1">Tiền đang đọng trong kho</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <p class="text-sm text-gray-500 mb-2">Sản phẩm sắp hết hàng</p>
            <p class="text-2xl font-bold text-red-600">{{ $lowStockProducts->total() }}</p>
            <p class="text-xs text-gray-400 mt-1">Cần nhập hàng gấp</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <p class="text-sm text-gray-500 mb-2">Hàng tồn đọng (Dead Stock)</p>
            <p class="text-2xl font-bold text-orange-500">{{ $deadStock->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">Không bán được trong 90 ngày</p>
        </div>
    </div>

    <!-- Sản phẩm sắp hết hàng -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4">⚠️ Sản phẩm cần nhập gấp (stock ≤ ngưỡng cảnh báo)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="py-3 px-2">Sản phẩm</th>
                        <th class="py-3 px-2">SKU</th>
                        <th class="py-3 px-2 text-center">Tồn kho</th>
                        <th class="py-3 px-2 text-center">Ngưỡng cảnh báo</th>
                        <th class="py-3 px-2 text-right">Giá vốn</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $p)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium">{{ $p->name }}</td>
                        <td class="py-3 px-2 text-gray-500">{{ $p->sku ?? '-' }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">{{ $p->stock }}</span>
                        </td>
                        <td class="py-3 px-2 text-center text-gray-500">{{ $p->low_stock_alert }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($p->cost_price) }} đ</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-400">Tất cả sản phẩm đều còn hàng tốt 👍</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $lowStockProducts->links() }}</div>
    </div>

    <!-- Hàng tồn đọng -->
    @if($deadStock->count())
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">💀 Hàng tồn đọng (90 ngày không có giao dịch xuất)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500">
                        <th class="py-3 px-2">Sản phẩm</th>
                        <th class="py-3 px-2 text-center">Tồn kho</th>
                        <th class="py-3 px-2 text-right">Vốn tồn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deadStock as $p)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-2 font-medium">{{ $p->name }}</td>
                        <td class="py-3 px-2 text-center">{{ $p->stock }}</td>
                        <td class="py-3 px-2 text-right">{{ number_format($p->stock * $p->cost_price) }} đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
