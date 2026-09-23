<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    // Trang hiển thị danh sách các mục menu để chỉnh sửa
    public function index()
    {
        $menus = AdminMenu::orderBy('order')->get();
        return view('admin.menus.index', compact('menus'));
    }

    // Xử lý lưu thay đổi tên menu khi bấm nút Lưu
    public function updateAll(Request $request)
    {
        $data = $request->input('menus', []);

        foreach ($data as $id => $item) {
            AdminMenu::where('id', $id)->update([
                'title'      => $item['title'],
                'group_name' => $item['group_name'],
                'order'      => $item['order'] ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Đã cập nhật tên Menu Admin thành công!');
    }
}