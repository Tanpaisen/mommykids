@extends('client.layouts.app')

@section('sidebar')

    <div class="hidden"></div>

@endsection

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

.mk-address-title-wrap {display: flex;flex-direction: column;gap: 12px;}

.mk-address-back {width: fit-content;display: inline-flex;align-items: center;gap: 7px;color: #29232d;font-size: 14px;font-weight: 700;text-decoration: none;transition: color .15s, transform .15s;}

.mk-address-back span {width: 30px;height: 30px;display: inline-flex;justify-content: center;align-items: center;border-radius: 50%;background: #fff;border: 1px solid #eadde2;font-size: 25px;line-height: 1;box-shadow: 0 2px 6px rgba(0,0,0,.05);}

.mk-address-back:hover {color: #ff3864;transform: translateX(-2px);}

@media(max-width:700px){.mk-address-page{margin:10px auto 30px;padding:22px 16px;border-radius:15px}.mk-address-head{align-items:stretch}.mk-address-head .mk-address-btn{width:100%}.mk-address-card{grid-template-columns:1fr;padding:17px;gap:15px}.mk-address-actions{justify-content:flex-start;border-top:1px solid #f8edf0;padding-top:12px}.mk-address-fields{grid-template-columns:1fr;gap:13px}.mk-address-field.wide{grid-column:auto}.mk-address-modal-head,.mk-address-modal-body,.mk-address-modal-foot{padding-left:17px;padding-right:17px}}

</style>

<div class="mk-address-page">

    <div class="mk-address-head">

            <div class="mk-address-title-wrap">

                @if(request('from') === 'checkout')

                    <a href="{{ route('checkout.index') }}"

                    class="mk-address-back">

                        <span>‹</span>

                        Quay lại thanh toán

                    </a>

                @endif

                <h1 class="mk-address-title">

                    <span>📍</span> Sổ địa chỉ nhận hàng

                </h1>

            </div>

            <button type="button"

                    class="mk-address-btn mk-address-primary"

                    id="mkOpenAddress">

                + Thêm địa chỉ mới

            </button>

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

                {{-- SỬA ĐỊA CHỈ: dùng chung popup Thêm địa chỉ --}}
                <button
                    type="button"
                    class="mk-address-btn mk-address-secondary mk-edit-address"
                    data-address-id="{{ $address->id }}"
                    data-update-url="{{ route('profile.addresses.update', $address->id) }}"
                    data-recipient="{{ $address->recipient_name }}"
                    data-phone="{{ $address->phone }}"
                    data-province-id="{{ $address->province_id }}"
                    data-district-id="{{ $address->district_id }}"
                    data-ward-code="{{ $address->ward_code }}"
                    data-address-detail="{{ $address->address_detail }}"
                    data-label="{{ $address->label }}"
                    data-is-default="{{ $address->is_default ? '1' : '0' }}"
                >
                    Sửa
                </button>

                {{-- ĐẶT MẶC ĐỊNH --}}

                @unless($address->is_default)

                    <form

                        action="{{ route('profile.addresses.default', $address->id) }}"

                        method="POST"

                    >

                        @csrf

                        @method('PATCH')

                        <button

                            type="submit"

                            class="mk-address-btn mk-address-secondary"

                        >

                            Đặt mặc định

                        </button>

                    </form>

                @endunless

                {{-- XÓA --}}

                <form

                    action="{{ route('profile.addresses.destroy', $address->id) }}"

                    method="POST"

                    onsubmit="return confirm('Bạn có chắc muốn xóa địa chỉ này?');"

                >

                    @csrf

                    @method('DELETE')

                    <button

                        type="submit"

                        class="mk-address-btn mk-address-danger"

                    >

                        Xóa

                    </button>

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

        <form
            action="{{ route('profile.addresses.store') }}"
            method="POST"
            id="mkAddressForm"
            data-store-url="{{ route('profile.addresses.store') }}"
        >
            @csrf

            <input
                type="hidden"
                name="_method"
                id="mkAddressMethod"
                value="PUT"
                disabled
            >

            <input
                type="hidden"
                name="edit_address_id"
                id="mkEditAddressId"
                value="{{ old('edit_address_id') }}"
            >

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

                <button type="submit" class="mk-address-btn mk-address-primary" id="mkAddressSubmit">Lưu địa chỉ</button>

            </div>

        </form>

    </section>

</div>

<script type="application/json" id="address-page-config">
{!! json_encode([
    'hasValidationErrors' => $errors->any(),
    'oldProvinceId' => old('province_id'),
    'oldDistrictId' => old('district_id'),
    'oldWardCode' => old('ward_code'),
    'oldEditAddressId' => old('edit_address_id'),
    'routes' => [
        'districts' => route('checkout.districts', [], false),
        'wards' => route('checkout.wards', [], false),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('mkAddressOverlay');
    const dialog = overlay?.querySelector('.mk-address-dialog');

    const form = document.getElementById('mkAddressForm');
    const methodInput = document.getElementById('mkAddressMethod');
    const editAddressId = document.getElementById('mkEditAddressId');
    const modalTitle = document.getElementById('mkAddressModalTitle');
    const submitButton = document.getElementById('mkAddressSubmit');

    const recipient = document.getElementById('mkRecipient');
    const phone = document.getElementById('mkPhone');
    const province = document.getElementById('mkProvince');
    const district = document.getElementById('mkDistrict');
    const ward = document.getElementById('mkWard');
    const street = document.getElementById('mkStreet');
    const label = document.getElementById('mkLabel');
    const defaultCheckbox = form?.querySelector('input[name="is_default"]');

    const provinceName = document.getElementById('mkProvinceName');
    const districtName = document.getElementById('mkDistrictName');
    const wardName = document.getElementById('mkWardName');
    const error = document.getElementById('mkAddressLoadError');

    const configElement = document.getElementById('address-page-config');
    const config = configElement
        ? JSON.parse(configElement.textContent)
        : {
            hasValidationErrors: false,
            oldProvinceId: null,
            oldDistrictId: null,
            oldWardCode: null,
            oldEditAddressId: null,
            routes: {}
        };

    let previousFocus = null;
    let provinceRequest = 0;
    let districtRequest = 0;

    function showLoadError(message = '') {
        if (!error) {
            return;
        }

        error.textContent = message;
        error.hidden = !message;
    }

    function openModal() {
        if (!overlay || !dialog) {
            return;
        }

        previousFocus = document.activeElement;
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        dialog.focus();
        recipient?.focus();
    }

    function closeModal() {
        if (!overlay) {
            return;
        }

        overlay.hidden = true;
        document.body.style.overflow = '';
        showLoadError('');

        if (
            previousFocus &&
            typeof previousFocus.focus === 'function'
        ) {
            previousFocus.focus();
        }
    }

    function resetSelect(element, labelText) {
        if (!element) {
            return;
        }

        element.replaceChildren(new Option(labelText, ''));
        element.disabled = true;
    }

    function setName(element, destination) {
        if (!element || !destination) {
            return;
        }

        destination.value =
            element.value && element.selectedOptions.length
                ? element.selectedOptions[0].textContent.trim()
                : '';
    }

    function normalizeRows(payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        return Array.isArray(payload?.data)
            ? payload.data
            : [];
    }

    async function loadDistricts(
        provinceId,
        selectedDistrictId = ''
    ) {
        const requestId = ++provinceRequest;
        ++districtRequest;

        resetSelect(district, '-- Chọn Quận/Huyện --');
        resetSelect(ward, '-- Chọn Phường/Xã --');

        if (districtName) {
            districtName.value = '';
        }

        if (wardName) {
            wardName.value = '';
        }

        if (!provinceId) {
            return false;
        }

        resetSelect(district, 'Đang tải...');

        try {
            const response = await fetch(
                config.routes.districts +
                    '?province_id=' +
                    encodeURIComponent(provinceId),
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const rows = normalizeRows(await response.json());

            if (requestId !== provinceRequest) {
                return false;
            }

            district.replaceChildren(
                new Option('-- Chọn Quận/Huyện --', '')
            );

            for (const item of rows) {
                if (
                    item &&
                    item.DistrictID != null &&
                    item.DistrictName != null
                ) {
                    district.add(
                        new Option(
                            String(item.DistrictName),
                            String(item.DistrictID)
                        )
                    );
                }
            }

            district.disabled = rows.length === 0;

            if (!selectedDistrictId) {
                return rows.length > 0;
            }

            const exists = [...district.options].some(
                option =>
                    String(option.value) ===
                    String(selectedDistrictId)
            );

            if (!exists) {
                return false;
            }

            district.value = String(selectedDistrictId);
            setName(district, districtName);

            return true;
        } catch (exception) {
            console.error(exception);

            if (requestId !== provinceRequest) {
                return false;
            }

            resetSelect(district, '-- Chọn Quận/Huyện --');
            showLoadError(
                'Không tải được quận/huyện. Vui lòng thử lại.'
            );

            return false;
        }
    }

    async function loadWards(
        districtId,
        selectedWardCode = ''
    ) {
        const requestId = ++districtRequest;

        resetSelect(ward, '-- Chọn Phường/Xã --');

        if (wardName) {
            wardName.value = '';
        }

        if (!districtId) {
            return false;
        }

        resetSelect(ward, 'Đang tải...');

        try {
            const response = await fetch(
                config.routes.wards +
                    '?district_id=' +
                    encodeURIComponent(districtId),
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const rows = normalizeRows(await response.json());

            if (requestId !== districtRequest) {
                return false;
            }

            ward.replaceChildren(
                new Option('-- Chọn Phường/Xã --', '')
            );

            for (const item of rows) {
                if (
                    item &&
                    item.WardCode != null &&
                    item.WardName != null
                ) {
                    ward.add(
                        new Option(
                            String(item.WardName),
                            String(item.WardCode)
                        )
                    );
                }
            }

            ward.disabled = rows.length === 0;

            if (!selectedWardCode) {
                return rows.length > 0;
            }

            const exists = [...ward.options].some(
                option =>
                    String(option.value) ===
                    String(selectedWardCode)
            );

            if (!exists) {
                return false;
            }

            ward.value = String(selectedWardCode);
            setName(ward, wardName);

            return true;
        } catch (exception) {
            console.error(exception);

            if (requestId !== districtRequest) {
                return false;
            }

            resetSelect(ward, '-- Chọn Phường/Xã --');
            showLoadError(
                'Không tải được phường/xã. Vui lòng thử lại.'
            );

            return false;
        }
    }

    function setAddMode() {
        if (!form) {
            return;
        }

        form.action = form.dataset.storeUrl;

        if (methodInput) {
            methodInput.disabled = true;
        }

        if (editAddressId) {
            editAddressId.value = '';
        }

        if (modalTitle) {
            modalTitle.textContent = 'Thêm địa chỉ mới';
        }

        if (submitButton) {
            submitButton.textContent = 'Lưu địa chỉ';
        }
    }

    function clearFormForAdd() {
        if (recipient) recipient.value = '';
        if (phone) phone.value = '';
        if (street) street.value = '';
        if (label) label.value = '';
        if (defaultCheckbox) defaultCheckbox.checked = false;

        if (province) province.value = '';
        if (provinceName) provinceName.value = '';
        if (districtName) districtName.value = '';
        if (wardName) wardName.value = '';

        resetSelect(district, '-- Chọn Quận/Huyện --');
        resetSelect(ward, '-- Chọn Phường/Xã --');
        showLoadError('');
    }

    function openAddModal() {
        setAddMode();
        clearFormForAdd();
        openModal();
    }

    async function openEditModal(button) {
        if (!form || !button) {
            return;
        }

        showLoadError('');

        form.action = button.dataset.updateUrl || form.dataset.storeUrl;

        if (methodInput) {
            methodInput.disabled = false;
            methodInput.value = 'PUT';
        }

        if (editAddressId) {
            editAddressId.value = button.dataset.addressId || '';
        }

        if (modalTitle) {
            modalTitle.textContent = 'Sửa địa chỉ';
        }

        if (submitButton) {
            submitButton.textContent = 'Lưu thay đổi';
        }

        if (recipient) {
            recipient.value = button.dataset.recipient || '';
        }

        if (phone) {
            phone.value = button.dataset.phone || '';
        }

        if (street) {
            street.value = button.dataset.addressDetail || '';
        }

        if (label) {
            label.value = button.dataset.label || '';
        }

        if (defaultCheckbox) {
            defaultCheckbox.checked =
                button.dataset.isDefault === '1';
        }

        const provinceId = button.dataset.provinceId || '';
        const districtId = button.dataset.districtId || '';
        const wardCode = button.dataset.wardCode || '';

        if (province) {
            province.value = String(provinceId);
            setName(province, provinceName);
        }

        openModal();

        const districtFound = await loadDistricts(
            provinceId,
            districtId
        );

        if (!districtFound && districtId) {
            showLoadError(
                'Quận/Huyện của địa chỉ này hiện không còn nằm trong tuyến GHN hỗ trợ. Vui lòng chọn lại.'
            );
            return;
        }

        const wardFound = await loadWards(
            districtId,
            wardCode
        );

        if (!wardFound && wardCode) {
            showLoadError(
                'Phường/Xã của địa chỉ này hiện không còn được GHN hỗ trợ. Vui lòng chọn lại.'
            );
        }
    }

    async function restoreValidationModal() {
        const editingId = config.oldEditAddressId
            ? String(config.oldEditAddressId)
            : '';

        if (editingId) {
            const editButton = document.querySelector(
                '.mk-edit-address[data-address-id="' +
                    CSS.escape(editingId) +
                    '"]'
            );

            if (editButton) {
                form.action = editButton.dataset.updateUrl;
            }

            if (methodInput) {
                methodInput.disabled = false;
                methodInput.value = 'PUT';
            }

            if (editAddressId) {
                editAddressId.value = editingId;
            }

            if (modalTitle) {
                modalTitle.textContent = 'Sửa địa chỉ';
            }

            if (submitButton) {
                submitButton.textContent = 'Lưu thay đổi';
            }
        } else {
            setAddMode();
        }

        openModal();

        const provinceId = config.oldProvinceId || '';
        const districtId = config.oldDistrictId || '';
        const wardCode = config.oldWardCode || '';

        if (!provinceId || !province) {
            return;
        }

        province.value = String(provinceId);
        setName(province, provinceName);

        const districtFound = await loadDistricts(
            provinceId,
            districtId
        );

        if (districtFound && districtId) {
            await loadWards(districtId, wardCode);
        }
    }

    document
        .getElementById('mkOpenAddress')
        ?.addEventListener('click', openAddModal);

    document
        .querySelectorAll('.mk-edit-address')
        .forEach(button => {
            button.addEventListener('click', function () {
                openEditModal(this);
            });
        });

    document
        .getElementById('mkCloseAddress')
        ?.addEventListener('click', closeModal);

    document
        .getElementById('mkCancelAddress')
        ?.addEventListener('click', closeModal);

    overlay?.addEventListener('click', function (event) {
        if (event.target === overlay) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!overlay || overlay.hidden || !dialog) {
            return;
        }

        if (event.key === 'Escape') {
            closeModal();
            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [
            ...dialog.querySelectorAll(
                'button:not([disabled]), ' +
                'input:not([disabled]):not([type="hidden"]), ' +
                'select:not([disabled])'
            )
        ];

        if (!focusable.length) {
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (
            event.shiftKey &&
            document.activeElement === first
        ) {
            event.preventDefault();
            last.focus();
        } else if (
            !event.shiftKey &&
            document.activeElement === last
        ) {
            event.preventDefault();
            first.focus();
        }
    });

    province?.addEventListener('change', async function () {
        showLoadError('');
        setName(province, provinceName);

        await loadDistricts(province.value);
    });

    district?.addEventListener('change', async function () {
        showLoadError('');
        setName(district, districtName);

        await loadWards(district.value);
    });

    ward?.addEventListener('change', function () {
        setName(ward, wardName);
    });

    const params = new URLSearchParams(window.location.search);
    const shouldOpenAddModal = params.get('add') === '1';

    if (config.hasValidationErrors) {
        restoreValidationModal();
    } else if (shouldOpenAddModal) {
        openAddModal();
    }
});
</script>

@endsection
