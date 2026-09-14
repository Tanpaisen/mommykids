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

    <!-- 4. KHU VỰC SĂN VOUCHER KIỂU TINDER (SWIPE CARD DECK) -->
    <div class="max-w-md mx-auto relative mb-12">
        
        <!-- Dòng hướng dẫn phong cách minigame -->
        <div class="text-center mb-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">💡 Bấm nút hoặc vuốt để khám phá ưu đãi</p>
        </div>

        @php
            $savedVoucherIds = [];
            if(auth()->check()) {
                $savedVoucherIds = auth()->user()->savedVouchers()->pluck('vouchers.id')->toArray();
            }
        @endphp

        <!-- Stack chứa các thẻ voucher -->
        <div id="swipe-card-container" class="relative h-[240px] w-full flex items-center justify-center">
            @forelse($vouchers as $index => $voucher)
                @php
                    $isShipping = $voucher->type === 'shipping';
                    $leftBgClass = $isShipping ? 'bg-emerald-400 border-emerald-200/50' : 'bg-pink-500 border-pink-200/50';
                    $badgeClass = $isShipping ? 'bg-emerald-100 text-emerald-700' : 'bg-pink-100 text-pink-600';
                    
                    $icon = $isShipping ? '🚚' : '🛒';
                    if (!$isShipping && $voucher->discount_type === 'percent') $icon = '✨';

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
                    $isSaved = in_array($voucher->id, $savedVoucherIds);
                @endphp

                <!-- Mỗi thẻ voucher (xếp chồng lên nhau) -->
                <div class="voucher-card absolute w-full h-[180px] bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transition-all duration-300 select-none cursor-grab active:cursor-grabbing flex"
                    style="z-index: {{ count($vouchers) - $index }};"
                    data-id="{{ $voucher->id }}"
                    data-type="{{ $voucher->type }}">
                    
                    <!-- Nửa trái (Full chiều cao h-full) -->
                    <div class="w-1/3 min-w-[110px] h-full {{ $leftBgClass }} flex flex-col items-center justify-center p-4 border-r-2 border-dashed relative text-white">
                        <div class="absolute -top-3 -right-3 w-6 h-6 bg-gray-50 rounded-full"></div>
                        <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-gray-50 rounded-full"></div>
                        <span class="text-3xl mb-1 drop-shadow-sm">{{ $icon }}</span>
                        <span class="font-bold text-lg text-center leading-tight">{!! $discountText !!}</span>
                    </div>
                    
                    <!-- Nửa phải -->
                    <div class="w-2/3 h-full p-4 flex flex-col justify-between bg-white">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <span class="{{ $badgeClass }} text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">
                                    {{ $voucher->type === 'shipping' ? 'Vận chuyển' : 'Đơn hàng' }}
                                </span>
                                @if($voucher->total_quantity && $voucher->total_quantity <= 10)
                                    <span class="text-[10px] font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded-full animate-pulse">
                                        🔥 Sắp hết
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                                        Đang diễn ra
                                    </span>
                                @endif
                            </div>
                            <h4 class="font-bold text-gray-800 text-sm leading-snug line-clamp-1" title="{{ $voucher->name }}">{{ $voucher->name }}</h4>
                            <p class="text-[11px] text-gray-500 mt-0.5 font-medium">{{ $discountTitle }}{{ $minOrderText }}</p>
                            <p class="text-[10px] text-gray-400 flex items-center gap-1 mt-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $expireText }}
                            </p>
                        </div>
                        
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                            <span class="text-[11px] text-gray-400">Thẻ số {{ $index + 1 }}/{{ count($vouchers) }}</span>
                            <span class="text-[11px] font-semibold text-pink-500">MommyKids</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="w-full py-16 text-center flex flex-col items-center justify-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <span class="text-6xl mb-4">🥺</span>
                    <h3 class="text-xl font-bold text-gray-700">Chưa có mã ưu đãi nào</h3>
                    <p class="text-gray-500 mt-2">Mẹ quay lại sau để săn voucher nhé!</p>
                </div>
            @endforelse
        </div>

        <!-- 5. THANH ĐIỀU KHIỂN HÀNH ĐỘNG (Nút X và Lưu) -->
        @if($vouchers->count() > 0)
        <div class="flex items-center justify-center gap-8 mt-8">
            <!-- Nút Bỏ qua (X) -->
            <button onclick="swipeCard('left')" class="w-14 h-14 bg-white border border-gray-200 rounded-full flex items-center justify-center text-red-500 shadow-md hover:bg-red-50 hover:scale-110 active:scale-95 transition-all text-xl font-bold">
                ✕
            </button>
            <!-- Nút Lưu quà (Lưu vào ví) -->
            <button onclick="swipeCard('right')" class="w-16 h-16 bg-gradient-to-tr from-pink-500 to-rose-500 rounded-full flex items-center justify-center text-white shadow-lg shadow-pink-200 hover:scale-110 active:scale-95 transition-all text-2xl">
                💾
            </button>
        </div>
        @endif

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
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.voucher-card');
        if (cards.length === 0) return;

        let currentIndex = cards.length - 1; 
        updateCardStack();

        window.swipeCard = async function(direction) {
            if (currentIndex < 0) return;

            const currentCard = cards[currentIndex];
            const voucherId = currentCard.getAttribute('data-id');

            currentCard.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
            if (direction === 'left') {
                currentCard.style.transform = 'translateX(-120%) rotate(-20deg)';
                currentCard.style.opacity = '0';
            } else {
                currentCard.style.transform = 'translateX(120%) rotate(20deg)';
                currentCard.style.opacity = '0';
                saveVoucherById(voucherId);
            }

            currentIndex--;

            if (currentIndex < 0) {
                setTimeout(() => {
                    document.getElementById('swipe-card-container').innerHTML = `
                        <div class="w-full py-16 text-center flex flex-col items-center justify-center bg-white rounded-2xl border border-gray-100 shadow-sm">
                            <span class="text-5xl mb-3">🎉</span>
                            <h3 class="text-lg font-bold text-gray-800">Đã xem hết ưu đãi!</h3>
                            <p class="text-xs text-gray-500 mt-1">Mẹ đã cất những chiếc mã tuyệt vời vào ví.</p>
                            <button onclick="location.reload()" class="mt-4 bg-pink-500 text-white text-xs font-bold px-5 py-2 rounded-full shadow-sm hover:bg-pink-600 transition-colors">Xem lại từ đầu</button>
                        </div>
                    `;
                    const controlBar = document.querySelector('.flex.items-center.justify-center.gap-8.mt-8');
                    if (controlBar) controlBar.style.display = 'none';
                }, 400);
            } else {
                updateCardStack();
            }
        }

        function updateCardStack() {
            cards.forEach((card, index) => {
                if (index <= currentIndex) {
                    let depth = currentIndex - index;
                    if (depth === 0) {
                        card.style.transform = 'scale(1) translateY(0px)';
                        card.style.zIndex = '10';
                        card.style.opacity = '1';
                    } else if (depth === 1) {
                        card.style.transform = 'scale(0.95) translateY(10px)';
                        card.style.zIndex = '5';
                        card.style.opacity = '0.7';
                    } else {
                        card.style.transform = 'scale(0.9) translateY(20px)';
                        card.style.zIndex = '1';
                        card.style.opacity = '0.4';
                    }
                }
            });
        }

        async function saveVoucherById(id) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                await fetch('/api/vouchers/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({ id: id })
                });
            } catch (error) {
                console.error('Lỗi lưu mã:', error);
            }
        }

        // Thêm tính năng kéo chuột/vuốt trên mobile & desktop cho thẻ trên cùng
        cards.forEach((card, index) => {
            let startX = 0, currentX = 0, isDragging = false;

            card.addEventListener('pointerdown', (e) => {
                if (index !== currentIndex) return; // Chỉ cho kéo thẻ trên cùng
                isDragging = true;
                startX = e.clientX;
                card.setPointerCapture(e.pointerId);
                card.style.transition = 'none';
            });

            card.addEventListener('pointermove', (e) => {
                if (!isDragging || index !== currentIndex) return;
                currentX = e.clientX - startX;
                let rotate = currentX * 0.08;
                card.style.transform = `translateX(${currentX}px) rotate(${rotate}deg)`;
            });

            card.addEventListener('pointerup', (e) => {
                if (!isDragging || index !== currentIndex) return;
                isDragging = false;
                card.releasePointerCapture(e.pointerId);

                if (currentX > 100) {
                    swipeCard('right'); // Kéo sang phải -> Lưu
                } else if (currentX < -100) {
                    swipeCard('left');  // Kéo sang trái -> Bỏ qua
                } else {
                    // Trả về vị trí cũ nếu kéo ít
                    card.style.transition = 'transform 0.3s ease';
                    card.style.transform = 'translateX(0px) rotate(0deg)';
                }
                currentX = 0;
            });
        });
    });
</script>
@endsection