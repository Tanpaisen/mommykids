@extends('admin.layouts.app')

@section('title', 'Thêm nhân viên')
@section('page_title', 'Thêm tài khoản quản trị')
@section('page_subtitle', 'Tạo tài khoản cho nhân viên và gán ngay nhóm quyền phù hợp')

@section('content')
    <form action="{{ route('admin.admins.store') }}" method="POST" class="card p-6 max-w-lg space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Họ tên</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full h-11 px-4 rounded-xl border border-admin-border focus:border-coral outline-none text-sm">
            @error('name') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full h-11 px-4 rounded-xl border border-admin-border focus:border-coral outline-none text-sm">
            @error('email') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Mật khẩu tạm thời</label>
            <input type="password" name="password" required class="w-full h-11 px-4 rounded-xl border border-admin-border focus:border-coral outline-none text-sm">
            @error('password') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-ink mb-1.5">Nhóm quyền</label>
            <select name="role" class="w-full h-11 px-4 rounded-xl border border-admin-border focus:border-coral outline-none text-sm">
                <option value="">— Chưa gán —</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary">Tạo tài khoản</button>
            <a href="{{ route('admin.roles.index') }}" class="btn-outline">Hủy</a>
        </div>
    </form>
@endsection
