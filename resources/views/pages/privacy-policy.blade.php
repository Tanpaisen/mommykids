@extends('client.layouts.app')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <nav class="flex text-xs text-slate-500 mb-6 gap-2">
            <a href="/" class="hover:text-rose-600">Trang chủ</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Chính sách bảo mật</span>
        </nav>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 space-y-6">
            <h1 class="text-3xl font-extrabold text-slate-800 border-b border-slate-100 pb-4">
                Chính sách bảo mật thông tin
            </h1>

            <div class="space-y-4 text-slate-700 leading-relaxed">
                <p>MommyKids cam kết bảo mật tuyệt đối thông tin cá nhân của khách hàng theo chính sách dưới đây:</p>

                <h3 class="text-lg font-bold text-slate-800">1. Mục đích thu thập thông tin</h3>
                <p>Thông tin cá nhân (Họ tên, Số điện thoại, Địa chỉ giao hàng) được sử dụng duy nhất để xử lý đơn hàng, giao hàng và gửi các thông tin ưu đãi từ MommyKids.</p>

                <h3 class="text-lg font-bold text-slate-800">2. Cam kết bảo mật</h3>
                <p>Chúng tôi tuyệt đối không chia sẻ, bán hoặc trao đổi thông tin của khách hàng cho bất kỳ bên thứ ba nào vì mục đích thương mại.</p>

                <h3 class="text-lg font-bold text-slate-800">3. Quyền lợi của khách hàng</h3>
                <p>Khách hàng có quyền chỉnh sửa hoặc yêu cầu xóa bỏ thông tin cá nhân trên hệ thống bất kỳ lúc nào bằng cách đăng nhập tài khoản hoặc liên hệ bộ phận hỗ trợ.</p>
            </div>
        </div>
    </div>
</div>
@endsection