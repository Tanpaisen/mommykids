@extends('client.layouts.app')

@section('sidebar')
    <div class="hidden"></div>
@endsection

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <div class="flex items-center gap-2 mb-6">
        <a href="{{ route('notifications.index') }}" class="text-gray-400 hover:text-coral">←</a>
        <h1 class="font-display text-2xl font-bold text-gray-800">Cài đặt thông báo</h1>
    </div>

    @if (session('status'))
        <div class="bg-green-50 text-green-700 rounded-xl p-3 text-sm mb-4">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('notifications.preferences.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xl">📦</span>
                <h2 class="font-semibold text-gray-800">Đơn hàng</h2>
            </div>

            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-600">Thông báo trong tài khoản</span>
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-lg">Luôn bật</span>
            </div>

            <label class="flex items-center justify-between py-2 cursor-pointer">
                <span class="text-sm text-gray-600">Gửi email khi trạng thái đơn thay đổi</span>
                <input type="checkbox" name="order_mail" value="1"
                       class="w-5 h-5 accent-coral"
                       @checked($prefs->get('order_status')?->mail ?? true)>
            </label>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xl">🎁</span>
                <h2 class="font-semibold text-gray-800">Khuyến mãi</h2>
            </div>

            <label class="flex items-center justify-between py-2 border-b border-gray-100 cursor-pointer">
                <span class="text-sm text-gray-600">Thông báo trong tài khoản</span>
                <input type="checkbox" name="marketing_database" value="1"
                       class="w-5 h-5 accent-coral"
                       @checked($prefs->get('marketing')?->database ?? true)>
            </label>

            <label class="flex items-center justify-between py-2 cursor-pointer">
                <span class="text-sm text-gray-600">Gửi email</span>
                <input type="checkbox" name="marketing_mail" value="1"
                       class="w-5 h-5 accent-coral"
                       @checked($prefs->get('marketing')?->mail ?? false)>
            </label>
        </div>

        <button type="submit" class="w-full bg-coral text-white rounded-xl py-3 text-sm font-semibold hover:opacity-90">
            Lưu cài đặt
        </button>
    </form>
</div>
@endsection