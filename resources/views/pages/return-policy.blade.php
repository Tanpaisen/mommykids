@extends('client.layouts.app')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-5xl mx-auto px-4">
        <nav class="flex text-xs text-slate-500 mb-6 gap-2">
            <a href="/" class="hover:text-rose-600">Trang chủ</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Chính sách đổi trả</span>
        </nav>

        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200/80 space-y-6">
            <h1 class="text-3xl font-extrabold text-slate-800 border-b border-slate-100 pb-4">
                Chính sách đổi trả hàng
            </h1>

            <div class="space-y-4 text-slate-700 leading-relaxed">
                <h3 class="text-lg font-bold text-slate-800">1. Thời hạn đổi trả</h3>
                <p>MommyKids hỗ trợ đổi trả sản phẩm trong vòng <strong>07 ngày</strong> kể từ ngày khách hàng nhận được đơn hàng.</p>

                <h3 class="text-lg font-bold text-slate-800">2. Điều kiện áp dụng</h3>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Sản phẩm còn nguyên tem, mác, niêm phong của nhà sản xuất.</li>
                    <li>Sản phẩm còn đầy đủ hộp, phụ kiện, quà tặng kèm (nếu có).</li>
                    <li>Có hóa đơn mua hàng hoặc thông tin đơn hàng trên hệ thống MommyKids.</li>
                    <li>Sản phẩm bị lỗi do nhà sản xuất hoặc hư hỏng trong quá trình vận chuyển.</li>
                </ul>

                <h3 class="text-lg font-bold text-slate-800">3. Các trường hợp không hỗ trợ đổi trả</h3>
                <p>Các sản phẩm thuộc nhóm vệ sinh cá nhân, tã bỉm đã mở bao bì, sữa thực phẩm đã bóc tem niêm phong (trừ trường hợp sản phẩm bị lỗi chất lượng do nhà sản xuất).</p>

                <h3 class="text-lg font-bold text-slate-800">4. Quy trình đổi trả</h3>
                <p>Liên hệ Hotline <strong>1800 6886</strong> hoặc mang trực tiếp sản phẩm đến cửa hàng MommyKids gần nhất để được nhân viên hỗ trợ đổi mới.</p>
            </div>
        </div>
    </div>
</div>
@endsection