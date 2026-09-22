<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AdminMenu; // <-- Khai báo Model AdminMenu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Hiển thị trang Cài đặt chung & Danh sách Menu Admin
     */
    public function index()
    {
        // Lấy bản ghi cài đặt đầu tiên hoặc tạo bản ghi mặc định nếu chưa có
        $setting = Setting::firstOrCreate([], [
            'site_name' => 'MommyKids',
            'hotline'   => '1800 6886',
            'email'     => 'hotro@mommykids.vn',
        ]);

        // Lấy toàn bộ Menu Admin theo thứ tự sắp xếp
        $menus = AdminMenu::orderBy('order')->get();

        return view('admin.settings.index', compact('setting', 'menus'));
    }

    /**
     * Cập nhật thông tin Cài đặt chung
     */
    public function update(Request $request)
    {
        $setting = Setting::firstOrCreate([]);

        // 1. Validate tất cả trường dữ liệu
        $request->validate([
            'site_name'          => 'nullable|string|max:255',
            'logo'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',
            'copyright'          => 'nullable|string|max:255',
            'hotline'            => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:500',
            'facebook_url'       => 'nullable|url|max:255',
            'zalo_url'           => 'nullable|url|max:255',
            'instagram_url'      => 'nullable|url|max:255',
            'top_announcement'   => 'nullable|string|max:255',
            'default_location'   => 'nullable|string|max:255',
            'search_placeholder' => 'nullable|string|max:255',
            'home_banner_title'  => 'nullable|string|max:255',
            'promo_title'        => 'nullable|string|max:255',
            'promo_subtitle'     => 'nullable|string|max:255',
            'promo_badge_1'      => 'nullable|string|max:255',
            'promo_badge_2'      => 'nullable|string|max:255',
            'promo_badge_3'      => 'nullable|string|max:255',
            'promo_button_text'  => 'nullable|string|max:255',
            'footer_description' => 'nullable|string',
            'meta_description'   => 'nullable|string',
            'header_scripts'     => 'nullable|string',
        ], [
            'logo.image'        => 'Logo phải là định dạng hình ảnh.',
            'logo.mimes'        => 'Logo hỗ trợ các định dạng: jpeg, png, jpg, gif, svg, webp.',
            'logo.max'          => 'Kích thước Logo không được vượt quá 2MB.',
            'favicon.image'     => 'Favicon phải là định dạng hình ảnh.',
            'favicon.max'       => 'Kích thước Favicon không được vượt quá 1MB.',
            'email.email'       => 'Định dạng Email không hợp lệ.',
            'facebook_url.url'  => 'Đường dẫn Facebook không hợp lệ.',
            'zalo_url.url'      => 'Đường dẫn Zalo không hợp lệ.',
            'instagram_url.url' => 'Đường dẫn Instagram không hợp lệ.',
        ]);

        // 2. Lấy toàn bộ dữ liệu gửi lên (loại trừ các file ảnh)
        $data = $request->except(['logo', 'favicon', '_token']);

        // 3. Xử lý Upload Logo mới
        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        // 4. Xử lý Upload Favicon mới
        if ($request->hasFile('favicon')) {
            if ($setting->favicon && Storage::disk('public')->exists($setting->favicon)) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        // 5. Cập nhật dữ liệu vào Database
        $setting->update($data);

        // 6. XÓA CACHE ĐỂ NGOÀI CLIENT ĐỔI TỨC THÌ
        Cache::forget('global_settings');

        return redirect()->back()->with('success', 'Cập nhật cài đặt hệ thống thành công!');
    }

    /**
     * Cập nhật danh sách tên Menu Admin
     */
    public function updateMenus(Request $request)
    {
        $menusData = $request->input('menus', []);

        foreach ($menusData as $id => $item) {
            AdminMenu::where('id', $id)->update([
                'title'      => $item['title'],
                'group_name' => $item['group_name'] ?? '',
                'order'      => $item['order'] ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật danh sách Menu Admin thành công!');
    }
}