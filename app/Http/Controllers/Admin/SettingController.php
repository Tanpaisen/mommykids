<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AdminMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{

    /**
     * Hiển thị trang Cài đặt chung
     */
    public function index()
    {
        $setting = Setting::first();

        if (!$setting) {

            $setting = Setting::create([
                'site_name' => 'MommyKids',
                'hotline'   => '1800 6886',
                'email'     => 'hotro@mommykids.vn',
            ]);

        }


        $menus = AdminMenu::orderBy('order')->get();


        return view(
            'admin.settings.index',
            compact('setting','menus')
        );
    }



    /**
     * Cập nhật cài đặt
     */
    public function update(Request $request)
    {

        $setting = Setting::first();


        if (!$setting) {

            $setting = Setting::create([
                'site_name' => 'MommyKids',
            ]);

        }



        $request->validate([

            'site_name' => 'nullable|string|max:255',

            'logo' =>
            'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',

            'favicon' =>
            'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',


            'copyright'
            =>'nullable|string|max:255',

            'hotline'
            =>'nullable|string|max:50',

            'email'
            =>'nullable|email|max:255',

            'address'
            =>'nullable|string|max:500',


            'facebook_url'
            =>'nullable|url|max:255',

            'zalo_url'
            =>'nullable|url|max:255',

            'instagram_url'
            =>'nullable|url|max:255',

            'meta_description'
            =>'nullable|string',

            'header_scripts'
            =>'nullable|string',


        ]);




        /*
        |--------------------------------------------------------------------------
        | Lấy dữ liệu text
        |--------------------------------------------------------------------------
        */

        $data = $request->except([
            'logo',
            'favicon',
            '_token'
        ]);





        /*
        |--------------------------------------------------------------------------
        | Upload LOGO
        |--------------------------------------------------------------------------
        */

        if($request->hasFile('logo')){


            // xóa logo cũ

            if(
                $setting->logo &&
                Storage::disk('public')
                ->exists($setting->logo)
            ){

                Storage::disk('public')
                ->delete($setting->logo);

            }



            // lưu logo mới

            $data['logo'] =
            $request
            ->file('logo')
            ->store(
                'settings',
                'public'
            );


        }







        /*
        |--------------------------------------------------------------------------
        | Upload FAVICON
        |--------------------------------------------------------------------------
        */

        if($request->hasFile('favicon')){


            if(
                $setting->favicon &&
                Storage::disk('public')
                ->exists($setting->favicon)
            ){

                Storage::disk('public')
                ->delete($setting->favicon);

            }



            $data['favicon'] =
            $request
            ->file('favicon')
            ->store(
                'settings',
                'public'
            );


        }






        /*
        |--------------------------------------------------------------------------
        | Lưu database
        |--------------------------------------------------------------------------
        */

        $setting->update($data);



        /*
        |--------------------------------------------------------------------------
        | Xóa cache
        |--------------------------------------------------------------------------
        */

        Cache::forget('global_settings');



        return back()
        ->with(
            'success',
            'Cập nhật cài đặt hệ thống thành công!'
        );

    }







    /**
     * Cập nhật menu Admin
     */
    public function updateMenus(Request $request)
    {

        $menusData =
        $request->input('menus',[]);



        foreach($menusData as $id=>$item){


            AdminMenu::where('id',$id)
            ->update([

                'title'=>
                $item['title'],

                'group_name'=>
                $item['group_name'] ?? '',


                'order'=>
                $item['order'] ?? 0,

            ]);

        }



        return back()
        ->with(
            'success',
            'Cập nhật danh sách Menu Admin thành công!'
        );

    }

}