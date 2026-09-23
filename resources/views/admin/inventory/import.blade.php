@extends('admin.layouts.app')
@section('content')
<div class="px-6 py-8 max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Nhập hàng vào Kho</h2>
            <p class="text-sm text-gray-500 mt-1">Cập nhật tồn kho + giá vốn khi nhận hàng từ nhà cung cấp</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">&larr; Quay lại</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.inventory.import.store') }}" class="bg-white rounded-xl shadow-sm border p-8">
        @csrf
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Sản phẩm *</label>
            <select name="product_id" required class="w-full rounded-lg border-gray-300">
                <option value="">-- Chọn sản phẩm --</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" {{ old('product_id')==$p->id?'selected':'' }}>
                    {{ $p->name }} (Tồn: {{ $p->stock }} | Giá vốn: {{ number_format($p->cost_price) }}đ)
                </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng nhập *</label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="w-full rounded-lg border-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Giá vốn mới (đ)</label>
                <input type="number" name="cost_price" value="{{ old('cost_price') }}" min="0" class="w-full rounded-lg border-gray-300" placeholder="Để trống = giữ nguyên giá vốn cũ">
                <p class="text-xs text-gray-400 mt-1">Nếu lô hàng này giá khác, nhập giá mới để cập nhật</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Ghi chú</label>
            <input type="text" name="note" value="{{ old('note') }}" class="w-full rounded-lg border-gray-300" placeholder="VD: Nhập lô 09/2026 từ nhà cung cấp Meiji">
        </div>

        <div class="flex justify-end gap-4 border-t pt-6">
            <a href="{{ route('admin.inventory.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Hủy</a>
            <button type="submit" class="px-6 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium">Xác nhận nhập hàng</button>
        </div>
    </form>
</div>
@endsection
