@extends('admin.layouts.app')

@section('content')
<div class="px-6 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản lý Voucher</h2>
            <p class="text-sm text-gray-500 mt-1">Danh sách các chương trình khuyến mãi, mã giảm giá</p>
        </div>
        <a href="{{ route('admin.vouchers.create') }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2.5 px-5 rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tạo Voucher mới
        </a>
    </div>

    <!-- Bảng dữ liệu -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">Mã Voucher</th>
                        <th class="px-6 py-4 font-medium">Chương trình</th>
                        <th class="px-6 py-4 font-medium">Loại giảm</th>
                        <th class="px-6 py-4 font-medium">Lượt dùng</th>
                        <th class="px-6 py-4 font-medium">Thời hạn</th>
                        <th class="px-6 py-4 font-medium">Trạng thái</th>
                        <th class="px-6 py-4 font-medium text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($vouchers as $voucher)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 bg-gray-100 text-gray-800 font-bold rounded text-sm tracking-wider">
                                {{ $voucher->code }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-800 text-sm">{{ $voucher->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">Đơn tối thiểu: {{ number_format($voucher->min_order_amount) }}đ</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($voucher->discount_type === 'percent')
                                <span class="text-sm font-semibold text-blue-600">Giảm {{ $voucher->discount_value }}%</span>
                            @elseif($voucher->discount_type === 'fixed')
                                <span class="text-sm font-semibold text-green-600">Giảm {{ number_format($voucher->discount_value) }}đ</span>
                            @else
                                <span class="text-sm font-semibold text-orange-600">Freeship</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $voucher->used_count }} / {{ $voucher->total_quantity ? $voucher->total_quantity : '∞' }}
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-xs text-gray-600">{{ $voucher->starts_at ? $voucher->starts_at->format('d/m/Y H:i') : 'Ngay lập tức' }}</p>
                            <p class="text-xs text-gray-500 mt-1">Đến: {{ $voucher->expires_at ? $voucher->expires_at->format('d/m/Y H:i') : 'Không giới hạn' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($voucher->status === 'active')
                                <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Hoạt động</span>
                            @elseif($voucher->status === 'draft')
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Bản nháp</span>
                            @elseif($voucher->status === 'expired')
                                <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Hết hạn</span>
                            @else
                                <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">{{ ucfirst($voucher->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium mr-3">Sửa</a>
                            <button class="text-red-500 hover:text-red-700 text-sm font-medium">Xóa</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            Chưa có mã giảm giá nào. Hãy tạo mã đầu tiên!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Phân trang -->
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $vouchers->links() }}
        </div>
    </div>
</div>
@endsection