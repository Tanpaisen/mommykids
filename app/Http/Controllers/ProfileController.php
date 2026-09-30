<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{

    /**
     * Trang hồ sơ khách hàng
     */
    public function edit()
    {
        /** @var User $authUser */
        $authUser = Auth::user();


        // Lấy lại dữ liệu mới nhất từ database
        $user = User::where('id', $authUser->id)->first();


        return view(
            'client.profile.edit',
            compact('user')
        );
    }



    /**
     * Cập nhật thông tin cá nhân
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();


        $request->validate([

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20'
            ],

            'birthday' => [
                'nullable',
                'date'
            ],

            'gender' => [
                'nullable',
                'in:nam,nu,khac'
            ],

        ]);



        $user->update([

            'name' => $request->name,

            'phone' => $request->phone,

            'birthday' => $request->birthday,

            'gender' => $request->gender,

        ]);



        return redirect()
            ->back()
            ->with(
                'success',
                'Cập nhật thông tin tài khoản thành công!'
            );
    }




    /**
     * Trung tâm hỗ trợ
     */
    public function support()
    {
        /** @var User $user */
        $user = Auth::user();


        return view(
            'client.profile.support',
            compact('user')
        );
    }





    /**
     * Quy định & chính sách
     */
    public function policy()
    {
        /** @var User $user */
        $user = Auth::user();


        return view(
            'client.profile.policy',
            compact('user')
        );
    }

}