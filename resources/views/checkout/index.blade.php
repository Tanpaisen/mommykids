@extends('client.layouts.app')

@section('title', 'Thanh toán - MommyKids')

@section('content')

@php
    $resolveImage = function (?string $image) {
        if (!$image) {
            return null;
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://')
        ) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    };
@endphp
<div class="mk-checkout-page">
    <div class="mk-checkout-wrap">
        <a href="{{ route('cart.index') }}" class="mk-back">← Quay lại giỏ hàng</a>

        <h1>Thanh toán</h1>
        <p class="mk-subtitle">Vui lòng kiểm tra thông tin trước khi đặt hàng</p>

        @if(session('error'))
            <div class="mk-alert mk-alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="mk-alert mk-alert-error">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

       <form method="POST" action="{{ route('checkout.store', [], false) }}">
            @csrf

            <div class="mk-checkout-grid">
                <div class="mk-left">
                    <section class="mk-card">
                        <h2>Thông tin nhận hàng</h2>

                        <div class="mk-form-grid">
                            <label class="mk-field mk-full">
                                <span>Họ và tên <b>*</b></span>
                                <input name="full_name" value="{{ old('full_name') }}" placeholder="Nguyễn Văn An" required>
                            </label>

                            <label class="mk-field">
                                <span>Số điện thoại <b>*</b></span>
                                <input name="phone" value="{{ old('phone') }}" placeholder="0901234567" required>
                            </label>

                            <label class="mk-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="an@example.com">
                            </label>

                            <label class="mk-field">
                                <span>Tỉnh / Thành phố <b>*</b></span>
                                <select id="province" name="province_id" required>
                                    <option value="">-- Chọn tỉnh/thành --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province['ProvinceID'] }}"
                                            {{ old('province_id') == $province['ProvinceID'] ? 'selected' : '' }}>
                                            {{ $province['ProvinceName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="mk-field">
                                <span>Quận / Huyện <b>*</b></span>
                                <select id="district" name="to_district_id" required disabled>
                                    <option value="">-- Chọn quận/huyện --</option>
                                </select>
                            </label>

                            <label class="mk-field mk-full">
                                <span>Phường / Xã <b>*</b></span>
                                <select id="ward" name="to_ward_code" required disabled>
                                    <option value="">-- Chọn phường/xã --</option>
                                </select>
                            </label>

                            <label class="mk-field mk-full">
                                <span>Địa chỉ chi tiết <b>*</b></span>
                                <input name="address" value="{{ old('address') }}" placeholder="Số nhà, tên đường..." required>
                            </label>

                            <label class="mk-field mk-full">
                                <span>Ghi chú</span>
                                <textarea name="note" rows="3" placeholder="Giao hàng giờ hành chính">{{ old('note') }}</textarea>
                            </label>
                        </div>
                    </section>

                    <section class="mk-card">
                        <div class="mk-voucher-heading">
                            <div>
                                <h2>Mã ưu đãi</h2>
                                <p class="mk-voucher-note">Chọn trực tiếp mã đã có trong ví. Mỗi đơn hàng dùng tối đa 1 mã đơn hàng và 1 mã vận chuyển.</p>
                            </div>
                            <a href="{{ route('vouchers.index') }}" class="mk-voucher-wallet-link">Xem & lưu thêm mã</a>
                        </div>

                        @guest
                            <div class="mk-voucher-login-note">
                                Đăng nhập để xem và chọn các voucher đã lưu trong ví của bạn.
                            </div>
                        @endguest

                        <div class="mk-voucher-grid">
                            <div class="mk-voucher-slot" data-voucher-slot="order">
                                <div class="mk-voucher-title">
                                    <span>🎟️</span>
                                    <div>
                                        <strong>Mã giảm giá đơn hàng</strong>
                                        <small>Chỉ hiện các mã trong ví đang phù hợp với giỏ hàng hiện tại</small>
                                    </div>
                                </div>
                                <div class="mk-voucher-controls">
                                    <select
                                        id="voucher-order-id"
                                        {{ auth()->guest() || $availableOrderVouchers->isEmpty() ? 'disabled' : '' }}
                                    >
                                        <option value="">
                                            @guest
                                                -- Đăng nhập để chọn mã --
                                            @else
                                                {{ $availableOrderVouchers->isEmpty() ? '-- Chưa có mã đơn hàng phù hợp --' : '-- Chọn mã đơn hàng --' }}
                                            @endguest
                                        </option>
                                        @foreach($availableOrderVouchers as $voucher)
                                            <option
                                                value="{{ $voucher['id'] }}"
                                                {{ ($checkoutVouchers['order']['id'] ?? null) === $voucher['id'] ? 'selected' : '' }}
                                            >
                                                {{ $voucher['code'] }} — {{ $voucher['benefit'] }} — {{ $voucher['name'] }}{{ $voucher['expires_at'] ? ' · HSD ' . $voucher['expires_at'] : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="button"
                                        data-voucher-apply="order"
                                        {{ auth()->guest() || $availableOrderVouchers->isEmpty() ? 'disabled' : '' }}
                                    >Áp dụng</button>
                                    <button
                                        type="button"
                                        class="mk-voucher-remove"
                                        data-voucher-remove="order"
                                        {{ empty($checkoutVouchers['order']) ? 'hidden' : '' }}
                                    >Gỡ</button>
                                </div>
                                <p class="mk-voucher-status" id="voucher-order-status">
                                    @if(!empty($checkoutVouchers['order']))
                                        Đang áp dụng: {{ $checkoutVouchers['order']['code'] }}
                                    @elseif(auth()->check() && $availableOrderVouchers->isEmpty())
                                        Bạn chưa có mã đơn hàng nào phù hợp với giỏ hàng này.
                                    @endif
                                </p>
                            </div>

                            <div class="mk-voucher-slot" data-voucher-slot="shipping">
                                <div class="mk-voucher-title">
                                    <span>🚚</span>
                                    <div>
                                        <strong>Mã vận chuyển</strong>
                                        <small>Chọn mã trong ví; mức giảm thực tế được tính theo phí GHN</small>
                                    </div>
                                </div>
                                <div class="mk-voucher-controls">
                                    <select
                                        id="voucher-shipping-id"
                                        {{ auth()->guest() || $availableShippingVouchers->isEmpty() ? 'disabled' : '' }}
                                    >
                                        <option value="">
                                            @guest
                                                -- Đăng nhập để chọn mã --
                                            @else
                                                {{ $availableShippingVouchers->isEmpty() ? '-- Chưa có mã vận chuyển phù hợp --' : '-- Chọn mã vận chuyển --' }}
                                            @endguest
                                        </option>
                                        @foreach($availableShippingVouchers as $voucher)
                                            <option
                                                value="{{ $voucher['id'] }}"
                                                {{ ($checkoutVouchers['shipping']['id'] ?? null) === $voucher['id'] ? 'selected' : '' }}
                                            >
                                                {{ $voucher['code'] }} — {{ $voucher['benefit'] }} — {{ $voucher['name'] }}{{ $voucher['expires_at'] ? ' · HSD ' . $voucher['expires_at'] : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button
                                        type="button"
                                        data-voucher-apply="shipping"
                                        {{ auth()->guest() || $availableShippingVouchers->isEmpty() ? 'disabled' : '' }}
                                    >Áp dụng</button>
                                    <button
                                        type="button"
                                        class="mk-voucher-remove"
                                        data-voucher-remove="shipping"
                                        {{ empty($checkoutVouchers['shipping']) ? 'hidden' : '' }}
                                    >Gỡ</button>
                                </div>
                                <p class="mk-voucher-status" id="voucher-shipping-status">
                                    @if(!empty($checkoutVouchers['shipping']))
                                        Đang áp dụng: {{ $checkoutVouchers['shipping']['code'] }}
                                    @elseif(auth()->check() && $availableShippingVouchers->isEmpty())
                                        Bạn chưa có mã vận chuyển nào phù hợp với giỏ hàng này.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="mk-card">
                        <h2>Phương thức thanh toán</h2>

                        <label class="mk-payment-option">
                            <input type="radio" name="payment_method" value="cod"
                                {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                            <span class="mk-payment-icon">💵</span>
                            <span>
                                <strong>Thanh toán khi nhận hàng</strong>
                                <small>Thanh toán bằng tiền mặt khi nhận sản phẩm</small>
                            </span>
                        </label>

                        <label class="mk-payment-option">
                            <input type="radio" name="payment_method" value="bank"
                                {{ old('payment_method') === 'bank' ? 'checked' : '' }}>
                            <span class="mk-payment-icon">🏦</span>
                            <span>
                                <strong>Chuyển khoản ngân hàng</strong>
                                <small>Thanh toán qua tài khoản ngân hàng</small>
                            </span>
                        </label>

                        @if(config('services.zalopay.enabled'))
                            <label class="mk-payment-option">
                                <input type="radio" name="payment_method" value="zalopay"
                                    {{ old('payment_method') === 'zalopay' ? 'checked' : '' }}>
                                <span class="mk-payment-icon">💙</span>
                                <span>
                                    <strong>ZaloPay</strong>
                                    <small>Thanh toán an toàn qua ZaloPay Sandbox</small>
                                </span>
                            </label>
                        @endif

                        @if(config('services.stripe.enabled'))
                            <label class="mk-payment-option">
                                <input type="radio" name="payment_method" value="stripe"
                                    {{ old('payment_method') === 'stripe' ? 'checked' : '' }}>
                                <span class="mk-payment-icon">💳</span>
                                <span>
                                    <strong>Stripe - Visa / Mastercard</strong>
                                    <small>Thanh toán bằng thẻ quốc tế qua Stripe Sandbox</small>
                                </span>
                            </label>
                        @endif

                        @if(config('services.paypal.enabled'))
                            <label class="mk-payment-option">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="paypal"
                                    {{ old('payment_method') === 'paypal' ? 'checked' : '' }}
                                >

                                <span class="mk-payment-icon">🅿️</span>

                                <span>
                                    <strong>PayPal</strong>
                                    <small>
                                        Thanh toán qua PayPal Sandbox
                                    </small>
                                </span>
                            </label>
                        @endif
                    </section>
                </div>

                <aside class="mk-card mk-summary">
                    <h2>Đơn hàng của bạn</h2>

                    @foreach($items as $item)
                        @php
                            $product = $item->product;
                            $imageUrl = $product
                                ? $resolveImage($product->image)
                                : null;
                        @endphp

                        @if($product)
                            <div class="mk-product">
                                <div class="mk-product-image">
                                    @if($imageUrl)
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $product->name }}"
                                            loading="lazy"
                                            onerror="
                                                this.style.display='none';
                                                this.nextElementSibling.style.display='flex';
                                            "
                                        >

                                        <div
                                            class="mk-image-fallback"
                                            style="display:none;"
                                        >
                                            🖼️
                                        </div>
                                    @else
                                        <div class="mk-image-fallback">
                                            🖼️
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <small>
                                        {{ number_format($product->price, 0, ',', '.') }}đ
                                        × {{ $item->quantity }}
                                    </small>
                                </div>

                                <b>
                                    {{ number_format(
                                        $product->price * $item->quantity,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </b>
                            </div>
                        @endif
                    @endforeach

                    <hr>

                    <div class="mk-row">
                        <span>Tạm tính</span>
                        <strong id="checkout-subtotal">{{ number_format($subtotal) }}đ</strong>
                    </div>

                    <div
                        class="mk-row mk-discount-row"
                        id="checkout-order-voucher-row"
                        {{ ($voucherBreakdown['order_discount'] ?? 0) <= 0 ? 'hidden' : '' }}
                    >
                        <span>Voucher đơn hàng</span>
                        <strong id="checkout-order-voucher">-{{ number_format($voucherBreakdown['order_discount'] ?? 0) }}đ</strong>
                    </div>

                    <div class="mk-row">
                        <span>Phí vận chuyển</span>
                        <strong id="checkout-shipping">Chưa tính</strong>
                    </div>

                    <div
                        class="mk-row mk-discount-row"
                        id="checkout-shipping-voucher-row"
                        {{ ($voucherBreakdown['shipping_discount'] ?? 0) <= 0 ? 'hidden' : '' }}
                    >
                        <span>Voucher vận chuyển</span>
                        <strong id="checkout-shipping-voucher">-{{ number_format($voucherBreakdown['shipping_discount'] ?? 0) }}đ</strong>
                    </div>

                    <div
                        class="mk-row mk-discount-row"
                        id="checkout-points-row"
                        {{ $pointsDiscount <= 0 ? 'hidden' : '' }}
                    >
                        <span>Điểm tích lũy</span>
                        <strong id="checkout-points-discount">-{{ number_format($pointsDiscount) }}đ</strong>
                    </div>

                    <hr>
                    <div class="mk-row mk-total">
                        <span>Tổng thanh toán</span>
                        <strong id="checkout-total">{{ number_format($total) }}đ</strong>
                    </div>

                    <button class="mk-primary" type="submit">Đặt hàng</button>
                </aside>
            </div>
        </form>
    </div>
</div>

<style>
.mk-checkout-page{background:#fff8f7;min-height:100vh;padding:32px 16px 60px}
.mk-checkout-wrap{max-width:1180px;margin:auto}.mk-back{color:#ff5f76;text-decoration:none}
.mk-checkout-wrap h1{font-size:34px;margin:18px 0 4px;color:#211d22}.mk-subtitle{color:#81777d;margin-bottom:24px}
.mk-checkout-grid{display:grid;grid-template-columns:minmax(0, 1.5fr) minmax(320px, 0.8fr);gap:24px;align-items:start;width:100%;}
.mk-left{display:flex;flex-direction:column;gap:20px}.mk-card{background:#fff;border:1px solid #f6e4e8;border-radius:18px;padding:24px;box-shadow:0 8px 28px rgba(70,40,45,.05)}
.mk-card h2{font-size:20px;margin:0 0 20px}.mk-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.mk-field{display:flex;flex-direction:column;gap:8px;min-width:0;}.mk-field span{font-weight:600}.mk-field b{color:#ff5f76}.mk-full{grid-column:1/-1}
.mk-field input,.mk-field textarea,.mk-field select{width:100%;max-width:100%; min-width:0;box-sizing:border-box;border:1px solid #eadfe2;border-radius:10px;padding:13px 14px;outline:0;font:inherit;background:#fff;}
.mk-field input:focus,.mk-field textarea:focus,.mk-field select:focus{border-color:#ff6b80;box-shadow:0 0 0 3px rgba(255,107,128,.1)}
.mk-field select:disabled{background:#f7f3f4;color:#9b9397;cursor:not-allowed}
.mk-payment-option{display:flex;align-items:center;gap:14px;border:1px solid #eadfe2;border-radius:14px;padding:15px;margin-top:12px;cursor:pointer}
.mk-payment-option:has(input:checked){border-color:#ff6b80;background:#fff6f8}.mk-payment-option input{accent-color:#ff5f76}
.mk-payment-icon{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:#ffecef}
.mk-payment-option strong{display:block}.mk-payment-option small{display:block;color:#81777d;margin-top:3px}
.mk-summary{position:sticky;top:100px}.mk-product{display:grid;grid-template-columns:64px 1fr auto;gap:12px;align-items:center;padding:8px 0 18px}
.mk-product-image{position:relative;width:64px;height:64px;border-radius:12px;overflow:hidden;background:#fff0f2;flex-shrink:0}
.mk-product-image img{width:100%;height:100%;object-fit:contain;background:#fff;padding:3px;box-sizing:border-box}
.mk-image-fallback{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:#fff0f2;font-size:22px}.mk-product small{display:block;color:#8b8287;margin-top:4px}.mk-product>b{color:#ff5f76;white-space:nowrap}
.mk-card hr{border:0;border-top:1px solid #f0e4e6;margin:18px 0}.mk-row{display:flex;justify-content:space-between;margin:12px 0}.mk-total{font-size:18px}.mk-total strong{color:#ff5f76;font-size:24px}
.mk-primary{width:100%;border:0;border-radius:28px;background:#ff536e;color:#fff;font-weight:700;padding:15px;margin-top:18px;cursor:pointer}
.mk-alert{padding:14px 16px;border-radius:12px;margin:0 0 18px}.mk-alert-error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}.mk-alert ul{margin:8px 0 0 18px}
.mk-voucher-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}.mk-voucher-heading h2{margin-bottom:8px}.mk-voucher-note{margin:0 0 16px;color:#81777d;font-size:14px}.mk-voucher-wallet-link{flex:0 0 auto;color:#ff536e;font-weight:700;font-size:13px;text-decoration:none;background:#fff3f5;border:1px solid #ffd6dd;border-radius:999px;padding:8px 12px}.mk-voucher-wallet-link:hover{text-decoration:underline}.mk-voucher-login-note{padding:11px 13px;border-radius:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;font-size:13px;margin-bottom:14px}
.mk-voucher-grid{display:grid;gap:14px}.mk-voucher-slot{border:1px solid #eadfe2;border-radius:14px;padding:15px;background:#fff}.mk-voucher-title{display:flex;gap:10px;align-items:flex-start}.mk-voucher-title>span{font-size:22px}.mk-voucher-title strong{display:block}.mk-voucher-title small{display:block;color:#81777d;margin-top:3px}
.mk-voucher-controls{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:8px;margin-top:12px}.mk-voucher-controls select{min-width:0;width:100%;border:1px solid #eadfe2;border-radius:10px;padding:11px 38px 11px 12px;font:inherit;background:#fff;outline:0;color:#2f2930}.mk-voucher-controls select:focus{border-color:#ff6b80;box-shadow:0 0 0 3px rgba(255,107,128,.1)}.mk-voucher-controls select:disabled{background:#f7f3f4;color:#9b9397;cursor:not-allowed}.mk-voucher-controls button{border:0;border-radius:10px;padding:0 14px;font-weight:700;cursor:pointer;background:#ff5f76;color:#fff}.mk-voucher-controls .mk-voucher-remove{background:#f3f4f6;color:#4b5563}.mk-voucher-controls button:disabled{opacity:.6;cursor:not-allowed}
.mk-voucher-status{min-height:18px;margin:8px 0 0;font-size:13px;color:#15803d}.mk-voucher-status.is-error{color:#be123c}.mk-discount-row strong{color:#15803d}
.mk-left{min-width:0;}.mk-summary{min-width:0;}
@media(max-width:900px){.mk-checkout-grid{grid-template-columns:1fr}.mk-summary{position:static}}
@media(max-width:600px){.mk-form-grid{grid-template-columns:1fr}.mk-full{grid-column:auto}.mk-product{grid-template-columns:54px 1fr}.mk-product>b{grid-column:2}.mk-checkout-page{padding:20px 10px 40px}.mk-voucher-heading{display:block}.mk-voucher-wallet-link{display:inline-block;margin:-4px 0 14px}.mk-voucher-controls{grid-template-columns:1fr 1fr}.mk-voucher-controls select{grid-column:1/-1}}
</style>

<script type="application/json" id="checkout-config">
{
    "subtotal": {{ (int) $subtotal }},
    "orderVoucherDiscount": {{ (int) ($voucherBreakdown['order_discount'] ?? 0) }},
    "shippingVoucherDiscount": {{ (int) ($voucherBreakdown['shipping_discount'] ?? 0) }},
    "pointsDiscount": {{ (int) $pointsDiscount }},
    "oldProvinceId": @json(old('province_id')),
    "oldDistrictId": @json(old('to_district_id')),
    "oldWardCode": @json(old('to_ward_code')),
    "routes": {
        "districts": @json(route('checkout.districts', [], false)),
        "wards": @json(route('checkout.wards', [], false)),
        "shippingFee": @json(route('checkout.shipping-fee', [], false)),
        "voucherApply": @json(route('checkout.vouchers.apply', [], false)),
        "voucherRemove": @json(route('checkout.vouchers.remove', [], false))
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const config = JSON.parse(document.getElementById('checkout-config').textContent);
    const province = document.getElementById('province');
    const district = document.getElementById('district');
    const ward = document.getElementById('ward');
    const shippingText = document.getElementById('checkout-shipping');
    const totalText = document.getElementById('checkout-total');
    const orderVoucherRow = document.getElementById('checkout-order-voucher-row');
    const orderVoucherText = document.getElementById('checkout-order-voucher');
    const shippingVoucherRow = document.getElementById('checkout-shipping-voucher-row');
    const shippingVoucherText = document.getElementById('checkout-shipping-voucher');
    const pointsRow = document.getElementById('checkout-points-row');
    const pointsText = document.getElementById('checkout-points-discount');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const pricing = {
        subtotal: Number(config.subtotal) || 0,
        shippingFee: 0,
        orderVoucherDiscount: Number(config.orderVoucherDiscount) || 0,
        shippingVoucherDiscount: Number(config.shippingVoucherDiscount) || 0,
        pointsDiscount: Number(config.pointsDiscount) || 0,
    };

    const formatMoney = (amount) => Number(amount || 0).toLocaleString('vi-VN') + 'đ';

    function renderPricing(serverTotal = null) {
        shippingText.textContent = pricing.shippingFee > 0
            ? formatMoney(pricing.shippingFee)
            : 'Chưa tính';

        orderVoucherRow.hidden = pricing.orderVoucherDiscount <= 0;
        orderVoucherText.textContent = '-' + formatMoney(pricing.orderVoucherDiscount);

        shippingVoucherRow.hidden = pricing.shippingVoucherDiscount <= 0;
        shippingVoucherText.textContent = '-' + formatMoney(pricing.shippingVoucherDiscount);

        pointsRow.hidden = pricing.pointsDiscount <= 0;
        pointsText.textContent = '-' + formatMoney(pricing.pointsDiscount);

        const total = serverTotal !== null
            ? Number(serverTotal)
            : Math.max(
                0,
                pricing.subtotal
                - pricing.orderVoucherDiscount
                + pricing.shippingFee
                - pricing.shippingVoucherDiscount
                - pricing.pointsDiscount
            );

        totalText.textContent = formatMoney(total);
    }

    function applyPricingPayload(data) {
        if (!data) return;

        pricing.shippingFee = Number(data.shipping_fee ?? pricing.shippingFee) || 0;
        pricing.orderVoucherDiscount = Number(data.order_voucher_discount ?? pricing.orderVoucherDiscount) || 0;
        pricing.shippingVoucherDiscount = Number(data.shipping_voucher_discount ?? pricing.shippingVoucherDiscount) || 0;
        pricing.pointsDiscount = Number(data.points_discount ?? pricing.pointsDiscount) || 0;

        renderPricing(data.total ?? null);
    }

    function resetShippingPricing() {
        pricing.shippingFee = 0;
        pricing.shippingVoucherDiscount = 0;
        renderPricing();
    }

    async function loadDistricts(provinceId, selectedDistrictId = null) {
        district.disabled = true;
        ward.disabled = true;
        district.innerHTML = '<option value="">Đang tải...</option>';
        ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
        resetShippingPricing();

        if (!provinceId) {
            district.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
            return;
        }

        try {
            const response = await fetch(`${config.routes.districts}?province_id=${provinceId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Không tải được quận/huyện.');

            const data = await response.json();
            district.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';

            data.forEach(item => {
                const isSelected = selectedDistrictId && String(selectedDistrictId) === String(item.DistrictID) ? 'selected' : '';
                district.innerHTML += `<option value="${item.DistrictID}" ${isSelected}>${item.DistrictName}</option>`;
            });

            district.disabled = false;
        } catch (error) {
            district.innerHTML = '<option value="">Không tải được quận/huyện</option>';
            console.error(error);
        }
    }

    async function loadWards(districtId, selectedWardCode = null) {
        ward.disabled = true;
        ward.innerHTML = '<option value="">Đang tải...</option>';
        resetShippingPricing();

        if (!districtId) {
            ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
            return;
        }

        try {
            const response = await fetch(`${config.routes.wards}?district_id=${districtId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) throw new Error('Không tải được phường/xã.');

            const data = await response.json();
            ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';

            data.forEach(item => {
                const isSelected = selectedWardCode && String(selectedWardCode) === String(item.WardCode) ? 'selected' : '';
                ward.innerHTML += `<option value="${item.WardCode}" ${isSelected}>${item.WardName}</option>`;
            });

            ward.disabled = false;
        } catch (error) {
            ward.innerHTML = '<option value="">Không tải được phường/xã</option>';
            console.error(error);
        }
    }

    async function calculateShippingFee() {
        if (!district.value || !ward.value) return;

        shippingText.textContent = 'Đang tính...';

        try {
            const response = await fetch(config.routes.shippingFee, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? ''
                },
                body: JSON.stringify({
                    district_id: Number(district.value),
                    ward_code: ward.value
                })
            });

            const data = await response.json();

            if (!response.ok) {
                shippingText.textContent = data.message ?? 'Không tính được phí';
                console.error(data);
                return;
            }

            applyPricingPayload(data);
        } catch (error) {
            shippingText.textContent = 'Không tính được phí';
            console.error(error);
        }
    }

    async function applyVoucher(type) {
        const select = document.getElementById(`voucher-${type}-id`);
        const status = document.getElementById(`voucher-${type}-status`);
        const button = document.querySelector(`[data-voucher-apply="${type}"]`);
        const removeButton = document.querySelector(`[data-voucher-remove="${type}"]`);
        const voucherId = select?.value ?? '';

        status.classList.remove('is-error');

        if (!voucherId) {
            status.textContent = 'Vui lòng chọn một mã ưu đãi trong ví.';
            status.classList.add('is-error');
            return;
        }

        button.disabled = true;
        status.textContent = 'Đang kiểm tra mã...';

        try {
            const response = await fetch(config.routes.voucherApply, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? ''
                },
                body: JSON.stringify({ type, voucher_id: voucherId })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message ?? 'Không thể áp dụng mã ưu đãi.');
            }

            select.value = data.voucher.id;
            status.textContent = data.message;
            removeButton.hidden = false;
            applyPricingPayload(data.pricing);
        } catch (error) {
            status.textContent = error.message;
            status.classList.add('is-error');
        } finally {
            button.disabled = false;
        }
    }

    async function removeVoucher(type) {
        const select = document.getElementById(`voucher-${type}-id`);
        const status = document.getElementById(`voucher-${type}-status`);
        const removeButton = document.querySelector(`[data-voucher-remove="${type}"]`);

        removeButton.disabled = true;
        status.classList.remove('is-error');

        try {
            const response = await fetch(config.routes.voucherRemove, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? ''
                },
                body: JSON.stringify({ type })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message ?? 'Không thể gỡ mã ưu đãi.');
            }

            select.value = '';
            status.textContent = data.message;
            removeButton.hidden = true;
            applyPricingPayload(data.pricing);
        } catch (error) {
            status.textContent = error.message;
            status.classList.add('is-error');
        } finally {
            removeButton.disabled = false;
        }
    }

    document.querySelectorAll('[data-voucher-apply]').forEach(button => {
        button.addEventListener('click', () => applyVoucher(button.dataset.voucherApply));
    });

    document.querySelectorAll('[data-voucher-remove]').forEach(button => {
        button.addEventListener('click', () => removeVoucher(button.dataset.voucherRemove));
    });

    province.addEventListener('change', () => loadDistricts(province.value));
    district.addEventListener('change', () => loadWards(district.value));
    ward.addEventListener('change', calculateShippingFee);

    async function restoreOldState() {
        if (config.oldProvinceId) {
            await loadDistricts(config.oldProvinceId, config.oldDistrictId);
            if (config.oldDistrictId) {
                await loadWards(config.oldDistrictId, config.oldWardCode);
                if (config.oldWardCode) {
                    await calculateShippingFee();
                }
            }
        }
    }

    renderPricing();
    restoreOldState();
});
</script>
@endsection