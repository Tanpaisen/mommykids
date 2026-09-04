@extends('admin.layouts.app')

@section('title', 'Tài khoản quản trị')

@section('page_header', true)
@section('page_title', 'Tài khoản quản trị')
@section('page_subtitle', 'Quản lý danh sách nhân viên và phân quyền hệ thống')

@section('page_actions')
    <a href="{{ route('admin.admins.create') }}" 
       class="inline-flex items-center gap-2 bg-coral hover:bg-coral-dark text-white font-semibold text-sm px-4 py-2 rounded-pill shadow-sm transition-all cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        <span>Thêm tài khoản</span>
    </a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-admin-border shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-admin-border bg-slate-50/50 text-xs font-bold text-ink-soft uppercase tracking-wider">
                    <th class="px-6 py-4">Nhân viên</th>
                    <th class="px-6 py-4">Nhóm quyền</th>
                    <th class="px-6 py-4">Ngày tạo</th>
                    <th class="px-6 py-4 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-admin-border text-sm">
                @forelse ($admins as $admin)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-full bg-coral-light text-coral font-display font-bold flex items-center justify-center text-sm">
                                    {{ strtoupper(substr($admin->name ?? 'A', 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-ink leading-tight">{{ $admin->name }}</p>
                                    <p class="text-xs text-ink-soft">{{ $admin->email }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <form action="{{ route('admin.admins.updateRole', $admin->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PATCH')
                                <select name="role" onchange="this.form.submit()"
                                        class="text-xs border border-admin-border rounded-pill px-3 py-1.5 focus:border-coral outline-none bg-white font-medium cursor-pointer">
                                    <option value="">— Chưa gán —</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}"
                                            @selected(
                                                optional($admin->roles)->pluck('name')?->contains($role->name) 
                                                || ($admin->role ?? null) == $role->name 
                                                || ($admin->role_id ?? null) == $role->id
                                            )>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>

                        <td class="px-6 py-4 text-ink-soft">
                            {{ $admin->created_at ? $admin->created_at->format('d/m/Y') : '—' }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            @if (auth()->id() !== $admin->id)
                                <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản {{ $admin->name }} không?')" 
                                      class="inline-block m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-coral hover:text-white bg-coral-light hover:bg-coral px-3 py-1.5 rounded-pill transition-all cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>Xóa</span>
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400 italic">Đang đăng nhập</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-ink-soft">Chưa có tài khoản quản trị nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection