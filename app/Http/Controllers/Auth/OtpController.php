<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    // Gửi OTP về Gmail
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email' => 'Email không đúng định dạng'
        ]);

        $otp = rand(100000, 999999);
        
        // Lưu OTP vào Cache 5 phút
        Cache::put('otp_' . $request->email, $otp, now()->addMinutes(5));

        // Gửi mail OTP
        try {
            Mail::to($request->email)->send(new SendOtpMail($otp));
            return response()->json(['success' => true, 'message' => 'Mã OTP đã được gửi về Email của bạn!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể gửi mail. Vui lòng kiểm tra lại cấu hình SMTP!'], 500);
        }
    }

    // Xác thực OTP & Đăng nhập
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric'
        ]);

        $cachedOtp = Cache::get('otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Mã OTP không đúng hoặc đã hết hạn!'], 400);
        }

        // Tìm hoặc tạo tài khoản mới nếu chưa tồn tại
        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['name' => explode('@', $request->email)[0], 'password' => bcrypt(rand(100000, 999999))]
        );

        Auth::login($user);
        Cache::forget('otp_' . $request->email); // Xóa OTP sau khi dùng xong

        return response()->json([
            'success' => true, 
            'message' => 'Đăng nhập thành công!',
            'user' => $user
        ]);
    }
}