@extends('admin.layouts.app')
@section('page_title', 'Import Sản Phẩm')

@section('content')
<div class="card max-w-2xl mx-auto p-6 bg-white rounded-xl shadow-sm border border-admin-border">
    <h2 class="text-lg font-bold mb-4">Upload File CSV</h2>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-lg">{{ session('success') }}</div>
    @endif

    @if (session('errors') && is_array(session('errors')))
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg space-y-1 text-sm">
            @foreach (session('errors') as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.products.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-2 font-semibold text-sm">Chọn file CSV (9 cột theo chuẩn)</label>
            <input type="file" name="file" accept=".csv" required
                   class="w-full border border-admin-border rounded-lg p-3">
            <p class="text-xs text-gray-500 mt-2">Cột: Tên, ID Danh mục, SKU, Barcode, Giá bán, Giá vốn, Tồn kho ban đầu, Khối lượng (g), Cảnh báo tồn.</p>
        </div>

        <div class="flex gap-3 mt-6">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 border rounded-lg hover:bg-gray-50">Quay lại</a>
            <button type="submit" class="px-5 py-2.5 bg-coral text-white font-bold rounded-lg hover:opacity-90">Tiến hành Import</button>
        </div>
    </form>
</div>
@endsection