<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    /**
     * Chuyển hướng người dùng sang Facebook để xác thực.
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Xử lý dữ liệu trả về từ Facebook sau khi xác thực thành công.
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // Nếu nick FB không có email (đăng ký bằng SĐT), tự sinh email tạm
            $email = $facebookUser->getEmail() ?? ('fb_' . $facebookUser->getId() . '@mommykids.vn');

            // Tạo hoặc cập nhật user theo ID Facebook
            $user = User::updateOrCreate(
                [
                    'provider' => 'facebook',
                    'provider_id' => $facebookUser->getId(),
                ],
                [
                    'name' => $facebookUser->getName() ?? 'Người dùng Facebook',
                    'email' => $email,
                    'avatar' => $facebookUser->getAvatar(),
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            Auth::login($user, true);

            return redirect('/')->with('success', 'Đăng nhập bằng Facebook thành công!');
        } catch (Exception $e) {
            return redirect('/')->with('error', 'Đăng nhập bằng Facebook thất bại, vui lòng thử lại.');
        }
    }
}