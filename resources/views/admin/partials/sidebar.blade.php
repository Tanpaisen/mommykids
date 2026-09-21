@php
    // Lấy danh sách menu động từ CSDL theo nhóm và thứ tự sắp xếp
    $adminMenuGroups = \App\Models\AdminMenu::where('is_active', true)
                        ->orderBy('order')
                        ->get()
                        ->groupBy('group_name');
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
        @foreach ($adminMenuGroups as $groupName => $items)
            <div class="px-3 py-2">
                <p class="px-2 text-[11px] uppercase tracking-wider text-white/40 font-semibold mb-1 flex items-center gap-1.5">
                    <span>📁</span> {{ $groupName }}
                </p>
                <ul>
                    @foreach ($items as $item)
                        @php
                            $routeName = $item->route_name;
                            $itemUrl = Route::has($routeName) ? route($routeName) : url('#');

                            // Kiểm tra trạng thái Active của trang hiện tại
                            if ($routeName === 'admin.dashboard') {
                                $isActive = request()->routeIs('admin.dashboard') || request()->path() === 'admin';
                            } else {
                                $isActive = Route::has($routeName) && request()->routeIs($routeName . '*');
                            }
                        @endphp
                        <li>
                            <a href="{{ $itemUrl }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm {{ $isActive ? 'bg-coral text-white font-semibold' : 'text-white/75 hover:bg-admin-sidebar-hover hover:text-white' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
                                {{ $item->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>