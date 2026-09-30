@extends('admin.layouts.app')

@section('content')
<div class="space-y-5">
    <h1 class="font-display text-2xl font-bold text-gray-800">Log thông báo</h1>

    {{-- Thẻ thống kê 24h --}}
    <div class="grid grid-cols-3 gap-4 max-w-xl">
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Đã gửi (24h)</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['sent'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Thất bại (24h)</div>
            <div class="text-2xl font-bold text-red-500">{{ $stats['failed'] ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm">
            <div class="text-xs text-gray-500">Đang chờ</div>
            <div class="text-2xl font-bold text-gray-500">{{ $stats['pending'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <form method="GET" class="flex flex-wrap items-center gap-2 bg-white rounded-2xl p-3 shadow-sm">
        <select name="channel" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
            <option value="">Mọi kênh</option>
            @foreach (['database' => 'Trong app', 'mail' => 'Email'] as $k => $label)
                <option value="{{ $k }}" @selected(($filters['channel'] ?? '') === $k)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="status" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
            <option value="">Mọi trạng thái</option>
            @foreach (['sent' => 'Đã gửi', 'failed' => 'Thất bại', 'pending' => 'Đang chờ'] as $k => $label)
                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $label }}</option>
            @endforeach
        </select>

        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Tên hoặc email người nhận"
               class="rounded-xl border border-gray-200 px-3 py-2 text-sm w-60">
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">

        <button type="submit" class="bg-coral text-white rounded-xl px-4 py-2 text-sm font-semibold">Lọc</button>
        <a href="{{ url()->current() }}" class="text-sm text-gray-500 hover:text-gray-800 px-2">Xoá lọc</a>
    </form>

    {{-- Bảng --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-4 py-3">Thời gian</th>
                    <th class="px-4 py-3">Người nhận</th>
                    <th class="px-4 py-3">Kênh</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Lỗi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3">
                            @if ($log->user)
                                <div class="font-medium text-gray-800">{{ $log->user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                            @else
                                <span class="text-gray-400">Khách vãng lai</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $log->channel === 'mail' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                {{ $log->channel === 'mail' ? 'Email' : 'Trong app' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold
                                {{ $log->status === 'sent' ? 'bg-green-50 text-green-600' : ($log->status === 'failed' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" title="{{ $log->error_message }}">
                            {{ $log->error_message }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Không có log nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
</div>
@endsection