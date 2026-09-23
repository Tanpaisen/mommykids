@extends('admin.layouts.app')

@section('page_title', 'Cấu hình hệ thống')
@section('page_subtitle', 'Quản lý toàn bộ thông tin chung, cấu hình text giao diện và mã SEO/Tracking')

@section('content')
<div class="space-y-6">

    <!-- Thông báo thành công -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Thông báo lỗi nhập liệu -->
    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm space-y-1">
            <div class="font-semibold">Vui lòng kiểm tra lại thông tin nhập:</div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Thanh chuyển Tab -->
    <div class="flex items-center gap-2 border-b border-slate-200 bg-white px-4 pt-3 rounded-t-xl overflow-x-auto">
        <button type="button" id="btn-tab-general" onclick="switchTab('general')" 
            class="tab-btn pb-3 px-4 text-sm border-b-2 font-bold border-rose-500 text-rose-600 transition whitespace-nowrap">
            ⚙️ Cấu hình chung & Liên hệ
        </button>
        <button type="button" id="btn-tab-text" onclick="switchTab('text')" 
            class="tab-btn pb-3 px-4 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition whitespace-nowrap">
            📝 Cấu hình Text & Giao diện
        </button>
        <button type="button" id="btn-tab-seo" onclick="switchTab('seo')" 
            class="tab-btn pb-3 px-4 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition whitespace-nowrap">
            🔍 SEO & Mã nhúng
        </button>
    </div>

    <!-- Form cập nhật cài đặt chính -->
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- ========================================== -->
        <!-- TAB 1: CẤU HÌNH CHUNG & LIÊN HỆ -->
        <!-- ========================================== -->
        <div id="tab-general" class="tab-content space-y-6">
            
            <!-- Card Thương hiệu & Logo -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">1. Nhận diện thương hiệu</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Tên Website / Thương hiệu</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $setting->site_name ?? 'MommyKids') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-sm text-slate-800 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Dòng bản quyền Footer (Copyright)</label>
                        <input type="text" name="copyright" value="{{ old('copyright', $setting->copyright ?? '© 2026 MommyKids. Đã đăng ký bản quyền.') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 text-sm text-slate-800 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <!-- Logo preview & Upload -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Logo Website hiện tại</label>
                        <div class="flex items-center gap-4">
                            <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 p-2 flex items-center justify-center bg-slate-50 shrink-0 overflow-hidden">
                                @php
                                    $logoUrl = null;

                                    // 1. Kiểm tra nếu trong CSDL có lưu logo
                                    if (!empty($setting->logo)) {
                                        if (\Illuminate\Support\Str::startsWith($setting->logo, ['http://', 'https://'])) {
                                            $logoUrl = $setting->logo;
                                        } else {
                                            $cleanLogo = ltrim(str_replace(['public/', 'storage/'], '', $setting->logo), '/');
                                            if (file_exists(public_path('storage/' . $cleanLogo))) {
                                                $logoUrl = asset('storage/' . $cleanLogo);
                                            } elseif (file_exists(public_path($cleanLogo))) {
                                                $logoUrl = asset($cleanLogo);
                                            } else {
                                                $logoUrl = asset('storage/' . $cleanLogo);
                                            }
                                        }
                                    }

                                    // 2. Nếu CSDL chưa lưu, tự quét tệp logo tĩnh ngoài trang chủ
                                    if (!$logoUrl) {
                                        $staticPaths = ['images/logo.png', 'images/logo.svg', 'assets/images/logo.png', 'frontend/images/logo.png'];
                                        foreach ($staticPaths as $path) {
                                            if (file_exists(public_path($path))) {
                                                $logoUrl = asset($path);
                                                break;
                                            }
                                        }
                                    }

                                    // 3. Fallback mặc định
                                    $logoUrl = $logoUrl ?? 'https://placehold.co/150x150/f8fafc/cbd5e1?text=No+Logo';
                                @endphp
                                <img id="logo-preview" 
                                     src="{{ $logoUrl }}" 
                                     onerror="this.onerror=null; this.src='https://placehold.co/150x150/f8fafc/cbd5e1?text=No+Logo';"
                                     class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="logo" accept="image/*" onchange="previewImage(event, 'logo-preview')" 
                                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-2">Hỗ trợ PNG, JPG, WEBP, SVG. Tối đa 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Favicon preview & Upload -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Favicon Browser hiện tại</label>
                        <div class="flex items-center gap-4">
                            <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 p-2 flex items-center justify-center bg-slate-50 shrink-0 overflow-hidden">
                                @php
                                    $faviconUrl = null;

                                    if (!empty($setting->favicon)) {
                                        if (\Illuminate\Support\Str::startsWith($setting->favicon, ['http://', 'https://'])) {
                                            $faviconUrl = $setting->favicon;
                                        } else {
                                            $cleanFavicon = ltrim(str_replace(['public/', 'storage/'], '', $setting->favicon), '/');
                                            if (file_exists(public_path('storage/' . $cleanFavicon))) {
                                                $faviconUrl = asset('storage/' . $cleanFavicon);
                                            } elseif (file_exists(public_path($cleanFavicon))) {
                                                $faviconUrl = asset($cleanFavicon);
                                            } else {
                                                $faviconUrl = asset('storage/' . $cleanFavicon);
                                            }
                                        }
                                    }

                                    if (!$faviconUrl) {
                                        $staticFavicons = ['favicon.ico', 'images/favicon.png', 'images/favicon.ico', 'assets/images/favicon.png'];
                                        foreach ($staticFavicons as $path) {
                                            if (file_exists(public_path($path))) {
                                                $faviconUrl = asset($path);
                                                break;
                                            }
                                        }
                                    }

                                    $faviconUrl = $faviconUrl ?? 'https://placehold.co/64x64/f8fafc/cbd5e1?text=Favicon';
                                @endphp
                                <img id="favicon-preview" 
                                     src="{{ $faviconUrl }}" 
                                     onerror="this.onerror=null; this.src='https://placehold.co/64x64/f8fafc/cbd5e1?text=Favicon';"
                                     class="w-10 h-10 object-contain">
                            </div>
                            <div class="flex-1">
                                <input type="file" name="favicon" accept="image/*" onchange="previewImage(event, 'favicon-preview')" 
                                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-600 hover:file:bg-rose-100 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-2">Icon trên Tab trình duyệt (.ico, .png 32x32px).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Thông tin liên hệ & Mạng xã hội -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3 md:col-span-2">2. Thông tin liên hệ & Mạng xã hội</h3>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Hotline tư vấn</label>
                    <input type="text" name="hotline" value="{{ old('hotline', $setting->hotline ?? '1800 6886') }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Email hỗ trợ</label>
                    <input type="email" name="email" value="{{ old('email', $setting->email ?? 'hotro@mommykids.vn') }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Địa chỉ cửa hàng / Trụ sở chính</label>
                    <textarea name="address" rows="2" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">{{ old('address', $setting->address ?? 'Số 123 Đường ABC, Quận XYZ, Hà Nội') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Trang Fanpage Facebook</label>
                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $setting->facebook_url ?? '') }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800" placeholder="https://facebook.com/mommykids">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Liên kết Zalo OA / Số Zalo</label>
                    <input type="url" name="zalo_url" value="{{ old('zalo_url', $setting->zalo_url ?? '') }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800" placeholder="https://zalo.me/0900000000">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Link Trang Instagram (Nút IG Chân Trang)</label>
                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $setting->instagram_url ?? '') }}" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800" placeholder="https://instagram.com/mommykids">
                </div>
            </div>

            <!-- Nút Lưu cho Tab 1 -->
            <div class="flex justify-end pt-2">
                <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm shadow-md shadow-rose-500/20 transition duration-200">
                    Lưu cấu hình hệ thống
                </button>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: CẤU HÌNH TEXT & GIAO DIỆN -->
        <!-- ========================================== -->
        <div id="tab-text" class="tab-content hidden space-y-6">
            
            <!-- 1. Header & Search Box -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">1. Thanh Header & Ô tìm kiếm</h3>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Thanh thông báo chạy chữ trên Header (Top Announcement)</label>
                    <input type="text" name="top_announcement" value="{{ old('top_announcement', $setting->top_announcement ?? '') }}" 
                        placeholder="VD: Miễn phí vận chuyển cho đơn hàng từ 300.000đ - Giao nhanh 2h" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Địa chỉ giao hàng mặc định Header</label>
                        <input type="text" name="default_location" value="{{ old('default_location', $setting->default_location ?? 'Hà Nội') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800" placeholder="VD: Hà Nội">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Gợi ý trong khung tìm kiếm (Search Placeholder)</label>
                        <input type="text" name="search_placeholder" value="{{ old('search_placeholder', $setting->search_placeholder ?? 'Ba mẹ cần tìm gì cho bé hôm nay?') }}" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800" placeholder="VD: Ba mẹ cần tìm gì cho bé hôm nay?">
                    </div>
                </div>
            </div>

            <!-- 2. Khối Banner Chính & Khối Banner Vàng -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">2. Tiêu đề các khối Trang chủ</h3>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Tiêu đề khối Banner chính Trang chủ</label>
                    <input type="text" name="home_banner_title" value="{{ old('home_banner_title', $setting->home_banner_title ?? '') }}" 
                        placeholder="VD: Sữa thùng giá tốt tháng này" 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">
                </div>

                <!-- Banner Vàng Ưu Đãi -->
                <div class="pt-3 border-t border-slate-100 space-y-4">
                    <h4 class="font-bold text-sm text-slate-700">Khối "Ưu đãi dành cho ba mẹ" (Banner Vàng)</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Tiêu đề khối</label>
                            <input type="text" name="promo_title" value="{{ old('promo_title', $setting->promo_title ?? 'Ưu đãi dành cho ba mẹ') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Mô tả phụ</label>
                            <input type="text" name="promo_subtitle" value="{{ old('promo_subtitle', $setting->promo_subtitle ?? 'Nhập mã ngay để nhận ưu đãi cho lần mua đầu tiên') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Hộp ưu đãi 1</label>
                            <input type="text" name="promo_badge_1" value="{{ old('promo_badge_1', $setting->promo_badge_1 ?? '30K Voucher') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Hộp ưu đãi 2</label>
                            <input type="text" name="promo_badge_2" value="{{ old('promo_badge_2', $setting->promo_badge_2 ?? '-12% Tã & Bỉm') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Hộp ưu đãi 3</label>
                            <input type="text" name="promo_badge_3" value="{{ old('promo_badge_3', $setting->promo_badge_3 ?? '-15% Sữa bột') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-2">Chữ trên nút bấm CTA</label>
                            <input type="text" name="promo_button_text" value="{{ old('promo_button_text', $setting->promo_button_text ?? 'Nhận ngay') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-rose-600">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Footer Text -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">3. Nội dung Chân trang (Footer Text)</h3>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Slogan / Đoạn văn giới thiệu thương hiệu ở Footer</label>
                    <textarea name="footer_description" rows="3" 
                        placeholder="VD: Chuỗi cửa hàng mẹ và bé chính hãng – Chính hãng, Hóa đơn VAT đầy đủ, Bảo giá tốt nhất."
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">{{ old('footer_description', $setting->footer_description ?? '') }}</textarea>
                </div>
            </div>

            <!-- Nút Lưu cho Tab 2 (Cấu hình hệ thống) -->
            <div class="flex justify-end pt-2 pb-4">
                <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm shadow-md shadow-rose-500/20 transition duration-200">
                    Lưu cấu hình hệ thống
                </button>
            </div>

            <!-- 4. QUẢN LÝ TÊN MENU ADMIN SIDEBAR -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span>⚙️</span> 4. Quản lý tên Menu Admin Sidebar
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Chỉnh sửa tên hiển thị của các mục menu ở thanh Sidebar bên trái</p>
                </div>

                <div class="space-y-4">
                    @php
                        $groupedMenus = isset($menus) ? $menus->groupBy('group_name') : collect();
                    @endphp

                    @foreach($groupedMenus as $groupName => $groupItems)
                        <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                            <h4 class="font-bold text-indigo-600 text-xs uppercase mb-3 flex items-center gap-1.5">
                                <span>📁</span> Nhóm: {{ $groupName }}
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($groupItems as $item)
                                    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                                        <label class="block text-[11px] font-semibold text-slate-400 mb-1">
                                            Route: <code class="text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">{{ $item->route_name }}</code>
                                        </label>
                                        
                                        <input type="text" 
                                               name="menus[{{ $item->id }}][title]" 
                                               value="{{ $item->title }}" 
                                               class="w-full text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-medium text-slate-700">
                                        
                                        <input type="hidden" name="menus[{{ $item->id }}][group_name]" value="{{ $item->group_name }}">
                                        <input type="hidden" name="menus[{{ $item->id }}][order]" value="{{ $item->order }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Nút Lưu cho Mục 4 (Tên Menu Admin) -->
                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" formaction="{{ route('admin.menus.updateAll') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl text-xs transition-colors flex items-center gap-2 shadow">
                        <span>💾</span> LƯU TÊN MENU ADMIN
                    </button>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- TAB 3: SEO & MÃ NHÚNG -->
        <!-- ========================================== -->
        <div id="tab-seo" class="tab-content hidden space-y-6">
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-sm p-6 space-y-5">
                <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">Cấu hình SEO & Mã Tracking</h3>
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Mô tả SEO Mặc định (Meta Description)</label>
                    <textarea name="meta_description" rows="3" 
                        placeholder="Mô tả mặc định hiển thị trên kết quả tìm kiếm Google khi tìm tên thương hiệu..." 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-800">{{ old('meta_description', $setting->meta_description ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Mã nhúng Header (Google Analytics / Facebook Pixel / Chat Widget)</label>
                    <textarea name="header_scripts" rows="6" 
                        placeholder="Dán mã <script>...</script> vào đây..." 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono text-slate-800 leading-relaxed">{{ old('header_scripts', $setting->header_scripts ?? '') }}</textarea>
                </div>
            </div>

            <!-- Nút Lưu cho Tab 3 -->
            <div class="flex justify-end pt-2">
                <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm shadow-md shadow-rose-500/20 transition duration-200">
                    Lưu cấu hình hệ thống
                </button>
            </div>
        </div>

    </form>
</div>

<!-- JavaScript Xử Lý Chuyển Tab & Live Preview Image -->
<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-rose-500', 'text-rose-600', 'font-bold');
            btn.classList.add('border-transparent', 'text-slate-500');
        });

        const targetTab = document.getElementById('tab-' + tabName);
        if (targetTab) {
            targetTab.classList.remove('hidden');
        }

        const activeBtn = document.getElementById('btn-tab-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-slate-500');
            activeBtn.classList.add('border-rose-500', 'text-rose-600', 'font-bold');
        }
    }

    function previewImage(event, previewId) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById(previewId).src = reader.result;
        };
        if (event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endsection