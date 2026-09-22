@php
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Route;

    // ==============================================================
    // 1. MẢNG MENU TĨNH (CỐT LÕI - GIỮ NGUYÊN 100% ROUTE GỐC CỦA BẠN)
    // ==============================================================
    $staticMenu = [
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
            'can' => ['catalog.manage', 'products.manage'],
            'items' => [
                ['label' => 'Giai đoạn của bé', 'route' => 'admin.stages.index', 'can' => 'catalog.manage'],
                ['label' => 'Danh mục & Thuộc tính', 'route' => 'admin.categories.index', 'can' => 'catalog.manage'],
                ['label' => 'Sản phẩm', 'route' => 'admin.products.index', 'can' => 'products.manage'],
                ['label' => 'Quản lý Kho', 'route' => 'admin.inventory.index', 'can' => 'products.manage'],
                ['label' => 'Đánh giá sản phẩm', 'route' => 'admin.reviews.index', 'can' => 'products.manage'],
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
            'can' => ['orders.view', 'refunds.manage'],
            'items' => [
                ['label' => 'Đơn hàng', 'route' => 'admin.orders.index', 'can' => 'orders.view'],
                ['label' => 'Vận chuyển (GHN)', 'route' => 'admin.shipments.index', 'can' => 'orders.view'],
                ['label' => 'Đổi trả & Hoàn tiền', 'route' => 'admin.refunds.index', 'can' => 'refunds.manage'],
            ],
        ],
        [
            'label' => 'CRM & Marketing',
            'icon' => '👥',
            'can' => ['crm.view', 'vouchers.manage', 'marketing.manage'],
            'items' => [
                ['label' => 'Khách hàng', 'route' => 'admin.clients.index', 'can' => 'crm.view'], 
                ['label' => 'Voucher', 'route' => 'admin.vouchers.index', 'can' => 'vouchers.manage'],
                ['label' => 'Cài đặt chung', 'route' => 'admin.settings.index', 'can' => 'marketing.manage'], 
            ],
        ],
        [
            'label' => 'Hệ thống',
            'icon' => '⚙️',
            'can' => 'roles.manage',
            'items' => [
                ['label' => 'Tài khoản quản trị', 'route' => 'admin.admins.index'],
                ['label' => 'Nhóm quyền', 'route' => 'admin.roles.index'],
                ['label' => 'Phân quyền', 'route' => 'admin.permissions.index'],
            ],
        ],
    ];

    // ==============================================================
    // 2. LẤY MENU ĐỘNG TỪ DATABASE (CÓ FALLBACK CHỐNG LỖI)
    // ==============================================================
    $dynamicMenu = [];
    try {
        if (Schema::hasTable('admin_menus')) {
            $dbMenus = \App\Models\AdminMenu::where('is_active', true)->orderBy('order')->get()->groupBy('group_name');
            
            foreach ($dbMenus as $groupName => $items) {
                // Thu thập quyền của nhóm
                $groupPermissions = $items->pluck('permission')->filter()->unique()->toArray();
                
                // Chuẩn hóa item con thành Array giống hệt mảng tĩnh
                $dynamicItems = [];
                foreach ($items as $item) {
                    $dynamicItems[] = [
                        'label' => $item->title,
                        'route' => $item->route_name,
                        'can'   => $item->permission, 
                    ];
                }

                $dynamicMenu[] = [
                    'label' => $groupName,
                    'icon'  => $items->first()->icon ?? '📁',
                    'can'   => empty($groupPermissions) ? null : $groupPermissions,
                    'items' => $dynamicItems,
                ];
            }
        }
    } catch (\Exception $e) {
        // Fallback im lặng nếu sập Database hoặc chưa có bảng
    }

    // ==============================================================
    // 3. GỘP CẢ 2 NGUỒN LẠI THÀNH MỘT MẢNG DUY NHẤT
    // ==============================================================
    $mergedMenu = array_merge($staticMenu, $dynamicMenu);
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
        @foreach ($mergedMenu as $group)
            {{-- KIỂM TRA QUYỀN TRÊN LEVEL NHÓM (HỖ TRỢ MẢNG QUYỀN BẰNG CANANY) --}}
            @if(empty($group['can']) || auth()->user()->hasAnyPermission((array) $group['can']) || auth()->user()->hasRole('Super Admin'))
            
            @php
                // KIỂM TRA ACTIVE CHO DROPDOWN CỦA NHÓM NÀY
                $isGroupActive = collect($group['items'])->contains(function($item) {
                    if ($item['route'] === 'admin.dashboard') {
                        return request()->routeIs('admin.dashboard') || request()->path() === 'admin';
                    }
                    return Route::has($item['route']) && request()->routeIs($item['route'] . '*');
                });
            @endphp

            <div class="px-3 py-1" x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }">

                <button type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-2 py-2 rounded-xl
                               text-[11px] uppercase tracking-wider font-semibold
                               {{ $isGroupActive ? 'text-white/80' : 'text-white/40' }}
                               hover:text-white/70 transition-colors">
                    <span>{{ $group['icon'] }} {{ $group['label'] }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-3.5 h-3.5 transition-transform duration-200"
                         :class="open ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-1 space-y-0.5">
                    
                    @foreach ($group['items'] as $item)
                        {{-- KIỂM TRA QUYỀN TRÊN TỪNG ITEM CON --}}
                        @if(empty($item['can']) || auth()->user()->can($item['can']) || auth()->user()->hasRole('Super Admin'))
                            @php
                                $itemUrl = Route::has($item['route']) ? route($item['route']) : url('#');
                                if ($item['route'] === 'admin.dashboard') {
                                    $isActive = request()->routeIs('admin.dashboard') || request()->path() === 'admin';
                                } else {
                                    $isActive = Route::has($item['route']) && request()->routeIs($item['route'] . '*');
                                }
                            @endphp
                            <li>
                                <a href="{{ $itemUrl }}"
                                   class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-colors
                                          {{ $isActive ? 'bg-coral text-white font-semibold' : 'text-white/70 hover:bg-admin-sidebar-hover hover:text-white' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60 shrink-0"></span>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif
        @endforeach
    </nav>
</aside>