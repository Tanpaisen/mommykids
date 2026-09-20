@extends('admin.layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4 text-gray-800">Quản lý Cấu trúc Cẩm nang (Dạng Sách)</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Form Thêm Chương / Mục -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h2 class="font-bold text-lg mb-4 text-gray-800">Thêm Chương / Mục mới</h2>
            <form action="{{ route('admin.handbook-categories.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Tên Chương / Mục</label>
                    <input type="text" name="name" class="w-full border rounded-lg p-2 text-sm focus:outline-none focus:border-[#FF2A54]" placeholder="VD: Chương 1: Mang thai" required>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Thuộc Chương (Để trống nếu là Chương lớn)</label>
                    <select name="parent_id" class="w-full border rounded-lg p-2 text-sm focus:outline-none focus:border-[#FF2A54]">
                        <option value="">-- Là Chương lớn (Cấp 1) --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Thứ tự sắp xếp</label>
                    <input type="number" name="sort_order" value="0" class="w-full border rounded-lg p-2 text-sm focus:outline-none focus:border-[#FF2A54]">
                </div>

                <button type="submit" class="w-full bg-[#FF2A54] hover:bg-[#e02047] text-white py-2 rounded-lg font-semibold text-sm transition-colors">Tạo ngay</button>
            </form>
        </div>

        <!-- Cây Cấu Trúc Hiện Tại -->
        <div class="md:col-span-2 bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <h2 class="font-bold text-lg mb-4 text-gray-800">Mục lục Cẩm nang hiện tại</h2>

            @if(session('success'))
                <div class="p-3 mb-4 text-sm text-green-700 bg-green-50 rounded-xl border border-green-200">
                    {{ session('success') }}
                </div>
            @endif
            
            <div class="space-y-4">
                @forelse($categories as $chapter)
                    <div class="border rounded-xl p-4 bg-gray-50">
                        <div class="flex items-center justify-between font-bold text-base text-gray-800">
                            <span>📖 {{ $chapter->name }}</span>
                            <form action="{{ route('admin.handbook-categories.destroy', $chapter->id) }}" method="POST" onsubmit="return confirm('Xóa chương này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 text-xs hover:underline">Xóa</button>
                            </form>
                        </div>

                        <!-- Các Mục Con -->
                        <div class="ml-6 mt-2 space-y-2 border-l-2 border-gray-200 pl-4">
                            @forelse($chapter->children as $sub)
                                <div class="flex items-center justify-between text-sm py-1">
                                    <span>📁 {{ $sub->name }}</span>
                                    <form action="{{ route('admin.handbook-categories.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Xóa mục này?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 text-xs hover:underline">Xóa</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 italic">Chưa có mục con nào trong chương này.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">Chưa có Chương nào được tạo.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection