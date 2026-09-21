@extends('client.layouts.app')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <nav class="flex text-xs text-slate-500 mb-6 gap-2">
            <a href="/" class="hover:text-rose-600">Trang chủ</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Hệ thống cửa hàng</span>
        </nav>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 space-y-6">
            <h1 class="text-3xl font-extrabold text-slate-800 border-b border-slate-100 pb-4">
                Hệ thống cửa hàng <span class="text-rose-500">MommyKids</span>
            </h1>

            <p class="text-slate-600">
                Ghé thăm các chi nhánh MommyKids gần nhất để trải nghiệm không gian mua sắm hiện đại và nhận sự tư vấn trực tiếp từ đội ngũ chuyên nghiệp.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <!-- Chi nhánh 1 -->
                <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/50 space-y-2">
                    <span class="inline-block px-3 py-1 bg-rose-100 text-rose-600 rounded-full text-xs font-bold">Chi nhánh 1</span>
                    <h3 class="font-bold text-slate-800 text-lg">MommyKids Cầu Giấy</h3>
                    <p class="text-sm text-slate-600">📍 <strong>Địa chỉ:</strong> Số 123 Đường Cầu Giấy, Quận Cầu Giấy, Hà Nội</p>
                    <p class="text-sm text-slate-600">📞 <strong>Hotline:</strong> 024 3888 6868</p>
                    <p class="text-sm text-slate-600">⏰ <strong>Giờ mở cửa:</strong> 08:00 - 22:00 (Tất cả các ngày trong tuần)</p>
                </div>

                <!-- Chi nhánh 2 -->
                <div class="border border-slate-200 rounded-xl p-5 bg-slate-50/50 space-y-2">
                    <span class="inline-block px-3 py-1 bg-rose-100 text-rose-600 rounded-full text-xs font-bold">Chi nhánh 2</span>
                    <h3 class="font-bold text-slate-800 text-lg">MommyKids Thanh Xuân</h3>
                    <p class="text-sm text-slate-600">📍 <strong>Địa chỉ:</strong> Số 456 Đường Nguyễn Trãi, Quận Thanh Xuân, Hà Nội</p>
                    <p class="text-sm text-slate-600">📞 <strong>Hotline:</strong> 024 3999 6868</p>
                    <p class="text-sm text-slate-600">⏰ <strong>Giờ mở cửa:</strong> 08:00 - 22:00 (Tất cả các ngày trong tuần)</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection