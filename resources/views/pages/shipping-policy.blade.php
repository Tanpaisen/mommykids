@extends('client.layouts.app')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <nav class="flex text-xs text-slate-500 mb-6 gap-2">
            <a href="/" class="hover:text-rose-600">Trang chủ</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Chính sách vận chuyển</span>
        </nav>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 space-y-6">
            <h1 class="text-3xl font-extrabold text-slate-800 border-b border-slate-100 pb-4">
                Chính sách vận chuyển & Giao hàng
            </h1>

            <div class="space-y-4 text-slate-700 leading-relaxed">
                <h3 class="text-lg font-bold text-slate-800">1. Phạm vi giao hàng</h3>
                <p>MommyKids hỗ trợ giao hàng trên toàn quốc tới 63 tỉnh thành thông qua các đối tác vận chuyển uy tín (GHN, Viettel Post, GHTK...).</p>

                <h3 class="text-lg font-bold text-slate-800">2. Mức phí vận chuyển</h3>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li><strong>Miễn phí vận chuyển (Freeship):</strong> Cho mọi đơn hàng có giá trị từ <strong>300.000đ</strong> trở lên.</li>
                    <li><strong>Đơn hàng dưới 300.000đ:</strong> Áp dụng đồng giá phí ship 20.000đ nội thành và 30.000đ ngoại thành/tỉnh.</li>
                </ul>

                <h3 class="text-lg font-bold text-slate-800">3. Thời gian nhận hàng</h3>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li><strong>Giao nhanh nội thành (Hà Nội):</strong> Nhận hàng trong vòng 2 - 4 giờ.</li>
                    <li><strong>Các tỉnh thành khác:</strong> Nhận hàng từ 2 - 4 ngày làm việc.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection