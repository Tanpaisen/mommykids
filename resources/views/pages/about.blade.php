@extends('client.layouts.app')

@section('sidebar')
    <div class="hidden"></div>
@endsection

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex text-xs text-slate-500 mb-6 gap-2">
            <a href="/" class="hover:text-rose-600">Trang chủ</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Giới thiệu</span>
        </nav>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 space-y-6">
            <h1 class="text-3xl font-extrabold text-slate-800 border-b border-slate-100 pb-4">
                Giới thiệu về <span class="text-rose-500">MommyKids</span>
            </h1>

            <div class="prose prose-slate max-w-none text-slate-700 space-y-4 leading-relaxed">
                <p class="text-lg font-medium text-slate-800">
                    Chào mừng ba mẹ đến với <strong>MommyKids</strong> - Hệ thống cửa hàng hàng đầu chuyên cung cấp các sản phẩm cao cấp dành riêng cho Mẹ bầu và Bé sơ sinh.
                </p>

                <h3 class="text-xl font-bold text-slate-800 pt-2">1. Sứ mệnh của chúng tôi</h3>
                <p>
                    MommyKids ra đời với sứ mệnh đồng hành cùng hàng triệu gia đình Việt Nam trong hành trình thiêng liêng: Chăm sóc và nuôi dạy con trẻ. Chúng tôi tin rằng mỗi em bé đều xứng đáng nhận được những điều tốt đẹp nhất ngay từ những năm tháng đầu đời.
                </p>

                <h3 class="text-xl font-bold text-slate-800 pt-2">2. Cam kết chất lượng</h3>
                <ul class="list-disc list-inside space-y-2 pl-2">
                    <li><strong>100% Sản phẩm chính hãng:</strong> Tất cả sản phẩm sữa, tã bỉm, đồ dùng sơ sinh đều có nguồn gốc rõ ràng, hóa đơn VAT đầy đủ.</li>
                    <li><strong>Giá cả cạnh tranh:</strong> Luôn mang lại mức giá hợp lý cùng nhiều chương trình ưu đãi hấp dẫn cho ba mẹ.</li>
                    <li><strong>Tư vấn tận tâm:</strong> Đội ngũ nhân viên am hiểu kiến thức chăm sóc mẹ và bé, sẵn sàng hỗ trợ 24/7.</li>
                </ul>

                <h3 class="text-xl font-bold text-slate-800 pt-2">3. Mạng lưới & Dịch vụ</h3>
                <p>
                    Với chuỗi cửa hàng hiện đại và hệ thống đặt hàng online giao nhanh 2 giờ, MommyKids cam kết mang tới trải nghiệm mua sắm tiện lợi, an tâm tuyệt đối cho mọi gia đình.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection