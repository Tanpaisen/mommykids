@extends('admin.layouts.app')
@section('content')
<div class="px-6 py-8 max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Điều chỉnh tồn kho (Kiểm kê)</h2>
            <p class="text-sm text-gray-500 mt-1">Chỉ dùng khi kiểm kê kho phát hiện chênh lệch — BẮT BUỘC ghi lý do</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">&larr; Quay lại</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-6 text-sm">
        ⚠️ Mọi điều chỉnh thủ công đều được ghi log với tên người thực hiện và lý do. Chỉ sử dụng khi kiểm kê kho.
    </div>

    <form method="POST" action="{{ route('admin.inventory.adjust.store') }}" class="bg-white rounded-xl shadow-sm border p-8">
        @csrf
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Sản phẩm *</label>
            <select name="product_id" id="product_select" required class="w-full rounded-lg border-gray-300">
                <option value="">-- Chọn sản phẩm --</option>
                @foreach($products as $p)
                <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" {{ old('product_id')==$p->id?'selected':'' }}>
                    {{ $p->name }} (Tồn hiện tại: {{ $p->stock }})
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tồn kho thực tế sau kiểm kê *</label>
            <input type="number" name="new_stock" id="new_stock" value="{{ old('new_stock', 0) }}" min="0" required class="w-full rounded-lg border-gray-300">
            <p class="text-xs text-gray-400 mt-1">Tồn hiện tại: <span id="current_stock" class="font-bold text-gray-600">--</span> → Chênh lệch: <span id="diff" class="font-bold">0</span></p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Lý do điều chỉnh *</label>
            <textarea name="note" rows="3" required class="w-full rounded-lg border-gray-300" placeholder="VD: Kiểm kê 22/09/2026, phát hiện thiếu 2 cái do thất lạc">{{ old('note') }}</textarea>
        </div>

        <div class="flex justify-end gap-4 border-t pt-6">
            <a href="{{ route('admin.inventory.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Hủy</a>
            <button type="submit" class="px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-medium">Xác nhận điều chỉnh</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('product_select');
    const currentStock = document.getElementById('current_stock');
    const newStock = document.getElementById('new_stock');
    const diff = document.getElementById('diff');

    function updateDiff() {
        const current = parseInt(select.options[select.selectedIndex]?.dataset?.stock || 0);
        const newVal = parseInt(newStock.value || 0);
        currentStock.textContent = current;
        const d = newVal - current;
        diff.textContent = (d > 0 ? '+' : '') + d;
        diff.className = 'font-bold ' + (d > 0 ? 'text-green-600' : d < 0 ? 'text-red-600' : 'text-gray-600');
    }

    select.addEventListener('change', updateDiff);
    newStock.addEventListener('input', updateDiff);
    updateDiff();
});
</script>
@endsection
