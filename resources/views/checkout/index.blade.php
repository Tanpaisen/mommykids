@extends('client.layouts.app')

@section('title', 'Thanh toán - MommyKids')

@section('content')
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

        <form method="POST" action="{{ route('checkout.store') }}">
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
                    </section>
                </div>

                <aside class="mk-card mk-summary">
                    <h2>Đơn hàng của bạn</h2>

                    @foreach($items as $item)
                        <div class="mk-product">
                            <img src="{{ $item->product->image ?: 'https://via.placeholder.com/80' }}" alt="{{ $item->product->name }}">
                            <div>
                                <strong>{{ $item->product->name }}</strong>
                                <small>{{ number_format($item->product->price) }}đ × {{ $item->quantity }}</small>
                            </div>
                            <b>{{ number_format($item->product->price * $item->quantity) }}đ</b>
                        </div>
                    @endforeach

                    <hr>

                    <div class="mk-row">
                        <span>Tạm tính</span>
                        <strong id="checkout-subtotal">{{ number_format($subtotal) }}đ</strong>
                    </div>

                    <div class="mk-row">
                        <span>Phí vận chuyển</span>
                        <strong id="checkout-shipping">Chưa tính</strong>
                    </div>

                    <hr>

                    <div class="mk-row mk-total">
                        <span>Tổng thanh toán</span>
                        <strong id="checkout-total">{{ number_format($subtotal) }}đ</strong>
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
.mk-product img{width:64px;height:64px;border-radius:12px;object-fit:cover;background:#fff0f2}.mk-product small{display:block;color:#8b8287;margin-top:4px}.mk-product>b{color:#ff5f76;white-space:nowrap}
.mk-card hr{border:0;border-top:1px solid #f0e4e6;margin:18px 0}.mk-row{display:flex;justify-content:space-between;margin:12px 0}.mk-total{font-size:18px}.mk-total strong{color:#ff5f76;font-size:24px}
.mk-primary{width:100%;border:0;border-radius:28px;background:#ff536e;color:#fff;font-weight:700;padding:15px;margin-top:18px;cursor:pointer}
.mk-alert{padding:14px 16px;border-radius:12px;margin:0 0 18px}.mk-alert-error{background:#fff1f2;color:#be123c;border:1px solid #fecdd3}.mk-alert ul{margin:8px 0 0 18px}
.mk-left{min-width:0;}.mk-summary{min-width:0;}
@media(max-width:900px){.mk-checkout-grid{grid-template-columns:1fr}.mk-summary{position:static}}
@media(max-width:600px){.mk-form-grid{grid-template-columns:1fr}.mk-full{grid-column:auto}.mk-product{grid-template-columns:54px 1fr}.mk-product>b{grid-column:2}.mk-checkout-page{padding:20px 10px 40px}}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const province = document.getElementById('province');
    const district = document.getElementById('district');
    const ward = document.getElementById('ward');
    const shippingText = document.getElementById('checkout-shipping');
    const totalText = document.getElementById('checkout-total');

    province.addEventListener('change', async () => {
        district.disabled = true;
        ward.disabled = true;
        district.innerHTML = '<option value="">Đang tải...</option>';
        ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
        shippingText.textContent = 'Chưa tính';
        totalText.textContent = '{{ number_format($subtotal) }}đ';

        if (!province.value) {
            district.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
            return;
        }

        try {
            const response = await fetch(
                `{{ route('checkout.districts') }}?province_id=${province.value}`,
                { headers: { 'Accept': 'application/json' } }
            );

            if (!response.ok) throw new Error('Không tải được quận/huyện.');

            const data = await response.json();
            district.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';

            data.forEach(item => {
                district.innerHTML += `<option value="${item.DistrictID}">${item.DistrictName}</option>`;
            });

            district.disabled = false;
        } catch (error) {
            district.innerHTML = '<option value="">Không tải được quận/huyện</option>';
            console.error(error);
        }
    });

    district.addEventListener('change', async () => {
        ward.disabled = true;
        ward.innerHTML = '<option value="">Đang tải...</option>';
        shippingText.textContent = 'Chưa tính';
        totalText.textContent = '{{ number_format($subtotal) }}đ';

        if (!district.value) {
            ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
            return;
        }

        try {
            const response = await fetch(
                `{{ route('checkout.wards') }}?district_id=${district.value}`,
                { headers: { 'Accept': 'application/json' } }
            );

            if (!response.ok) throw new Error('Không tải được phường/xã.');

            const data = await response.json();
            ward.innerHTML = '<option value="">-- Chọn phường/xã --</option>';

            data.forEach(item => {
                ward.innerHTML += `<option value="${item.WardCode}">${item.WardName}</option>`;
            });

            ward.disabled = false;
        } catch (error) {
            ward.innerHTML = '<option value="">Không tải được phường/xã</option>';
            console.error(error);
        }
    });

    ward.addEventListener('change', async () => {
        if (!district.value || !ward.value) return;

        shippingText.textContent = 'Đang tính...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            const response = await fetch(
                `{{ route('checkout.shipping-fee') }}`,
                {
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
                }
            );

            const data = await response.json();

            if (!response.ok) {
                shippingText.textContent = data.message ?? 'Không tính được phí';
                console.error(data);
                return;
            }

            shippingText.textContent = Number(data.shipping_fee).toLocaleString('vi-VN') + 'đ';
            totalText.textContent = Number(data.total).toLocaleString('vi-VN') + 'đ';
        } catch (error) {
            shippingText.textContent = 'Không tính được phí';
            console.error(error);
        }
    });
});
</script>
@endsection
