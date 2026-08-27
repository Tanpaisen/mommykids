@extends('admin.layouts.app')

@section('title', 'Thêm nhóm quyền')
@section('page_title', 'Thêm nhóm quyền mới')
@section('page_subtitle', 'Đặt tên và chọn các quyền tương ứng cho vai trò này')

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.roles.form', ['role' => null, 'permissionGroups' => $permissionGroups, 'rolePermissions' => []])

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Lưu nhóm quyền</button>
            <a href="{{ route('admin.roles.index') }}" class="btn-outline">Hủy</a>
        </div>
    </form>
@endsection
