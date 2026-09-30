@extends('client.layouts.app')

@section('sidebar')
    <div class="hidden"></div>
@endsection

@section('content')
<h1>Cài đặt thông báo</h1>

@if (session('status'))
    <p>{{ session('status') }}</p>
@endif

<form method="POST" action="{{ route('notifications.preferences.update') }}">
    @csrf
    @method('PUT')

    <h3>Đơn hàng</h3>
    <p>Thông báo trong tài khoản: luôn bật</p>
    <label>
        <input type="checkbox" name="order_mail" value="1"
               @checked($prefs->get('order_status')?->mail ?? true)>
        Gửi email khi trạng thái đơn thay đổi
    </label>

    <h3>Khuyến mãi</h3>
    <label>
        <input type="checkbox" name="marketing_database" value="1"
               @checked($prefs->get('marketing')?->database ?? true)>
        Thông báo trong tài khoản
    </label>
    <label>
        <input type="checkbox" name="marketing_mail" value="1"
               @checked($prefs->get('marketing')?->mail ?? false)>
        Gửi email
    </label>

    <button type="submit">Lưu</button>
</form>
@endsection