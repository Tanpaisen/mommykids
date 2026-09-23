@extends('admin.layouts.app')
@section('content')
<div class="px-6 py-8 max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Lịch sử biến động Kho</h2>
            <p class="text-sm text-gray-500 mt-1">Mọi thay đổi tồn kho đều được ghi lại — chống gian lận, đối soát</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">&larr; Quay lại</a>
    </div>

    <!-- Bộ lọc -->
    <form method="GET" class="bg-white rounded-xl shadow-sm border p-4 mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Loại biến động</label>
            <select name="type" class="rounded-lg border-gray-300 text-sm">
                <option value="">Tất cả</option>
                <option value="import" {{ request('type')=='import'?'selected':'' }}>Nhập hàng</option>
                <option value="export" {{ request('type')=='export'?'selected':'' }}>Xuất bán</option>
                <option value="return" {{ request('type')=='return'?'selected':'' }}>Khách trả</option>
                <option value="damage" {{ request('type')=='damage'?'selected':'' }}>Hỏng/Thất thoát</option>
                <option value="adjust" {{ request('type')=='adjust'?'selected':'' }}>Chỉnh tay</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sản phẩm</label>
            <select name="product_id" class="rounded-lg border-gray-300 text-sm">
                <option value="">Tất cả</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" {{ request('product_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Từ ngày</label>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Đến ngày</label>
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600">Lọc</button>
        <a href="{{ route('admin.inventory.movements') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Xóa lọc</a>
    </form>

    <!-- Danh sách biến động -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-500">
                        <th class="py-3 px-4">Thời gian</th>
                        <th class="py-3 px-4">Sản phẩm</th>
                        <th class="py-3 px-4">Loại</th>
                        <th class="py-3 px-4 text-center">Biến động</th>
                        <th class="py-3 px-4 text-center">Trước → Sau</th>
                        <th class="py-3 px-4">Người thực hiện</th>
                        <th class="py-3 px-4">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="py-3 px-4 text-gray-500 text-xs">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 font-medium">{{ $m->product->name ?? 'N/A' }}</td>
                        <td class="py-3 px-4">
                            @php
                                $typeLabels = ['import'=>'Nhập hàng','export'=>'Xuất bán','return'=>'Khách trả','damage'=>'Hỏng/Thất thoát','adjust'=>'Chỉnh tay'];
                                $typeColors = ['import'=>'bg-green-100 text-green-700','export'=>'bg-blue-100 text-blue-700','return'=>'bg-purple-100 text-purple-700','damage'=>'bg-red-100 text-red-700','adjust'=>'bg-yellow-100 text-yellow-700'];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-medium {{ $typeColors[$m->type] ?? 'bg-gray-100' }}">{{ $typeLabels[$m->type] ?? $m->type }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold {{ $m->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}
                        </td>
                        <td class="py-3 px-4 text-center text-gray-500 text-xs">{{ $m->stock_before }} → {{ $m->stock_after }}</td>
                        <td class="py-3 px-4 text-gray-600 text-xs">{{ $m->user->name ?? 'Hệ thống' }}</td>
                        <td class="py-3 px-4 text-gray-500 text-xs">{{ $m->note ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-12 text-center text-gray-400">Chưa có biến động kho nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $movements->links() }}</div>
    </div>
</div>
@endsection
