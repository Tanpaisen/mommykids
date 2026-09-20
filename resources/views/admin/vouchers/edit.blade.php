@extends('admin.layouts.app')
@section('content')
<div class="px-6 py-8 max-w-5xl mx-auto">
    @cannot('vouchers.manage')
        <div class="bg-gray-100 text-gray-600 p-4 rounded-lg mb-6 text-sm">
            🔒 Chế độ chỉ xem: Bạn không có quyền <strong>vouchers.manage</strong> nên không thể chỉnh sửa Voucher này.
        </div>
    @endcannot

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Cập nhật Voucher: {{ $voucher->code }}</h2>
            <p class="text-sm text-gray-500 mt-1">Chỉnh sửa thông tin và điều kiện của mã ưu đãi</p>
        </div>
        <a href="{{ route('admin.vouchers.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
            &larr; Quay lại
        </a>
    </div>
    <fieldset @cannot('vouchers.manage') disabled @endcannot>
        <form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8" onsubmit="return validateDates()">
            @csrf
            @method('PUT')

            <!-- TAB (KHÓA Ở EDIT) -->
            <div class="mb-8">
                <div class="flex p-1 bg-gray-100 rounded-lg w-fit pointer-events-none opacity-80">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="order" class="peer hidden" {{ $voucher->type == 'order' ? 'checked' : '' }}>
                        <div class="px-6 py-2 rounded-md peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-red-600 font-bold text-sm text-gray-500 transition-all">
                            🛒 Mã Đơn Hàng (Sản phẩm)
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="shipping" class="peer hidden" {{ $voucher->type == 'shipping' ? 'checked' : '' }}>
                        <div class="px-6 py-2 rounded-md peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-red-600 font-bold text-sm text-gray-500 transition-all">
                            🚚 Mã Vận Chuyển (Freeship)
                        </div>
                    </label>
                </div>
                <p class="text-xs text-gray-500 mt-2">Phân loại Voucher không thể thay đổi sau khi phát hành.</p>
            </div>

            <!-- 1. THÔNG TIN CƠ BẢN -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">1. Thông tin cơ bản</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã Voucher (Code) *</label>
                    <input type="text" name="code" value="{{ old('code', $voucher->code) }}" required class="w-full rounded-lg border-gray-300 bg-gray-100 uppercase pointer-events-none" readonly>
                    <p class="text-xs text-gray-500 mt-1">Không thể thay đổi mã Code sau khi đã tạo.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình *</label>
                    <input type="text" name="name" value="{{ old('name', $voucher->name) }}" required class="w-full rounded-lg border-gray-300">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả (Tùy chọn)</label>
                    <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300">{{ old('description', $voucher->description) }}</textarea>
                </div>
            </div>

            <!-- 2. CẤU HÌNH GIẢM GIÁ -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">2. Thiết lập giảm giá</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại ưu đãi *</label>
                    <select name="discount_type" id="discount_type" class="w-full rounded-lg border-gray-300 bg-gray-100 pointer-events-none" readonly>
                        <option value="fixed" {{ $voucher->discount_type == 'fixed' ? 'selected' : '' }}>Giảm số tiền (VNĐ)</option>
                        <option value="percent" {{ $voucher->discount_type == 'percent' ? 'selected' : '' }}>Giảm phần trăm (%)</option>
                        <option value="free_shipping" {{ $voucher->discount_type == 'free_shipping' ? 'selected' : '' }}>Miễn phí vận chuyển</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Không thể đổi loại ưu đãi.</p>
                </div>

                <div id="wrapper_discount_value">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="label_discount_value">Mức giảm (VNĐ) *</label>
                    <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value', $voucher->discount_value) }}" required min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('discount_value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div id="wrapper_max_discount" class="{{ $voucher->discount_type == 'fixed' ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="label_max_discount">Giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}" min="0" class="w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount" value="{{ old('min_order_amount', $voucher->min_order_amount) }}" min="0" class="w-full rounded-lg border-gray-300">
                </div>
            </div>

            <!-- 3. GIỚI HẠN & THỜI GIAN -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">3. Giới hạn & Thời gian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng lượt dùng</label>
                    <input type="number" name="total_quantity" value="{{ old('total_quantity', $voucher->total_quantity) }}" min="1" class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tối đa mỗi khách *</label>
                    <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $voucher->usage_limit_per_user) }}" required min="1" class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bắt đầu</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $voucher->starts_at ? date('Y-m-d\TH:i', strtotime($voucher->starts_at)) : '') }}" class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $voucher->expires_at ? date('Y-m-d\TH:i', strtotime($voucher->expires_at)) : '') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('expires_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- 4. ĐỐI TƯỢNG -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">4. Đối tượng & Trạng thái</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đối tượng áp dụng *</label>
                    <select name="apply_to" id="apply_to" class="w-full rounded-lg border-gray-300">
                        <option value="all" {{ $voucher->apply_to == 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="new_user" {{ $voucher->apply_to == 'new_user' ? 'selected' : '' }}>Khách mới</option>
                        <option value="specific_tiers" {{ $voucher->apply_to == 'specific_tiers' ? 'selected' : '' }}>Hạng thành viên</option>
                        <option value="specific_users" {{ $voucher->apply_to == 'specific_users' ? 'selected' : '' }}>Khách cụ thể</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái *</label>
                    <select name="status" class="w-full rounded-lg border-gray-300">
                        <option value="draft" {{ $voucher->status == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                        <option value="scheduled" {{ $voucher->status == 'scheduled' ? 'selected' : '' }}>Chờ chạy</option>
                        <option value="active" {{ $voucher->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="paused" {{ $voucher->status == 'paused' ? 'selected' : '' }}>Tạm dừng</option>
                        <option value="expired" {{ $voucher->status == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    </select>
                </div>
            </div>

            <div id="wrapper_specific_tiers" class="{{ $voucher->apply_to == 'specific_tiers' ? '' : 'hidden' }} bg-gray-50 p-4 rounded-lg mb-6">
                <label class="block text-sm font-bold mb-3">Chọn hạng thẻ:</label>
                <div class="flex gap-6">
                    @foreach(['member' => 'Member', 'silver' => 'Silver', 'gold' => 'Gold', 'diamond' => 'Diamond'] as $val => $label)
                    <label class="flex items-center">
                        <input type="checkbox" name="tiers[]" value="{{ $val }}" {{ in_array($val, old('tiers', $selectedTiers)) ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                        <span class="ml-2 text-sm">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="wrapper_specific_users" class="{{ $voucher->apply_to == 'specific_users' ? '' : 'hidden' }} bg-gray-50 p-4 rounded-lg mb-6">
                <label class="block text-sm font-bold mb-1">Danh sách Email:</label>
                <textarea name="user_emails" rows="2" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">{{ old('user_emails', $selectedEmails) }}</textarea>
            </div>

            <!-- PHẠM VI ÁP DỤNG -->
            <div id="section_scope_wrapper" class="{{ $voucher->type == 'shipping' ? 'hidden' : '' }}">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">5. Phạm vi áp dụng</h3>
                <div class="mb-6">
                    <div class="flex gap-6 mb-4">
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="all" {{ $currentScope == 'all' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500 border-gray-300">
                            <span class="ml-2 text-sm">Toàn shop</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="specific_categories" {{ $currentScope == 'specific_categories' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500 border-gray-300">
                            <span class="ml-2 text-sm">Danh mục cụ thể</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="scope" value="specific_products" {{ $currentScope == 'specific_products' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500 border-gray-300">
                            <span class="ml-2 text-sm">Sản phẩm cụ thể</span>
                        </label>
                    </div>

                    <!-- Chọn Danh mục -->
                    <div id="wrapper_specific_categories" class="{{ $currentScope == 'specific_categories' ? '' : 'hidden' }} bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($categories as $category)
                            <label class="flex items-center hover:bg-gray-100 p-2 rounded transition-colors cursor-pointer">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', $selectedCategories)) ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
                                <span class="ml-2 text-sm">{{ $category->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Chọn Sản phẩm -->
                    <div id="wrapper_specific_products" class="{{ $currentScope == 'specific_products' ? '' : 'hidden' }} bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-bold mb-2">Nhập danh sách ID sản phẩm (dạng JSON hoặc phân tách bằng dấu phẩy):</label>
                        <textarea name="product_ids" rows="3" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" placeholder="VD: prod_abc123, prod_def456">{{ old('product_ids', implode(', ', $selectedProducts)) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Nhập nhiều mã phân tách bằng dấu phẩy, hệ thống sẽ tự chuyển đổi.</p>
                    </div>
                </div>
            </div>

            <!-- 6. NÂNG CAO -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">6. Tùy chọn nâng cao</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="flex items-center">
                    <input type="checkbox" name="is_public" id="is_public" value="1" {{ $voucher->is_public ? 'checked' : '' }} class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="is_public" class="ml-2 text-sm text-gray-700">Công khai trên kho Voucher</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="auto_apply" id="auto_apply" value="1" {{ $voucher->auto_apply ? 'checked' : '' }} class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="auto_apply" class="ml-2 text-sm text-gray-700">Tự động áp dụng ở Giỏ hàng</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="require_save_to_user" id="require_save_to_user" value="1" {{ $voucher->require_save_to_user ? 'checked' : '' }} class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="require_save_to_user" class="ml-2 text-sm text-gray-700" title="Bỏ chọn = Hệ thống tự hiển thị khi đủ điều kiện, KHÔNG cần khách lưu">
                        Phải lưu vào tài khoản mới dùng
                        <span class="text-gray-400 text-xs block">Bỏ chọn = Tự xuất hiện khi thanh toán</span>
                    </label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_stackable" id="is_stackable" value="1" {{ $voucher->is_stackable ? 'checked' : '' }} class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="is_stackable" class="ml-2 text-sm text-gray-700">Cho phép ghép với mã khác</label>
                </div>
            </div>

            <div class="flex justify-end gap-4 border-t pt-6">
                <a href="{{ route('admin.vouchers.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">Hủy</a>
                <button type="submit" class="px-6 py-2.5 bg-red-500 text-white font-medium rounded-lg shadow-sm hover:bg-red-600 transition-colors">Lưu cập nhật</button>
            </div>
        </form>
    </fieldset>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('discount_type');
    const wrapperDiscountValue = document.getElementById('wrapper_discount_value');
    const labelDiscountValue = document.getElementById('label_discount_value');
    const inputDiscountValue = document.getElementById('discount_value');
    const maxDiscountWrapper = document.getElementById('wrapper_max_discount');
    const labelMaxDiscount = document.getElementById('label_max_discount');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const freeShippingOption = typeSelect.querySelector('option[value="free_shipping"]');
    const scopeSectionWrapper = document.getElementById('section_scope_wrapper');

    function toggleFields() {
        let currentTab = 'order';
        const checkedTab = document.querySelector('input[name="type"]:checked');
        if (checkedTab) currentTab = checkedTab.value;
        const discountType = typeSelect.value;

        if (currentTab === 'shipping' && discountType === 'free_shipping') {
            if(wrapperDiscountValue) wrapperDiscountValue.classList.add('hidden');
            if(inputDiscountValue) inputDiscountValue.value = 0;
            if(maxDiscountWrapper) maxDiscountWrapper.classList.add('hidden');
            return;
        }

        if (discountType === 'percent') {
            if(wrapperDiscountValue) wrapperDiscountValue.classList.remove('hidden');
            if(labelDiscountValue) labelDiscountValue.textContent = 'Mức giảm (%) *';
            if(inputDiscountValue) inputDiscountValue.setAttribute('max', '100');
            if(maxDiscountWrapper) maxDiscountWrapper.classList.remove('hidden');
            if (labelMaxDiscount) {
                labelMaxDiscount.textContent = currentTab === 'shipping'
                    ? 'Hỗ trợ ship tối đa (VNĐ)'
                    : 'Giảm tối đa (VNĐ)';
            }
        } else if (discountType === 'free_shipping') {
            if(wrapperDiscountValue) wrapperDiscountValue.classList.add('hidden');
            if(inputDiscountValue) inputDiscountValue.value = 0;
            if(maxDiscountWrapper) maxDiscountWrapper.classList.add('hidden');
        } else {
            if(wrapperDiscountValue) wrapperDiscountValue.classList.remove('hidden');
            if(labelDiscountValue) labelDiscountValue.textContent = 'Mức giảm (VNĐ) *';
            if(inputDiscountValue) inputDiscountValue.removeAttribute('max');
            if(maxDiscountWrapper) maxDiscountWrapper.classList.add('hidden');
        }
    }
    typeSelect.addEventListener('change', toggleFields);

    function toggleVoucherType() {
        const checkedTab = document.querySelector('input[name="type"]:checked');
        if (!checkedTab) return;
        const currentTab = checkedTab.value;
        if (currentTab === 'shipping') {
            if(freeShippingOption) freeShippingOption.style.display = 'block';
            if(scopeSectionWrapper) scopeSectionWrapper.classList.add('hidden');
        } else {
            if(freeShippingOption) freeShippingOption.style.display = 'none';
            if(scopeSectionWrapper) scopeSectionWrapper.classList.remove('hidden');
            if (typeSelect.value === 'free_shipping') typeSelect.value = 'fixed';
        }
        toggleFields();
    }
    typeRadios.forEach(radio => radio.addEventListener('change', toggleVoucherType));
    toggleVoucherType();

    const applyToSelect = document.getElementById('apply_to');
    const wrapperTiers = document.getElementById('wrapper_specific_tiers');
    const wrapperUsers = document.getElementById('wrapper_specific_users');
    function toggleAudienceFields() {
        if(!applyToSelect) return;
        const applyTo = applyToSelect.value;
        if(wrapperTiers) wrapperTiers.classList.add('hidden');
        if(wrapperUsers) wrapperUsers.classList.add('hidden');
        if (applyTo === 'specific_tiers' && wrapperTiers) wrapperTiers.classList.remove('hidden');
        else if (applyTo === 'specific_users' && wrapperUsers) wrapperUsers.classList.remove('hidden');
    }
    if(applyToSelect) applyToSelect.addEventListener('change', toggleAudienceFields);
    toggleAudienceFields();

    // --- PHẠM VI: Toàn / Danh mục / Sản phẩm ---
    const scopeRadios = document.querySelectorAll('input[name="scope"]');
    const wrapperCategories = document.getElementById('wrapper_specific_categories');
    const wrapperProducts = document.getElementById('wrapper_specific_products');

    function toggleScopeFields() {
        const selectedScope = document.querySelector('input[name="scope"]:checked');
        if (!selectedScope) return;

        if(wrapperCategories) wrapperCategories.classList.add('hidden');
        if(wrapperProducts) wrapperProducts.classList.add('hidden');

        if (selectedScope.value === 'specific_categories') {
            if(wrapperCategories) wrapperCategories.classList.remove('hidden');
        } else if (selectedScope.value === 'specific_products') {
            if(wrapperProducts) wrapperProducts.classList.remove('hidden');
        } else {
            // all — không xóa dữ liệu ở input, chỉ ẩn
        }
    }
    scopeRadios.forEach(radio => radio.addEventListener('change', toggleScopeFields));
    toggleScopeFields();
});

function validateDates() {
    const start = document.querySelector('input[name="starts_at"]').value;
    const end = document.querySelector('input[name="expires_at"]').value;
    if (start && end && new Date(end) < new Date(start)) {
        alert('⚠️ Thời gian kết thúc phải sau thời gian bắt đầu!');
        return false;
    }
    return true;
}
</script>
@endsection