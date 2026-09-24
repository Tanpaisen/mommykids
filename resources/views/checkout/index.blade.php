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

    $savedAddresses = auth()->check()
        ? auth()->user()->addresses()->get()
        : collect();

    $defaultAddress = $savedAddresses->firstWhere('is_default', true)
        ?? $savedAddresses->first();

    $initialFullName = old(
        'full_name',
        $defaultAddress?->recipient_name ?? auth()->user()?->name ?? ''
    );

    $initialPhone = old(
        'phone',
        $defaultAddress?->phone ?? auth()->user()?->phone ?? ''
    );

    $initialEmail = old(
        'email',
        auth()->user()?->email ?? ''
    );

    $initialProvinceId = old(
        'province_id',
        $defaultAddress?->province_id ?? ''
    );

    $initialDistrictId = old(
        'to_district_id',
        $defaultAddress?->district_id ?? ''
    );

    $initialWardCode = old(
        'to_ward_code',
        $defaultAddress?->ward_code ?? ''
    );

    $initialAddress = old(
        'address',
        $defaultAddress?->address_detail ?? ''
    );

    $initialAddressLine = $defaultAddress
        ? $defaultAddress->full_address
        : $initialAddress;

    $hasAddressValue = filled($initialFullName)
        || filled($initialPhone)
        || filled($initialAddress);
@endphp

<div class="tt-checkout-page">
    <div class="tt-checkout-wrap">
        <div class="tt-page-head">
            <a href="{{ route('cart.index') }}" class="tt-back" aria-label="Quay lại giỏ hàng">
                <span aria-hidden="true">‹</span>
            </a>
            <div>
                <h1>Thanh toán</h1>
                <p>Kiểm tra thông tin đơn hàng trước khi đặt</p>
            </div>
        </div>

        @if(session('error'))
            <div class="tt-alert tt-alert-error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="tt-alert tt-alert-error">
                <strong>Vui lòng kiểm tra lại thông tin:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store', [], false) }}" id="checkout-form">
            @csrf

            <div class="tt-layout">
                <main class="tt-main">
                    {{-- ĐỊA CHỈ --}}
                    <section class="tt-card tt-address-card">
                        <button type="button" class="tt-address-summary" data-open-address>
                            <span class="tt-address-icon" aria-hidden="true">⌖</span>
                            <span class="tt-address-copy">
                                <span class="tt-address-title-row">
                                    <strong id="address-summary-name">
                                        {{ $initialFullName ?: 'Địa chỉ giao hàng' }}
                                    </strong>
                                    <span id="address-summary-phone">
                                        {{ $initialPhone }}
                                    </span>
                                </span>
                                <span class="tt-address-line" id="address-summary-line">
                                    @if($hasAddressValue)
                                        {{ $initialAddressLine }}
                                    @else
                                        Thêm địa chỉ nhận hàng
                                    @endif
                                </span>
                                <span class="tt-address-helper">
                                    Nhấn để {{ $hasAddressValue ? 'chỉnh sửa' : 'nhập' }} thông tin nhận hàng
                                </span>
                            </span>
                            <span class="tt-chevron" aria-hidden="true">›</span>
                        </button>
                        <div class="tt-airmail" aria-hidden="true"></div>
                    </section>

                    {{-- SẢN PHẨM --}}
                    <section class="tt-card tt-shop-card">
                        <div class="tt-shop-head">
                            <div>
                                <span class="tt-star-shop">MommyKids</span>
                                <strong>MommyKids Store</strong>
                            </div>
                            <button type="button" class="tt-link-button" data-open-address>
                                Ghi chú <span>›</span>
                            </button>
                        </div>

                        <div class="tt-product-list">
                            @foreach ($items as $item)
                                @php
                                    $product = $item->product;
                                    $imageUrl = $product
                                        ? $resolveImage($product->image)
                                        : null;
                                @endphp
                                @if($product)
                                    <article class="tt-product">
                                        <div class="tt-product-image">
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
                                                <div class="tt-image-fallback" style="display:none;">🖼️</div>
                                            @else
                                                <div class="tt-image-fallback">🖼️</div>
                                            @endif
                                        </div>
                                        <div class="tt-product-info">
                                            <strong class="tt-product-name">{{ $product->name }}</strong>
                                            <span class="tt-product-meta">
                                                {{ number_format($product->price, 0, ',', '.') }}đ
                                                · Số lượng {{ $item->quantity }}
                                            </span>
                                            <strong class="tt-product-price">
                                                {{ number_format(
                                                    $product->price * $item->quantity,
                                                    0,
                                                    ',',
                                                    '.'
                                                ) }}đ
                                            </strong>
                                        </div>
                                        <span class="tt-product-qty">×{{ $item->quantity }}</span>
                                    </article>
                                @endif
                            @endforeach
                        </div>

                        <div class="tt-delivery-row">
                            <div>
                                <strong>Phương thức vận chuyển</strong>
                                <small>GHN · Phí được tính theo địa chỉ nhận hàng</small>
                            </div>
                            <div class="tt-delivery-price">
                                <strong data-checkout-shipping>Chưa tính</strong>
                                <span>›</span>
                            </div>
                        </div>
                    </section>

                    {{-- VOUCHER KIỂU TIKTOK --}}
                    <section class="tt-card tt-accordion-card">
                        <button type="button" class="tt-section-trigger" id="voucher-toggle">
                            <span class="tt-section-icon">🎟️</span>
                            <span class="tt-section-label">Giảm giá từ MommyKids</span>
                            <span class="tt-section-value" id="voucher-trigger-value">
                                Chọn voucher
                            </span>
                            <span class="tt-chevron">›</span>
                        </button>

                        <div class="tt-expand-panel" id="voucher-panel" hidden>
                            <div class="tt-voucher-panel-head">
                                <div>
                                    <strong>Voucher của bạn</strong>
                                    <small>Tối đa 1 mã đơn hàng và 1 mã vận chuyển</small>
                                </div>
                                <a href="{{ route('vouchers.index') }}">Xem thêm mã</a>
                            </div>

                            @guest
                                <div class="tt-voucher-login-note">
                                    Đăng nhập để xem và chọn voucher đã lưu trong ví.
                                </div>
                            @endguest

                            <div class="tt-voucher-grid">
                                <div class="tt-voucher-box" data-voucher-slot="order">
                                    <div class="tt-voucher-box-title">
                                        <span>🎫</span>
                                        <div>
                                            <strong>Voucher đơn hàng</strong>
                                            <small>Mã giảm trực tiếp trên tiền hàng</small>
                                        </div>
                                    </div>
                                    <div class="tt-voucher-controls">
                                        <select
                                            id="voucher-order-id"
                                            {{ auth()->guest() || $availableOrderVouchers->isEmpty() ? 'disabled' : '' }}
                                        >
                                            <option value="">
                                                @guest
                                                    -- Đăng nhập để chọn mã --
                                                @else
                                                    {{ $availableOrderVouchers->isEmpty()
                                                        ? '-- Chưa có mã đơn hàng phù hợp --'
                                                        : '-- Chọn mã đơn hàng --' }}
                                                @endguest
                                            </option>
                                            @foreach ($availableOrderVouchers as $voucher)
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
                                        >
                                            Áp dụng
                                        </button>
                                        <button
                                            type="button"
                                            class="tt-voucher-remove"
                                            data-voucher-remove="order"
                                            {{ empty($checkoutVouchers['order']) ? 'hidden' : '' }}
                                        >
                                            Gỡ
                                        </button>
                                    </div>
                                    <p class="tt-voucher-status" id="voucher-order-status">
                                        @if(!empty($checkoutVouchers['order']))
                                            Đang áp dụng: {{ $checkoutVouchers['order']['code'] }}
                                        @elseif(auth()->check() && $availableOrderVouchers->isEmpty())
                                            Bạn chưa có mã đơn hàng phù hợp với giỏ hàng này.
                                        @endif
                                    </p>
                                </div>

                                <div class="tt-voucher-box" data-voucher-slot="shipping">
                                    <div class="tt-voucher-box-title">
                                        <span>🚚</span>
                                        <div>
                                            <strong>Voucher vận chuyển</strong>
                                            <small>Mức giảm thực tế dựa trên phí GHN</small>
                                        </div>
                                    </div>
                                    <div class="tt-voucher-controls">
                                        <select
                                            id="voucher-shipping-id"
                                            {{ auth()->guest() || $availableShippingVouchers->isEmpty() ? 'disabled' : '' }}
                                        >
                                            <option value="">
                                                @guest
                                                    -- Đăng nhập để chọn mã --
                                                @else
                                                    {{ $availableShippingVouchers->isEmpty()
                                                        ? '-- Chưa có mã vận chuyển phù hợp --'
                                                        : '-- Chọn mã vận chuyển --' }}
                                                @endguest
                                            </option>
                                            @foreach ($availableShippingVouchers as $voucher)
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
                                        >
                                            Áp dụng
                                        </button>
                                        <button
                                            type="button"
                                            class="tt-voucher-remove"
                                            data-voucher-remove="shipping"
                                            {{ empty($checkoutVouchers['shipping']) ? 'hidden' : '' }}
                                        >
                                            Gỡ
                                        </button>
                                    </div>
                                    <p class="tt-voucher-status" id="voucher-shipping-status">
                                        @if(!empty($checkoutVouchers['shipping']))
                                            Đang áp dụng: {{ $checkoutVouchers['shipping']['code'] }}
                                        @elseif(auth()->check() && $availableShippingVouchers->isEmpty())
                                            Bạn chưa có mã vận chuyển phù hợp với giỏ hàng này.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- THANH TOÁN --}}
                    <section class="tt-card tt-payment-card">
                        <div class="tt-section-title">
                            <span class="tt-section-icon">💳</span>
                            <div>
                                <strong>Phương thức thanh toán</strong>
                                <small>Chọn cách thanh toán phù hợp</small>
                            </div>
                        </div>

                        <div class="tt-payment-list">
                            <label class="tt-payment-option">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                                >
                                <span class="tt-payment-icon">💵</span>
                                <span class="tt-payment-copy">
                                    <strong>Thanh toán khi nhận hàng</strong>
                                    <small>Thanh toán bằng tiền mặt khi nhận sản phẩm</small>
                                </span>
                                <span class="tt-radio-ui"></span>
                            </label>

                            <label class="tt-payment-option">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="bank"
                                    {{ old('payment_method') === 'bank' ? 'checked' : '' }}
                                >
                                <span class="tt-payment-icon">🏦</span>
                                <span class="tt-payment-copy">
                                    <strong>Chuyển khoản ngân hàng</strong>
                                    <small>Thanh toán qua tài khoản ngân hàng</small>
                                </span>
                                <span class="tt-radio-ui"></span>
                            </label>

                            @if(config('services.zalopay.enabled'))
                                <label class="tt-payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="zalopay"
                                        {{ old('payment_method') === 'zalopay' ? 'checked' : '' }}
                                    >
                                    <span class="tt-payment-icon">💙</span>
                                    <span class="tt-payment-copy">
                                        <strong>ZaloPay</strong>
                                        <small>Thanh toán an toàn qua ZaloPay Sandbox</small>
                                    </span>
                                    <span class="tt-radio-ui"></span>
                                </label>
                            @endif

                            @if(config('services.stripe.enabled'))
                                <label class="tt-payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="stripe"
                                        {{ old('payment_method') === 'stripe' ? 'checked' : '' }}
                                    >
                                    <span class="tt-payment-icon">💳</span>
                                    <span class="tt-payment-copy">
                                        <strong>Stripe - Visa / Mastercard</strong>
                                        <small>Thanh toán bằng thẻ quốc tế qua Stripe Sandbox</small>
                                    </span>
                                    <span class="tt-radio-ui"></span>
                                </label>
                            @endif

                            @if(config('services.paypal.enabled'))
                                <label class="tt-payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="paypal"
                                        {{ old('payment_method') === 'paypal' ? 'checked' : '' }}
                                    >
                                    <span class="tt-payment-icon">🅿️</span>
                                    <span class="tt-payment-copy">
                                        <strong>PayPal</strong>
                                        <small>Thanh toán qua PayPal Sandbox</small>
                                    </span>
                                    <span class="tt-radio-ui"></span>
                                </label>
                            @endif
                        </div>
                    </section>
                </main>

                {{-- TÓM TẮT --}}
                <aside class="tt-summary-card">
                    <h2>Tóm tắt đơn hàng</h2>

                    <div class="tt-summary-row">
                        <span>Tổng phụ sản phẩm</span>
                        <strong id="checkout-subtotal">{{ number_format($subtotal, 0, ',', '.') }}đ</strong>
                    </div>

                    <div
                        class="tt-summary-row tt-discount-row"
                        id="checkout-order-voucher-row"
                        {{ ($voucherBreakdown['order_discount'] ?? 0) <= 0 ? 'hidden' : '' }}
                    >
                        <span>Giảm giá sản phẩm</span>
                        <strong id="checkout-order-voucher">
                            -{{ number_format($voucherBreakdown['order_discount'] ?? 0, 0, ',', '.') }}đ
                        </strong>
                    </div>

                    <div class="tt-summary-row">
                        <span>Tổng phụ vận chuyển</span>
                        <strong id="checkout-shipping">Chưa tính</strong>
                    </div>

                    <div
                        class="tt-summary-row tt-discount-row"
                        id="checkout-shipping-voucher-row"
                        {{ ($voucherBreakdown['shipping_discount'] ?? 0) <= 0 ? 'hidden' : '' }}
                    >
                        <span>Giảm giá vận chuyển</span>
                        <strong id="checkout-shipping-voucher">
                            -{{ number_format($voucherBreakdown['shipping_discount'] ?? 0, 0, ',', '.') }}đ
                        </strong>
                    </div>

                    <div
                        class="tt-summary-row tt-discount-row"
                        id="checkout-points-row"
                        {{ $pointsDiscount <= 0 ? 'hidden' : '' }}
                    >
                        <span>Điểm tích lũy</span>
                        <strong id="checkout-points-discount">
                            -{{ number_format($pointsDiscount, 0, ',', '.') }}đ
                        </strong>
                    </div>

                    <div class="tt-summary-divider"></div>

                    <div class="tt-summary-total">
                        <span>Tổng</span>
                        <strong id="checkout-total">{{ number_format($total, 0, ',', '.') }}đ</strong>
                    </div>

                    <button class="tt-primary" type="submit">
                        Đặt hàng
                    </button>

                    <p class="tt-submit-note">
                        Bằng việc đặt hàng, bạn đồng ý với điều khoản mua hàng của MommyKids.
                    </p>
                </aside>
            </div>

            {{-- MOBILE STICKY BAR --}}
            <div class="tt-mobile-bar">
                <div>
                    <small>Tổng</small>
                    <strong id="checkout-total-mobile">{{ number_format($total, 0, ',', '.') }}đ</strong>
                </div>
                <button type="submit">Đặt hàng</button>
            </div>

            {{-- ADDRESS DRAWER / MODAL --}}
            <div class="tt-address-overlay" id="address-editor" hidden>
                <div class="tt-address-sheet" role="dialog" aria-modal="true" aria-labelledby="address-editor-title">
                    <div class="tt-address-sheet-head">
                        <button type="button" class="tt-sheet-back" data-close-address>‹</button>
                        <h2 id="address-editor-title">Địa chỉ của bạn</h2>
                        <span></span>
                    </div>

                    <div class="tt-address-sheet-body">
                        @auth
                            <div class="tt-saved-addresses">
                                <div class="tt-saved-addresses-head">
                                    <div>
                                        <strong>Địa chỉ đã lưu</strong>
                                        <small>Chọn một địa chỉ để dùng ngay cho đơn hàng này</small>
                                    </div>
                                    <a href="{{ url('/ho-so/dia-chi') }}">＋ Thêm địa chỉ</a>
                                </div>
                                @forelse($savedAddresses as $savedAddress)
                                    <label class="tt-saved-address-item">
                                        <input
                                            type="radio"
                                            name="checkout_saved_address"
                                            value="{{ $savedAddress->id }}"
                                            data-saved-address
                                            data-recipient-name="{{ $savedAddress->recipient_name }}"
                                            data-phone="{{ $savedAddress->phone }}"
                                            data-province-id="{{ $savedAddress->province_id }}"
                                            data-district-id="{{ $savedAddress->district_id }}"
                                            data-ward-code="{{ $savedAddress->ward_code }}"
                                            data-address-detail="{{ $savedAddress->address_detail }}"
                                            {{ $defaultAddress?->id === $savedAddress->id ? 'checked' : '' }}
                                        >
                                        <span class="tt-saved-radio-ui"></span>
                                        <span class="tt-saved-address-copy">
                                            <span class="tt-saved-name-row">
                                                <strong>{{ $savedAddress->recipient_name }}</strong>
                                                <span>{{ $savedAddress->phone }}</span>
                                            </span>
                                            <span class="tt-saved-address-line">
                                                {{ $savedAddress->full_address }}
                                            </span>
                                            <span class="tt-saved-tags">
                                                @if($savedAddress->is_default)
                                                    <em>Mặc định</em>
                                                @endif
                                                @if($savedAddress->label)
                                                    <em>{{ $savedAddress->label }}</em>
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                @empty
                                    <div class="tt-saved-empty">
                                        Bạn chưa có địa chỉ nào trong kho.
                                        Hãy thêm địa chỉ để lần sau chỉ cần chọn.
                                    </div>
                                @endforelse

                                @if($savedAddresses->isNotEmpty())
                                    <button
                                        type="button"
                                        class="tt-use-saved-address"
                                        id="use-saved-address"
                                    >
                                        Dùng địa chỉ đã chọn
                                    </button>
                                @endif
                            </div>
                            <div class="tt-address-divider">
                                <span>hoặc nhập địa chỉ khác</span>
                            </div>
                        @endauth

                        <div class="tt-address-form-title">
                            <span>＋</span>
                            <strong>Thông tin nhận hàng</strong>
                        </div>

                        <div class="tt-form-grid">
                            <label class="tt-field tt-full">
                                <span>Họ và tên <b>*</b></span>
                                <input
                                    id="full_name"
                                    name="full_name"
                                    value="{{ $initialFullName }}"
                                    placeholder="Nguyễn Văn An"
                                    required
                                >
                            </label>

                            <label class="tt-field">
                                <span>Số điện thoại <b>*</b></span>
                                <input
                                    id="phone"
                                    name="phone"
                                    value="{{ $initialPhone }}"
                                    placeholder="0901234567"
                                    required
                                >
                            </label>

                            <label class="tt-field">
                                <span>Email</span>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ $initialEmail }}"
                                    placeholder="an@example.com"
                                >
                            </label>

                            <label class="tt-field">
                                <span>Tỉnh / Thành phố <b>*</b></span>
                                <select id="province" name="province_id" required>
                                    <option value="">-- Chọn tỉnh/thành --</option>
                                    @foreach ($provinces as $province)
                                        <option
                                            value="{{ $province['ProvinceID'] }}"
                                            {{ (string) $initialProvinceId === (string) $province['ProvinceID'] ? 'selected' : '' }}
                                        >
                                            {{ $province['ProvinceName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="tt-field">
                                <span>Quận / Huyện <b>*</b></span>
                                <select id="district" name="to_district_id" required disabled>
                                    <option value="">-- Chọn quận/huyện --</option>
                                </select>
                            </label>

                            <label class="tt-field tt-full">
                                <span>Phường / Xã <b>*</b></span>
                                <select id="ward" name="to_ward_code" required disabled>
                                    <option value="">-- Chọn phường/xã --</option>
                                </select>
                            </label>

                            <label class="tt-field tt-full">
                                <span>Địa chỉ chi tiết <b>*</b></span>
                                <input
                                    id="address"
                                    name="address"
                                    value="{{ $initialAddress }}"
                                    placeholder="Số nhà, tên đường..."
                                    required
                                >
                            </label>

                            <label class="tt-field tt-full">
                                <span>Ghi chú</span>
                                <textarea
                                    id="note"
                                    name="note"
                                    rows="3"
                                    placeholder="Giao hàng giờ hành chính"
                                >{{ old('note') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="tt-address-sheet-footer">
                        <button type="button" class="tt-address-save" id="save-address">
                            Xác nhận địa chỉ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
:root{
    --tt-pink:#ff2f55;
    --tt-pink-dark:#ed1745;
    --tt-text:#111827;
    --tt-muted:#6b7280;
    --tt-line:#eceff3;
    --tt-soft:#f7f8fa;
    --tt-page:#f5f5f5;
    --tt-blue-soft:#effcff;
    --tt-green:#149447;
}

.tt-checkout-page{min-height:100vh;background:var(--tt-page);padding:24px 14px 90px;color:var(--tt-text)}
.tt-checkout-wrap{width:min(1180px,100%);margin:0 auto}
.tt-page-head{display:flex;align-items:center;gap:14px;margin-bottom:18px}
.tt-page-head h1{margin:0;font-size:30px;line-height:1.15;font-weight:800}
.tt-page-head p{margin:6px 0 0;color:var(--tt-muted);font-size:14px}
.tt-back{display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:#fff;color:#111;text-decoration:none;font-size:35px;line-height:1;box-shadow:0 1px 5px rgba(0,0,0,.08)}
.tt-layout{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(320px,.7fr);gap:18px;align-items:start}
.tt-main{display:flex;flex-direction:column;gap:12px;min-width:0}
.tt-card,.tt-summary-card{background:#fff;border-radius:14px;border:1px solid #eee;box-shadow:0 1px 2px rgba(0,0,0,.03)}

.tt-address-card{overflow:hidden}
.tt-address-summary{width:100%;border:0;background:#fff;padding:20px 22px;display:grid;grid-template-columns:34px 1fr 22px;gap:12px;align-items:center;text-align:left;cursor:pointer}
.tt-address-icon{font-size:28px;color:#111;align-self:start}
.tt-address-copy{min-width:0;display:flex;flex-direction:column;gap:5px}
.tt-address-title-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:16px}
.tt-address-title-row strong{font-size:18px}
.tt-address-title-row span{color:#333}
.tt-address-line{display:block;color:#333;font-size:15px;line-height:1.5;word-break:break-word}
.tt-address-helper{font-size:12px;color:#9ca3af}
.tt-chevron{font-size:30px;color:#9ca3af;line-height:1}
.tt-airmail{height:4px;background:repeating-linear-gradient(135deg,#ff4665 0 22px,#fff 22px 34px,#27c3d7 34px 56px,#fff 56px 68px)}

.tt-shop-card{padding:0 0 14px;overflow:hidden}
.tt-shop-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 20px 12px;border-bottom:1px solid #f1f1f1}
.tt-shop-head>div{display:flex;align-items:center;gap:8px;min-width:0}
.tt-shop-head strong{font-size:17px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tt-star-shop{font-size:12px;font-weight:800;background:#ffe6b5;color:#37250a;border-radius:4px;padding:3px 7px}
.tt-link-button{border:0;background:transparent;color:#737373;font:inherit;cursor:pointer;white-space:nowrap}
.tt-link-button span{font-size:22px;margin-left:4px}
.tt-product-list{padding:0 20px}
.tt-product{display:grid;grid-template-columns:92px minmax(0,1fr) auto;gap:14px;align-items:center;padding:16px 0;border-bottom:1px solid #f5f5f5}
.tt-product:last-child{border-bottom:0}
.tt-product-image{width:92px;height:92px;border-radius:8px;overflow:hidden;background:#f6f6f6;position:relative}
.tt-product-image img{width:100%;height:100%;object-fit:contain;background:#fff}
.tt-image-fallback{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:26px;background:#f6f6f6}
.tt-product-info{min-width:0}
.tt-product-name{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:15px;line-height:1.45}
.tt-product-meta{display:block;margin-top:6px;color:#737373;font-size:13px}
.tt-product-price{display:block;margin-top:10px;color:#e11d48;font-size:18px}
.tt-product-qty{align-self:end;color:#666;font-size:14px;padding-bottom:4px}
.tt-delivery-row{margin:4px 20px 0;background:var(--tt-blue-soft);border-radius:10px;padding:14px 16px;display:flex;justify-content:space-between;gap:16px;align-items:center}
.tt-delivery-row strong{display:block}
.tt-delivery-row small{display:block;color:#6b7280;margin-top:4px}
.tt-delivery-price{display:flex;align-items:center;gap:8px;white-space:nowrap}
.tt-delivery-price>span{font-size:22px;color:#9ca3af}

.tt-accordion-card{overflow:hidden}
.tt-section-trigger{width:100%;border:0;background:#fff;padding:18px 20px;display:grid;grid-template-columns:30px 1fr auto 18px;gap:8px;align-items:center;text-align:left;cursor:pointer}
.tt-section-icon{font-size:21px}
.tt-section-label{font-size:17px;font-weight:800}
.tt-section-value{color:var(--tt-pink);font-size:13px;white-space:nowrap}
.tt-expand-panel{border-top:1px solid #f0f0f0;padding:18px 20px 20px}
.tt-voucher-panel-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin-bottom:14px}
.tt-voucher-panel-head strong{display:block}
.tt-voucher-panel-head small{display:block;color:var(--tt-muted);margin-top:3px}
.tt-voucher-panel-head a{color:var(--tt-pink);font-weight:700;font-size:13px;text-decoration:none}
.tt-voucher-grid{display:grid;gap:12px}
.tt-voucher-box{border:1px solid #ececec;border-radius:10px;padding:14px;background:#fff}
.tt-voucher-box-title{display:flex;gap:10px;align-items:flex-start}
.tt-voucher-box-title>span{font-size:22px}
.tt-voucher-box-title strong{display:block}
.tt-voucher-box-title small{display:block;color:var(--tt-muted);margin-top:3px}
.tt-voucher-controls{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:8px;margin-top:12px}
.tt-voucher-controls select{min-width:0;width:100%;border:1px solid #ddd;border-radius:8px;padding:11px 36px 11px 11px;background:#fff;font:inherit;outline:none}
.tt-voucher-controls button{border:0;border-radius:8px;background:var(--tt-pink);color:#fff;font-weight:700;padding:0 14px;cursor:pointer}
.tt-voucher-controls button:disabled{opacity:.55;cursor:not-allowed}
.tt-voucher-controls .tt-voucher-remove{background:#f2f3f5;color:#4b5563}
.tt-voucher-status{min-height:18px;margin:8px 0 0;font-size:13px;color:var(--tt-green)}
.tt-voucher-status.is-error{color:#be123c}
.tt-voucher-login-note{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px}

.tt-payment-card{padding:18px 20px}
.tt-section-title{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.tt-section-title>div{display:flex;flex-direction:column}
.tt-section-title strong{font-size:17px}
.tt-section-title small{color:var(--tt-muted);margin-top:3px}
.tt-payment-list{display:grid;gap:0}
.tt-payment-option{display:grid;grid-template-columns:38px minmax(0,1fr) 24px;gap:12px;align-items:center;padding:15px 0;border-bottom:1px solid #f1f1f1;cursor:pointer}
.tt-payment-option:last-child{border-bottom:0}
.tt-payment-option input{position:absolute;opacity:0;pointer-events:none}
.tt-payment-icon{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;background:#f5f5f5}
.tt-payment-copy strong{display:block;font-size:15px}
.tt-payment-copy small{display:block;color:var(--tt-muted);margin-top:3px;line-height:1.35}
.tt-radio-ui{width:20px;height:20px;border:2px solid #c9cdd4;border-radius:50%;position:relative}
.tt-payment-option:has(input:checked) .tt-radio-ui{border-color:var(--tt-pink)}
.tt-payment-option:has(input:checked) .tt-radio-ui:after{content:"";position:absolute;inset:3px;border-radius:50%;background:var(--tt-pink)}
.tt-payment-option:has(input:checked) .tt-payment-copy strong{color:#111}

.tt-summary-card{position:sticky;top:92px;padding:22px}
.tt-summary-card h2{margin:0 0 20px;font-size:22px}
.tt-summary-row{display:flex;justify-content:space-between;gap:20px;align-items:flex-start;margin:14px 0;font-size:14px}
.tt-summary-row span{color:#303030}
.tt-summary-row strong{font-weight:600;text-align:right}
.tt-discount-row strong{color:#e11d48}
.tt-summary-divider{height:1px;background:#eee;margin:18px 0}
.tt-summary-total{display:flex;justify-content:space-between;gap:18px;align-items:center;font-size:22px;font-weight:800}
.tt-summary-total strong{color:var(--tt-pink);font-size:28px}
.tt-primary{width:100%;border:0;border-radius:999px;background:var(--tt-pink);color:#fff;font-weight:800;font-size:16px;padding:15px 20px;margin-top:22px;cursor:pointer;transition:.2s}
.tt-primary:hover{background:var(--tt-pink-dark)}
.tt-submit-note{margin:10px 8px 0;color:#9ca3af;font-size:11px;text-align:center;line-height:1.4}

.tt-alert{padding:13px 15px;border-radius:10px;margin-bottom:14px}
.tt-alert-error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}
.tt-alert ul{margin:8px 0 0 18px}

.tt-address-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;display:flex;align-items:flex-end;justify-content:center;padding:18px}
.tt-address-overlay[hidden]{display:none}
.tt-address-sheet{width:min(720px,100%);max-height:92vh;background:#fff;border-radius:18px 18px 12px 12px;display:flex;flex-direction:column;box-shadow:0 20px 70px rgba(0,0,0,.25);overflow:hidden}
.tt-address-sheet-head{display:grid;grid-template-columns:42px 1fr 42px;align-items:center;padding:14px 18px;border-bottom:1px solid #eee}
.tt-address-sheet-head h2{margin:0;text-align:center;font-size:20px}
.tt-sheet-back{border:0;background:transparent;font-size:34px;cursor:pointer;line-height:1}
.tt-address-sheet-body{overflow:auto;padding:20px}
.tt-address-form-title{display:flex;gap:10px;align-items:center;font-size:17px;margin-bottom:18px}
.tt-address-form-title>span{font-size:28px;color:#777}
.tt-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.tt-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.tt-field>span{font-weight:700;font-size:14px}
.tt-field b{color:var(--tt-pink)}
.tt-full{grid-column:1/-1}
.tt-field input,.tt-field textarea,.tt-field select{width:100%;min-width:0;box-sizing:border-box;border:1px solid #ddd;border-radius:10px;background:#fff;padding:13px 14px;font:inherit;outline:none}
.tt-field input:focus,.tt-field textarea:focus,.tt-field select:focus{border-color:var(--tt-pink);box-shadow:0 0 0 3px rgba(255,47,85,.08)}
.tt-field select:disabled{background:#f5f5f5;color:#a0a0a0}
.tt-address-sheet-footer{border-top:1px solid #eee;padding:14px 20px;background:#fff}
.tt-address-save{width:100%;border:0;border-radius:999px;background:var(--tt-pink);color:#fff;font-weight:800;font-size:16px;padding:14px;cursor:pointer}

/* Kho địa chỉ */
.tt-saved-addresses{border:1px solid #ececec;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:18px}
.tt-saved-addresses-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:15px 16px;border-bottom:1px solid #f0f0f0;background:#fafafa}
.tt-saved-addresses-head strong{display:block;font-size:16px}
.tt-saved-addresses-head small{display:block;color:var(--tt-muted);margin-top:3px;font-size:12px}
.tt-saved-addresses-head a{color:var(--tt-pink);font-weight:800;font-size:13px;text-decoration:none;white-space:nowrap}
.tt-saved-address-item{display:grid;grid-template-columns:22px minmax(0,1fr);gap:12px;align-items:flex-start;padding:15px 16px;border-bottom:1px solid #f1f1f1;cursor:pointer;position:relative}
.tt-saved-address-item:last-of-type{border-bottom:0}
.tt-saved-address-item>input{position:absolute;opacity:0;pointer-events:none}
.tt-saved-radio-ui{width:18px;height:18px;border:2px solid #c7cbd1;border-radius:50%;margin-top:2px;position:relative}
.tt-saved-address-item:has(input:checked){background:#fff7f9}
.tt-saved-address-item:has(input:checked) .tt-saved-radio-ui{border-color:var(--tt-pink)}
.tt-saved-address-item:has(input:checked) .tt-saved-radio-ui:after{content:"";position:absolute;inset:3px;border-radius:50%;background:var(--tt-pink)}
.tt-saved-address-copy{display:flex;flex-direction:column;gap:5px;min-width:0}
.tt-saved-name-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.tt-saved-name-row strong{font-size:15px}
.tt-saved-name-row span{color:#555;font-size:14px}
.tt-saved-address-line{color:#555;line-height:1.45;font-size:14px}
.tt-saved-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:2px}
.tt-saved-tags em{font-style:normal;font-size:11px;border:1px solid #ffd0d9;color:var(--tt-pink);border-radius:4px;padding:2px 6px;background:#fff}
.tt-saved-empty{padding:20px 16px;color:var(--tt-muted);font-size:14px;line-height:1.5}
.tt-use-saved-address{display:block;width:calc(100% - 32px);margin:12px 16px 16px;border:0;border-radius:999px;background:var(--tt-pink);color:#fff;font-weight:800;padding:12px 16px;cursor:pointer}
.tt-address-divider{display:flex;align-items:center;gap:12px;color:#9ca3af;font-size:12px;margin:4px 0 18px}
.tt-address-divider:before,.tt-address-divider:after{content:"";height:1px;background:#eee;flex:1}

.tt-mobile-bar{display:none}

@media(max-width:900px){
    .tt-checkout-page{padding:0 0 92px;background:#f4f4f4}
    .tt-checkout-wrap{max-width:none}
    .tt-page-head{background:#fff;padding:14px 16px;margin:0;border-bottom:1px solid #eee}
    .tt-page-head h1{font-size:21px}
    .tt-page-head p{display:none}
    .tt-back{box-shadow:none;width:36px;height:36px;background:transparent}
    .tt-layout{display:block}
    .tt-main{gap:10px}
    .tt-card{border-radius:0;border-left:0;border-right:0}
    .tt-summary-card{position:static;border-radius:0;border-left:0;border-right:0;margin-top:10px;padding:20px 18px 110px}
    .tt-summary-card .tt-primary,.tt-summary-card .tt-submit-note{display:none}
    .tt-mobile-bar{position:fixed;left:0;right:0;bottom:0;z-index:9000;display:flex;align-items:center;justify-content:space-between;gap:16px;background:#fff;border-top:1px solid #e9e9e9;padding:10px 14px calc(10px + env(safe-area-inset-bottom));box-shadow:0 -6px 20px rgba(0,0,0,.06)}
    .tt-mobile-bar>div{display:flex;flex-direction:column;min-width:0}
    .tt-mobile-bar small{font-size:12px;color:#555}
    .tt-mobile-bar strong{font-size:22px;color:var(--tt-pink);white-space:nowrap}
    .tt-mobile-bar button{border:0;border-radius:999px;background:var(--tt-pink);color:#fff;font-weight:800;font-size:16px;padding:13px 34px;cursor:pointer}
    .tt-address-overlay{padding:0;align-items:stretch}
    .tt-address-sheet{width:100%;max-height:none;height:100%;border-radius:0}
}

@media(max-width:600px){
    .tt-address-summary{padding:17px 16px;grid-template-columns:30px 1fr 18px}
    .tt-address-title-row strong{font-size:16px}
    .tt-address-line{font-size:14px}
    .tt-shop-head{padding:15px 16px 10px}
    .tt-product-list{padding:0 16px}
    .tt-product{grid-template-columns:78px minmax(0,1fr) auto;gap:11px}
    .tt-product-image{width:78px;height:78px}
    .tt-product-price{font-size:17px}
    .tt-delivery-row{margin:4px 16px 0;padding:13px 14px}
    .tt-delivery-row small{font-size:11px}
    .tt-section-trigger{padding:17px 16px;grid-template-columns:28px 1fr auto 16px}
    .tt-section-label{font-size:16px}
    .tt-expand-panel{padding:16px}
    .tt-voucher-controls{grid-template-columns:1fr 1fr}
    .tt-voucher-controls select{grid-column:1/-1}
    .tt-payment-card{padding:16px}
    .tt-summary-total{font-size:20px}
    .tt-summary-total strong{font-size:25px}
    .tt-form-grid{grid-template-columns:1fr}
    .tt-full{grid-column:auto}
}
</style>

<script type="application/json" id="checkout-config">
{
    "subtotal": {{ (int) $subtotal }},
    "orderVoucherDiscount": {{ (int) ($voucherBreakdown['order_discount'] ?? 0) }},
    "shippingVoucherDiscount": {{ (int) ($voucherBreakdown['shipping_discount'] ?? 0) }},
    "pointsDiscount": {{ (int) $pointsDiscount }},
    "oldProvinceId": @json($initialProvinceId),
    "oldDistrictId": @json($initialDistrictId),
    "oldWardCode": @json($initialWardCode),
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

    const fullName = document.getElementById('full_name');
    const phone = document.getElementById('phone');
    const address = document.getElementById('address');

    const shippingText = document.getElementById('checkout-shipping');
    const shippingMirrors = document.querySelectorAll('[data-checkout-shipping]');
    const totalText = document.getElementById('checkout-total');
    const totalMobile = document.getElementById('checkout-total-mobile');

    const orderVoucherRow = document.getElementById('checkout-order-voucher-row');
    const orderVoucherText = document.getElementById('checkout-order-voucher');
    const shippingVoucherRow = document.getElementById('checkout-shipping-voucher-row');
    const shippingVoucherText = document.getElementById('checkout-shipping-voucher');
    const pointsRow = document.getElementById('checkout-points-row');
    const pointsText = document.getElementById('checkout-points-discount');

    const voucherToggle = document.getElementById('voucher-toggle');
    const voucherPanel = document.getElementById('voucher-panel');
    const voucherTriggerValue = document.getElementById('voucher-trigger-value');

    const addressEditor = document.getElementById('address-editor');
    const addressSummaryName = document.getElementById('address-summary-name');
    const addressSummaryPhone = document.getElementById('address-summary-phone');
    const addressSummaryLine = document.getElementById('address-summary-line');
    const saveAddressButton = document.getElementById('save-address');
    const useSavedAddressButton = document.getElementById('use-saved-address');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const pricing = {
        subtotal: Number(config.subtotal) || 0,
        shippingFee: 0,
        orderVoucherDiscount: Number(config.orderVoucherDiscount) || 0,
        shippingVoucherDiscount: Number(config.shippingVoucherDiscount) || 0,
        pointsDiscount: Number(config.pointsDiscount) || 0,
    };

    const formatMoney = (amount) =>
        Number(amount || 0).toLocaleString('vi-VN') + 'đ';

    function maskPhone(value) {
        const raw = String(value || '').trim();

        if (raw.length < 7) {
            return raw;
        }

        return raw.slice(0, 3) + '****' + raw.slice(-3);
    }

    function selectedText(select) {
        if (!select || !select.value) {
            return '';
        }

        return select.options[select.selectedIndex]?.text?.trim() || '';
    }

    function updateAddressSummary() {
        const name = fullName?.value?.trim() || '';
        const phoneValue = phone?.value?.trim() || '';
        const detail = address?.value?.trim() || '';
        const wardText = selectedText(ward);
        const districtText = selectedText(district);
        const provinceText = selectedText(province);

        addressSummaryName.textContent = name || 'Địa chỉ giao hàng';
        addressSummaryPhone.textContent = phoneValue ? maskPhone(phoneValue) : '';

        const parts = [detail, wardText, districtText, provinceText]
            .filter(Boolean);

        addressSummaryLine.textContent = parts.length
            ? parts.join(', ')
            : 'Thêm địa chỉ nhận hàng';
    }

    function openAddressEditor() {
        addressEditor.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeAddressEditor() {
        addressEditor.hidden = true;
        document.body.style.overflow = '';
    }

    function validateAddressBeforeClose() {
        const requiredFields = [
            fullName,
            phone,
            province,
            district,
            ward,
            address,
        ];

        for (const field of requiredFields) {
            if (!field || !field.value) {
                field?.reportValidity?.();
                field?.focus?.();
                return false;
            }
        }

        return true;
    }

    async function useSelectedSavedAddress() {
        const selected = document.querySelector('[data-saved-address]:checked');

        if (!selected) {
            return;
        }

        fullName.value = selected.dataset.recipientName || '';
        phone.value = selected.dataset.phone || '';
        address.value = selected.dataset.addressDetail || '';

        const provinceId = selected.dataset.provinceId || '';
        const districtId = selected.dataset.districtId || '';
        const wardCode = selected.dataset.wardCode || '';

        province.value = provinceId;

        if (provinceId) {
            await loadDistricts(provinceId, districtId);
        }

        if (districtId) {
            await loadWards(districtId, wardCode);
        }

        if (districtId && wardCode) {
            await calculateShippingFee();
        }

        updateAddressSummary();
        closeAddressEditor();
    }

    useSavedAddressButton?.addEventListener('click', useSelectedSavedAddress);

    document.querySelectorAll('[data-open-address]').forEach(button => {
        button.addEventListener('click', openAddressEditor);
    });

    document.querySelectorAll('[data-close-address]').forEach(button => {
        button.addEventListener('click', closeAddressEditor);
    });

    saveAddressButton?.addEventListener('click', () => {
        if (!validateAddressBeforeClose()) {
            return;
        }

        updateAddressSummary();
        closeAddressEditor();
    });

    addressEditor?.addEventListener('click', event => {
        if (event.target === addressEditor) {
            closeAddressEditor();
        }
    });

    voucherToggle?.addEventListener('click', () => {
        voucherPanel.hidden = !voucherPanel.hidden;
    });

    function updateVoucherTrigger() {
        const totalVoucher =
            pricing.orderVoucherDiscount + pricing.shippingVoucherDiscount;

        voucherTriggerValue.textContent = totalVoucher > 0
            ? '-' + formatMoney(totalVoucher)
            : 'Chọn voucher';
    }

    function renderPricing(serverTotal = null) {
        const shippingLabel = pricing.shippingFee > 0
            ? formatMoney(pricing.shippingFee)
            : 'Chưa tính';

        shippingText.textContent = shippingLabel;
        shippingMirrors.forEach(element => {
            element.textContent = shippingLabel;
        });

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

        const totalLabel = formatMoney(total);
        totalText.textContent = totalLabel;

        if (totalMobile) {
            totalMobile.textContent = totalLabel;
        }

        updateVoucherTrigger();
    }

    function applyPricingPayload(data) {
        if (!data) {
            return;
        }

        pricing.shippingFee = Number(
            data.shipping_fee ?? pricing.shippingFee
        ) || 0;

        pricing.orderVoucherDiscount = Number(
            data.order_voucher_discount ?? pricing.orderVoucherDiscount
        ) || 0;

        pricing.shippingVoucherDiscount = Number(
            data.shipping_voucher_discount ?? pricing.shippingVoucherDiscount
        ) || 0;

        pricing.pointsDiscount = Number(
            data.points_discount ?? pricing.pointsDiscount
        ) || 0;

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
            updateAddressSummary();
            return;
        }

        try {
            const response = await fetch(
                `${config.routes.districts}?province_id=${provinceId}`,
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Không tải được quận/huyện.');
            }

            const data = await response.json();
            district.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';

            data.forEach(item => {
                const isSelected = selectedDistrictId
                    && String(selectedDistrictId) === String(item.DistrictID)
                    ? 'selected'
                    : '';

                district.innerHTML += `
                    <option value="${item.DistrictID}" ${isSelected}>
                        ${item.DistrictName}
                    </option>
                `;
            });

            district.disabled = false;
            updateAddressSummary();
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
            updateAddressSummary();
            return;
        }

        try {
            const response = await fetch(
                `${config.routes.wards}?district_id=${districtId}`,
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                }
            );

            if (!response.ok) {
                throw new Error('Không tải được phường/xã.');
            }

            const data = await response.json();
            ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';

            data.forEach(item => {
                const isSelected = selectedWardCode
                    && String(selectedWardCode) === String(item.WardCode)
                    ? 'selected'
                    : '';

                ward.innerHTML += `
                    <option value="${item.WardCode}" ${isSelected}>
                        ${item.WardName}
                    </option>
                `;
            });

            ward.disabled = false;
            updateAddressSummary();
        } catch (error) {
            ward.innerHTML = '<option value="">Không tải được phường/xã</option>';
            console.error(error);
        }
    }

    async function calculateShippingFee() {
        if (!district.value || !ward.value) {
            return;
        }

        shippingText.textContent = 'Đang tính...';
        shippingMirrors.forEach(element => {
            element.textContent = 'Đang tính...';
        });

        try {
            const response = await fetch(config.routes.shippingFee, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({
                    district_id: Number(district.value),
                    ward_code: ward.value,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                const message = data.message ?? 'Không tính được phí';
                shippingText.textContent = message;
                shippingMirrors.forEach(element => {
                    element.textContent = message;
                });
                console.error(data);
                return;
            }

            applyPricingPayload(data);
        } catch (error) {
            shippingText.textContent = 'Không tính được phí';
            shippingMirrors.forEach(element => {
                element.textContent = 'Không tính được phí';
            });
            console.error(error);
        }
    }

    async function applyVoucher(type) {
        const select = document.getElementById(`voucher-${type}-id`);
        const status = document.getElementById(`voucher-${type}-status`);
        const button = document.querySelector(
            `[data-voucher-apply="${type}"]`
        );
        const removeButton = document.querySelector(
            `[data-voucher-remove="${type}"]`
        );
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
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({
                    type,
                    voucher_id: voucherId,
                }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message ?? 'Không thể áp dụng mã ưu đãi.'
                );
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
        const removeButton = document.querySelector(
            `[data-voucher-remove="${type}"]`
        );

        removeButton.disabled = true;
        status.classList.remove('is-error');

        try {
            const response = await fetch(config.routes.voucherRemove, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({ type }),
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message ?? 'Không thể gỡ mã ưu đãi.'
                );
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
        button.addEventListener('click', () => {
            applyVoucher(button.dataset.voucherApply);
        });
    });

    document.querySelectorAll('[data-voucher-remove]').forEach(button => {
        button.addEventListener('click', () => {
            removeVoucher(button.dataset.voucherRemove);
        });
    });

    fullName?.addEventListener('input', updateAddressSummary);
    phone?.addEventListener('input', updateAddressSummary);
    address?.addEventListener('input', updateAddressSummary);

    province.addEventListener('change', async () => {
        await loadDistricts(province.value);
        updateAddressSummary();
    });

    district.addEventListener('change', async () => {
        await loadWards(district.value);
        updateAddressSummary();
    });

    ward.addEventListener('change', async () => {
        updateAddressSummary();
        await calculateShippingFee();
    });

    async function restoreOldState() {
        if (config.oldProvinceId) {
            await loadDistricts(
                config.oldProvinceId,
                config.oldDistrictId
            );

            if (config.oldDistrictId) {
                await loadWards(
                    config.oldDistrictId,
                    config.oldWardCode
                );

                if (config.oldWardCode) {
                    await calculateShippingFee();
                }
            }
        }

        updateAddressSummary();
    }

    renderPricing();
    restoreOldState();

    @if($errors->any())
        openAddressEditor();
    @endif
});
</script>
@endsection