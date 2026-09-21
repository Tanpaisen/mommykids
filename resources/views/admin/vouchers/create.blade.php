@extends('admin.layouts.app')
@section('content')
<div class="px-6 py-8 max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Thêm mới Voucher</h2>
            <p class="text-sm text-gray-500 mt-1">Thiết lập các điều kiện và giới hạn cho mã ưu đãi</p>
        </div>
        <a href="{{ route('admin.vouchers.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
            &larr; Quay lại danh sách
        </a>
    </div>

    <form action="{{ route('admin.vouchers.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        @csrf

        <fieldset @cannot('vouchers.manage') disabled @endcannot>

            <!-- TABS -->
            <div class="flex p-1 bg-gray-100 rounded-lg mb-8 w-fit">
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="order" class="peer hidden" {{ old('type', 'order') === 'order' ? 'checked' : '' }}>
                    <div class="px-6 py-2 rounded-md peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-red-600 font-bold text-sm text-gray-500 transition-all">
                        🛒 Mã Đơn Hàng (Sản phẩm)
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="shipping" class="peer hidden" {{ old('type') === 'shipping' ? 'checked' : '' }}>
                    <div class="px-6 py-2 rounded-md peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-red-600 font-bold text-sm text-gray-500 transition-all">
                        🚚 Mã Vận Chuyển (Freeship)
                    </div>
                </label>
            </div>

            <!-- 1. THÔNG TIN CƠ BẢN -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">1. Thông tin cơ bản</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã Voucher (Code) *</label>
                    <div class="relative">
                        <input type="text" name="code" id="voucher_code" value="{{ old('code') }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 uppercase pr-32"
                            placeholder="VD: MEBE50K">
                        <button type="button" id="btn_generate_code"
                            class="absolute right-1 top-1 bottom-1 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded border border-gray-200 transition-colors flex items-center">
                            🎲 Tạo ngẫu nhiên
                        </button>
                    </div>
                    @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="VD: Giảm 50K cho đơn 300K">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả (Tùy chọn)</label>
                    <textarea name="description" rows="2"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- 2. CẤU HÌNH GIẢM GIÁ -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">2. Thiết lập giảm giá</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại ưu đãi *</label>
                    <select name="discount_type" id="discount_type"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="fixed" {{ old('discount_type', 'fixed') === 'fixed' ? 'selected' : '' }}>Giảm số tiền (VNĐ)</option>
                        <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Giảm phần trăm (%)</option>
                        <option value="free_shipping" {{ old('discount_type') === 'free_shipping' ? 'selected' : '' }}>Miễn phí vận chuyển</option>
                    </select>
                </div>

                <div id="wrapper_discount_value">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="label_discount_value">Mức giảm (VNĐ) *</label>
                    <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value', 0) }}" min="0"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('discount_value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div id="wrapper_max_discount" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="label_max_discount">Giảm tối đa (VNĐ)</label>
                    <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Để trống = Không giới hạn">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount" value="{{ old('min_order_amount', 0) }}" min="0"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <!-- 3. GIỚI HẠN & THỜI GIAN -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">3. Giới hạn & Thời gian</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tổng lượt dùng toàn hệ thống</label>
                    <input type="number" name="total_quantity" value="{{ old('total_quantity') }}" min="1"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Để trống = Không giới hạn">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lượt dùng tối đa mỗi khách hàng *</label>
                    <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', 1) }}" required min="1"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('expires_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- 4. ĐỐI TƯỢNG & TRẠNG THÁI -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">4. Đối tượng & Trạng thái</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Đối tượng áp dụng *</label>
                    <select name="apply_to" id="apply_to"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="all" {{ old('apply_to', 'all') === 'all' ? 'selected' : '' }}>Tất cả khách hàng</option>
                        <option value="new_user" {{ old('apply_to') === 'new_user' ? 'selected' : '' }}>Khách hàng mới</option>
                        <option value="specific_tiers" {{ old('apply_to') === 'specific_tiers' ? 'selected' : '' }}>Chỉ định Hạng thành viên</option>
                        <option value="specific_users" {{ old('apply_to') === 'specific_users' ? 'selected' : '' }}>Chỉ định Khách hàng cụ thể</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái phát hành *</label>
                    <select name="status"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Bản nháp (Chưa kích hoạt)</option>
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Hoạt động ngay</option>
                    </select>
                </div>
            </div>

            <!-- Hạng thành viên -->
            <div id="wrapper_specific_tiers" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-3">Chọn các hạng được phép áp dụng:</label>
                <div class="flex flex-wrap gap-6">
                    @foreach(['member' => 'Member', 'silver' => 'Silver', 'gold' => 'Gold', 'diamond' => 'Diamond'] as $val => $name)
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="tiers[]" value="{{ $val }}"
                            {{ in_array($val, old('tiers', [])) ? 'checked' : '' }}
                            class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-700 {{ $val === 'gold' ? 'font-semibold text-yellow-600' : ($val === 'diamond' ? 'font-bold text-blue-600' : '') }}">{{ $name }}</span>
                    </label>
                    @endforeach
                </div>
                @error('tiers')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            <!-- Danh sách khách hàng -->
            <div id="wrapper_specific_users" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-1">Danh sách Email khách hàng:</label>
                <p class="text-xs text-gray-500 mb-2">Nhập Email, cách nhau bằng dấu phẩy (,)</p>
                <textarea name="user_emails" rows="3"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="a@gmail.com, b@yahoo.com">{{ old('user_emails') }}</textarea>
                @error('user_emails')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            <!-- 5. PHẠM VI ÁP DỤNG -->
            <div id="section_scope_wrapper">
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">5. Phạm vi áp dụng</h3>
                <div class="mb-6">
                    <div class="flex gap-6 mb-4 flex-wrap">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="scope" value="all"
                                {{ old('scope', 'all') === 'all' ? 'checked' : '' }}
                                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Áp dụng toàn Shop</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="scope" value="specific_categories"
                                {{ old('scope') === 'specific_categories' ? 'checked' : '' }}
                                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Chỉ định Danh mục</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="scope" value="specific_products"
                                {{ old('scope') === 'specific_products' ? 'checked' : '' }}
                                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Chỉ định Sản phẩm</span>
                        </label>
                    </div>

                    <!-- Chọn Danh mục -->
                    <div id="wrapper_specific_categories" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Chọn danh mục áp dụng:</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @if(isset($categories) && $categories->count())
                                @foreach($categories as $cat)
                                <label class="flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                        {{ in_array($cat->id, old('category_ids', [])) ? 'checked' : '' }}
                                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $cat->name }}</span>
                                </label>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-500 col-span-full">Chưa có danh mục nào.</p>
                            @endif
                        </div>
                        @error('category_ids')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                    </div>

                    <!-- Chọn Sản phẩm -->
                    <div id="wrapper_specific_products" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Chọn sản phẩm áp dụng:</label>
                        <p class="text-xs text-gray-500 mb-3">Gõ tên/SKU để tìm, click để chọn</p>
                        <input type="text" id="product_search" placeholder="🔍 Tìm sản phẩm..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 mb-3">
                        <div id="product_search_results" class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg bg-white hidden mb-3"></div>
                        <div id="selected_products" class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                            <span class="text-gray-400 text-xs">Chưa chọn sản phẩm nào</span>
                        </div>
                        <input type="hidden" name="product_ids" id="product_ids_input" value="{{ old('product_ids', '[]') }}">
                        @error('product_ids')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- 6. TÙY CHỌN NÂNG CAO -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">6. Tùy chọn nâng cao</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="flex items-center">
                    <input type="checkbox" name="is_public" id="is_public" value="1"
                        {{ old('is_public', 1) ? 'checked' : '' }}
                        class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="is_public" class="ml-2 text-sm text-gray-700">Công khai trên kho Voucher</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="auto_apply" id="auto_apply" value="1"
                        {{ old('auto_apply') ? 'checked' : '' }}
                        class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="auto_apply" class="ml-2 text-sm text-gray-700">Tự động áp dụng ở Giỏ hàng</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="require_save_to_user" id="require_save_to_user" value="1"
                        {{ old('require_save_to_user') ? 'checked' : '' }}
                        class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="require_save_to_user" class="ml-2 text-sm text-gray-700">
                        Phải lưu vào tài khoản mới dùng
                        <span class="text-gray-400 text-xs block">Bỏ chọn = Tự xuất hiện khi thanh toán</span>
                    </label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_stackable" id="is_stackable" value="1"
                        {{ old('is_stackable') ? 'checked' : '' }}
                        class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                    <label for="is_stackable" class="ml-2 text-sm text-gray-700">
                        Cho phép kết hợp với mã khác
                        <span class="text-gray-400 text-xs block">Đồng áp nhiều mã cùng lúc</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 border-t pt-6">
                <a href="{{ route('admin.vouchers.index') }}"
                    class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">Hủy</a>
                <button type="submit" onclick="return validateDates()"
                    class="px-6 py-2.5 bg-red-500 text-white font-medium rounded-lg hover:bg-red-600 transition-colors shadow-sm">Lưu cấu hình</button>
            </div>
        </fieldset>
    </form>
</div>

<script>
// ✅ Đưa ra ngoài phạm vi DOMContentLoaded để onclick gọi được
window.validateDates = function() {
    const start = document.querySelector('input[name="starts_at"]').value;
    const end = document.querySelector('input[name="expires_at"]').value;
    if (start && end && new Date(end) < new Date(start)) {
        alert('⚠️ Thời gian kết thúc phải sau thời gian bắt đầu!');
        return false;
    }
    return true;
};

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('discount_type');
    const wrapperDiscountValue = document.getElementById('wrapper_discount_value');
    const labelDiscountValue = document.getElementById('label_discount_value');
    const inputDiscountValue = document.getElementById('discount_value');
    const maxDiscountWrapper = document.getElementById('wrapper_max_discount');
    const labelMaxDiscount = document.getElementById('label_max_discount');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const freeShippingOption = typeSelect.querySelector('option[value="free_shipping"]');
    const scopeSection = document.getElementById('section_scope_wrapper');
    const applyToSelect = document.getElementById('apply_to');
    const wrapperTiers = document.getElementById('wrapper_specific_tiers');
    const wrapperUsers = document.getElementById('wrapper_specific_users');
    const scopeRadios = document.querySelectorAll('input[name="scope"]');
    const wrapperCategories = document.getElementById('wrapper_specific_categories');
    const wrapperProducts = document.getElementById('wrapper_specific_products');

    // --- 1. Ẩn/Hiện ô nhập giảm giá ---
    function toggleFields() {
        const checkedTab = document.querySelector('input[name="type"]:checked');
        const currentTab = checkedTab ? checkedTab.value : 'order';
        const discountType = typeSelect.value;

        if (currentTab === 'shipping' && discountType === 'free_shipping') {
            wrapperDiscountValue.classList.add('hidden');
            inputDiscountValue.value = 0;
            maxDiscountWrapper.classList.add('hidden');
            return;
        }

        if (discountType === 'percent') {
            wrapperDiscountValue.classList.remove('hidden');
            labelDiscountValue.textContent = 'Mức giảm (%) *';
            inputDiscountValue.setAttribute('max', '100');
            maxDiscountWrapper.classList.remove('hidden');
            labelMaxDiscount.textContent = currentTab === 'shipping' ? 'Hỗ trợ ship tối đa (VNĐ)' : 'Giảm tối đa (VNĐ)';
        } else if (discountType === 'free_shipping') {
            wrapperDiscountValue.classList.add('hidden');
            inputDiscountValue.value = 0;
            maxDiscountWrapper.classList.add('hidden');
        } else {
            wrapperDiscountValue.classList.remove('hidden');
            labelDiscountValue.textContent = 'Mức giảm (VNĐ) *';
            inputDiscountValue.removeAttribute('max');
            maxDiscountWrapper.classList.add('hidden');
        }
    }
    typeSelect.addEventListener('change', toggleFields);

    // --- 2. Chuyển tab Đơn hàng / Vận chuyển ---
    function toggleVoucherType() {
        const checkedTab = document.querySelector('input[name="type"]:checked');
        if (!checkedTab) return;
        const currentTab = checkedTab.value;

        if (currentTab === 'shipping') {
            freeShippingOption.style.display = 'block';
            scopeSection.classList.add('hidden');
            if (typeSelect.value !== 'free_shipping') {
                typeSelect.value = 'free_shipping';
            }
        } else {
            freeShippingOption.style.display = 'none';
            scopeSection.classList.remove('hidden');
            if (typeSelect.value === 'free_shipping') {
                typeSelect.value = 'fixed';
            }
        }
        toggleFields();
    }
    typeRadios.forEach(r => r.addEventListener('change', toggleVoucherType));
    toggleVoucherType();

    // --- 3. Đối tượng áp dụng ---
    function toggleAudienceFields() {
        const val = applyToSelect.value;
        wrapperTiers.classList.add('hidden');
        wrapperUsers.classList.add('hidden');
        if (val === 'specific_tiers') wrapperTiers.classList.remove('hidden');
        else if (val === 'specific_users') wrapperUsers.classList.remove('hidden');
    }
    applyToSelect.addEventListener('change', toggleAudienceFields);
    toggleAudienceFields();

    // --- 4. Phạm vi áp dụng ---
    function toggleScopeFields() {
        const selected = document.querySelector('input[name="scope"]:checked');
        if (!selected) return;
        const val = selected.value;
        wrapperCategories.classList.add('hidden');
        wrapperProducts.classList.add('hidden');
        if (val === 'specific_categories') wrapperCategories.classList.remove('hidden');
        else if (val === 'specific_products') wrapperProducts.classList.remove('hidden');
    }
    scopeRadios.forEach(r => r.addEventListener('change', toggleScopeFields));
    toggleScopeFields();

    // --- 5. Tạo mã ngẫu nhiên ---
    document.getElementById('btn_generate_code').addEventListener('click', function() {
        const rnd = Math.random().toString(36).slice(2, 6).toUpperCase();
        const time = Date.now().toString().slice(-4);
        document.getElementById('voucher_code').value = `KM-${rnd}-${time}`;
    });

    // --- 6. Tìm & chọn sản phẩm ---
    const productSearch = document.getElementById('product_search');
    const productResults = document.getElementById('product_search_results');
    const selectedBox = document.getElementById('selected_products');
    const productInput = document.getElementById('product_ids_input');
    let selectedProducts = [];

    // Khôi phục từ old() — SỬA: chỉ có id, cần bổ sung name
    try {
        const saved = productInput.value ? JSON.parse(productInput.value) : [];
        if (Array.isArray(saved)) {
            // Nếu chỉ là mảng id → chuyển sang object, hiển thị tạm id
            selectedProducts = saved.map(item => {
                if (typeof item === 'object' && item.id) return item;
                return { id: item, name: `ID: ${item}` };
            });
        }
    } catch(e) {
        selectedProducts = [];
    }

    function renderProducts() {
        selectedBox.innerHTML = '';
        if (!selectedProducts.length) {
            selectedBox.innerHTML = '<span class="text-gray-400 text-xs">Chưa chọn sản phẩm nào</span>';
            updateInput();
            return;
        }
        selectedProducts.forEach((p, idx) => {
            const tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full';
            tag.innerHTML = `${p.name} <button type="button" class="font-bold ml-1 hover:text-red-900">×</button>`;
            tag.querySelector('button').onclick = () => {
                selectedProducts.splice(idx, 1);
                renderProducts();
            };
            selectedBox.appendChild(tag);
        });
        updateInput();
    }

    function updateInput() {
        // Lưu chỉ mảng id cho Controller dễ xử lý
        productInput.value = JSON.stringify(selectedProducts.map(p => p.id));
    }

    if (productSearch) {
        let timer;
        productSearch.addEventListener('input', function() {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) {
                productResults.classList.add('hidden');
                return;
            }
            timer = setTimeout(() => {
                fetch(`/admin/san-pham/search?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(list => {
                        productResults.innerHTML = '';
                        if (!list.length) {
                            productResults.innerHTML = '<p class="p-3 text-sm text-gray-400">Không tìm thấy sản phẩm.</p>';
                        }
                        list.forEach(p => {
                            if (selectedProducts.find(x => String(x.id) === String(p.id))) return;
                            const item = document.createElement('div');
                            item.className = 'p-2 hover:bg-red-50 cursor-pointer text-sm border-b last:border-0';
                            item.textContent = `${p.name}`;
                            item.onclick = () => {
                                selectedProducts.push({ id: p.id, name: p.name });
                                renderProducts();
                                productSearch.value = '';
                                productResults.classList.add('hidden');
                            };
                            productResults.appendChild(item);
                        });
                        productResults.classList.remove('hidden');
                    })
                    .catch(() => {
                        productResults.innerHTML = '<p class="p-3 text-sm text-red-400">Lỗi tìm kiếm</p>';
                        productResults.classList.remove('hidden');
                    });
            }, 300);
        });

        // Đóng gợi ý khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!productSearch.contains(e.target) && !productResults.contains(e.target)) {
                productResults.classList.add('hidden');
            }
        });
    }

    renderProducts();
});
</script>
@endsection