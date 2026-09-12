@extends('client.layouts.app')

@section('title', 'Quy định & Chính sách - MommyKids')

@section('content')
@php
    $totalSpent = $user->total_spent ?? 0;

    // Tự động tính toán Hạng thành viên và Tiến trình nâng hạng
    if ($totalSpent >= 10000000) {
        $currentTier = 'Hạng Kim Cương';
        $progress = [
            'percent'   => 100, 
            'needed'    => 0, 
            'next_tier' => ''
        ];
    } elseif ($totalSpent >= 5000000) {
        $currentTier = 'Hạng Vàng';
        $progress = [
            'percent'   => round(($totalSpent - 5000000) / 5000000 * 100),
            'needed'    => 10000000 - $totalSpent,
            'next_tier' => 'Hạng Kim Cương'
        ];
    } elseif ($totalSpent >= 2000000) {
        $currentTier = 'Hạng Bạc';
        $progress = [
            'percent'   => round(($totalSpent - 2000000) / 3000000 * 100),
            'needed'    => 5000000 - $totalSpent,
            'next_tier' => 'Hạng Vàng'
        ];
    } else {
        $currentTier = 'Hạng Thành viên';
        $progress = [
            'percent'   => round($totalSpent / 2000000 * 100),
            'needed'    => 2000000 - $totalSpent,
            'next_tier' => 'Hạng Bạc'
        ];
    }

    // ĐỒNG BỘ MÃ KHÁCH HÀNG CHUẨN VỚI CÁC TRANG KHÁC
    $customerCode = $user->loyalty_code ?? ('MK-' . $user->id);
@endphp

<style>
    /* CSS Hồ Sơ Rực Rỡ & Hiện Đại - MommyKids */
    .profile-master-container {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #2D3748;
    }

    /* 1. Breadcrumb Nằm Ngang 1 Hàng */
    .breadcrumb-row {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 13.5px;
        margin-bottom: 20px;
    }
    .breadcrumb-item-link {
        color: #718096;
        text-decoration: none;
        transition: color 0.2s;
    }
    .breadcrumb-item-link:hover {
        color: #FF2A54;
    }

    /* 2. Thẻ VIP Card Rực Rỡ Gradient Glow */
    .vip-card-vibrant {
        background: linear-gradient(135deg, #FF2A54 0%, #FF5A78 50%, #FF8A00 100%);
        border-radius: 20px;
        color: #ffffff;
        padding: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(255, 42, 84, 0.28);
    }
    .vip-card-vibrant::before {
        content: "";
        position: absolute;
        top: -40px;
        right: -30px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }
    .avatar-glow {
        width: 62px;
        height: 62px;
        background: #ffffff;
        color: #FF2A54;
        font-weight: 800;
        font-size: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }
    .glass-pill {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 4px 14px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    /* 3. Khung Tiến Trình Tích Điểm Neon */
    .loyalty-progress-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #F0F4F8;
        padding: 18px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }
    .progress-track {
        height: 9px;
        background-color: #EDF2F7;
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-fill-neon {
        background: linear-gradient(90deg, #FF8A00 0%, #FF2A54 100%);
        height: 100%;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(255, 42, 84, 0.5);
    }

    /* 4. Menu Điều Hướng Icon Màu Nổi Bật */
    .menu-link-vibrant {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 14px;
        color: #4A5568;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.25s ease;
        background: #ffffff;
        border: 1px solid #F0F4F8;
        margin-bottom: 8px;
    }
    .menu-link-vibrant:hover {
        transform: translateX(5px);
        background: #FFF0F3;
        color: #FF2A54;
        border-color: #FFD0D9;
    }
    .menu-link-vibrant.active {
        background: linear-gradient(135deg, #FF2A54 0%, #FF5A78 100%);
        color: #ffffff;
        border: none;
        box-shadow: 0 8px 20px rgba(255, 42, 84, 0.25);
    }
    .icon-box-sm {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        background: #F7FAFC;
    }
    .menu-link-vibrant.active .icon-box-sm {
        background: rgba(255, 255, 255, 0.2);
    }

    /* 5. Accordion Quy định chính sách */
    .policy-accordion-item {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #F0F4F8;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        transition: all 0.25s ease;
        margin-bottom: 16px;
    }
    .policy-accordion-item:hover {
        border-color: #FFD0D9;
    }
    .policy-summary {
        padding: 18px 20px;
        font-weight: 700;
        font-size: 15px;
        color: #1A202C;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        list-style: none;
        user-select: none;
    }
    .policy-summary::-webkit-details-marker {
        display: none;
    }
    details[open] .policy-summary {
        border-bottom: 1px solid #EDF2F7;
        color: #FF2A54;
    }
    details[open] .arrow-icon {
        transform: rotate(180deg);
        color: #FF2A54;
    }
</style>

<div class="profile-master-container py-2">
    <!-- Breadcrumb chuẩn 1 hàng ngang -->
    <div class="breadcrumb-row">
        <a href="{{ route('home') }}" class="breadcrumb-item-link">Trang chủ</a>
        <span class="text-muted opacity-50">/</span>
        <span class="text-secondary">Cá nhân</span>
        <span class="text-muted opacity-50">/</span>
        <span class="fw-bold" style="color: #FF2A54;">Quy định & Chính sách</span>
    </div>

    <div class="row g-4">
        <!-- CỘT TRÁI: THẺ VIP & MENU ĐIỀU HƯỚNG -->
        <div class="col-lg-4 col-md-5">
            <!-- Thẻ VIP Rực Rỡ -->
            <div class="vip-card-vibrant mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-glow">
                        {{ strtoupper(substr($user->name ?? 'K', 0, 1)) }}
                    </div>
                    <div>
                        <span class="glass-pill d-inline-block mb-1">
                            👑 {{ $currentTier }}
                        </span>
                        <h5 class="fw-bold mb-0 text-white" style="font-size: 17px;">
                            {{ $user->name ?? ($user->phone ? (substr($user->phone, 0, 4) . '*****' . substr($user->phone, -3)) : 'Khách hàng') }}
                        </h5>
                    </div>
                </div>

                <div class="pt-3 border-top border-white-20 d-flex justify-content-between text-white-50" style="font-size: 12.5px;">
                    <!-- ĐÃ ĐỒNG BỘ MÃ KHÁCH HÀNG -->
                    <span>Mã KH: <strong class="text-white fw-bold">{{ $customerCode }}</strong></span>
                    <span>Điểm tích lũy: <strong class="text-white fw-bold">{{ number_format($user->points ?? 0) }} đ</strong></span>
                </div>
            </div>

            <!-- Khung Tiến Trình Đăng Hạng -->
            <div class="loyalty-progress-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 12.5px;">
                    <span class="text-secondary">Chi tiêu: <strong class="text-dark">{{ number_format($totalSpent) }}đ</strong></span>
                    @if(($progress['needed'] ?? 0) > 0)
                        <span class="fw-bold" style="color: #FF2A54;">Cần {{ number_format($progress['needed']) }}đ lên {{ $progress['next_tier'] }}</span>
                    @else
                        <span class="text-success fw-bold">Hạng Cao Nhất</span>
                    @endif
                </div>
                <div class="progress-track">
                    <div class="progress-fill-neon" style="width: {{ $progress['percent'] }}%;"></div>
                </div>
            </div>

            <!-- Menu Điều Hướng Sidebar -->
            <div class="d-flex flex-column">
                <a href="{{ route('profile.edit') }}" class="menu-link-vibrant">
                    <span class="icon-box-sm">👤</span>
                    Thông tin tài khoản
                </a>
                <a href="#" class="menu-link-vibrant">
                    <span class="icon-box-sm">🛍️</span>
                    Quản lý đơn hàng
                </a>
                <a href="{{ route('profile.support') }}" class="menu-link-vibrant">
                    <span class="icon-box-sm">🎧</span>
                    Trung tâm hỗ trợ
                </a>
                <a href="{{ route('profile.policy') }}" class="menu-link-vibrant active">
                    <span class="icon-box-sm">📜</span>
                    Quy định & Chính sách
                </a>
                <a href="{{ route('notifications.index') }}" class="menu-link-vibrant">
                    <span class="icon-box-sm">🔔</span>
                    Thông báo & Ưu đãi
                </a>
            </div>
        </div>

        <!-- CỘT PHẢI: QUY ĐỊNH CHÍNH SÁCH -->
        <div class="col-lg-8 col-md-7">
            <h5 class="fw-bold mb-3 text-dark" style="font-size: 19px;">Quy định & Chính sách</h5>

            <!-- 1. QUY ĐỊNH CHUNG -->
            <details class="policy-accordion-item" open>
                <summary class="policy-summary">
                    <span class="d-flex align-items-center gap-2">
                        <span style="color: #FF2A54;">📋</span> QUY ĐỊNH CHUNG
                    </span>
                    <svg class="arrow-icon text-secondary" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                
                <div class="p-4 text-secondary" style="font-size: 14px; line-height: 1.6;">
                    <p class="mb-3">
                        Chào mừng Quý khách đến với <strong>MommyKids</strong> - Hệ thống bán lẻ đa kênh dành cho Mẹ và Bé hàng đầu Việt Nam.
                        MommyKids hiện có hệ thống cửa hàng bán lẻ phủ khắp các tỉnh thành trên toàn quốc.
                        Ngoài ra chúng tôi có website và ứng dụng chính thức được xây dựng nhằm phục vụ Khách hàng mua hàng và tìm hiểu thông tin về MommyKids.
                        Khi đăng ký và đăng nhập vào Website và App, Quý khách cần tìm hiểu và tuân thủ các Quy định sử dụng này.
                    </p>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">1. Hiệu lực của Quy định sử dụng</h6>
                        <ul class="ps-3 mb-0" style="list-style-type: disc;">
                            <li>Các điều kiện, điều khoản và nội dung của trang website/app này được xây dựng và điều chỉnh trên cơ sở tuân thủ các quy định của pháp luật và với mục đích xây dựng môi trường kinh doanh lành mạnh, minh bạch vì lợi ích của Khách hàng và Đối tác.</li>
                            <li>Quý khách vui lòng đọc kỹ và xác nhận đồng ý với các điều khoản để được cấp tài khoản thành viên.</li>
                            <li>MommyKids có toàn quyền điều chỉnh Quy định này mà không cần thông báo trước. Vui lòng kiểm tra ứng dụng thường xuyên để cập nhật thay đổi.</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">2. Thỏa thuận tuân thủ Quy định sử dụng</h6>
                        <ul class="ps-3 mb-0" style="list-style-type: disc;">
                            <li>MommyKids cấp tài khoản Website and App để Quý khách sử dụng với điều kiện người dùng tuân thủ Điều khoản trong Quy định này.</li>
                            <li>Nếu vi phạm bất kỳ điều nào, MommyKids có quyền vô hiệu tài khoản mà không cần báo trước.</li>
                            <li>Người dùng tối thiểu phải 18 tuổi hoặc truy cập dưới sự giám sát của cha mẹ hay người giám hộ hợp pháp.</li>
                            <li>Mỗi cá nhân có trách nhiệm bảo mật mật khẩu, tài khoản của mình.</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">3. Thương hiệu và bản quyền thương hiệu</h6>
                        <p class="mb-0">
                            MommyKids là thương hiệu đã được đăng ký sở hữu trí tuệ. Mọi thông tin bao gồm hình ảnh, thiết kế, văn bản, phần mềm... đều là tài sản trí tuệ của MommyKids. Mọi hành vi sao chép chưa được cấp phép bằng văn bản đều bị xử lý theo pháp luật.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">4. Bảo mật thông tin</h6>
                        <p class="mb-0">
                            MommyKids coi trọng việc bảo mật thông tin và áp dụng các biện pháp tốt nhất để bảo vệ thông tin cá nhân. Mọi thông tin giao dịch đều được mã hóa an toàn.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">5. Mua hàng và thanh toán</h6>
                        <p class="mb-2">Hỗ trợ 2 hình thức thanh toán linh hoạt:</p>
                        <ol class="ps-3 mb-0">
                            <li><strong>Thanh toán khi nhận hàng (COD):</strong> Đặt hàng &rsaquo; Xác nhận &rsaquo; Vận chuyển &rsaquo; Kiểm tra & Thanh toán.</li>
                            <li><strong>Thanh toán chuyển khoản:</strong> Đặt hàng &rsaquo; Chuyển khoản theo cú pháp &rsaquo; Xác nhận &rsaquo; Giao hàng.</li>
                        </ol>
                    </div>

                    <div>
                        <h6 class="fw-bold text-dark mb-1" style="font-size: 14px;">6. Giải quyết khiếu nại và tranh chấp</h6>
                        <p class="mb-0">
                            Phòng Chăm sóc Khách hàng qua Hotline <strong style="color: #FF2A54;">1800 6886</strong> là đầu mối tiếp nhận phản hồi, khiếu nại. MommyKids cam kết xử lý trong vòng 24h làm việc.
                        </p>
                    </div>
                </div>
            </details>

            <!-- 2. CHÍNH SÁCH BẢO MẬT -->
            <details class="policy-accordion-item">
                <summary class="policy-summary">
                    <span class="d-flex align-items-center gap-2">
                        <span style="color: #FF2A54;">🔒</span> CHÍNH SÁCH BẢO MẬT
                    </span>
                    <svg class="arrow-icon text-secondary" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="p-4 text-secondary" style="font-size: 14px; line-height: 1.6;">
                    <p class="mb-2">MommyKids cam kết bảo mật tuyệt đối các thông tin cá nhân của Khách hàng theo chính sách bảo vệ thông tin cá nhân của người tiêu dùng.</p>
                    <p class="mb-1">• <strong>Mục đích thu thập:</strong> Xử lý đơn hàng, cung cấp dịch vụ hỗ trợ, gửi thông báo khuyến mại khi được sự cho phép.</p>
                    <p class="mb-1">• <strong>Phạm vi sử dụng:</strong> Nội bộ hệ thống MommyKids và đơn vị vận chuyển trực tiếp.</p>
                    <p class="mb-0">• <strong>Thời gian lưu trữ:</strong> Dữ liệu cá nhân được lưu trữ cho đến khi có yêu cầu hủy bỏ từ Khách hàng.</p>
                </div>
            </details>

            <!-- 3. CHÍNH SÁCH BẢO HÀNH -->
            <details class="policy-accordion-item">
                <summary class="policy-summary">
                    <span class="d-flex align-items-center gap-2">
                        <span style="color: #FF2A54;">🛡️</span> CHÍNH SÁCH BẢO HÀNH
                    </span>
                    <svg class="arrow-icon text-secondary" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="p-4 text-secondary" style="font-size: 14px; line-height: 1.6;">
                    <p class="mb-1">• Tất cả các sản phẩm điện tử, máy hút sữa, máy tiệt trùng... mua tại MommyKids đều được bảo hành chính hãng theo tiêu chuẩn của nhà sản xuất.</p>
                    <p class="mb-1">• Thời gian tiếp nhận và xử lý bảo hành từ 7 - 14 ngày làm việc.</p>
                    <p class="mb-0">• Khách hàng có thể mang sản phẩm đến trực tiếp cửa hàng MommyKids gần nhất hoặc gửi qua bưu điện kèm hóa đơn mua hàng.</p>
                </div>
            </details>

            <!-- 4. QUY ĐỊNH TÍCH & TIÊU XU -->
            <details class="policy-accordion-item">
                <summary class="policy-summary">
                    <span class="d-flex align-items-center gap-2">
                        <span style="color: #FF2A54;">🪙</span> QUY ĐỊNH TÍCH & TIÊU XU (MOMMY XU)
                    </span>
                    <svg class="arrow-icon text-secondary" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="p-4 text-secondary" style="font-size: 14px; line-height: 1.6;">
                    <p class="mb-1">• <strong>Tích xu:</strong> Mỗi đơn hàng hoàn tất thành công sẽ được tích lũy xu tương ứng 1% giá trị đơn hàng.</p>
                    <p class="mb-1">• <strong>Quy đổi:</strong> 1 Mommy Xu = 1 VNĐ.</p>
                    <p class="mb-0">• <strong>Sử dụng:</strong> Khách hàng có thể dùng Xu để giảm trừ trực tiếp trên tổng giá trị thanh toán cho các đơn hàng tiếp theo.</p>
                </div>
            </details>

        </div>
    </div>
</div>
@endsection