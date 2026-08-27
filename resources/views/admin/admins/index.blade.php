@extends('admin.layouts.app')

@section('page_title', 'Tài khoản quản trị')
@section('page_subtitle', 'Quản lý nhân viên và gán nhóm quyền')

@section('page_actions')
    <a href="{{ route('admin.admins.create') }}" class="btn-primary">+ Thêm nhân viên</a>
@endsection

@section('content')
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-admin-bg text-ink-soft text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Nhân viên</th>
                    <th class="text-left px-5 py-3 font-semibold">Nhóm quyền</th>
                    <th class="text-left px-5 py-3 font-semibold">Ngày tạo</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-admin-border">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-admin-bg/60">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-full bg-coral-light text-coral font-bold flex items-center justify-center text-sm">
                                    {{ strtoupper(substr($admin->name, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-ink">{{ $admin->name }}</p>
                                    <p class="text-xs text-ink-soft">{{ $admin->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <form action="{{ route('admin.admins.updateRole', $admin) }}" method="POST">
                                @csrf @method('PATCH')
                                <select name="role" onchange="this.form.submit()"
                                        class="text-sm border border-admin-border rounded-pill px-3 py-1.5 focus:border-coral outline-none">
                                    <option value="">— Chưa gán —</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                                @selected($admin->roles->pluck('name')->contains($role->name))>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-5 py-3.5 text-ink-soft">
                            {{ $admin->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            @if ($admin->id !== auth('admin')->id())
                                <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST"
                                      onsubmit="return confirm('Xóa tài khoản {{ $admin->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-ink-soft hover:text-coral text-sm">Xóa</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-ink-soft">Chưa có tài khoản nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection