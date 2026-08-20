@php
    // Each nav item's `can` maps to a Spatie permission (see database/seeders/PermissionSeeder.php).
    // Items whose permission the logged-in admin doesn't have are simply not rendered.
    $menu = [
        [
            'label' => 'Dashboard & Thống kê',
            'icon' => '📊',
            'can' => 'dashboard.view',
            'items' => [
                ['label' => 'Tổng quan', 'route' => 'admin.dashboard'],
            ],
        ],
        [
            'label' => 'Kiến thức & Sản phẩm',
            'icon' => '📦',
            'can' => 'catalog.view',
            'items' => [
                ['label' => 'Giai đoạn của bé', 'route' => 'admin.stages.index'],
                ['label' => 'Danh mục & Thuộc tính', 'route' => 'admin.categories.index'],
                ['label' => 'Sản phẩm', 'route' => 'admin.products.index'],
            ],
        ],
        [
            'label' => 'Cẩm nang & Tương tác',
            'icon' => '📚',
            'can' => 'handbook.view',
            'items' => [
                ['label' => 'Bài viết Cẩm nang', 'route' => 'admin.articles.index'],
                ['label' => 'Trung tâm Hỏi đáp', 'route' => 'admin.comments.index'],
            ],
        ],
        [
            'label' => 'Đơn hàng & Dòng tiền',
            'icon' => '🚚',
            'can' => 'orders.view',
            'items' => [
                ['label' => 'Đơn hàng', 'route' => 'admin.orders.index'],
                ['label' => 'Vận chuyển (GHN)', 'route' => 'admin.shipments.index'],
                ['label' => 'Đổi trả & Hoàn tiền', 'route' => 'admin.refunds.index'],
            ],
        ],
        [
            'label' => 'CRM & Marketing',
            'icon' => '👥',
            'can' => 'crm.view',
            'items' => [
                ['label' => 'Khách hàng', 'route' => 'admin.clients.index'],
                ['label' => 'Voucher', 'route' => 'admin.vouchers.index'],
                ['label' => 'Banner', 'route' => 'admin.banners.index'],
                ['label' => 'Tài khoản & Phân quyền', 'route' => 'admin.roles.index', 'can' => 'roles.manage'],
            ],
        ],
    ];
@endphp

<aside id="admin-sidebar"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-admin-sidebar text-white
              -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">

    <div class="flex items-center justify-between px-5 h-16 border-b border-white/10">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-blob bg-coral flex items-center justify-center font-display font-bold">M</span>
            <span class="font-display font-bold">MommyKids <span class="text-white/50 font-body font-normal text-xs">Admin</span></span>
        </a>
        <button id="admin-sidebar-close" class="lg:hidden text-white/70">✕</button>
    </div>

    <nav class="py-3">
        {{-- @foreach ($menu as $group)
            @can($group['can'])
                <div class="px-3 py-2">
                    <p class="px-2 text-[11px] uppercase tracking-wider text-white/40 font-semibold mb-1">
                        {{ $group['icon'] }} {{ $group['label'] }}
                    </p>
                    <ul>
                        @foreach ($group['items'] as $item)
                            @can($item['can'] ?? $group['can'])
                                <li>
                                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm
                                              {{ request()->routeIs($item['route'].'*') ? 'bg-coral text-white font-semibold' : 'text-white/75 hover:bg-admin-sidebar-hover hover:text-white' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endcan
                        @endforeach
                    </ul>
                </div>
            @endcan
        @endforeach --}}
        @foreach ($menu as $group)
    <div class="px-3 py-2">
        <p class="px-2 text-[11px] uppercase tracking-wider text-white/40 font-semibold mb-1">
            {{ $group['icon'] }} {{ $group['label'] }}
        </p>
        <ul>
            @foreach ($group['items'] as $item)
                <li>
                    <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm
                              {{ request()->routeIs($item['route'].'*') ? 'bg-coral text-white font-semibold' : 'text-white/75 hover:bg-admin-sidebar-hover hover:text-white' }}">
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
