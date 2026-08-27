<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    protected function permissionGroups(): array
    {
        // Copy nguyên từ RoleController::permissionGroups()
        return app(RoleController::class)->permissionGroups();
    }

    public function index()
    {
        $roles = Role::where('guard_name', 'admin')->get();
        $first = $roles->first();

        return redirect()->route('admin.permissions.edit', $first->id);
    }

    public function edit(Role $nhomQuyen)
    {
        $roles = Role::where('guard_name', 'admin')->get();

        return view('admin.permissions.edit', [
            'roles' => $roles,
            'selectedRole' => $nhomQuyen,
            'permissionGroups' => app(RoleController::class)->permissionGroups(),
            'rolePermissions' => $nhomQuyen->permissions->pluck('name')->toArray(),
        ]);
    }

    public function update(Role $nhomQuyen, \Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'permissions'   => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $nhomQuyen->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', "Đã cập nhật phân quyền cho '{$nhomQuyen->name}'.");
    }
}