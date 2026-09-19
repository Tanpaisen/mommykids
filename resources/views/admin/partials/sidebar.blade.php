@php
    $menu = [
        [
            'label' => 'Dashboard & Thống kê',
            'icon' => '📊',
            'can' => 'dashboard.view',
            'items' => [
                ['label' => 'Tổng quan', 'route' => 'admin.dashboard', 'url' => '/admin'],
            ],
        ],
        [
            'label' => 'Kiến thức & Sản phẩm',
            'icon' => '📦',
            'can' => 'catalog.view',
            'items' => [
                ['label' => 'Giai đoạn của bé', 'route' => 'admin.stages.index', 'url' => '/admin/stages'],
                ['label' => 'Danh mục & Thuộc tính', 'route' => 'admin.categories.index', 'url' => '/admin/categories'],
                ['label' => 'Sản phẩm', 'route' => 'admin.products.index', 'url' => '/admin/products'],
            ],
        ],
        [
            'label' => 'Cẩm nang & Tương tác',
            'icon' => '📚',
            'can' => 'handbook.view',
            'items' => [
                ['label' => 'Bài viết Cẩm nang', 'route' => 'admin.handbook-categories.index', 'url' => '/admin/cam-nang'],
                ['label' => 'Trung tâm Hỏi đáp', 'route' => 'hoi-dap.index', 'url' => '/admin/hoi-dap'],
            ],
        ],
        [
            'label' => 'Đơn hàng & Dòng tiền',
            'icon' => '🚚',
            'can' => 'orders.view',
            'items' => [
                ['label' => 'Đơn hàng', 'route' => 'admin.orders.index', 'url' => '/admin/orders'],
                ['label' => 'Vận chuyển (GHN)', 'route' => 'admin.shipments.index', 'url' => '/admin/shipments'],
                ['label' => 'Đổi trả & Hoàn tiền', 'route' => 'admin.refunds.index', 'url' => '/admin/refunds'],
            ],
        ],
        [
            'label' => 'CRM & Marketing',
            'icon' => '👥',
            'can' => 'crm.view',
            'items' => [
                ['label' => 'Khách hàng', 'route' => 'admin.customers.index', 'url' => '/admin/khach-hang'],
                ['label' => 'Voucher', 'route' => 'admin.vouchers.index', 'url' => '/admin/vouchers'],
                ['label' => 'Banner', 'route' => 'admin.banners.index', 'url' => '/admin/banners'],
            ],
        ],
        [
            'label' => 'Hệ thống',
            'icon' => '⚙️',
            'can' => 'roles.manage',
            'items' => [
                ['label' => 'Tài khoản quản trị', 'route' => 'admin.admins.index', 'url' => '/admin/admins'],
                ['label' => 'Nhóm quyền', 'route' => 'admin.roles.index', 'url' => '/admin/roles'],
                ['label' => 'Phân quyền', 'route' => 'admin.permissions.index', 'url' => '/admin/permissions'],
            ],
        ],
    ];
@endphp

<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-admin-sidebar text-white -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
    <div class="flex items-center justify-between px-5 h-16 border-b border-white/10">
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin') }}" class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-blob bg-coral flex items-center justify-center font-display font-bold">M</span>
            <span class="font-display font-bold">MommyKids <span class="text-white/50 font-body font-normal text-xs">Admin</span></span>
        </a>
        <button id="admin-sidebar-close" class="lg:hidden text-white/70">✕</button>
    </div>

    <nav class="py-3">
        @foreach ($menu as $group)
            <div class="px-3 py-2">
                <p class="px-2 text-[11px] uppercase tracking-wider text-white/40 font-semibold mb-1">
                    {{ $group['icon'] }} {{ $group['label'] }}
                </p>
                <ul>
                    @foreach ($group['items'] as $item)
                        @php
                            $itemUrl = Route::has($item['route']) ? route($item['route']) : url($item['url'] ?? '#');

                            if ($item['url'] === '/admin' || $item['route'] === 'admin.dashboard') {
                                $isActive = request()->routeIs('admin.dashboard') || request()->path() === 'admin';
                            } else {
                                $cleanUrl = ltrim($item['url'] ?? '', '/');
                                $isActive = (Route::has($item['route']) && request()->routeIs($item['route'] . '*')) || ($cleanUrl && request()->is($cleanUrl . '*'));
                            }
                        @endphp
                        <li>
                            <a href="{{ $itemUrl }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm {{ $isActive ? 'bg-coral text-white font-semibold' : 'text-white/75 hover:bg-admin-sidebar-hover hover:text-white' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>