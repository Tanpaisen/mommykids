@extends('client.layouts.app')

@section('title', 'Giới thiệu về MommyKids - Hệ thống Mẹ & Bé Hàng Đầu')

@section('content')
<div class="space-y-12 pb-16">

    {{-- 1. HERO BANNER (Phong cách AvaKids & Con Cung) --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-rose-500 via-pink-500 to-rose-600 text-white p-8 md:p-12 shadow-xl">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
            <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-bold uppercase tracking-wider">
                    🎀 Hệ Thống Siêu Thị Mẹ & Bé Uy Tín
                </span>
                <h1 class="text-3xl md:text-5xl font-black leading-tight">
                    Nâng Niu Hành Trình Khôn Lớn <br class="hidden sm:inline"/> Cùng Mẹ & Bé Việt Nam
                </h1>
                <p class="text-rose-100 text-sm md:text-base leading-relaxed max-w-2xl">
                    MommyKids tiên phong mang đến hệ sinh thái sản phẩm chính hãng 100%, kết hợp cùng dịch vụ tư vấn chuẩn Y khoa, giúp ba mẹ an tâm nuôi dạy con nhẹ nhàng và trọn vẹn niềm vui.
                </p>
                <div class="pt-2 flex flex-wrap gap-4 justify-center lg:justify-start">
                    <a href="{{ url('/') }}" class="px-7 py-3.5 rounded-full bg-white text-rose-600 font-bold text-sm hover:bg-rose-50 transition shadow-lg">
                        Khám phá cửa hàng ngay ➔
                    </a>
                    <a href="#timeline" class="px-7 py-3.5 rounded-full bg-rose-700/50 hover:bg-rose-700 text-white font-semibold text-sm transition border border-white/30">
                        Hành trình phát triển
                    </a>
                </div>
            </div>

            {{-- Image Stack --}}
            <div class="lg:col-span-5 relative">
                <div class="relative mx-auto max-w-sm lg:max-w-none">
                    <img src="https://images.unsplash.com/photo-1555252333-9f8e92e65df9?q=80&w=800&auto=format&fit=crop" 
                         alt="MommyKids Mẹ và bé" 
                         class="rounded-3xl shadow-2xl object-cover h-80 lg:h-96 w-full border-4 border-white/30 transform hover:scale-[1.02] transition duration-500"/>
                    <div class="absolute -bottom-4 -left-4 bg-white text-gray-900 p-4 rounded-2xl shadow-xl border border-rose-100 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 font-bold text-2xl">
                            🏆
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900">Top 10 Thương Hiệu Mẹ & Bé</p>
                            <p class="text-[11px] text-gray-500">Được hơn 500.000 Mẹ bỉm tin chọn</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. THỐNG KÊ NỔI BẬT (Stats Bar - Tham khảo KidsPlaza) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-rose-100 text-center shadow-sm hover:shadow-md transition">
            <div class="text-3xl md:text-4xl font-black text-rose-600">100%</div>
            <div class="text-xs text-gray-600 font-bold mt-1">Sản phẩm chính hãng</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Hóa đơn & Kiểm định đầy đủ</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-rose-100 text-center shadow-sm hover:shadow-md transition">
            <div class="text-3xl md:text-4xl font-black text-rose-600">500.000+</div>
            <div class="text-xs text-gray-600 font-bold mt-1">Khách hàng tin dùng</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Trên toàn quốc</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-rose-100 text-center shadow-sm hover:shadow-md transition">
            <div class="text-3xl md:text-4xl font-black text-rose-600">2.000+</div>
            <div class="text-xs text-gray-600 font-bold mt-1">Sản phẩm chọn lọc</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Từ các thương hiệu uy tín</div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-rose-100 text-center shadow-sm hover:shadow-md transition">
            <div class="text-3xl md:text-4xl font-black text-rose-600">2 Giờ</div>
            <div class="text-xs text-gray-600 font-bold mt-1">Giao hàng hỏa tốc</div>
            <div class="text-[10px] text-gray-400 mt-0.5">Áp dụng khu vực nội thành</div>
        </div>
    </div>

    {{-- 3. CÂU CHUYỆN & SỨ MỆNH (Phong cách BiboMart) --}}
    <div class="bg-white rounded-3xl p-6 md:p-10 border border-gray-100 shadow-sm space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div class="space-y-4">
                <span class="inline-block px-3 py-1 rounded-lg bg-rose-50 text-rose-600 text-xs font-bold">
                    Khởi nguồn từ tình yêu thương
                </span>
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 leading-snug">
                    Sứ Mệnh Giúp Mẹ Nuôi Con Nhẹ Nhàng & Khoa Học
                </h2>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Được thành lập với tâm huyết của những người làm cha làm mẹ, <strong>MommyKids</strong> hiểu rõ mỗi giai đoạn phát triển của trẻ nhỏ đều cần sự chăm sóc đặc biệt và tỉ mỉ nhất.
                </p>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Chúng tôi không chỉ kinh doanh các sản phẩm Mẹ & Bé, mà còn xây dựng một điểm tựa tin cậy cho các gia đình – nơi cung cấp thông tin hữu ích, sản phẩm đạt chuẩn an toàn y tế và sự phục vụ tận tâm nhất.
                </p>
            </div>
            <div>
                <img src="https://images.unsplash.com/photo-1519689680058-324335c77eba?q=80&w=800&auto=format&fit=crop" 
                     alt="Chăm sóc bé MommyKids" 
                     class="rounded-2xl shadow-lg object-cover h-72 md:h-80 w-full border border-gray-100"/>
            </div>
        </div>
    </div>

    {{-- 4. CỘT MỐC PHÁT TRIỂN / TIMELINE (Học hỏi từ KidsPlaza) --}}
    <div id="timeline" class="space-y-6">
        <div class="text-center max-w-xl mx-auto space-y-2">
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">Hành Trình Phát Triển</h2>
            <p class="text-xs md:text-sm text-gray-500">Những bước tiến khẳng định vị thế thương hiệu của MommyKids</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl border-t-4 border-rose-500 shadow-sm space-y-2">
                <span class="text-rose-600 font-black text-xl">2021</span>
                <h4 class="font-bold text-gray-900 text-sm">Khởi Đầu Tận Tâm</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Mở cửa hàng đầu tiên chuyên tư vấn đồ sơ sinh trọn gói cho mẹ bầu.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border-t-4 border-rose-500 shadow-sm space-y-2">
                <span class="text-rose-600 font-black text-xl">2023</span>
                <h4 class="font-bold text-gray-900 text-sm">Mở Rộng Hệ Thống</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Đạt mốc 10 cửa hàng và ký kết hợp tác trực tiếp với các tập đoàn Dinh dưỡng toàn cầu.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border-t-4 border-rose-500 shadow-sm space-y-2">
                <span class="text-rose-600 font-black text-xl">2025</span>
                <h4 class="font-bold text-gray-900 text-sm">Chuyển Đổi Số</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Ra mắt Nền tảng Website E-commerce mua sắm giao hỏa tốc trong 2 giờ.</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border-t-4 border-rose-500 shadow-sm space-y-2">
                <span class="text-rose-600 font-black text-xl">Hiện Tại</span>
                <h4 class="font-bold text-gray-900 text-sm">Thương Hiệu Yêu Thích</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Trở thành một trong những chuỗi bán lẻ Mẹ & Bé có tốc độ tăng trưởng cao nhất.</p>
            </div>
        </div>
    </div>

    {{-- 5. 6 CAM KẾT VÀNG (Tham khảo AvaKids & Con Cung) --}}
    <div class="space-y-6">
        <div class="text-center max-w-xl mx-auto space-y-2">
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">6 Cam Kết Vàng Từ MommyKids</h2>
            <p class="text-xs md:text-sm text-gray-500">Tiêu chuẩn phục vụ vượt trội mang đến sự an tâm tuyệt đối</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-start">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex-shrink-0 flex items-center justify-center font-bold text-xl">🛡️</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-gray-900">100% Chính Hãng</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Nguồn gốc minh bạch, đầy đủ hóa đơn chứng từ kiểm định an toàn.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-start">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex-shrink-0 flex items-center justify-center font-bold text-xl">💰</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-gray-900">Giá Cả Cạnh Tranh</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Luôn có chương trình ưu đãi, quà tặng hấp dẫn dành riêng cho Mẹ bỉm.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-start">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex-shrink-0 flex items-center justify-center font-bold text-xl">⚡</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-gray-900">Giao Nhanh 2 Giờ</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Đặt hàng online, nhận hàng siêu tốc đáp ứng nhu cầu khẩn cấp cho Bé.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-start">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex-shrink-0 flex items-center justify-center font-bold text-xl">👩‍⚕️</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-gray-900">Tư Vấn Chuyên Sâu</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Đội ngũ nhân viên am hiểu kiến thức dinh dưỡng & tư vấn đồ sơ sinh.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-start">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex-shrink-0 flex items-center justify-center font-bold text-xl">🔄</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-gray-900">Đổi Trả 15 Ngày</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Chính sách hậu mãi linh hoạt, xử lý nhanh chóng trong vòng 24h.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-4 items-start">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex-shrink-0 flex items-center justify-center font-bold text-xl">🎁</div>
                <div class="space-y-1">
                    <h4 class="font-bold text-sm text-gray-900">Tích Điểm Cực Khủng</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Hệ thống thành viên VIP tích điểm chiết khấu cực cao cho mọi đơn hàng.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 6. ĐỐI TÁC THƯƠNG HIỆU HÀNG ĐẦU (Brands Carousel/Grid) --}}
    <div class="bg-rose-50/60 rounded-3xl p-8 border border-rose-100 space-y-4">
        <div class="text-center space-y-1">
            <h3 class="font-black text-gray-900 text-lg">Đối Tác Chiến Lược Của MommyKids</h3>
            <p class="text-xs text-gray-500">Được phân phối chính thức bởi các thương hiệu Mẹ & Bé hàng đầu thế giới</p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-6 md:gap-10 pt-2 opacity-80 font-black text-gray-400 text-sm md:text-base">
            <span class="px-4 py-2 bg-white rounded-xl shadow-sm border border-rose-100 text-rose-500">MEIJI</span>
            <span class="px-4 py-2 bg-white rounded-xl shadow-sm border border-rose-100 text-rose-500">APTAMIL</span>
            <span class="px-4 py-2 bg-white rounded-xl shadow-sm border border-rose-100 text-rose-500">MERRIES</span>
            <span class="px-4 py-2 bg-white rounded-xl shadow-sm border border-rose-100 text-rose-500">MOONY</span>
            <span class="px-4 py-2 bg-white rounded-xl shadow-sm border border-rose-100 text-rose-500">PIGEON</span>
            <span class="px-4 py-2 bg-white rounded-xl shadow-sm border border-rose-100 text-rose-500">COMBI</span>
        </div>
    </div>

    {{-- 7. LỜI KHUYÊN & ĐÁNH GIÁ TỪ MẸ BỈM (Testimonials) --}}
    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
        <div class="text-center max-w-xl mx-auto space-y-1">
            <h2 class="text-xl md:text-2xl font-black text-gray-900">Ba Mẹ Nói Gì Về MommyKids?</h2>
            <p class="text-xs text-gray-500">Niềm tin của khách hàng là tài sản lớn nhất của chúng tôi</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100 space-y-3">
                <div class="flex text-amber-400 text-sm">⭐⭐⭐⭐⭐</div>
                <p class="text-xs text-gray-600 italic leading-relaxed">
                    "Đồ sơ sinh ở MommyKids đầy đủ và rất chất lượng. Nhân viên tư vấn nhiệt tình đúng nhu cầu chứ không chèo kéo mua dư thừa."
                </p>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-full bg-rose-200 text-rose-700 font-bold text-xs flex items-center justify-center">M</div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Chị Minh Anh</p>
                        <p class="text-[10px] text-gray-400">Mẹ bé Bắp (Hà Nội)</p>
                    </div>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100 space-y-3">
                <div class="flex text-amber-400 text-sm">⭐⭐⭐⭐⭐</div>
                <p class="text-xs text-gray-600 italic leading-relaxed">
                    "Thích nhất dịch vụ giao hỏa tốc 2h. Nhiều hôm con hết sữa bất ngờ đặt trên web MommyKids một tí là có người giao tận nhà!"
                </p>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-full bg-rose-200 text-rose-700 font-bold text-xs flex items-center justify-center">T</div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Chị Thanh Hằng</p>
                        <p class="text-[10px] text-gray-400">Mẹ bé Chip (Đà Nẵng)</p>
                    </div>
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100 space-y-3">
                <div class="flex text-amber-400 text-sm">⭐⭐⭐⭐⭐</div>
                <p class="text-xs text-gray-600 italic leading-relaxed">
                    "Sữa và Tã bỉm chuẩn chính hãng 100%, tích điểm thành viên giảm được khá nhiều tiền mỗi tháng. Rất yên tâm khi mua ở đây."
                </p>
                <div class="flex items-center gap-3 pt-2">
                    <div class="w-8 h-8 rounded-full bg-rose-200 text-rose-700 font-bold text-xs flex items-center justify-center">H</div>
                    <div>
                        <p class="text-xs font-bold text-gray-800">Anh Hoàng Nam</p>
                        <p class="text-[10px] text-gray-400">Bố bé Ben (TP.HCM)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 8. CALL TO ACTION (CTA) --}}
    <div class="rounded-3xl bg-gradient-to-r from-rose-600 to-pink-500 p-8 md:p-12 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 text-center md:text-left">
            <h3 class="text-2xl md:text-3xl font-black">"Sự An Toàn Của Bé – Niềm An Tâm Của Mẹ"</h3>
            <p class="text-rose-100 text-xs md:text-sm max-w-lg">
                Đăng ký tài khoản ngay hôm nay để nhận voucher giảm 10% cho đơn hàng đầu tiên!
            </p>
        </div>
        <a href="{{ url('/') }}" class="px-8 py-3.5 bg-white text-rose-600 rounded-full font-bold text-sm hover:bg-rose-50 transition shadow-md whitespace-nowrap">
            Mua sắm ngay ➔
        </a>
    </div>

</div>
@endsection