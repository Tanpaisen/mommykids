<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'catalog.view', 'catalog.manage', 'products.manage',
            'handbook.view', 'articles.manage', 'comments.manage',
            'orders.view', 'orders.manage', 'refunds.manage',
            'crm.view', 'vouchers.manage', 'banners.manage', 'roles.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }

        // Super Admin — toàn quyền, không thể sửa/xóa (RoleController chặn tường minh).
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdmin->syncPermissions($permissions);

        // Biên tập viên — chỉ Module 3 (Cẩm nang & Tương tác), đúng ví dụ trong yêu cầu ban đầu.
        $editor = Role::firstOrCreate(['name' => 'Biên tập viên', 'guard_name' => 'admin']);
        $editor->syncPermissions(['dashboard.view', 'handbook.view', 'articles.manage', 'comments.manage']);

        // Kế toán — chỉ Module 4 (Đơn hàng & Dòng tiền).
        $accountant = Role::firstOrCreate(['name' => 'Kế toán', 'guard_name' => 'admin']);
        $accountant->syncPermissions(['dashboard.view', 'orders.view', 'orders.manage', 'refunds.manage']);

        // Tài khoản Super Admin đầu tiên để đăng nhập thử.
        $owner = Admin::firstOrCreate(
            ['email' => 'admin@mommykids.vn'],
            ['name' => 'Chủ shop', 'password' => Hash::make('password')]
        );
        $owner->syncRoles(['Super Admin']);
    }
}
