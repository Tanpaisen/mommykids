@extends('admin.layouts.app')

@section('title', 'Thêm nhóm quyền')
@section('page_title', 'Thêm nhóm quyền mới')
@section('page_subtitle', 'Đặt tên và chọn các quyền tương ứng cho vai trò này')

@section('content')
    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-6">
        @csrf
        <fieldset @cannot('roles.manage') disabled @endcannot>
            @include('admin.roles.form', ['role' => null, 'permissionGroups' => $permissionGroups, 'rolePermissions' => []])
        </fieldset>
        
        <div class="flex gap-3">
            @can('roles.manage')
                <button type="submit" class="btn-primary">Lưu nhóm quyền</button>
            @endcan
            <a href="{{ route('admin.roles.index') }}" class="btn-outline">
                @can('roles.manage') Hủy @else Quay lại @endcan
            </a>
        </div>
    </form>
@endsection
