@extends('admin.layouts.app')

@section('title', 'Nhóm quyền')
@section('page_title', 'Nhóm quyền & Phân quyền')
@section('page_subtitle', 'Quản lý vai trò quản trị viên và giới hạn quyền truy cập theo từng module')

@section('page_actions')
    <a href="{{ route('admin.roles.create') }}" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Thêm nhóm quyền
    </a>
@endsection

@section('content')

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-admin-bg text-ink-soft text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold">Nhóm quyền</th>
                    <th class="text-left px-5 py-3 font-semibold">Số quyền được cấp</th>
                    <th class="text-left px-5 py-3 font-semibold">Số tài khoản</th>
                    <th class="text-left px-5 py-3 font-semibold">Cập nhật</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-admin-border">
                @forelse ($roles as $role)
                    <tr class="hover:bg-admin-bg/60">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-ink">{{ $role->name }}</p>
                            @if ($role->name === 'Super Admin')
                                <span class="badge-discount bg-ink mt-1 inline-block">Toàn quyền</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-ink-soft">{{ $role->permissions_count }} quyền</td>
                        <td class="px-5 py-3.5 text-ink-soft">{{ $role->users_count }} người</td>
                        <td class="px-5 py-3.5 text-ink-soft">{{ $role->updated_at?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="text-coral font-semibold hover:underline">Sửa quyền</a>
                            @if ($role->name !== 'Super Admin')
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Xóa nhóm quyền {{ $role->name }}? Các tài khoản đang gán nhóm này sẽ mất toàn bộ quyền tương ứng.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ml-3 text-ink-soft hover:text-coral">Xóa</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-ink-soft">Chưa có nhóm quyền nào. Chạy <code class="bg-admin-bg px-1.5 py-0.5 rounded">php artisan db:seed --class=PermissionSeeder</code>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ ADMIN ACCOUNTS + ROLE ASSIGNMENT ============ --}}
    <div class="card p-5 lg:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-bold text-lg text-ink">Tài khoản quản trị</h2>
            <a href="{{ route('admin.admins.create') ?? '#' }}" class="btn-outline text-sm px-4 py-2">+ Thêm nhân viên</a>
        </div>

        <div class="divide-y divide-admin-border">
            @forelse ($admins as $admin)
                <div class="flex items-center gap-3 py-3">
                    <span class="w-9 h-9 rounded-full bg-coral-light text-coral font-display font-bold flex items-center justify-center text-sm">
                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-ink">{{ $admin->name }}</p>
                        <p class="text-xs text-ink-soft truncate">{{ $admin->email }}</p>
                    </div>

                    {{-- Inline role reassignment --}}
                    <form action="{{ route('admin.admins.updateRole', $admin) ?? '#' }}" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="role" onchange="this.form.submit()"
                                class="text-sm border border-admin-border rounded-pill px-3 py-1.5 focus:border-coral outline-none">
                            <option value="">— Chưa gán —</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected($admin->roles->pluck('name')->contains($role->name))>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @empty
                <p class="text-ink-soft text-sm py-6 text-center">Chưa có tài khoản quản trị nào khác ngoài bạn.</p>
            @endforelse
        </div>
    </div>

@endsection
