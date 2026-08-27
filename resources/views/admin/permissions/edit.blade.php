@extends('admin.layouts.app')

@section('page_title', 'Phân quyền')
@section('page_subtitle', 'Chọn nhóm quyền và tick các quyền tương ứng')

@section('content')

    {{-- Chọn role bằng id --}}
    <div class="card p-4 mb-4">
        <form method="GET">
            <label class="text-sm font-semibold text-ink mr-3">Nhóm quyền:</label>
            <select onchange="window.location='/admin/phan-quyen/'+this.value"
                    class="border border-admin-border rounded-xl px-4 py-2 text-sm focus:border-coral outline-none">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                            @selected($selectedRole->id === $role->id)>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <form action="{{ route('admin.permissions.update', $selectedRole->id) }}" method="POST">
        @csrf @method('PUT')

        @include('admin.roles.form', [
            'role' => null,
            'permissionGroups' => $permissionGroups,
            'rolePermissions' => $rolePermissions,
            'hideNameField' => true,
        ])

        <div class="flex gap-3 mt-4">
            <button type="submit" class="btn-primary">Lưu phân quyền</button>
        </div>
    </form>

@endsection