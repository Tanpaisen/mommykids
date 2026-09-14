@extends('client.layouts.app')

@section('title', 'Thanh toán ZaloPay - MommyKids')

@section('content')
<div style="
    max-width: 560px;
    margin: 40px auto;
    padding: 32px;
    background: #fff;
    border: 1px solid #f3e3e7;
    border-radius: 22px;
    text-align: center;
    box-shadow: 0 12px 36px rgba(70, 40, 45, .08);
">
    <div style="
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: #f1f5ff;
        font-size: 30px;
    ">💙</div>

    <h1 style="font-size: 28px; margin: 0 0 10px;">
        Thanh toán ZaloPay
    </h1>

    <p style="margin: 0 0 8px; color: #6f676b;">
        Đơn hàng:
        <strong>{{ $order->code }}</strong>
    </p>

    <p style="
        margin: 8px 0 22px;
        font-size: 26px;
        font-weight: 800;
        color: #ff536e;
    ">
        {{ number_format($order->total, 0, ',', '.') }}đ
    </p>

    <div style="
        display: inline-block;
        padding: 16px;
        background: #fff;
        border: 1px solid #ece6e8;
        border-radius: 18px;
    ">
        <div id="zalopay-qr"></div>
    </div>

    <p style="margin: 20px 0 6px; font-weight: 700;">
        Quét mã QR để thanh toán
    </p>

    <p style="margin: 0; color: #81777d; line-height: 1.6;">
        Đây là giao dịch ZaloPay Sandbox. Với môi trường test,
        hãy dùng ứng dụng/phương thức thanh toán phù hợp với Sandbox.
    </p>

    <div id="payment-status" style="
        margin-top: 22px;
        padding: 12px 14px;
        border-radius: 12px;
        background: #fff7e6;
        color: #9a6700;
        font-weight: 700;
    ">
        ⏳ Đang chờ thanh toán...
    </div>

    <a href="{{ route('checkout.index', [], false) }}" style="
        display: inline-block;
        margin-top: 20px;
        color: #ff536e;
        text-decoration: none;
        font-weight: 700;
    ">
        ← Quay lại thanh toán
    </a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const qrTarget = document.getElementById('zalopay-qr');
    const statusTarget = document.getElementById('payment-status');

    new QRCode(qrTarget, {
        text: @json($qrCode),
        width: 280,
        height: 280,
        correctLevel: QRCode.CorrectLevel.M
    });

    const statusUrl = @json(route('zalopay.status', [], false));

    const timer = setInterval(async () => {
        try {
            const response = await fetch(statusUrl, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            if (data.paid) {
                clearInterval(timer);

                statusTarget.textContent = '✅ Thanh toán thành công';
                statusTarget.style.background = '#ecfdf3';
                statusTarget.style.color = '#067647';

                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }
        } catch (error) {
            console.error('ZaloPay status error:', error);
        }
    }, 2500);
});
</script>
@endsection
