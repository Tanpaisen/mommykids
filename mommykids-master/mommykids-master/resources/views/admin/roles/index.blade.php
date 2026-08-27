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

@endsection
