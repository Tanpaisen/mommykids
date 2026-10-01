@extends('client.layouts.app')

@section('sidebar')
    <div class="hidden"></div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-2xl font-bold text-gray-800">Thông báo</h1>
        <a href="{{ route('notifications.preferences') }}" class="text-sm text-gray-500 hover:text-coral flex items-center gap-1">
            ⚙️ Cài đặt
        </a>
    </div>

    @if (session('status'))
        <div class="bg-green-50 text-green-700 rounded-xl p-3 text-sm mb-4">{{ session('status') }}</div>
    @endif

    @if ($notifications->isNotEmpty())
        <form method="POST" action="{{ route('notifications.readAll') }}" class="mb-4">
            @csrf
            <button type="submit" class="text-sm text-coral font-medium hover:underline">
                Đánh dấu tất cả đã đọc
            </button>
        </form>
    @endif

    <div class="space-y-3">
        @forelse ($notifications as $n)
            @php
                $isMarketing = ($n->data['category'] ?? null) === 'marketing';
                $unread = is_null($n->read_at);
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border {{ $unread ? 'border-coral/30' : 'border-transparent' }} overflow-hidden">
                <div class="flex items-start gap-3 p-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg shrink-0
                                {{ $isMarketing ? 'bg-orange-50' : 'bg-pink-50' }}">
                        {{ $isMarketing ? '🎁' : '📦' }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                            @csrf
                            <button type="submit" class="text-left w-full">
                                <p class="text-sm {{ $unread ? 'font-semibold text-gray-800' : 'text-gray-600' }}">
                                    {{ $n->data['title'] ?? '' }}
                                </p>
                                <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">
                                    {{ $n->data['body'] ?? '' }}
                                </p>
                            </button>
                        </form>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</span>
                            <form method="POST" action="{{ route('notifications.destroy', $n->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-gray-400 hover:text-red-500">Xoá</button>
                            </form>
                        </div>
                    </div>

                    @if ($unread)
                        <span class="w-2 h-2 rounded-full bg-coral mt-2 shrink-0"></span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm p-10 text-center text-gray-400">
                <div class="text-4xl mb-2">🔔</div>
                Chưa có thông báo nào.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
@endsection