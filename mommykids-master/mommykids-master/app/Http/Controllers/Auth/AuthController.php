<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Hàm hiển thị giao diện đăng nhập
    public function showLoginForm()
    {
        return "Giao diện trang Đăng Nhập / Đăng Ký sẽ nằm ở đây!";
    }
}