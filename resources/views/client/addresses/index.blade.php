@extends('client.layouts.app')

@section('title', 'Sổ địa chỉ nhận hàng - MommyKids')

@section('content')
<div class="container py-4 md:py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <span style="color: #FF2A54;">📍</span> Sổ địa chỉ nhận hàng
                </h4>
                <button type="button" class="btn text-white fw-bold" style="background: #FF2A54; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    + Thêm địa chỉ mới
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-3 mb-4">✅ {{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as$error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    @forelse($addresses as$addr)
                        <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 16px;">
                                        {{ $addr->recipient_name }}
                                        @if($addr->is_default)
                                            <span class="badge ms-2" style="background: #fff0f2; color: #FF2A54; border: 1px solid #FF2A54;">Mặc định</span>
                                        @endif
                                        @if($addr->label)
                                            <span class="badge bg-light text-secondary ms-1 border">{{ $addr->label }}</span>
                                        @endif
                                    </div>
                                    <div class="text-secondary small mb-1">SĐT: {{ $addr->phone }}</div>
                                    <div class="text-secondary small mb-0">
                                        {{ $addr->address_detail }}<br>
                                        {{ $addr->ward_name }}, {{ $addr->district_name }}, {{$addr->province_name }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2 shrink-0">
                                    @if(!$addr->is_default)
                                        <form action="{{ route('profile.addresses.default', $addr->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill" style="font-size: 12px;">Đặt mặc định</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('profile.addresses.destroy', $addr->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa chỉ này?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" style="font-size: 12px;">Xóa</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div style="font-size: 40px; color: #ccc; margin-bottom: 10px;">🏠</div>
                            <p class="text-secondary mb-0">Bạn chưa lưu địa chỉ nhận hàng nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL THÊM ĐỊA CHỈ --}}
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('profile.addresses.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Thêm địa chỉ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-3" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Tỉnh / Thành phố <span class="text-danger">*</span></label>
                            <select id="province" name="province_id" class="form-select rounded-3" required>
                                <option value="">-- Chọn Tỉnh/Thành --</option>
                                @foreach ($provinces as$prov)
                                    <option value="{{ $prov['ProvinceID'] }}">{{ $prov['ProvinceName'] }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="province_name" id="province_name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Quận / Huyện <span class="text-danger">*</span></label>
                            <select id="district" name="district_id" class="form-select rounded-3" required disabled>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                            <input type="hidden" name="district_name" id="district_name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Phường / Xã <span class="text-danger">*</span></label>
                            <select id="ward" name="ward_code" class="form-select rounded-3" required disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                            <input type="hidden" name="ward_name" id="ward_name">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                            <input type="text" name="address_detail" class="form-control rounded-3" placeholder="Số nhà, tên đường..." required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Loại địa chỉ (Tùy chọn)</label>
                            <input type="text" name="label" class="form-control rounded-3" placeholder="Ví dụ: Nhà riêng, Công ty...">
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_default" name="is_default" value="1">
                                <label class="form-check-label" for="is_default">Đặt làm địa chỉ mặc định</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background: #FF2A54;">Lưu địa chỉ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const provinceSelect = document.getElementById('province');
    const districtSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');

    const provinceName = document.getElementById('province_name');
    const districtName = document.getElementById('district_name');
    const wardName = document.getElementById('ward_name');

    provinceSelect.addEventListener('change', async function() {
        const pid = this.value;
        provinceName.value = this.options[this.selectedIndex].text;
        
        districtSelect.disabled = true;
        wardSelect.disabled = true;
        districtSelect.innerHTML = '<option value="">Đang tải...</option>';
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';

        if (!pid) return;

        try {
            const res = await fetch(`/checkout/districts?province_id=${pid}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            data.forEach(item => {
                districtSelect.innerHTML += `<option value="${item.DistrictID}">${item.DistrictName}</option>`;
            });
            districtSelect.disabled = false;
        } catch (error) {
            console.error('Lỗi tải quận huyện', error);
        }
    });

    districtSelect.addEventListener('change', async function() {
        const did = this.value;
        districtName.value = this.options[this.selectedIndex].text;
        
        wardSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">Đang tải...</option>';

        if (!did) return;

        try {
            const res = await fetch(`/checkout/wards?district_id=${did}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            data.forEach(item => {
                wardSelect.innerHTML += `<option value="${item.WardCode}">${item.WardName}</option>`;
            });
            wardSelect.disabled = false;
        } catch (error) {
            console.error('Lỗi tải phường xã', error);
        }
    });

    wardSelect.addEventListener('change', function() {
        wardName.value = this.options[this.selectedIndex].text;
    });
});
</script>
@endpush