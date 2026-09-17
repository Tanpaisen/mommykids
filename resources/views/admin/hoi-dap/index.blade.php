@extends('admin.layouts.app')

@section('content')
<div class="p-4 space-y-4">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
        <div>
            <h1 class="text-lg font-bold text-gray-800">Trung tâm Hỏi đáp (FAQs)</h1>
            <p class="text-xs text-gray-500">Quản lý câu hỏi thường gặp của khách hàng MommyKids</p>
        </div>
        <button onclick="document.getElementById('addFaqModal').classList.remove('hidden')" class="px-4 py-2 bg-[#FF2A54] text-white rounded-lg text-sm font-semibold hover:bg-pink-600 transition">
            + Thêm câu hỏi
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase">
                    <th class="p-3">Câu hỏi</th>
                    <th class="p-3">Danh mục</th>
                    <th class="p-3">Thứ tự</th>
                    <th class="p-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                @forelse($faqs as $faq)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 font-semibold text-gray-800">{{ $faq->question }}</td>
                    <td class="p-3"><span class="px-2 py-1 bg-pink-50 text-[#FF2A54] rounded-md text-xs font-semibold">{{ $faq->category }}</span></td>
                    <td class="p-3">{{ $faq->sort_order }}</td>
                    <td class="p-3 text-right">
                        <form action="{{ route('admin.hoi-dap.destroy', $faq->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Xóa câu hỏi này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold">Xóa</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-400">Chưa có câu hỏi nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm Câu Hỏi -->
<div id="addFaqModal" class="fixed inset-0 bg-black/40 hidden flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl max-w-lg w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-2">
            <h3 class="font-bold text-gray-800">Thêm câu hỏi mới</h3>
            <button onclick="document.getElementById('addFaqModal').classList.add('hidden')" class="text-gray-400 text-xl">&times;</button>
        </div>
        <form action="{{ route('admin.hoi-dap.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Câu hỏi</label>
                <input type="text" name="question" required class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-[#FF2A54]">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Câu trả lời</label>
                <textarea name="answer" rows="3" required class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-[#FF2A54]"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('addFaqModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-semibold">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-[#FF2A54] text-white rounded-lg text-sm font-semibold">Lưu câu hỏi</button>
            </div>
        </form>
    </div>
</div>
@endsection