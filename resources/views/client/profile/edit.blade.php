@extends('client.layouts.app')

@section('content')
@php
    // Gán thử số tiền chi tiêu để test (Bỏ comment dòng bên dưới để test nhanh)
    //$user->total_spent = 75000000; 

    $totalSpent = $user->total_spent ?? 0;

    // Tự động xác định Hạng hiện tại và Tiến trình nâng hạng
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

    // ĐỒNG BỘ MÃ KHÁCH HÀNG
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

    /* 5. Khung Nội Dung Bên Phải */
    .info-card-modern {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #F0F4F8;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }
    .field-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 14px 18px;
        transition: all 0.2s;
    }
    .field-box:hover {
        border-color: #FF2A54;
        background: #ffffff;
    }
    .label-title {
        font-size: 12px;
        color: #718096;
        font-weight: 500;
        margin-bottom: 2px;
    }
    .label-value {
        font-size: 15px;
        font-weight: 700;
        color: #1A202C;
    }

    /* Nút bấm Gradient */
    .btn-gradient-danger {
        background: linear-gradient(135deg, #FF2A54 0%, #FF5A78 100%);
        color: #ffffff !important;
        border-radius: 25px;
        font-weight: 700;
        font-size: 13px;
        padding: 9px 24px;
        border: none;
        box-shadow: 0 4px 15px rgba(255, 42, 84, 0.3);
        transition: all 0.25s ease;
        text-decoration: none;
        display: inline-block;
    }
    .btn-gradient-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(255, 42, 84, 0.45);
    }
</style>

<div class="profile-master-container py-2">
    <!-- Breadcrumb chuẩn 1 hàng ngang -->
    <div class="breadcrumb-row">
        <a href="{{ route('home') }}" class="breadcrumb-item-link">Trang chủ</a>
        <span class="text-muted opacity-50">/</span>
        <span class="text-secondary">Cá nhân</span>
        <span class="text-muted opacity-50">/</span>
        <span class="fw-bold" style="color: #FF2A54;">Tài khoản</span>
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
                        <!-- Hiển thị $currentTier động -->
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
                <a href="{{ route('profile.edit') }}" class="menu-link-vibrant active">
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
                <a href="{{ route('profile.policy') }}" class="menu-link-vibrant">
                    <span class="icon-box-sm">📜</span>
                    Quy định & Chính sách
                </a>
                <a href="{{ route('notifications.index') }}" class="menu-link-vibrant">
                    <span class="icon-box-sm">🔔</span>
                    Thông báo & Ưu đãi
                </a>
            </div>
        </div>

        <!-- CỘT PHẢI: CHI TIẾT THÔNG TIN -->
        <div class="col-lg-8 col-md-7">
            <h5 class="fw-bold mb-3 text-dark" style="font-size: 19px;">Hồ sơ cá nhân</h5>

            <!-- Box 1: Thông tin cá nhân -->
            <div class="info-card-modern mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <span style="color: #FF2A54;">✨</span> Thông tin cá nhân
                    </h6>
                    <a href="#" class="text-decoration-none fw-bold" style="color: #FF2A54; font-size: 13px;">Chỉnh sửa</a>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="field-box">
                            <div class="label-title">Họ và tên</div>
                            <div class="label-value">{{ $user->name ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="field-box">
                            <div class="label-title">Số điện thoại</div>
                            <div class="label-value">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="field-box">
                            <div class="label-title">Ngày tháng năm sinh</div>
                            <div class="label-value">{{ $user->dob ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="field-box">
                            <div class="label-title">Giới tính</div>
                            <div class="label-value">{{ $user->gender ?? 'Nữ' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="field-box">
                            <div class="label-title">Địa chỉ Email</div>
                            <div class="label-value">{{ $user->email ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 2: Banner Khuyến Mãi Bé Yêu -->
            <div class="info-card-modern mb-4" style="background: linear-gradient(135deg, #FFF8F0 0%, #FFF0F3 100%); border-color: #FFE1E7;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-white rounded-circle shadow-sm flex-shrink-0" style="font-size: 26px;">
                            🎂
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1" style="color: #9C4221; font-size: 15px;">Thông tin bé yêu</h6>
                            <p class="mb-0 text-secondary" style="font-size: 13px; max-width: 400px; line-height: 1.4;">
                                Thêm ngày sinh của bé để MommyKids gửi tặng voucher đặc biệt trong tháng sinh nhật!
                            </p>
                        </div>
                    </div>
                    <button class="btn-gradient-danger">Cập nhật ngay</button>
                </div>
            </div>

            <!-- Box 3: Sổ địa chỉ nhận hàng -->
            <div class="info-card-modern">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <span style="color: #FF2A54;">📍</span> Sổ địa chỉ nhận hàng
                    </h6>
                    <button class="btn-gradient-danger" style="font-size: 12px; padding: 7px 18px;">+ Thêm địa chỉ mới</button>
                </div>

                <div class="text-center py-4">
                    <div class="mb-2" style="font-size: 36px; opacity: 0.6;">🏡</div>
                    <p class="text-secondary small mb-0">Bạn chưa lưu địa chỉ nhận hàng nào.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection