<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    // 1. Trang Giới thiệu
    public function about()
    {
        return view('pages.about');
    }

    // 2. Hệ thống cửa hàng
    public function stores()
    {
        return view('pages.stores');
    }

    // 3. Tuyển dụng
    public function recruitment()
    {
        return view('pages.recruitment');
    }

    // 4. Chính sách đổi trả
    public function returnPolicy()
    {
        return view('pages.return-policy');
    }

    // 5. Chính sách vận chuyển
    public function shippingPolicy()
    {
        return view('pages.shipping-policy');
    }

    // 6. Chính sách bảo mật
    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }
}