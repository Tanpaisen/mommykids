<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Permission groups mirror the 5 product modules exactly, so the checkbox matrix
     * in admin/roles/form.blade.php and the sidebar menu (admin/partials/sidebar.blade.php)
     * always stay in sync. Add a new permission here + a matching Gate check where needed.
     */
    public function permissionGroups(): array
    {
        $labels = [
            'dashboard' => ['icon' => '📊', 'label' => 'Dashboard & Thống kê', 'permissions' => [
                'dashboard.view' => 'Xem tổng quan doanh thu & đơn hàng',
            ]],
            'catalog' => ['icon' => '📦', 'label' => 'Kiến thức & Sản phẩm', 'permissions' => [
                'catalog.view' => 'Xem danh mục / sản phẩm / giai đoạn',
                'catalog.manage' => 'Thêm/Sửa/Xóa danh mục, thuộc tính, giai đoạn',
                'products.manage' => 'Thêm/Sửa giá, tồn kho, gắn tag sản phẩm',
            ]],
            'handbook' => ['icon' => '📚', 'label' => 'Cẩm nang & Tương tác', 'permissions' => [
                'handbook.view' => 'Xem bài viết & bình luận',
                'articles.manage' => 'Viết/Sửa bài Cẩm nang, gắn sản phẩm đính kèm',
                'comments.manage' => 'Trả lời, ẩn/xóa bình luận',
            ]],
            'orders' => ['icon' => '🚚', 'label' => 'Đơn hàng & Dòng tiền', 'permissions' => [
                'orders.view' => 'Xem danh sách đơn hàng',
                'orders.manage' => 'Cập nhật trạng thái, đóng gói, đẩy đơn GHN',
                'refunds.manage' => 'Xử lý đổi trả & hoàn tiền',
            ]],
            'crm' => ['icon' => '👥', 'label' => 'CRM & Marketing', 'permissions' => [
                'crm.view' => 'Xem hồ sơ khách hàng',
                'vouchers.manage' => 'Tạo/Sửa mã giảm giá',
                'banners.manage' => 'Thay banner trang chủ',
                'roles.manage' => 'Quản lý nhóm quyền & tài khoản quản trị',
            ]],
        ];

        return collect($labels)->map(function ($group, $key) {
            return [
                'key' => $key,
                'icon' => $group['icon'],
                'label' => $group['label'],
                'permissions' => collect($group['permissions'])->map(fn ($label, $name) => [
                    'name' => $name,
                    'label' => $label,
                ])->values(),
            ];
        })->values()->all();
    }

    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->get();
        $admins = Admin::with('roles')->latest()->get();

        return view('admin.roles.index', compact('roles', 'admins'));
    }

    public function create()
    {
        return view('admin.roles.create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'admin']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', "Đã tạo nhóm quyền “{$role->name}”.");
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Không thể chỉnh sửa nhóm quyền Super Admin.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', "Đã cập nhật nhóm quyền “{$role->name}”.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Không thể xóa nhóm quyền Super Admin.');
        }

        $role->delete();

        return back()->with('success', 'Đã xóa nhóm quyền.');
    }
}
