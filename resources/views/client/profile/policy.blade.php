@extends('client.layouts.app')

@section('title', 'Quy định chính sách - MommyKids')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 lg:px-6 py-6">

    {{-- Breadcrumbs --}}
    <div class="flex items-center gap-2 text-xs text-ink-soft mb-6">
        <a href="{{ route('home') }}" class="hover:text-coral">Trang chủ</a>
        <span>/</span>
        <span>Cá nhân</span>
        <span>/</span>
        <span class="text-coral font-medium">Quy định chính sách</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Sidebar bên trái --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl p-5 border border-coral-light/60 shadow-sm sticky top-20">
                {{-- Header Tên khách hàng --}}
                <div class="mb-4">
                    <h3 class="font-bold text-ink text-base truncate">
                        Xin chào, {{ $user->name ?? $user->email }}
                    </h3>
                    <span class="inline-block mt-1 px-3 py-1 bg-coral-light/50 text-coral text-xs font-semibold rounded-full">
                        Khách hàng &rsaquo;
                    </span>
                </div>

                {{-- Mã khách hàng thân thiết --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center my-4">
                    <p class="text-xs text-amber-800 font-medium">Mã khách hàng thân thiết</p>
                    <p class="font-mono font-bold text-lg text-amber-900 tracking-wider mt-1">
                        893{{ str_pad($user->id, 10, '0', STR_PAD_LEFT) }}
                    </p>
                </div>

                {{-- Danh sách menu --}}
                <nav class="space-y-1 text-sm font-medium text-ink-soft">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Tài khoản
                    </a>
                    <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Đơn hàng
                    </a>
                    <a href="{{ route('profile.support') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-coral-light/20 hover:text-coral transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Hỗ trợ người dùng
                    </a>
                    <a href="{{ route('profile.policy') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-coral-light/40 text-coral font-bold">
                        <svg class="w-5 h-5 text-coral" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Quy định, chính sách
                    </a>
                </nav>
            </div>
        </div>

        {{-- Nội dung Khung Quy định chính sách bên phải --}}
        <div class="lg:col-span-3 space-y-4">
            <h1 class="text-2xl font-bold text-ink mb-6">Quy định chính sách</h1>

            {{-- 1. QUY ĐỊNH CHUNG (Chi tiết mở sẵn) --}}
            <details class="bg-white rounded-2xl border border-coral-light/60 shadow-sm group" open>
                <summary class="flex justify-between items-center p-5 cursor-pointer font-bold text-base text-ink select-none group-open:border-b group-open:border-gray-100">
                    <span>QUY ĐỊNH CHUNG</span>
                    <span class="transition transform group-open:rotate-180 text-coral">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </summary>
                
                <div class="p-6 text-sm text-ink leading-relaxed space-y-4">
                    <p>
                        Chào mừng Quý khách đến với <strong>MommyKids</strong> - Hệ thống bán lẻ đa kênh dành cho Mẹ và Bé hàng đầu Việt Nam.
                        MommyKids hiện có hệ thống cửa hàng bán lẻ phủ khắp các tỉnh thành trên toàn quốc.
                        Ngoài ra chúng tôi có website và ứng dụng chính thức được xây dựng nhằm phục vụ Khách hàng mua hàng và tìm hiểu thông tin về MommyKids.
                        Khi đăng ký và đăng nhập vào Website và App, Quý khách cần tìm hiểu và tuân thủ các Quy định sử dụng này.
                    </p>

                    <div>
                        <h3 class="font-bold text-ink mb-1">1. Hiệu lực của Quy định sử dụng</h3>
                        <ul class="list-disc pl-5 space-y-1 text-ink-soft">
                            <li>Các điều kiện, điều khoản và nội dung của trang website/app này được xây dựng và điều chỉnh trên cơ sở tuân thủ các quy định của pháp luật và với mục đích xây dựng môi trường kinh doanh lành mạnh, minh bạch vì lợi ích của Khách hàng và Đối tác.</li>
                            <li>Quý khách vui lòng đọc kỹ và xác nhận đồng ý với các điều khoản để được cấp tài khoản thành viên.</li>
                            <li>MommyKids có toàn quyền điều chỉnh Quy định này mà không cần thông báo trước. Vui lòng kiểm tra ứng dụng thường xuyên để cập nhật thay đổi.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-bold text-ink mb-1">2. Thỏa thuận tuân thủ Quy định sử dụng</h3>
                        <ul class="list-disc pl-5 space-y-1 text-ink-soft">
                            <li>MommyKids cấp tài khoản Website và App để Quý khách sử dụng với điều kiện người dùng tuân thủ Điều khoản trong Quy định này.</li>
                            <li>Nếu vi phạm bất kỳ điều nào, MommyKids có quyền vô hiệu tài khoản mà không cần báo trước.</li>
                            <li>Người dùng tối thiểu phải 18 tuổi hoặc truy cập dưới sự giám sát của cha mẹ hay người giám hộ hợp pháp.</li>
                            <li>Mỗi cá nhân có trách nhiệm bảo mật mật khẩu, tài khoản của mình.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-bold text-ink mb-1">3. Thương hiệu và bản quyền thương hiệu</h3>
                        <p class="text-ink-soft">
                            MommyKids là thương hiệu đã được đăng ký sở hữu trí tuệ. Mọi thông tin bao gồm hình ảnh, thiết kế, văn bản, phần mềm... đều là tài sản trí tuệ của MommyKids. Mọi hành vi sao chép chưa được cấp phép bằng văn bản đều bị xử lý theo pháp luật.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-bold text-ink mb-1">4. Bảo mật thông tin</h3>
                        <p class="text-ink-soft">
                            MommyKids coi trọng việc bảo mật thông tin và áp dụng các biện pháp tốt nhất để bảo vệ thông tin cá nhân. Mọi thông tin giao dịch đều được mã hóa an toàn.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-bold text-ink mb-1">5. Mua hàng và thanh toán</h3>
                        <p class="text-ink-soft mb-2">Hỗ trợ 2 hình thức thanh toán linh hoạt:</p>
                        <ol class="list-decimal pl-5 space-y-1 text-ink-soft">
                            <li><strong>Thanh toán khi nhận hàng (COD):</strong> Đặt hàng &rsaquo; Xác nhận &rsaquo; Vận chuyển &rsaquo; Kiểm tra & Thanh toán.</li>
                            <li><strong>Thanh toán chuyển khoản:</strong> Đặt hàng &rsaquo; Chuyển khoản theo cú pháp &rsaquo; Xác nhận &rsaquo; Giao hàng.</li>
                        </ol>
                    </div>

                    <div>
                        <h3 class="font-bold text-ink mb-1">6. Giải quyết khiếu nại và tranh chấp</h3>
                        <p class="text-ink-soft">
                            Phòng Chăm sóc Khách hàng qua Hotline <strong>1800 6886</strong> là đầu mối tiếp nhận phản hồi, khiếu nại. MommyKids cam kết xử lý trong vòng 24h làm việc.
                        </p>
                    </div>
                </div>
            </details>

            {{-- 2. CHÍNH SÁCH BẢO MẬT --}}
            <details class="bg-white rounded-2xl border border-coral-light/60 shadow-sm group">
                <summary class="flex justify-between items-center p-5 cursor-pointer font-bold text-base text-ink select-none group-open:border-b group-open:border-gray-100">
                    <span>CHÍNH SÁCH BẢO MẬT</span>
                    <span class="transition transform group-open:rotate-180 text-coral">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </summary>
                <div class="p-6 text-sm text-ink-soft leading-relaxed space-y-3">
                    <p>MommyKids cam kết bảo mật tuyệt đối các thông tin cá nhân của Khách hàng theo chính sách bảo vệ thông tin cá nhân của người tiêu dùng.</p>
                    <p>• <strong>Mục đích thu thập:</strong> Xử lý đơn hàng, cung cấp dịch vụ hỗ trợ, gửi thông báo khuyến mại khi được sự cho phép.</p>
                    <p>• <strong>Phạm vi sử dụng:</strong> Nội bộ hệ thống MommyKids và đơn vị vận chuyển trực tiếp.</p>
                    <p>• <strong>Thời gian lưu trữ:</strong> Dữ liệu cá nhân được lưu trữ cho đến khi có yêu cầu hủy bỏ từ Khách hàng.</p>
                </div>
            </details>

            {{-- 3. CHÍNH SÁCH BẢO HÀNH --}}
            <details class="bg-white rounded-2xl border border-coral-light/60 shadow-sm group">
                <summary class="flex justify-between items-center p-5 cursor-pointer font-bold text-base text-ink select-none group-open:border-b group-open:border-gray-100">
                    <span>CHÍNH SÁCH BẢO HÀNH</span>
                    <span class="transition transform group-open:rotate-180 text-coral">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </summary>
                <div class="p-6 text-sm text-ink-soft leading-relaxed space-y-3">
                    <p>• Tất cả các sản phẩm điện tử, máy hút sữa, máy tiệt trùng... mua tại MommyKids đều được bảo hành chính hãng theo tiêu chuẩn của nhà sản xuất.</p>
                    <p>• Thời gian tiếp nhận và xử lý bảo hành từ 7 - 14 ngày làm việc.</p>
                    <p>• Khách hàng có thể mang sản phẩm đến trực tiếp cửa hàng MommyKids gần nhất hoặc gửi qua bưu điện kèm hóa đơn mua hàng.</p>
                </div>
            </details>

            {{-- 4. QUY ĐỊNH TÍCH & TIÊU XU --}}
            <details class="bg-white rounded-2xl border border-coral-light/60 shadow-sm group">
                <summary class="flex justify-between items-center p-5 cursor-pointer font-bold text-base text-ink select-none group-open:border-b group-open:border-gray-100">
                    <span>QUY ĐỊNH TÍCH & TIÊU XU (MOMMY XU)</span>
                    <span class="transition transform group-open:rotate-180 text-coral">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </summary>
                <div class="p-6 text-sm text-ink-soft leading-relaxed space-y-3">
                    <p>• <strong>Tích xu:</strong> Mỗi đơn hàng hoàn tất thành công sẽ được tích lũy xu tương ứng 1% giá trị đơn hàng.</p>
                    <p>• <strong>Quy đổi:</strong> 1 Mommy Xu = 1 VNĐ.</p>
                    <p>• <strong>Sử dụng:</strong> Khách hàng có thể dùng Xu để giảm trừ trực tiếp trên tổng giá trị thanh toán cho các đơn hàng tiếp theo.</p>
                </div>
            </details>

        </div>

    </div>
</div>
@endsection