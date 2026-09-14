@extends('client.layouts.app')

@section('title', 'Thanh toán thành công - MommyKids')

@section('content')
<div class="mk-success-page">
    <div class="mk-success-wrap">
        <div class="mk-success-hero">
            <div class="mk-check">✓</div>
            <h1>Thanh toán thành công!</h1>
            <p>Cảm ơn bạn đã mua hàng tại MommyKids</p>
        </div>

        <section class="mk-card">
            <div class="mk-title-row">
                <h2>Thông tin thanh toán</h2>
                <span class="mk-badge">Đã thanh toán</span>
            </div>
            <div class="mk-info-row"><span>Mã đơn hàng</span><strong>{{ $order['code'] }}</strong></div>
            <div class="mk-info-row"><span>Thời gian thanh toán</span><strong>{{ $payment['paid_at'] ?? now()->toDateTimeString() }}</strong></div>
            <div class="mk-info-row"><span>Phương thức thanh toán</span><strong>{{ $payment ? 'Chuyển khoản ngân hàng' : 'Thanh toán khi nhận hàng' }}</strong></div>
            <div class="mk-info-row"><span>Trạng thái</span><strong class="mk-green">Thành công</strong></div>
        </section>

        @if($payment)
            <section class="mk-card">
                <h2>Chi tiết thanh toán</h2>
                <div class="mk-info-row"><span>Số tiền</span><strong class="mk-pink">{{ number_format($payment['amount']) }}đ</strong></div>
                <div class="mk-info-row"><span>Ngân hàng</span><strong>{{ $payment['bank'] }}</strong></div>
                <div class="mk-info-row"><span>Mã giao dịch</span><strong>{{ $payment['transaction_id'] }}</strong></div>
                <div class="mk-info-row"><span>Nội dung chuyển khoản</span><strong>{{ $payment['content'] }}</strong></div>
            </section>
        @endif

        <section class="mk-card">
            <h2>Đơn hàng của bạn</h2>
            @foreach($items as $item)
                <div class="mk-product">
                    <img src="{{ $item->product->image ?: 'https://via.placeholder.com/80' }}" alt="{{ $item->product->name }}">
                    <div>
                        <strong>{{ $item->product->name }}</strong>
                        <small>× {{ $item->quantity }}</small>
                    </div>
                    <b>{{ number_format($item->product->price * $item->quantity) }}đ</b>
                </div>
            @endforeach
            <hr>
            <div class="mk-info-row"><span>Tạm tính</span><strong>{{ number_format($total) }}đ</strong></div>
            <div class="mk-info-row"><span>Phí vận chuyển</span><strong>0đ</strong></div>
            <div class="mk-info-row mk-total"><span>Tổng thanh toán</span><strong>{{ number_format($total) }}đ</strong></div>
        </section>

        <div class="mk-actions">
            <a href="{{ route('home') }}" class="mk-outline">Xem đơn hàng</a>
            <a href="{{ route('home') }}" class="mk-primary">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>

<style>
.mk-success-page{background:#fff8f7;min-height:100vh;padding:36px 16px 70px}.mk-success-wrap{max-width:720px;margin:auto}
.mk-success-hero{text-align:center;margin-bottom:24px}.mk-check{width:74px;height:74px;border-radius:50%;background:#33bf73;color:#fff;font-size:42px;display:grid;place-items:center;margin:0 auto 14px}
.mk-success-hero h1{font-size:30px;margin:0}.mk-success-hero p{color:#81777d}
.mk-card{background:#fff;border:1px solid #f0e1e4;border-radius:18px;padding:24px;margin-top:18px;box-shadow:0 8px 28px rgba(70,40,45,.05)}
.mk-card h2{font-size:19px;margin:0 0 16px}.mk-title-row{display:flex;justify-content:space-between;align-items:center}.mk-badge{background:#e8fbef;color:#2e9d5b;border-radius:20px;padding:6px 10px;font-size:12px;font-weight:700}
.mk-info-row{display:flex;justify-content:space-between;gap:20px;padding:9px 0}.mk-info-row span{color:#81777d}.mk-info-row strong{text-align:right}.mk-green{color:#2faf65}.mk-pink,.mk-total strong{color:#ff536e}
.mk-product{display:grid;grid-template-columns:58px 1fr auto;gap:12px;align-items:center;padding:12px 0}.mk-product img{width:58px;height:58px;object-fit:cover;border-radius:10px;background:#fff0f2}.mk-product small{display:block;color:#81777d}.mk-product b{white-space:nowrap}
.mk-card hr{border:0;border-top:1px solid #f0e1e4;margin:12px 0}.mk-total{font-size:18px}.mk-total strong{font-size:22px}
.mk-actions{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:18px}.mk-actions a{text-align:center;text-decoration:none;border-radius:10px;padding:14px;font-weight:700}
.mk-outline{border:1px solid #ff536e;color:#ff536e;background:#fff}.mk-primary{background:#ff536e;color:#fff}
@media(max-width:600px){.mk-actions{grid-template-columns:1fr}.mk-info-row{align-items:flex-start}}
</style>
@endsection
