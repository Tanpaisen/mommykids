@extends('client.layouts.app')

@section('sidebar')
    <div class="hidden"></div>
@endsection

@section('content')
<h1>Thông báo</h1>

<form method="POST" action="{{ route('notifications.readAll') }}">
    @csrf
    <button type="submit">Đánh dấu tất cả đã đọc</button>
</form>

@forelse ($notifications as $n)
    <div style="{{ $n->read_at ? '' : 'font-weight:bold' }}">
        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
            @csrf
            <button type="submit">{{ $n->data['title'] ?? '' }}: {{ $n->data['body'] ?? '' }}</button>
        </form>
        <small>{{ $n->created_at->diffForHumans() }}</small>

        <form method="POST" action="{{ route('notifications.destroy', $n->id) }}">
            @csrf @method('DELETE')
            <button type="submit">Xoá</button>
        </form>
    </div>
@empty
    <p>Chưa có thông báo nào.</p>
@endforelse

{{ $notifications->links() }}
@endsection