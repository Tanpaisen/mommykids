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
        
        <!-- THANH ĐIỀU HƯỚNG TABS (KHÔNG LOAD TRANG) -->
        <div class="flex p-1 bg-gray-100 rounded-lg mb-8 w-fit">
            <label class="cursor-pointer">
                <input type="radio" name="type" value="order" class="peer hidden" checked>
                <div class="px-6 py-2 rounded-md peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-red-600 font-bold text-sm text-gray-500 transition-all">
                    🛒 Mã Đơn Hàng (Sản phẩm)
                </div>
            </label>
            <label class="cursor-pointer">
                <input type="radio" name="type" value="shipping" class="peer hidden">
                <div class="px-6 py-2 rounded-md peer-checked:bg-white peer-checked:shadow-sm peer-checked:text-red-600 font-bold text-sm text-gray-500 transition-all">
                    🚚 Mã Vận Chuyển (Freeship)
                </div>
            </label>
        </div>

        <!-- 1. THÔNG TIN ĐỊNH DANH -->
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">1. Thông tin cơ bản</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã Voucher (Code) *</label>
                <div class="relative">
                    <input type="text" name="code" id="voucher_code" value="{{ old('code') }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 uppercase pr-32" placeholder="VD: MEBE50K">
                    <button type="button" id="btn_generate_code" class="absolute right-1 top-1 bottom-1 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded border border-gray-200 transition-colors flex items-center">
                        🎲 Tạo ngẫu nhiên
                    </button>
                </div>
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="VD: Giảm 50K cho đơn 300K">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả (Tùy chọn)</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- 2. CẤU HÌNH GIẢM GIÁ -->
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">2. Thiết lập giảm giá</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loại ưu đãi *</label>
                <select name="discount_type" id="discount_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="fixed">Giảm số tiền (VNĐ)</option>
                    <option value="percent">Giảm phần trăm (%)</option>
                    <option value="free_shipping">Miễn phí vận chuyển</option>
                </select>
            </div>
            
            <div id="wrapper_discount_value">
                <label class="block text-sm font-medium text-gray-700 mb-1" id="label_discount_value">Mức giảm (VNĐ) *</label>
                <input type="number" name="discount_value" id="discount_value" value="{{ old('discount_value', 0) }}" required min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                @error('discount_value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div id="wrapper_max_discount" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1" id="label_max_discount">Giảm tối đa (VNĐ)</label>
                <input type="number" name="max_discount_amount" value="{{ old('max_discount_amount') }}" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Để trống = Không giới hạn">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Đơn tối thiểu (VNĐ)</label>
                <input type="number" name="min_order_amount" value="{{ old('min_order_amount', 0) }}" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
        </div>

        <!-- 3. QUẢN LÝ LƯỢT DÙNG & THỜI GIAN -->
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">3. Giới hạn & Thời gian</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tổng lượt dùng toàn hệ thống</label>
                <input type="number" name="total_quantity" value="{{ old('total_quantity') }}" min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Để trống = Không giới hạn">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lượt dùng tối đa mỗi khách hàng *</label>
                <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', 1) }}" required min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
        </div>

        <!-- 4. ĐỐI TƯỢNG & TRẠNG THÁI -->
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">4. Đối tượng & Trạng thái</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Đối tượng áp dụng *</label>
                <select name="apply_to" id="apply_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="all">Tất cả khách hàng</option>
                    <option value="new_user">Khách hàng mới</option>
                    <option value="specific_tiers">Chỉ định Hạng thành viên</option>
                    <option value="specific_users">Chỉ định Khách hàng cụ thể</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái phát hành *</label>
                <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="draft">Bản nháp (Chưa kích hoạt)</option>
                    <option value="active">Hoạt động ngay</option>
                </select>
            </div>
        </div>

        <!-- Khối ẩn: Chọn Hạng Thành Viên -->
        <div id="wrapper_specific_tiers" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-3">Chọn các hạng được phép áp dụng:</label>
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="tiers[]" value="member" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">Member (Thành viên mới)</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="tiers[]" value="silver" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">Silver (Bạc)</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="tiers[]" value="gold" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700 font-semibold text-yellow-600">Gold (Vàng)</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="tiers[]" value="diamond" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700 font-bold text-blue-600">Diamond (Kim cương)</span>
                </label>
            </div>
            @error('tiers')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        </div>

        <!-- Khối ẩn: Nhập danh sách Khách hàng -->
        <div id="wrapper_specific_users" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-1">Danh sách Email khách hàng:</label>
            <p class="text-xs text-gray-500 mb-2">Nhập Email của các khách hàng được tặng mã, cách nhau bằng dấu phẩy (,)</p>
            <textarea name="user_emails" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="VD: nguyenvan_a@gmail.com, khachvip@yahoo.com">{{ old('user_emails') }}</textarea>
            @error('user_emails')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        </div>

        <!-- Khối bọc khu vực Danh mục để JS điều khiển -->
        <div id="section_category_wrapper">
            <!-- 5. PHẠM VI ÁP DỤNG (DANH MỤC) -->
            <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">5. Phạm vi áp dụng</h3>
            <div class="mb-6">
                <div class="flex gap-6 mb-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="scope" value="all" checked class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Áp dụng toàn Shop</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="scope" value="specific_categories" class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Chỉ định Danh mục cụ thể</span>
                    </label>
                </div>

                <!-- Khối ẩn: Chọn Danh mục -->
                <div id="wrapper_specific_categories" class="hidden bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <label class="block text-sm font-bold text-gray-700 mb-3">Chọn các danh mục được phép giảm giá:</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @if(isset($categories) && $categories->count() > 0)
                            @foreach($categories as $category)
                            <label class="flex items-center cursor-pointer hover:bg-gray-100 p-2 rounded transition-colors">
                                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $category->name }}</span>
                            </label>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 col-span-full">Chưa có danh mục nào trên hệ thống.</p>
                        @endif
                    </div>
                    @error('category_ids')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <!-- 6. TÙY CHỌN NÂNG CAO -->
        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b mt-8">6. Tùy chọn nâng cao</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="flex items-center">
                <input type="checkbox" name="is_public" id="is_public" value="1" checked class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                <label for="is_public" class="ml-2 text-sm text-gray-700">Công khai trên kho Voucher</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="auto_apply" id="auto_apply" value="1" class="w-5 h-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                <label for="auto_apply" class="ml-2 text-sm text-gray-700">Tự động áp dụng ở Giỏ hàng</label>
            </div>
        </div>
        
        <div class="flex justify-end gap-4 border-t pt-6">
            <a href="{{ route('admin.vouchers.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">Hủy</a>
            <button type="submit" class="px-6 py-2.5 bg-red-500 text-white font-medium rounded-lg hover:bg-red-600 transition-colors shadow-sm">Lưu cấu hình</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // CÁC BIẾN DOM DÙNG CHUNG
        const typeSelect = document.getElementById('discount_type');
        const wrapperDiscountValue = document.getElementById('wrapper_discount_value'); 
        const labelDiscountValue = document.getElementById('label_discount_value');
        const inputDiscountValue = document.getElementById('discount_value');
        
        const maxDiscountWrapper = document.getElementById('wrapper_max_discount');
        const labelMaxDiscount = document.getElementById('label_max_discount');
        
        const typeRadios = document.querySelectorAll('input[name="type"]');
        const freeShippingOption = typeSelect.querySelector('option[value="free_shipping"]');
        const categorySectionWrapper = document.getElementById('section_category_wrapper');

        // --- 1. HÀM XỬ LÝ ẨN/HIỆN Ô NHẬP TIỀN & ĐỔI LABEL ---
        function toggleFields() {
            let currentTab = 'order';
            const checkedTab = document.querySelector('input[name="type"]:checked');
            if (checkedTab) currentTab = checkedTab.value;

            const discountType = typeSelect.value;
            
            // Xử lý riêng khi đang ở Tab Vận chuyển và chọn Miễn phí vận chuyển
            if (currentTab === 'shipping' && discountType === 'free_shipping') {
                if(wrapperDiscountValue) wrapperDiscountValue.classList.add('hidden');
                if(inputDiscountValue) inputDiscountValue.value = 0; 
                
                if(maxDiscountWrapper) {
                    maxDiscountWrapper.classList.add('hidden'); 
                    const maxInput = maxDiscountWrapper.querySelector('input');
                    if(maxInput) maxInput.value = ''; 
                }
                return; // Thoát luôn, không chạy xuống dưới nữa
            }

            if (discountType === 'percent') {
                if(wrapperDiscountValue) wrapperDiscountValue.classList.remove('hidden');
                if(labelDiscountValue) labelDiscountValue.textContent = 'Mức giảm (%) *';
                if(inputDiscountValue) inputDiscountValue.setAttribute('max', '100');
                
                if(maxDiscountWrapper) maxDiscountWrapper.classList.remove('hidden');
                
                if (labelMaxDiscount) {
                    if (currentTab === 'shipping') {
                        labelMaxDiscount.textContent = 'Hỗ trợ ship tối đa (VNĐ)';
                    } else {
                        labelMaxDiscount.textContent = 'Giảm tối đa (VNĐ)';
                    }
                }
                
            } else if (discountType === 'free_shipping') {
                if(wrapperDiscountValue) wrapperDiscountValue.classList.add('hidden');
                if(inputDiscountValue) inputDiscountValue.value = 0; 

                if(maxDiscountWrapper) {
                    maxDiscountWrapper.classList.add('hidden'); 
                    const maxInput = maxDiscountWrapper.querySelector('input');
                    if(maxInput) maxInput.value = ''; 
                }
                
            } else { 
                // FIXED (Giảm số tiền)
                if(wrapperDiscountValue) wrapperDiscountValue.classList.remove('hidden');
                if(labelDiscountValue) labelDiscountValue.textContent = 'Mức giảm (VNĐ) *';
                if(inputDiscountValue) inputDiscountValue.removeAttribute('max');
                
                if(maxDiscountWrapper) {
                    maxDiscountWrapper.classList.add('hidden');
                    const maxInput = maxDiscountWrapper.querySelector('input');
                    if(maxInput) maxInput.value = '';
                }
            }
        }
        typeSelect.addEventListener('change', toggleFields);

        // --- 2. HÀM XỬ LÝ KHI CHUYỂN TAB VOUCHER (Đơn hàng / Vận chuyển) ---
        function toggleVoucherType() {
            const checkedTab = document.querySelector('input[name="type"]:checked');
            if (!checkedTab) return;
            const currentTab = checkedTab.value;

            if (currentTab === 'shipping') {
                if(freeShippingOption) freeShippingOption.style.display = 'block';
                if(categorySectionWrapper) categorySectionWrapper.classList.add('hidden');
            } else {
                if(freeShippingOption) freeShippingOption.style.display = 'none';
                if(categorySectionWrapper) categorySectionWrapper.classList.remove('hidden');

                if (typeSelect.value === 'free_shipping') {
                    typeSelect.value = 'fixed';
                }
            }
            toggleFields();
        }
        typeRadios.forEach(radio => radio.addEventListener('change', toggleVoucherType));
        toggleVoucherType();

        // --- 3. HÀM XỬ LÝ ĐỐI TƯỢNG (TIER / USER) ---
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

        // --- 4. HÀM XỬ LÝ PHẠM VI DANH MỤC ---
        const scopeRadios = document.querySelectorAll('input[name="scope"]');
        const wrapperCategories = document.getElementById('wrapper_specific_categories');

        function toggleScopeFields() {
            const selectedScope = document.querySelector('input[name="scope"]:checked');
            if (!selectedScope || !wrapperCategories) return;
            
            if (selectedScope.value === 'specific_categories') {
                wrapperCategories.classList.remove('hidden');
            } else {
                wrapperCategories.classList.add('hidden');
                document.querySelectorAll('input[name="category_ids[]"]').forEach(cb => cb.checked = false);
            }
        }
        scopeRadios.forEach(radio => radio.addEventListener('change', toggleScopeFields));
        toggleScopeFields();

        // --- 5. HÀM SINH MÃ VOUCHER NGẪU NHIÊN ---
        const btnGenerateCode = document.getElementById('btn_generate_code');
        const inputVoucherCode = document.getElementById('voucher_code');
        
        if (btnGenerateCode && inputVoucherCode) {
            btnGenerateCode.addEventListener('click', function() {
                // Lấy 4 ký tự random (Chữ và số)
                const randomStr = Math.random().toString(36).substring(2, 6).toUpperCase(); 
                // Lấy 4 số cuối của mốc thời gian hiện tại (Millisecond) để chốt hạ không bao giờ trùng
                const timeStr = Date.now().toString().slice(-4); 
                
                // Gộp lại, ví dụ kết quả: KM-X8J9-9432
                inputVoucherCode.value = 'KM-' + randomStr + '-' + timeStr;
            });
        }
    });
</script>
@endsection