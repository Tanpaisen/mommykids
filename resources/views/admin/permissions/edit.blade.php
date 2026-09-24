@extends('admin.layouts.app')

@section('title', 'Phân quyền — ' . $selectedRole->name)
@section('page_title', 'Phân quyền')
@section('page_subtitle', 'Tick quyền cho từng nhóm — thay đổi áp dụng ngay cho mọi tài khoản thuộc nhóm này')

@section('content')

    {{-- Chọn role bằng id --}}
    <div class="card p-4 mb-4 flex items-center gap-4">
        <label class="text-sm font-semibold text-ink shrink-0">Nhóm quyền:</label>
        <select onchange="window.location='/admin/phan-quyen/'+this.value"
                class="border border-admin-border rounded-xl px-4 py-2 text-sm focus:border-coral outline-none">
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected($selectedRole->id === $role->id)>
                    {{ $role->name }}
                    ({{ $role->permissions_count ?? $role->permissions->count() }} quyền)
                </option>
            @endforeach
        </select>

        {{-- Badge tên role đang chọn --}}
        <span class="ml-auto px-3 py-1 rounded-pill bg-coral-light text-coral text-sm font-semibold">
            {{ $selectedRole->name }}
        </span>
    </div>


    <form action="{{ route('admin.permissions.update', $selectedRole->id) }}" method="POST">
        @csrf @method('PUT')

        @php $isSuperAdmin = $selectedRole->name === 'Super Admin'; @endphp

        @if($isSuperAdmin)
            <div class="card p-4 mb-4 bg-gold-light text-gold-dark text-sm">
                ⭐ Super Admin luôn có toàn bộ quyền và không thể chỉnh sửa.
            </div>
        @endif

        @cannot('roles.manage')
            <div class="card p-4 mb-4 bg-gray-100 text-gray-600 text-sm">
                🔒 Chế độ chỉ xem: Bạn không có quyền <strong>roles.manage</strong> nên không thể thay đổi phân quyền.
            </div>
        @endcannot

        <div class="card p-5 lg:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-display font-bold text-ink">Ma trận phân quyền</h2>
                @can('roles.manage')
                    @unless($isSuperAdmin)
                        <button type="button" onclick="mkToggleAll(true)"
                                class="text-xs text-coral font-semibold hover:underline">
                            Chọn tất cả
                        </button>
                    @endunless
                @endcan
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($permissionGroups as $group)
                    <div class="border border-admin-border rounded-xl overflow-hidden">

                        {{-- Header nhóm --}}
                        <label class="flex items-center gap-2 bg-admin-bg px-4 py-2.5 cursor-pointer">
                            <input type="checkbox"
                                   class="mk-module-toggle w-4 h-4 accent-coral"
                                   data-module="{{ $group['key'] }}"
                                   onchange="mkToggleModule('{{ $group['key'] }}', this.checked)"
                                   {{ $isSuperAdmin ? 'disabled checked' : '' }}>
                                   @cannot('roles.manage') disabled @endcannot>
                            <span class="text-sm font-semibold text-ink">
                                {{ $group['icon'] }} {{ $group['label'] }}
                            </span>
                        </label>

                        {{-- Danh sách permission --}}
                        <div class="p-4 space-y-2.5">
                            @foreach($group['permissions'] as $permission)
                                <label class="flex items-start gap-2 text-sm text-ink-soft cursor-pointer hover:text-ink">
                                    <input type="checkbox"
                                           name="permissions[]"
                                           value="{{ $permission['name'] }}"
                                           class="mk-perm-checkbox w-4 h-4 accent-coral mt-0.5 shrink-0"
                                           data-module="{{ $group['key'] }}"
                                           {{ $isSuperAdmin ? 'disabled checked' : '' }}
                                           @checked(in_array($permission['name'], $rolePermissions))>
                                           @cannot('roles.manage') disabled @endcannot>
                                    <div>
                                        <span>{{ $permission['label'] }}</span>
                                        <code class="block text-[10px] text-ink-soft/60 mt-0.5">{{ $permission['name'] }}</code>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @unless($isSuperAdmin)
            <div class="flex gap-3 mt-4">
                @can('roles.manage')
                    <button type="submit" class="btn-primary">Lưu phân quyền</button>
                @endcan
                <a href="{{ route('admin.roles.index') }}" class="btn-outline">Quay lại</a>
            </div>
        @endunless
    </form>

@endsection

@push('scripts')
<script>
function mkToggleModule(moduleKey, checked) {
    document.querySelectorAll(`.mk-perm-checkbox[data-module="${moduleKey}"]`)
        .forEach(cb => cb.checked = checked);
}

function mkToggleAll(checked) {
    document.querySelectorAll('.mk-perm-checkbox, .mk-module-toggle')
        .forEach(cb => cb.checked = checked);
}

// Sync module toggle khi tick từng permission
document.addEventListener('change', e => {
    if (!e.target.classList.contains('mk-perm-checkbox')) return;
    const mod = e.target.dataset.module;
    const items = document.querySelectorAll(`.mk-perm-checkbox[data-module="${mod}"]`);
    document.querySelector(`.mk-module-toggle[data-module="${mod}"]`).checked =
        [...items].every(cb => cb.checked);
});

// Khởi tạo trạng thái module toggle khi load trang
document.querySelectorAll('.mk-module-toggle').forEach(toggle => {
    const mod = toggle.dataset.module;
    const items = document.querySelectorAll(`.mk-perm-checkbox[data-module="${mod}"]`);
    toggle.checked = [...items].every(cb => cb.checked);
});
</script>
@endpush