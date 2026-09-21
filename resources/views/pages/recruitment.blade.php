@extends('client.layouts.app')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <nav class="flex text-xs text-slate-500 mb-6 gap-2">
            <a href="/" class="hover:text-rose-600">Trang chủ</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Tuyển dụng</span>
        </nav>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 space-y-6">
            <h1 class="text-3xl font-extrabold text-slate-800 border-b border-slate-100 pb-4">
                Cơ hội nghề nghiệp tại <span class="text-rose-500">MommyKids</span>
            </h1>

            <p class="text-slate-600">
                Gia nhập gia đình MommyKids để cùng xây dựng môi trường làm việc năng động, chuyên nghiệp và nhận chế độ đãi ngộ hấp dẫn.
            </p>

            <div class="space-y-4 pt-2">
                <!-- Vị trí 1 -->
                <div class="border border-slate-200 rounded-xl p-5 hover:border-rose-300 transition flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">1. Nhân viên Tư vấn Bán hàng (Full-time / Part-time)</h3>
                        <p class="text-sm text-slate-500">Địa điểm: Các cửa hàng tại Hà Nội | Số lượng: 05 người</p>
                        <p class="text-sm text-slate-600 mt-1">Mức lương: 7.000.000đ - 10.000.000đ + Doanh số</p>
                    </div>
                    <a href="mailto:tuyendung@mommykids.vn" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition shrink-0">Ứng tuyển ngay</a>
                </div>

                <!-- Vị trí 2 -->
                <div class="border border-slate-200 rounded-xl p-5 hover:border-rose-300 transition flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">2. Specialist Marketing Online</h3>
                        <p class="text-sm text-slate-500">Địa điểm: Văn phòng Cầu Giấy, Hà Nội | Số lượng: 02 người</p>
                        <p class="text-sm text-slate-600 mt-1">Mức lương: 10.000.000đ - 15.000.000đ</p>
                    </div>
                    <a href="mailto:tuyendung@mommykids.vn" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition shrink-0">Ứng tuyển ngay</a>
                </div>
            </div>

            <div class="bg-rose-50 p-4 rounded-xl text-sm text-rose-800">
                📧 Gửi CV ứng tuyển trực tiếp về Email: <strong>tuyendung@mommykids.vn</strong> (Tiêu đề: [Họ tên] - [Vị trí ứng tuyển])
            </div>
        </div>
    </div>
</div>
@endsection