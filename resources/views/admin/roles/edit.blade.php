@extends('admin.layouts.app')

@section('title', 'Sửa nhóm quyền')
@section('page_title', 'Sửa nhóm quyền: ' . $role->name)
@section('page_subtitle', 'Điều chỉnh tên và ma trận quyền — thay đổi áp dụng ngay cho mọi tài khoản thuộc nhóm này')

@section('content')
    @cannot('roles.manage')
        <div class="card p-4 mb-5 bg-gray-100 text-gray-600 text-sm">
            🔒 Chế độ chỉ xem: Bạn không có quyền <strong>roles.manage</strong> nên không thể chỉnh sửa nhóm quyền này.
        </div>
    @endcannot
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <fieldset @cannot('roles.manage') disabled @endcannot>
            @include('admin.roles.form', [
                'role' => $role,
                'permissionGroups' => $permissionGroups,
                'rolePermissions' => $role->permissions->pluck('name')->toArray(),
            ])
        </fieldset>

        <div class="flex gap-3">
            @can('roles.manage')
                <button type="submit" class="btn-primary">Cập nhật</button>
            @endcan
            <a href="{{ route('admin.roles.index') }}" class="btn-outline">
                @can('roles.manage') Hủy @else Quay lại @endcan
            </a>
        </div>
    </form>
@endsection
