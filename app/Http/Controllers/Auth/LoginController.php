<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{


    // Hiện giao diện login

    public function index()
    {
        return view('auth.login');
    }



    // Xử lý đăng nhập

    public function login(Request $request)
    {


        $request->validate([

            'email'=>'required|email',

            'password'=>'required'

        ]);



        $remember = $request->has('remember');



        if(Auth::attempt(
            [
                'email'=>$request->email,
                'password'=>$request->password
            ],
            $remember
        ))
        {


            $request->session()->regenerate();


            return redirect('/');

        }



        return back()->withErrors([

            'email'=>'Email hoặc mật khẩu không đúng'

        ]);


    }



    // Đăng xuất


    public function logout()
    {

        Auth::logout();


        return redirect('/dang-nhap');

    }


}