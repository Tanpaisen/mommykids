@extends('client.layouts.app')



@section('title', 'Sổ địa chỉ nhận hàng - MommyKids')



@section('content')

<style>
/* MommyKids - sổ địa chỉ: scoped styles, không thay đổi layout dùng chung */
.mk-address-page, .mk-address-page * {box-sizing:border-box}
.mk-address-page {width:min(100%,1000px);margin:22px auto 52px;padding:28px 32px 36px;background:#fff8fa;border:1px solid #ffe4eb;border-radius:22px;color:#29232d;font-family:inherit}
.mk-address-head {display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;padding-bottom:19px;border-bottom:1px solid #f3e1e6}
.mk-address-title {margin:0;font-size:clamp(21px,2.2vw,27px);font-weight:750;line-height:1.35}
.mk-address-title span {color:#fa4770;margin-right:6px}
.mk-address-btn {display:inline-flex;justify-content:center;align-items:center;gap:7px;min-height:40px;padding:10px 17px;border:1px solid transparent;border-radius:11px;font:inherit;font-size:13px;font-weight:700;line-height:1.35;cursor:pointer;text-decoration:none;transition:background .16s,box-shadow .16s,transform .16s}
.mk-address-btn:hover {transform:translateY(-1px)}
.mk-address-btn:focus-visible,.mk-address-page input:focus-visible,.mk-address-page select:focus-visible {outline:3px solid #ffbdd0;outline-offset:2px}
.mk-address-primary {background:#ff3864;color:#fff;box-shadow:0 5px 13px rgba(255,56,100,.14)}
.mk-address-primary:hover {background:#ee2855;color:#fff}
.mk-address-secondary {background:#fff;color:#4a4651;border-color:#efd7df}
.mk-address-secondary:hover {background:#fff3f6}
.mk-address-danger {background:#fff;color:#d52a51;border-color:#ffc0cf}
.mk-address-danger:hover {background:#fff0f4}
.mk-address-alert {border-radius:12px;padding:13px 16px;margin-bottom:16px;font-size:14px}
.mk-address-success {background:#e9f9ee;border:1px solid #c4ebd0;color:#186b35}
.mk-address-error {background:#fff1f3;border:1px solid #ffcad4;color:#ad2347}
.mk-address-alert ul {margin:8px 0 0;padding-left:21px}
.mk-address-list {display:grid;grid-template-columns:minmax(0,1fr);gap:14px}
.mk-address-card {min-width:0;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:20px;background:#fff;border:1px solid #f0dfe5;border-radius:16px;padding:19px 22px;box-shadow:0 3px 14px rgba(48,23,36,.035)}
.mk-address-card.is-default {border-color:#ffa9bc;background:linear-gradient(90deg,#fff5f8 0%,#fff 35%);box-shadow:0 3px 14px rgba(255,56,100,.045)}
.mk-address-info {min-width:0;overflow-wrap:anywhere}
.mk-address-person {display:flex;flex-wrap:wrap;align-items:center;gap:8px;color:#28232d;font-size:16px;font-weight:750}
.mk-address-phone {margin-top:8px;font-size:14px;color:#55515b}
.mk-address-detail {margin-top:7px;font-size:14px;color:#706b75;line-height:1.65}
.mk-address-tag {display:inline-flex;align-items:center;padding:3px 9px;border-radius:50px;background:#fff0f4;color:#dd315d;font-size:11px;font-weight:700;line-height:1.45}
.mk-address-actions {display:flex;align-items:center;justify-content:flex-end;gap:9px;flex-wrap:wrap}
.mk-address-actions form {margin:0}
.mk-address-actions .mk-address-btn {white-space:nowrap}
.mk-address-empty {background:#fff;border:1px dashed #f4c8d5;border-radius:17px;padding:52px 18px;text-align:center}
.mk-address-empty-icon {display:flex;align-items:center;justify-content:center;width:70px;height:70px;border-radius:50%;margin:0 auto 16px;background:#fff0f4;font-size:34px}
.mk-address-empty h3 {font-size:18px;margin:0 0 8px}
.mk-address-empty p {margin:0;color:#77727c;font-size:14px}
.mk-address-overlay[hidden] {display:none!important}
.mk-address-overlay {position:fixed;inset:0;z-index:2147483000;display:grid;place-items:center;padding:16px;background:rgba(26,20,31,.58);overflow-y:auto}
.mk-address-dialog {width:min(100%,680px);max-height:min(92vh,850px);overflow-y:auto;background:#fff;border-radius:20px;box-shadow:0 24px 80px rgba(20,12,25,.24);margin:auto}
.mk-address-modal-head {display:flex;justify-content:space-between;align-items:center;gap:12px;padding:21px 26px 17px;border-bottom:1px solid #f3e6e9}
.mk-address-modal-head h3 {font-weight:750;font-size:21px;margin:0}
.mk-address-x {background:transparent;border:0;cursor:pointer;font-size:29px;line-height:1;color:#777;padding:0 5px}
.mk-address-modal-body {padding:23px 26px}
.mk-address-fields {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px 16px}
.mk-address-field {min-width:0}
.mk-address-field.wide {grid-column:1/-1}
.mk-address-field label {display:block;color:#48434d;font-size:13px;font-weight:650;margin-bottom:7px}
.mk-address-field label span {color:#e73561}
.mk-address-field input:not([type='checkbox']),.mk-address-field select {width:100%;height:44px;padding:10px 13px;border:1px solid #e9dce1;border-radius:10px;background:#fff;font:inherit;font-size:14px;color:#34303a}
.mk-address-field select:disabled {background:#f9f8f9;color:#999}
.mk-address-check {display:flex;align-items:center;gap:9px;font-size:14px;color:#49434b;cursor:pointer}
.mk-address-check input {accent-color:#f5446b;width:17px;height:17px}
.mk-address-modal-foot {padding:16px 26px 22px;border-top:1px solid #f3e6e9;display:flex;justify-content:flex-end;gap:10px}
.mk-address-load-error {color:#b42344;font-size:13px;margin:2px 0 0}
@media(max-width:700px){.mk-address-page{margin:10px auto 30px;padding:22px 16px;border-radius:15px}.mk-address-head{align-items:stretch}.mk-address-head .mk-address-btn{width:100%}.mk-address-card{grid-template-columns:1fr;padding:17px;gap:15px}.mk-address-actions{justify-content:flex-start;border-top:1px solid #f8edf0;padding-top:12px}.mk-address-fields{grid-template-columns:1fr;gap:13px}.mk-address-field.wide{grid-column:auto}.mk-address-modal-head,.mk-address-modal-body,.mk-address-modal-foot{padding-left:17px;padding-right:17px}}
</style>



<div class="mk-address-page">

    <div class="mk-address-head">

        <h1 class="mk-address-title"><span>📍</span> Sổ địa chỉ nhận hàng</h1>

        <button type="button" class="mk-address-btn mk-address-primary" id="mkOpenAddress">+ Thêm địa chỉ mới</button>

    </div>



    @if(session('success'))

        <div class="mk-address-alert mk-address-success" role="status">{{ session('success') }}</div>

    @endif

    @if($errors->any())

        <div class="mk-address-alert mk-address-error" role="alert">

            <strong>Vui lòng kiểm tra lại thông tin:</strong>

            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>

        </div>

    @endif



    @forelse($addresses as $address)

        @if($loop->first)<div class="mk-address-list">@endif

        <div class="mk-address-card {{ $address->is_default ? 'is-default' : '' }}">

            <div class="mk-address-info">

                <div class="mk-address-person">

                    {{ $address->recipient_name }}

                    @if($address->is_default)<span class="mk-address-tag">Mặc định</span>@endif

                    @if($address->label)<span class="mk-address-tag">{{ $address->label }}</span>@endif

                </div>

                <div class="mk-address-phone">{{ $address->phone }}</div>

                <div class="mk-address-detail">

                    {{ $address->address_detail }}<br>

                    {{ collect([$address->ward_name, $address->district_name, $address->province_name])->filter()->implode(', ') }}

                </div>

            </div>

            <div class="mk-address-actions">

                @unless($address->is_default)

                    <form action="{{ route('profile.addresses.default', $address->id) }}" method="POST">

                        @csrf @method('PATCH')

                        <button type="submit" class="mk-address-btn mk-address-secondary">Đặt mặc định</button>

                    </form>

                @endunless

                <form action="{{ route('profile.addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">

                    @csrf @method('DELETE')

                    <button type="submit" class="mk-address-btn mk-address-danger">Xóa</button>

                </form>

            </div>

        </div>

        @if($loop->last)</div>@endif

    @empty

        <div class="mk-address-empty">

            <div class="mk-address-empty-icon">🏠</div>

            <h3>Bạn chưa lưu địa chỉ nhận hàng nào</h3>

            <p>Thêm địa chỉ để thuận tiện hơn khi đặt hàng tại MommyKids.</p>

        </div>

    @endforelse

</div>



<div class="mk-address-overlay" id="mkAddressOverlay" hidden>

    <section class="mk-address-dialog" role="dialog" aria-modal="true" aria-labelledby="mkAddressModalTitle" tabindex="-1">

        <div class="mk-address-modal-head">

            <h3 id="mkAddressModalTitle">Thêm địa chỉ mới</h3>

            <button type="button" class="mk-address-x" id="mkCloseAddress" aria-label="Đóng">&times;</button>

        </div>

        <form action="{{ route('profile.addresses.store') }}" method="POST" id="mkAddressForm">

            @csrf

            <div class="mk-address-modal-body">

                <div class="mk-address-fields">

                    <div class="mk-address-field">

                        <label for="mkRecipient">Họ và tên <span>*</span></label>

                        <input id="mkRecipient" name="recipient_name" type="text" maxlength="255" autocomplete="name" value="{{ old('recipient_name') }}" placeholder="Nguyễn Văn A" required>

                    </div>

                    <div class="mk-address-field">

                        <label for="mkPhone">Số điện thoại <span>*</span></label>

                        <input id="mkPhone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="{{ old('phone') }}" placeholder="Nhập số điện thoại" required>

                    </div>

                    <div class="mk-address-field wide">

                        <label for="mkProvince">Tỉnh / Thành phố <span>*</span></label>

                        <select id="mkProvince" name="province_id" required>

                            <option value="">-- Chọn Tỉnh/Thành --</option>

                            @foreach($provinces as $province)

                                <option value="{{ $province['ProvinceID'] }}" @selected((string) old('province_id') === (string) $province['ProvinceID'])>{{ $province['ProvinceName'] }}</option>

                            @endforeach

                        </select>

                        <input type="hidden" name="province_name" id="mkProvinceName" value="{{ old('province_name') }}">

                    </div>

                    <div class="mk-address-field">

                        <label for="mkDistrict">Quận / Huyện <span>*</span></label>

                        <select id="mkDistrict" name="district_id" required disabled><option value="">-- Chọn Quận/Huyện --</option></select>

                        <input type="hidden" name="district_name" id="mkDistrictName" value="{{ old('district_name') }}">

                    </div>

                    <div class="mk-address-field">

                        <label for="mkWard">Phường / Xã <span>*</span></label>

                        <select id="mkWard" name="ward_code" required disabled><option value="">-- Chọn Phường/Xã --</option></select>

                        <input type="hidden" name="ward_name" id="mkWardName" value="{{ old('ward_name') }}">

                    </div>

                    <div class="mk-address-field wide">

                        <label for="mkStreet">Địa chỉ chi tiết <span>*</span></label>

                        <input id="mkStreet" name="address_detail" type="text" maxlength="500" autocomplete="street-address" value="{{ old('address_detail') }}" placeholder="Số nhà, tên đường..." required>

                    </div>

                    <div class="mk-address-field wide">

                        <label for="mkLabel">Loại địa chỉ (tùy chọn)</label>

                        <input id="mkLabel" name="label" type="text" maxlength="100" value="{{ old('label') }}" placeholder="Ví dụ: Nhà riêng, Công ty">

                    </div>

                    <div class="mk-address-field wide">

                        <label class="mk-address-check"><input type="checkbox" name="is_default" value="1" @checked(old('is_default'))> Đặt làm địa chỉ mặc định</label>

                    </div>

                </div>

                <p id="mkAddressLoadError" class="mk-address-load-error" role="alert" hidden></p>

            </div>

            <div class="mk-address-modal-foot">

                <button type="button" class="mk-address-btn mk-address-secondary" id="mkCancelAddress">Hủy</button>

                <button type="submit" class="mk-address-btn mk-address-primary">Lưu địa chỉ</button>

            </div>

        </form>

    </section>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const overlay = document.getElementById('mkAddressOverlay');

    const dialog = overlay.querySelector('.mk-address-dialog');

    const province = document.getElementById('mkProvince');

    const district = document.getElementById('mkDistrict');

    const ward = document.getElementById('mkWard');

    const error = document.getElementById('mkAddressLoadError');

    let previousFocus = null;

    let provinceRequest = 0;

    let districtRequest = 0;



    function openModal() {

        previousFocus = document.activeElement;

        overlay.hidden = false;

        document.body.style.overflow = 'hidden';

        dialog.focus();

        document.getElementById('mkRecipient').focus();

    }

    function closeModal() {

        overlay.hidden = true;

        document.body.style.overflow = '';

        if (previousFocus && previousFocus.focus) previousFocus.focus();

    }

    document.getElementById('mkOpenAddress').addEventListener('click', openModal);

    document.getElementById('mkCloseAddress').addEventListener('click', closeModal);

    document.getElementById('mkCancelAddress').addEventListener('click', closeModal);

    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    document.addEventListener('keydown', e => {

        if (overlay.hidden) return;

        if (e.key === 'Escape') closeModal();

        if (e.key !== 'Tab') return;

        const focusable = [...dialog.querySelectorAll('button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled])')];

        if (!focusable.length) return;

        const first = focusable[0], last = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }

        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }

    });



    function resetSelect(el, label) {

        el.replaceChildren(new Option(label, ''));

        el.disabled = true;

    }

    function setName(el, dest) {

        document.getElementById(dest).value = el.value ? el.selectedOptions[0].textContent : '';

    }

    province.addEventListener('change', async function () {

        const req = ++provinceRequest;

        ++districtRequest;

        error.hidden = true;

        setName(province, 'mkProvinceName');

        document.getElementById('mkDistrictName').value = '';

        document.getElementById('mkWardName').value = '';

        resetSelect(district, '-- Chọn Quận/Huyện --');

        resetSelect(ward, '-- Chọn Phường/Xã --');

        if (!province.value) return;

        resetSelect(district, 'Đang tải...');

        try {

            const res = await fetch('/checkout/districts?province_id=' + encodeURIComponent(province.value), {headers: {'Accept': 'application/json'}});

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const payload = await res.json();

            if (req !== provinceRequest) return;

            const rows = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);

            district.replaceChildren(new Option('-- Chọn Quận/Huyện --', ''));

            for (const item of rows) if (item && item.DistrictID != null && item.DistrictName != null) district.add(new Option(String(item.DistrictName), String(item.DistrictID)));

            district.disabled = rows.length === 0;

        } catch (e) {

            if (req !== provinceRequest) return;

            resetSelect(district, '-- Chọn Quận/Huyện --');

            error.textContent = 'Không tải được quận/huyện. Vui lòng thử lại.';

            error.hidden = false;

        }

    });

    district.addEventListener('change', async function () {

        const req = ++districtRequest;

        error.hidden = true;

        setName(district, 'mkDistrictName');

        document.getElementById('mkWardName').value = '';

        resetSelect(ward, '-- Chọn Phường/Xã --');

        if (!district.value) return;

        resetSelect(ward, 'Đang tải...');

        try {

            const res = await fetch('/checkout/wards?district_id=' + encodeURIComponent(district.value), {headers: {'Accept': 'application/json'}});

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const payload = await res.json();

            if (req !== districtRequest) return;

            const rows = Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);

            ward.replaceChildren(new Option('-- Chọn Phường/Xã --', ''));

            for (const item of rows) if (item && item.WardCode != null && item.WardName != null) ward.add(new Option(String(item.WardName), String(item.WardCode)));

            ward.disabled = rows.length === 0;

        } catch (e) {

            if (req !== districtRequest) return;

            resetSelect(ward, '-- Chọn Phường/Xã --');

            error.textContent = 'Không tải được phường/xã. Vui lòng thử lại.';

            error.hidden = false;

        }

    });

    ward.addEventListener('change', () => setName(ward, 'mkWardName'));



    @if($errors->any())

        openModal();

        @if(old('province_id'))

        province.dispatchEvent(new Event('change'));

        @endif

    @endif

});

</script>

@endsection
