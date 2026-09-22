<svg class="wave-divider text-coral-light" viewBox="0 0 1440 48" preserveAspectRatio="none" fill="currentColor">
    <path d="M0,24 C240,48 480,0 720,12 C960,24 1200,48 1440,24 L1440,48 L0,48 Z"></path>
</svg>

<footer class="bg-coral-light/70 pt-10 pb-24 lg:pb-10">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-6 grid grid-cols-2 lg:grid-cols-5 gap-8 text-sm">

        {{-- Cột 1: Thông tin thương hiệu & Liên hệ --}}
        <div class="col-span-2">
            <a href="{{ url('/') }}" class="flex items-center gap-2 mb-3">
                @if(!empty($globalSetting->logo))
                    <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="{{ $globalSetting->site_name ?? 'MommyKids' }}" class="h-9 w-auto object-contain">
                @else
                    <span class="w-9 h-9 rounded-blob bg-coral flex items-center justify-center text-white font-display font-bold">M</span>
                    <span class="font-display font-extrabold text-lg">{{ $globalSetting->site_name ?? 'MommyKids' }}</span>
                @endif
            </a>

            <p class="text-ink-soft leading-relaxed">
                {{ $globalSetting->footer_description ?? 'Chuỗi cửa hàng mẹ và bé chính hãng — Chính hãng, Hóa đơn VAT đầy đủ, Bảo giá tốt nhất.' }}
            </p>

            <div class="mt-3 space-y-1 text-ink-soft">
                <p>Hotline: <a href="tel:{{ $globalSetting->hotline }}" class="font-semibold text-coral hover:underline">{{ $globalSetting->hotline ?? '1800 6886' }}</a></p>
                @if(!empty($globalSetting->email))
                    <p>Email: <a href="mailto:{{ $globalSetting->email }}" class="hover:text-coral">{{ $globalSetting->email }}</a></p>
                @endif
                @if(!empty($globalSetting->address))
                    <p>Địa chỉ: <span class="text-ink">{{ $globalSetting->address }}</span></p>
                @endif
            </div>
        </div>

        {{-- Cột 2: Giới thiệu --}}
        <div>
            <h4 class="font-display font-semibold mb-3">Về {{ $globalSetting->site_name ?? 'MommyKids' }}</h4>
            <ul class="space-y-2 text-ink-soft">
                <li><a href="{{ route('pages.about') }}" class="hover:text-coral transition-colors">Giới thiệu</a></li>
                <li><a href="{{ route('pages.stores') }}" class="hover:text-coral transition-colors">Hệ thống cửa hàng</a></li>
                <li><a href="{{ route('pages.recruitment') }}" class="hover:text-coral transition-colors">Tuyển dụng</a></li>
            </ul>
        </div>

        {{-- Cột 3: Chính sách --}}
        <div>
            <h4 class="font-display font-semibold mb-3">Chính sách</h4>
            <ul class="space-y-2 text-ink-soft">
                <li><a href="{{ route('pages.return') }}" class="hover:text-coral transition-colors">Đổi trả hàng</a></li>
                <li><a href="{{ route('pages.shipping') }}" class="hover:text-coral transition-colors">Vận chuyển</a></li>
                <li><a href="{{ route('pages.privacy') }}" class="hover:text-coral transition-colors">Bảo mật</a></li>
            </ul>
        </div>

        {{-- Cột 4: Mạng xã hội --}}
        <div>
            <h4 class="font-display font-semibold mb-3">Kết nối</h4>
            <div class="flex gap-3">
                <a href="{{ $globalSetting->facebook_url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white flex items-center justify-center shadow-soft hover:text-coral transition-colors" title="Facebook">FB</a>
                <a href="{{ $globalSetting->zalo_url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white flex items-center justify-center shadow-soft hover:text-coral transition-colors" title="Zalo">Zalo</a>
                <a href="{{ $globalSetting->instagram_url ?? '#' }}" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-white flex items-center justify-center shadow-soft hover:text-coral transition-colors" title="Instagram">IG</a>
            </div>
        </div>
    </div>

    {{-- Dòng Copyright cuối trang --}}
    <div class="max-w-[1280px] mx-auto px-4 lg:px-6 mt-8 pt-4 border-t border-coral/20 text-xs text-ink-soft">
        {{ $globalSetting->copyright ?? ('© ' . date('Y') . ' MommyKids. Đã đăng ký bản quyền.') }}
    </div>
</footer>