<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\SendOtpMail;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    // Gửi OTP về Gmail
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email'    => 'Email không đúng định dạng'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $otp = rand(100000, 999999);
            
            // Lưu OTP vào Cache trong 5 phút
            Cache::put('otp_' . $request->email, $otp, now()->addMinutes(5));

            // Gửi mail OTP
            Mail::to($request->email)->send(new SendOtpMail($otp));

            return response()->json([
                'success' => true, 
                'message' => 'Mã OTP đã được gửi về Email của bạn!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Lỗi gửi mail: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xác thực OTP & Đăng nhập
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required'
        ], [
            'email.required' => 'Vui lòng nhập Email',
            'email.email'    => 'Email không đúng định dạng',
            'otp.required'   => 'Vui lòng nhập mã OTP'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $cachedOtp = Cache::get('otp_' . $request->email);

            if (!$cachedOtp || $cachedOtp != $request->otp) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Mã OTP không đúng hoặc đã hết hạn!'
                ], 400);
            }

            // Tìm hoặc tạo tài khoản mới
            $user = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name'     => explode('@', $request->email)[0],
                    'password' => bcrypt(Str::random(10)),
                ]
            );

            // Đăng nhập hệ thống
            Auth::login($user);

            // Gộp giỏ hàng khách vãng lai vào tài khoản user
            try {
                app(CartService::class)->mergeGuestCart($user->id);
            } catch (\Exception $e) {
                Log::error('Lỗi gộp giỏ hàng post-login: ' . $e->getMessage());
            }

            // Xóa OTP khỏi Cache
            Cache::forget('otp_' . $request->email);

            return response()->json([
                'success'  => true, 
                'message'  => 'Đăng nhập thành công!',
                'redirect' => route('home'),
                'user'     => $user
            ]);

        } catch (\Exception $e) {
            // Bắt và hiển thị chính xác lý do gây ra lỗi 500
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
}