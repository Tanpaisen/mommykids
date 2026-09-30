@extends('admin.layouts.app')

@section('content')
<div class="max-w-xl space-y-4">
    <h1 class="font-display text-2xl font-bold text-gray-800">Soạn thông báo marketing</h1>

    <form method="POST" action="{{ route('admin.notification-campaigns.store') }}" class="bg-white rounded-2xl shadow-sm p-5 space-y-4">
        @csrf
        <div>
            <label class="text-sm text-gray-600">Tiêu đề</label>
            <input name="title" value="{{ old('title') }}" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
            @error('title') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm text-gray-600">Nội dung</label>
            <textarea name="body" rows="4" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">{{ old('body') }}</textarea>
            @error('body') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm text-gray-600">Link khi bấm (tuỳ chọn, bắt đầu bằng /)</label>
            <input name="action_url" value="{{ old('action_url') }}" placeholder="/san-pham" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm">
            @error('action_url') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="text-sm space-x-4">
            <label><input type="checkbox" name="channels[]" value="in_app" checked> Trong app</label>
            <label><input type="checkbox" name="channels[]" value="mail"> Email</label>
            @error('channels') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
        <button class="bg-coral text-white rounded-xl px-4 py-2 text-sm font-semibold">Lưu bản nháp</button>
    </form>
</div>
@endsection