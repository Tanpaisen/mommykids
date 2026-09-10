@extends('client.layouts.app')

@section('content')
<style>
    body {
        background-color: #F5F6F8 !important;
    }
    .profile-page {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #2D2D2D;
    }
    .profile-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border: none;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        color: #999;
    }
    .badge-customer-pill {
        background-color: #FDE8E8;
        color: #E31837;
        font-weight: 500;
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .loyalty-box-yellow {
        background-color: #FFF7EB;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .sidebar-menu-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        color: #4A4A4A;
        font-weight: 500;
        font-size: 15px;
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    .sidebar-menu-link:hover {
        background-color: #F8F9FA;
        color: #E31837;
    }
    .sidebar-menu-link.active {
        color: #E31837;
        font-weight: 600;
    }
    .card-border-subtle {
        border: 1px solid #ECECEC;
        border-radius: 16px;
        background: #ffffff;
    }
    .btn-red-action {
        background-color: #E31837;
        color: #ffffff !important;
        border-radius: 24px;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 24px;
        border: none;
        transition: background-color 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }
    .btn-red-action:hover {
        background-color: #C7122C;
    }
    .baby-info-box {
        background-color: #FFFBF2;
        border: 1px solid #FDE3A7;
        border-radius: 16px;
    }
    .info-label {
        color: #888888;
        font-size: 13px;
        margin-bottom: 4px;
    }
    .info-value {
        color: #111111;
        font-weight: 700;
        font-size: 15px;
    }
</style>

<div class="profile-page py-4">
    <div class="container" style="max-width: 1140px;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 14px;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none" style="color: #666;">Trang chủ</a></li>
                <li class="breadcrumb-item"><span style="color: #666;">Cá nhân</span></li>
                <li class="breadcrumb-item active fw-medium" style="color: #E31837;" aria-current="page">Tài khoản</li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Sidebar Trái -->
            <div class="col-lg-4 col-md-5">
                <div class="profile-card p-4">
                    <!-- User Greeting -->
                    <div class="text-center mb-3">
                        <h6 class="fw-bold mb-3 fs-5" style="color: #222;">
                            Xin chào, {{ $user->name ?? ($user->phone ? (substr($user->phone, 0, 4) . '*****' . substr($user->phone, -3)) : 'Khách hàng') }}
                        </h6>
                        
                        <div class="mb-3">
                            <span class="badge-customer-pill">
                                Khách hàng 
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </span>
                        </div>

                        <!-- Loyalty Code -->
                        <div class="loyalty-box-yellow mb-4">
                            <div class="info-label" style="font-size: 13px;">Mã khách hàng thân thiết</div>
                            <div class="fw-bold fs-5" style="color: #222; letter-spacing: 0.5px;">
                                {{ $user->loyalty_code ?? '8932369232370' }}
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Menu -->
                    <div class="d-flex flex-column gap-1">
                        <a href="{{ route('profile.edit') }}" class="sidebar-menu-link active">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#E31837"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            Tài khoản
                        </a>
                        <a href="#" class="sidebar-menu-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#666"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12z"/></svg>
                            Đơn hàng
                        </a>
                        <a href="{{ route('profile.support') }}" class="sidebar-menu-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#666"><path d="M12 1c-4.97 0-9 4.03-9 9v7c0 1.66 1.34 3 3 3h3v-8H5v-2c0-3.87 3.13-7 7-7s7 3.13 7 7v2h-4v8h3c1.66 0 3-1.34 3-3v-7c0-4.97-4.03-9-9-9z"/></svg>
                            Hỗ trợ người dùng
                        </a>
                        <a href="{{ route('profile.policy') }}" class="sidebar-menu-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#666"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                            Quy định, chính sách
                        </a>
                        <a href="{{ route('notifications.index') }}" class="sidebar-menu-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#666"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                            Thông báo, bài viết
                        </a>
                        <a href="#" class="sidebar-menu-link">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="#666"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                            Về MommyKids
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content Phải -->
            <div class="col-lg-8 col-md-7">
                <h4 class="fw-bold mb-4" style="color: #111; font-size: 24px;">Thông tin tài khoản</h4>

                <!-- Khung 1: Thông tin cá nhân -->
                <div class="card-border-subtle p-4 mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#E31837" class="me-2"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <h5 class="fw-bold mb-0" style="color: #111; font-size: 18px;">Thông tin cá nhân</h5>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">Họ và tên</div>
                            <div class="info-value">{{ $user->name ?? ($user->phone ? (substr($user->phone, 0, 4) . '*****' . substr($user->phone, -3)) : 'Chưa cập nhật') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Số điện thoại</div>
                            <div class="info-value">{{ $user->phone ?? 'Chưa cập nhật' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Ngày tháng năm sinh</div>
                            <div class="info-value">{{ $user->dob ?? 'Chưa cập nhật' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Giới tính</div>
                            <div class="info-value">{{ $user->gender ?? 'Nữ' }}</div>
                        </div>
                        <div class="col-md-12">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $user->email ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Khung 2: Thông tin bé yêu -->
                <div class="baby-info-box p-4 mb-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 52px; height: 52px; background-color: #FEF0D5;">
                                <span style="font-size: 26px;">🎁</span>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="color: #A05E03; font-size: 16px;">Thông tin bé yêu</h6>
                                <p class="text-muted mb-0" style="font-size: 13.5px; line-height: 1.4; max-width: 440px;">
                                    Thêm chính xác thông tin con để nhận quà tặng đặc biệt từ MommyKids trong ngày sinh nhật của bé.
                                </p>
                            </div>
                        </div>
                        <button class="btn-red-action">Cập nhật ngay</button>
                    </div>
                </div>

                <!-- Khung 3: Danh sách địa chỉ nhận hàng -->
                <div class="card-border-subtle p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="#E31837" class="me-2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            <h5 class="fw-bold mb-0" style="color: #111; font-size: 18px;">Danh sách địa chỉ nhận hàng</h5>
                        </div>
                        <button class="btn-red-action" style="font-size: 13.5px; padding: 8px 18px;">+ Thêm địa chỉ mới</button>
                    </div>

                    <div class="text-center py-5 text-muted" style="font-size: 14px; color: #888 !important;">
                        Chưa có địa chỉ giao hàng nào được lưu.
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection