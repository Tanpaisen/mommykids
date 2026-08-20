{{--
    Shared by admin/roles/create.blade.php and admin/roles/edit.blade.php.
    Expects: $role (nullable, for edit), $permissionGroups (see RoleController), $rolePermissions (array of permission names already granted, for edit)
--}}
@php
    $rolePermissions = $rolePermissions ?? [];
    $isSuperAdmin = isset($role) && $role->name === 'Super Admin';
@endphp

<div class="card p-5 lg:p-6 space-y-6">

    <div>
        <label class="block text-sm font-semibold text-ink mb-1.5">Tên nhóm quyền</label>
        <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" required
               {{ $isSuperAdmin ? 'readonly' : '' }}
               placeholder="VD: Biên tập viên, Kế toán, Nhân viên CSKH..."
               class="w-full max-w-md h-11 px-4 rounded-xl border border-admin-border focus:border-coral outline-none text-sm {{ $isSuperAdmin ? 'bg-admin-bg text-ink-soft' : '' }}">
        @error('name') <p class="text-xs text-coral mt-1">{{ $message }}</p> @enderror
    </div>

    @if ($isSuperAdmin)
        <p class="text-sm text-gold bg-gold-light rounded-xl px-4 py-3">
            Super Admin luôn có toàn bộ quyền và không thể chỉnh sửa ma trận bên dưới.
        </p>
    @endif

    <div>
        <div class="flex items-center justify-between mb-3">
            <label class="block text-sm font-semibold text-ink">Ma trận phân quyền theo module</label>
            @unless ($isSuperAdmin)
                <button type="button" onclick="mkToggleAllPermissions(true)" class="text-xs text-coral font-semibold hover:underline">Chọn tất cả</button>
            @endunless
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($permissionGroups as $group)
                <div class="border border-admin-border rounded-xl overflow-hidden">
                    <label class="flex items-center gap-2 bg-admin-bg px-4 py-2.5 cursor-pointer">
                        <input type="checkbox"
                               class="mk-module-toggle w-4 h-4 accent-coral"
                               data-module="{{ $group['key'] }}"
                               onchange="mkToggleModule('{{ $group['key'] }}', this.checked)"
                               {{ $isSuperAdmin ? 'disabled checked' : '' }}>
                        <span class="text-sm font-semibold text-ink">{{ $group['icon'] }} {{ $group['label'] }}</span>
                    </label>

                    <div class="p-4 space-y-2">
                        @foreach ($group['permissions'] as $permission)
                            <label class="flex items-center gap-2 text-sm text-ink-soft cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="{{ $permission['name'] }}"
                                       class="mk-permission-checkbox w-4 h-4 accent-coral"
                                       data-module="{{ $group['key'] }}"
                                       {{ $isSuperAdmin ? 'disabled checked' : '' }}
                                       @checked(in_array($permission['name'], $rolePermissions))>
                                {{ $permission['label'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function mkToggleModule(moduleKey, checked) {
        document.querySelectorAll(`.mk-permission-checkbox[data-module="${moduleKey}"]`)
            .forEach(cb => cb.checked = checked);
    }
    function mkToggleAllPermissions(checked) {
        document.querySelectorAll('.mk-permission-checkbox, .mk-module-toggle').forEach(cb => cb.checked = checked);
    }
    // Keep each module's "select all" checkbox in sync when individual items change.
    document.addEventListener('change', (e) => {
        if (!e.target.classList.contains('mk-permission-checkbox')) return;
        const moduleKey = e.target.dataset.module;
        const items = document.querySelectorAll(`.mk-permission-checkbox[data-module="${moduleKey}"]`);
        const allChecked = [...items].every(cb => cb.checked);
        document.querySelector(`.mk-module-toggle[data-module="${moduleKey}"]`).checked = allChecked;
    });
</script>
