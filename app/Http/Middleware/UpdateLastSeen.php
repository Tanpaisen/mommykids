<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Cập nhật last_seen_at mỗi khi tài khoản có thao tác/lướt trang
            User::where('id', Auth::id())->update([
                'last_seen_at' => now()
            ]);
        }

        return $next($request);
    }
}