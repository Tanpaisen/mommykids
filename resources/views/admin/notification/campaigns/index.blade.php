@extends('admin.layouts.app')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="font-display text-2xl font-bold text-gray-800">Thông báo marketing</h1>
        <a href="{{ route('admin.notification-campaigns.create') }}" class="bg-coral text-white rounded-xl px-4 py-2 text-sm font-semibold">+ Soạn mới</a>
    </div>

    @if (session('success')) <div class="bg-green-50 text-green-700 rounded-xl p-3 text-sm">{{ session('success') }}</div> @endif
    @if (session('error'))   <div class="bg-red-50 text-red-700 rounded-xl p-3 text-sm">{{ session('error') }}</div> @endif

    <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                <tr><th class="px-4 py-3">Tiêu đề</th><th class="px-4 py-3">Kênh</th><th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Người nhận</th><th class="px-4 py-3">Gửi / Lỗi</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            @forelse ($campaigns as $c)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $c->title }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ collect([$c->send_in_app ? 'Trong app' : null, $c->send_mail ? 'Email' : null])->filter()->join(' + ') }}</td>
                    <td class="px-4 py-3">{{ $c->status }}</td>
                    <td class="px-4 py-3">{{ $c->recipients_count }}</td>
                    <td class="px-4 py-3"><span class="text-green-600">{{ $c->sent_count }}</span> / <span class="text-red-500">{{ $c->failed_count }}</span></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if ($c->status === 'draft')
                            <form method="POST" action="{{ route('admin.notification-campaigns.test', $c) }}" class="inline">@csrf
                                <button class="text-blue-600 text-xs mr-2">Gửi thử cho tôi</button></form>
                            <form method="POST" action="{{ route('admin.notification-campaigns.send', $c) }}" class="inline"
                                onsubmit="return confirm('Gửi cho TẤT CẢ người đã bật nhận khuyến mãi?')">@csrf
                                <button class="bg-coral text-white rounded-lg px-3 py-1 text-xs font-semibold">Gửi tất cả</button></form>
                        @elseif ($c->status === 'sending')
                            <span class="text-xs text-amber-600">Đang gửi...</span>
                            <form method="POST" action="{{ route('admin.notification-campaigns.reset', $c) }}" class="inline"
                                onsubmit="return confirm('Đặt lại về bản nháp? Chỉ làm việc này nếu chiến dịch bị treo quá lâu.')">@csrf
                                <button class="text-gray-500 text-xs ml-2 underline">Đặt lại</button></form>
                        @elseif ($c->status === 'done')
                            <form method="POST" action="{{ route('admin.notification-campaigns.duplicate', $c) }}" class="inline">@csrf
                                <button class="text-blue-600 text-xs">Nhân bản để gửi lại</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Chưa có chiến dịch nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $campaigns->links() }}
</div>
@endsection