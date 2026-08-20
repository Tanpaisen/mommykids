@extends('admin.layouts.app')

@section('title', 'Sửa nhóm quyền')
@section('page_title', 'Sửa nhóm quyền: ' . $role->name)
@section('page_subtitle', 'Điều chỉnh tên và ma trận quyền — thay đổi áp dụng ngay cho mọi tài khoản thuộc nhóm này')

@section('content')
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.roles.form', [
            'role' => $role,
            'permissionGroups' => $permissionGroups,
            'rolePermissions' => $role->permissions->pluck('name')->toArray(),
        ])

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Cập nhật</button>
            <a href="{{ route('admin.roles.index') }}" class="btn-outline">Hủy</a>
        </div>
    </form>
@endsection
