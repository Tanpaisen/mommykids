<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Lấy đường dẫn chuyển hướng khi người dùng chưa đăng nhập.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Nếu truy cập trang Admin -> Chuyển hướng về trang Đăng nhập Admin
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.auth.login');
            }

            // Mặc định khách hàng thường -> Chuyển hướng về trang Đăng nhập Frontend
            return route('login');
        }

        return null;
    }
}