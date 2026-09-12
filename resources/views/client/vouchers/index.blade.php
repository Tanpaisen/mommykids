@extends('client.layouts.app')

@section('content')
<!-- Mở rộng container ra max-w-7xl để tận dụng tối đa màn hình Desktop -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans bg-gray-50 min-h-screen">
    
    <!-- 1. HEADER & Ô NHẬP MÃ (Dàn ngang trên Desktop) -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm mb-8 flex flex-col md:flex-row items-center justify-between gap-6 border border-gray-100">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kho Voucher của bạn</h1>
            <p class="text-sm text-gray-500 mt-2">Lưu ngay mã ưu đãi để mua sắm tiết kiệm hơn tại MommyKids</p>
        </div>
        
        <!-- Form nhập mã -->
        <div class="flex gap-3 w-full md:w-[400px]">
            <input type="text" placeholder="Nhập mã voucher..." class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-pink-300 focus:border-pink-300 outline-none uppercase font-bold text-gray-700 placeholder-gray-400 transition-all">
            <button class="bg-pink-500 hover:bg-pink-600 text-white font-bold rounded-xl px-6 py-3 text-sm transition-all shadow-md shadow-pink-200 hover:shadow-lg whitespace-nowrap active:scale-95">
                LƯU MÃ
            </button>
        </div>
    </div>

    <!-- 2. KHU VỰC BANNER & THỐNG KÊ (Chia cột trên Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Banner Khuyến mãi (Chiếm 2 phần) -->
        <div class="lg:col-span-2 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-2xl p-6 md:p-8 flex flex-col justify-center text-white shadow-sm relative overflow-hidden">
            <!-- Vòng tròn trang trí -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
            <div class="absolute right-20 -bottom-10 w-24 h-24 bg-white opacity-10 rounded-full"></div>
            
            <div class="relative z-10">
                <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-sm">Khách hàng mới</span>
                <h2 class="text-2xl md:text-3xl font-bold mt-3 mb-2">Miễn phí vận chuyển 100%</h2>
                <p class="text-emerald-50 max-w-md">Nhận ngay ưu đãi Freeship không giới hạn cho đơn hàng đầu tiên của ba mẹ. Số lượng có hạn!</p>
                <button class="mt-5 bg-white text-emerald-600 font-bold px-6 py-2.5 rounded-full shadow-sm hover:scale-105 transition-transform w-fit">
                    Khám phá ngay
                </button>
            </div>
        </div>

        <!-- Block Tích xu đổi quà (Chiếm 1 phần) -->
        <div class="bg-gradient-to-br from-orange-400 to-pink-500 rounded-2xl p-6 md:p-8 flex flex-col items-center justify-center text-white shadow-sm relative overflow-hidden text-center">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl mb-3 backdrop-blur-sm shadow-inner">
                    🎁
                </div>
                <h3 class="font-bold text-lg">Tích xu đổi quà</h3>
                <p class="text-sm opacity-90 mt-1">Số dư hiện tại</p>
                <p class="font-black text-3xl text-yellow-200 drop-shadow-md mt-1">1,250 <span class="text-lg font-bold">Xu</span></p>
                <button class="mt-4 bg-white text-pink-500 text-sm font-bold px-8 py-2.5 rounded-full shadow-md hover:bg-gray-50 transition-colors w-full">
                    Đổi thưởng ngay
                </button>
            </div>
        </div>
    </div>

    <!-- 3. TABS ĐIỀU HƯỚNG -->
    <div class="flex items-center justify-between border-b border-gray-200 mb-6 pb-4">
        <div class="flex gap-2 md:gap-4 overflow-x-auto scrollbar-hide w-full" id="voucher-tabs">
            <button data-filter="all" class="tab-btn bg-pink-100 text-pink-600 border border-pink-200 font-bold px-5 py-2 rounded-full text-sm whitespace-nowrap transition-colors">
                Tất cả ({{ $vouchers->count() }})
            </button>
            <button data-filter="order" class="tab-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-800 font-medium px-5 py-2 rounded-full text-sm whitespace-nowrap transition-colors">
                🛒 Mã Đơn hàng ({{ $orderVouchers->count() }})
            </button>
            <button data-filter="shipping" class="tab-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-100 hover:text-gray-800 font-medium px-5 py-2 rounded-full text-sm whitespace-nowrap transition-colors">
                🚚 Phí Vận chuyển ({{ $shippingVouchers->count() }})
            </button>
        </div>
        <a href="#" class="hidden md:block text-sm font-medium text-gray-500 hover:text-pink-500 whitespace-nowrap ml-4 transition-colors">Xem lịch sử ></a>
    </div>

    <!-- 4. DANH SÁCH VOUCHER -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="voucher-list">
        @php
            $savedVoucherIds = [];
            if(auth()->check()) {
                // Pluck lấy mảng các ID voucher mà user này đã lưu
                $savedVoucherIds = auth()->user()->savedVouchers()->pluck('vouchers.id')->toArray();
            }
        @endphp

        @forelse($vouchers as $voucher)
            @php
                // --- XỬ LÝ MÀU SẮC & ICON THEO LOẠI MÃ ---
                $isShipping = $voucher->type === 'shipping';
                
                $leftBgClass = $isShipping ? 'bg-emerald-400 group-hover:bg-emerald-500 border-emerald-200/50' : 'bg-pink-500 group-hover:bg-pink-600 border-pink-200/50';
                $badgeClass = $isShipping ? 'bg-emerald-100 text-emerald-700' : 'bg-pink-100 text-pink-600';
                
                $icon = $isShipping ? '🚚' : '🛒';
                if (!$isShipping && $voucher->discount_type === 'percent') $icon = '✨';

                // --- XỬ LÝ TEXT HIỂN THỊ MỨC GIẢM ---
                $discountText = '';   
                $discountTitle = '';  
                
                if ($voucher->discount_type === 'percent') {
                    $discountText = 'Giảm<br>' . $voucher->discount_value . '%';
                    $discountTitle = 'Giảm ' . $voucher->discount_value . '%';
                    if ($voucher->max_discount_amount) {
                        $discountTitle .= ' (Tối đa ' . number_format($voucher->max_discount_amount, 0, ',', '.') . 'đ)';
                    }
                } elseif ($voucher->discount_type === 'free_shipping') {
                    $discountText = 'Free<br>Ship';
                    $discountTitle = 'Miễn phí vận chuyển';
                    if ($voucher->max_discount_amount) {
                        $discountTitle = 'Hỗ trợ tối đa ' . number_format($voucher->max_discount_amount, 0, ',', '.') . 'đ phí ship';
                    }
                } else {
                    $valK = $voucher->discount_value / 1000;
                    $discountText = 'Giảm<br>' . $valK . 'K';
                    $discountTitle = 'Giảm ' . number_format($voucher->discount_value, 0, ',', '.') . 'đ';
                }

                $expireText = $voucher->expires_at ? 'HSD: ' . \Carbon\Carbon::parse($voucher->expires_at)->format('d/m/Y H:i') : 'Không thời hạn';
                $minOrderText = $voucher->min_order_amount > 0 ? ' cho đơn từ ' . number_format($voucher->min_order_amount, 0, ',', '.') . 'đ' : '';
            @endphp

            <div class="voucher-item flex bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden relative transition-all hover:-translate-y-1 group" data-type="{{ $voucher->type }}">
                
                <!-- Nửa trái -->
                <div class="w-1/3 min-w-[100px] {{ $leftBgClass }} flex flex-col items-center justify-center p-4 border-r-2 border-dashed relative transition-colors">
                    <div class="absolute -top-3 -right-3 w-6 h-6 bg-gray-50 rounded-full"></div>
                    <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-gray-50 rounded-full"></div>
                    <span class="text-4xl mb-2 drop-shadow-sm">{{ $icon }}</span>
                    <span class="text-white font-bold text-xl text-center leading-tight">{!! $discountText !!}</span>
                </div>
                
                <!-- Nửa phải -->
                <div class="w-2/3 p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <span class="{{ $badgeClass }} text-[10px] md:text-xs font-bold px-2 py-0.5 rounded uppercase tracking-wide">
                                {{ $voucher->type === 'shipping' ? 'Vận chuyển' : 'Đơn hàng' }}
                            </span>
                            @if($voucher->total_quantity && $voucher->total_quantity <= 10)
                                <!-- Sắp hết: Báo động đỏ tạo hiệu ứng FOMO cho khách -->
                                <span class="text-[10px] font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-full animate-pulse">
                                    🔥 Sắp hết
                                </span>
                            @elseif($voucher->total_quantity)
                                <!-- Còn nhiều -->
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                    Đang diễn ra
                                </span>
                            @endif
                        </div>
                        <h4 class="font-bold text-gray-800 text-base leading-snug line-clamp-2" title="{{ $voucher->name }}">{{ $voucher->name }}</h4>
                        <p class="text-xs text-gray-500 mt-1 mb-1 font-medium">{{ $discountTitle }}{{ $minOrderText }}</p>
                        <p class="text-xs text-gray-400 flex items-center gap-1 {{ $voucher->expires_at && \Carbon\Carbon::parse($voucher->expires_at)->diffInHours(now()) < 24 ? 'text-red-500' : '' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $expireText }}
                        </p>
                    </div>
                    
                    <div class="flex justify-between items-end mt-4">
                        <button class="text-gray-400 hover:text-pink-500 text-xs font-medium transition-colors">Điều kiện</button>
                        <!-- Nút LƯU MÃ - Tích hợp gọi API -->
                        @if(in_array($voucher->id, $savedVoucherIds))
                            <!-- Trạng thái đã lưu -->
                            <button disabled class="bg-pink-500 text-white text-sm font-bold px-6 py-1.5 rounded-lg whitespace-nowrap opacity-80 cursor-not-allowed">
                                Đã cất ví ✔
                            </button>
                        @else
                            <!-- Trạng thái chưa lưu -->
                            <button onclick="saveVoucherCode('{{ $voucher->code }}', this)" class="border-2 border-pink-500 text-pink-500 hover:bg-pink-50 text-sm font-bold px-6 py-1.5 rounded-lg transition-all active:scale-95 whitespace-nowrap">
                                Lưu mã
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center flex flex-col items-center justify-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                <span class="text-6xl mb-4">🥺</span>
                <h3 class="text-xl font-bold text-gray-700">Chưa có mã ưu đãi nào</h3>
                <p class="text-gray-500 mt-2">Mẹ quay lại sau để săn voucher nhé!</p>
            </div>
        @endforelse

    </div>
</div>

<style>
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
</style>

<script>
    // 1. Chức năng Lọc theo Tabs
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-btn');
        const items = document.querySelectorAll('.voucher-item');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                tabs.forEach(t => {
                    t.classList.remove('bg-pink-100', 'text-pink-600', 'border-pink-200');
                    t.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                });
                
                this.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');
                this.classList.add('bg-pink-100', 'text-pink-600', 'border-pink-200');

                const filter = this.getAttribute('data-filter');
                items.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-type') === filter) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });

    // 2. Gọi API Lưu mã vào Ví 
    async function saveVoucherCode(code, btnElement) {
        // Tránh user spam click nhiều lần
        if (btnElement.disabled) return;

        const originalText = btnElement.innerText;
        btnElement.innerText = 'Đang lưu...';
        btnElement.disabled = true;

        try {
            // Lấy CSRF Token để Laravel bảo mật POST request
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/api/vouchers/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({ code: code })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                btnElement.innerText = 'Đã cất ví ✔';
                btnElement.classList.replace('text-pink-500', 'text-white');
                btnElement.classList.replace('hover:bg-pink-50', 'hover:bg-pink-600');
                btnElement.classList.add('bg-pink-500');
            } else {
                // Báo lỗi 
                btnElement.innerText = originalText;
                btnElement.disabled = false;

                // NẾU LÀ LỖI 401 (CHƯA ĐĂNG NHẬP) -> BẬT POPUP 
                if (response.status === 401) {
                    // Kiểm tra xem hàm openLoginModal có tồn tại ở layout cha không
                    if (typeof openLoginModal === 'function') {
                        openLoginModal(); 
                    } else {
                        alert('Vui lòng đăng nhập để lưu mã ưu đãi!');
                    }
                } else {
                    // Các lỗi khác (đã lưu rồi, hết lượt...)
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            }
        } catch (error) {
            console.error('Lỗi mạng:', error);
            alert('Mất kết nối đến máy chủ, vui lòng thử lại.');
            btnElement.innerText = originalText;
            btnElement.disabled = false;
        }
    }
</script>
@endsection