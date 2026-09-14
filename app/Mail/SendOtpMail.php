<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject('Mã xác thực đăng nhập MommyKids')
                    ->html("<h3>Mã OTP đăng nhập của bạn là: <b style='color:#e11d48; font-size:24px;'>{$this->otp}</b></h3><p>Mã có hiệu lực trong 5 phút.</p>");
    }
}