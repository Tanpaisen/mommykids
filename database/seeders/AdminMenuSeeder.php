<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminMenu;

class AdminMenuSeeder extends Seeder
{
    public function run(): void
    {
        AdminMenu::truncate();

        $menus = [
            // Group 1
            ['group_name' => 'DASHBOARD & THỐNG KÊ', 'title' => 'Tổng quan', 'route_name' => 'admin.dashboard', 'order' => 1],

            // Group 2
            ['group_name' => 'KIẾN THỨC & SẢN PHẨM', 'title' => 'Giai đoạn của bé', 'route_name' => 'admin.stages.index', 'order' => 2],
            ['group_name' => 'KIẾN THỨC & SẢN PHẨM', 'title' => 'Danh mục & Thuộc tính', 'route_name' => 'admin.categories.index', 'order' => 3],
            ['group_name' => 'KIẾN THỨC & SẢN PHẨM', 'title' => 'Sản phẩm', 'route_name' => 'admin.products.index', 'order' => 4],

            // Group 3
            ['group_name' => 'CẨM NANG & TƯƠNG TÁC', 'title' => 'Bài viết Cẩm nang', 'route_name' => 'admin.blogs.index', 'order' => 5],
            ['group_name' => 'CẨM NANG & TƯƠNG TÁC', 'title' => 'Trung tâm Hỏi đáp', 'route_name' => 'admin.faqs.index', 'order' => 6],

            // Group 4
            ['group_name' => 'ĐƠN HÀNG & DÒNG TIỀN', 'title' => 'Đơn hàng', 'route_name' => 'admin.orders.index', 'order' => 7],
            ['group_name' => 'ĐƠN HÀNG & DÒNG TIỀN', 'title' => 'Vận chuyển (GHN)', 'route_name' => 'admin.shipping.index', 'order' => 8],
            ['group_name' => 'ĐƠN HÀNG & DÒNG TIỀN', 'title' => 'Đổi trả & Hoàn tiền', 'route_name' => 'admin.refunds.index', 'order' => 9],

            // Group 5
            ['group_name' => 'CRM & MARKETING', 'title' => 'Khách hàng', 'route_name' => 'admin.customers.index', 'order' => 10],
            ['group_name' => 'CRM & MARKETING', 'title' => 'Voucher', 'route_name' => 'admin.vouchers.index', 'order' => 11],
            ['group_name' => 'CRM & MARKETING', 'title' => 'Cài đặt chung', 'route_name' => 'admin.settings.index', 'order' => 12],

            // Group 6
            ['group_name' => 'HỆ THỐNG', 'title' => 'Tài khoản quản trị', 'route_name' => 'admin.admins.index', 'order' => 13],
            ['group_name' => 'HỆ THỐNG', 'title' => 'Nhóm quyền', 'route_name' => 'admin.roles.index', 'order' => 14],
            ['group_name' => 'HỆ THỐNG', 'title' => 'Phân quyền', 'route_name' => 'admin.permissions.index', 'order' => 15],
        ];

        foreach ($menus as $menu) {
            AdminMenu::create($menu);
        }
    }
}