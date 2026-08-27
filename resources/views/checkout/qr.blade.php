@extends('client.layouts.app')

@section('title', 'Quét QR thanh toán - MommyKids')

@section('content')
<div class="mk-pay-page">
    <div class="mk-pay-wrap">
        <a href="{{ route('checkout.index') }}" class="mk-back">← Quay lại thanh toán</a>

        <div class="mk-countdown-card">
            <div class="mk-clock">◷</div>
            <div>
                <strong>Vui lòng thanh toán trong vòng <span id="mk-countdown">15:00</span></strong>
                <small>Sau khi hết thời gian, đơn hàng sẽ bị hủy.</small>
            </div>
        </div>

        <section class="mk-qr-card">
            <h1>Chuyển khoản ngân hàng</h1>
            <p>Quét mã QR bên dưới để thanh toán đơn hàng</p>

            <img src="{{ $qrUrl }}" class="mk-qr" alt="VietQR">

            <div class="mk-bank-info">
                <div><span>Số tài khoản</span><strong>{{ $accountNo }}</strong></div>
                <div><span>Chủ tài khoản</span><strong>{{ $accountName }}</strong></div>
                <div><span>Ngân hàng</span><strong>Techcombank</strong></div>
                <div><span>Số tiền</span><strong class="mk-pink">{{ number_format($total) }}đ</strong></div>
                <div><span>Nội dung chuyển khoản</span><strong>{{ $transferContent }}</strong></div>
            </div>

            <div class="mk-note">
                <strong>Lưu ý:</strong>
                <ul>
                    <li>Vui lòng chuyển đúng số tiền và nội dung chuyển khoản.</li>
                    <li>Ở bản test này, sau khi chuyển khoản hãy bấm nút bên dưới.</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('checkout.confirm-transfer') }}">
                @csrf
                <button class="mk-confirm" type="submit">↻ Tôi đã chuyển khoản</button>
            </form>
        </section>
    </div>
</div>

<style>
.mk-pay-page{background:#fff8f7;min-height:100vh;padding:32px 16px 60px}.mk-pay-wrap{max-width:620px;margin:auto}
.mk-back{color:#ff5f76;text-decoration:none}.mk-countdown-card{display:flex;gap:14px;align-items:center;background:#fff8e8;border:1px solid #ffd98a;border-radius:14px;padding:16px;margin:20px 0}
.mk-countdown-card strong{display:block}.mk-countdown-card small{display:block;color:#746a59;margin-top:4px}.mk-clock{font-size:30px;color:#f2a900}
.mk-qr-card{background:#fff;border:1px solid #f0e1e4;border-radius:18px;padding:26px;box-shadow:0 8px 28px rgba(70,40,45,.05);text-align:center}
.mk-qr-card h1{font-size:24px;margin:0 0 6px}.mk-qr-card>p{color:#81777d}.mk-qr{width:330px;max-width:100%;margin:18px auto;display:block;border-radius:14px}
.mk-bank-info{background:#fff7f8;border:1px solid #ffd9df;border-radius:14px;padding:16px;text-align:left}
.mk-bank-info>div{display:flex;justify-content:space-between;gap:20px;padding:8px 0}.mk-bank-info span{color:#81777d}.mk-bank-info strong{text-align:right}.mk-pink{color:#ff536e}
.mk-note{margin-top:16px;background:#eef9ff;border:1px solid #bfe9ff;border-radius:14px;padding:16px;text-align:left;color:#31596b}.mk-note ul{margin:8px 0 0 18px}
.mk-confirm{margin-top:20px;border:1px solid #ddd;background:#fff;border-radius:10px;padding:12px 20px;font-weight:700;cursor:pointer}
</style>

<script>
(function(){
    let seconds = 15 * 60;
    const el = document.getElementById('mk-countdown');
    const timer = setInterval(() => {
        seconds--;
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        el.textContent = `${m}:${s}`;
        if (seconds <= 0) {
            clearInterval(timer);
            el.textContent = '00:00';
        }
    }, 1000);
})();
</script>
@endsection
