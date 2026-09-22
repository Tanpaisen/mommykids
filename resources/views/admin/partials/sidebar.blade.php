@php
    $menu = [
        [
            'label' => 'Dashboard & Thống kê',
            'icon' => '📊',
            'can' => 'dashboard.view',
            'items' => [
                [
                    'label' => 'Tổng quan',
                    'route' => 'admin.dashboard',
                    'active' => 'admin.dashboard',
                ],
            ],
        ],

        [
            'label' => 'Kiến thức & Sản phẩm',
            'icon' => '📦',
            'can' => [
                'catalog.manage',
                'products.manage',
            ],
            'items' => [
                [
                    'label' => 'Giai đoạn của bé',
                    'route' => 'admin.stages.index',
                    'active' => 'admin.stages.*',
                    'can' => 'catalog.manage',
                ],
                [
                    'label' => 'Danh mục & Thuộc tính',
                    'route' => 'admin.categories.index',
                    'active' => 'admin.categories.*',
                    'can' => 'catalog.manage',
                ],
                [
                    'label' => 'Sản phẩm',
                    'route' => 'admin.products.index',
                    'active' => 'admin.products.*',
                    'can' => 'products.manage',
                ],
                [
                    'label' => 'Đánh giá sản phẩm',
                    'route' => 'admin.reviews.index',
                    'active' => 'admin.reviews.*',
                    'can' => 'products.manage',
                ],
            ],
        ],

        [
            'label' => 'Cẩm nang & Tương tác',
            'icon' => '📚',
            'can' => 'handbook.view',
            'items' => [
                [
                    'label' => 'Bài viết Cẩm nang',
                    'route' => 'admin.articles.index',
                    'active' => 'admin.articles.*',
                ],
                [
                    'label' => 'Trung tâm Hỏi đáp',
                    'route' => 'admin.comments.index',
                    'active' => 'admin.comments.*',
                ],
            ],
        ],

        [
            'label' => 'Đơn hàng & Dòng tiền',
            'icon' => '🚚',
            'can' => [
                'orders.view',
                'refunds.manage',
            ],
            'items' => [
                [
                    'label' => 'Đơn hàng',
                    'route' => 'admin.orders.index',
                    'active' => 'admin.orders.*',
                    'can' => 'orders.view',
                ],
                [
                    'label' => 'Vận chuyển (GHN)',
                    'route' => 'admin.shipments.index',
                    'active' => 'admin.shipments.*',
                    'can' => 'orders.view',
                ],
                [
                    'label' => 'Đổi trả & Hoàn tiền',
                    'route' => 'admin.refunds.index',
                    'active' => 'admin.refunds.*',
                    'can' => 'refunds.manage',
                ],
            ],
        ],

        [
            'label' => 'CRM & Marketing',
            'icon' => '👥',
            'can' => [
                'crm.view',
                'vouchers.manage',
                'marketing.manage',
            ],
            'items' => [
                [
                    'label' => 'Khách hàng',
                    'route' => 'admin.clients.index',
                    'active' => 'admin.clients.*',
                    'can' => 'crm.view',
                ],
                [
                    'label' => 'Voucher',
                    'route' => 'admin.vouchers.index',
                    'active' => 'admin.vouchers.*',
                    'can' => 'vouchers.manage',
                ],

                // Campaign hiện đang dùng chung quyền vouchers.manage
                [
                    'label' => 'Chiến dịch',
                    'route' => 'admin.campaigns.index',
                    'active' => 'admin.campaigns.*',
                    'can' => 'vouchers.manage',
                ],

                [
                    'label' => 'Banner',
                    'route' => 'admin.banners.index',
                    'active' => 'admin.banners.*',
                    'can' => 'marketing.manage',
                ],
            ],
        ],

        [
            'label' => 'Hệ thống',
            'icon' => '⚙️',
            'can' => 'roles.manage',
            'items' => [
                [
                    'label' => 'Tài khoản quản trị',
                    'route' => 'admin.admins.index',
                    'active' => 'admin.admins.*',
                ],
                [
                    'label' => 'Nhóm quyền',
                    'route' => 'admin.roles.index',
                    'active' => 'admin.roles.*',
                ],
                [
                    'label' => 'Phân quyền',
                    'route' => 'admin.permissions.index',
                    'active' => 'admin.permissions.*',
                ],
            ],
        ],
    ];
@endphp

<aside
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-admin-sidebar text-white
           -translate-x-full lg:translate-x-0 transition-transform duration-300
           overflow-y-auto"
>
    <div class="flex items-center justify-between px-5 h-16 border-b border-white/10">
        <a
            href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-2"
        >
            <span
                class="w-8 h-8 rounded-blob bg-coral flex items-center justify-center
                       font-display font-bold"
            >
                M
            </span>

            <span class="font-display font-bold">
                MommyKids
                <span class="text-white/50 font-body font-normal text-xs">
                    Admin
                </span>
            </span>
        </a>

        <button
            id="admin-sidebar-close"
            type="button"
            class="lg:hidden text-white/70 hover:text-white"
            aria-label="Đóng menu"
        >
            ✕
        </button>
    </div>

    <nav class="py-3">

        @foreach ($menu as $group)

            @canany((array) $group['can'])

                @php
                    $isGroupActive = collect($group['items'])
                        ->contains(function ($item) {
                            $activePattern = $item['active']
                                ?? $item['route'];

                            return request()->routeIs($activePattern);
                        });
                @endphp

                <div
                    class="px-3 py-1"
                    x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-2 py-2 rounded-xl
                               text-[11px] uppercase tracking-wider font-semibold
                               transition-colors
                               {{ $isGroupActive
                                    ? 'text-white/80'
                                    : 'text-white/40'
                               }}
                               hover:text-white/70"
                    >
                        <span>
                            {{ $group['icon'] }}
                            {{ $group['label'] }}
                        </span>

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-3.5 h-3.5 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </button>

                    <ul
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mt-1 space-y-0.5"
                    >
                        @foreach ($group['items'] as $item)

                            @canany(
                                (array) (
                                    $item['can']
                                    ?? $group['can']
                                )
                            )

                                @php
                                    $activePattern = $item['active']
                                        ?? $item['route'];

                                    $isItemActive = request()->routeIs(
                                        $activePattern
                                    );

                                    $routeExists = Route::has(
                                        $item['route']
                                    );
                                @endphp

                                <li>
                                    <a
                                        href="{{ $routeExists
                                            ? route($item['route'])
                                            : '#'
                                        }}"
                                        class="flex items-center gap-2 px-3 py-2 rounded-xl
                                               text-sm transition-colors
                                               {{ $isItemActive
                                                    ? 'bg-coral text-white font-semibold'
                                                    : 'text-white/70 hover:bg-admin-sidebar-hover hover:text-white'
                                               }}"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-current
                                                   opacity-60 shrink-0"
                                        ></span>

                                        <span>
                                            {{ $item['label'] }}
                                        </span>
                                    </a>
                                </li>

                            @endcanany

                        @endforeach
                    </ul>
                </div>

            @endcanany

        @endforeach

    </nav>
</aside>