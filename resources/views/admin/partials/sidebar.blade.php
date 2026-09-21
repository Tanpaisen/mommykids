@php
    // Lấy danh sách menu động từ CSDL theo nhóm và thứ tự sắp xếp
    // Yêu cầu: Model AdminMenu cần có thêm cột 'permission' (VD: 'dashboard.view') và cột 'icon' (VD: '📊')
    $adminMenuGroups = \App\Models\AdminMenu::where('is_active', true)
                        ->orderBy('order')
                        ->get()
                        ->groupBy('group_name');
@endphp

<aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-admin-sidebar text-white -translate-x-full lg:translate-x-0 transition-transform duration-300 overflow-y-auto">
    <div class="flex items-center justify-between px-5 h-16 border-b border-white/10">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-blob bg-coral flex items-center justify-center font-display font-bold">M</span>
            <span class="font-display font-bold">MommyKids <span class="text-white/50 font-body font-normal text-xs">Admin</span></span>
        </a>
        <button id="admin-sidebar-close" class="lg:hidden text-white/70">✕</button>
    </div>

    <nav class="py-3">
        @foreach ($adminMenuGroups as $groupName => $items)
            @php
                // Kiểm tra xem nhóm này có item nào active không để mở sẵn menu
                $isGroupActive = $items->contains(function($item) {
                    $routeName = $item->route_name;
                    if ($routeName === 'admin.dashboard') {
                        return request()->routeIs('admin.dashboard') || request()->path() === 'admin';
                    }
                    return Route::has($routeName) && request()->routeIs($routeName . '*');
                });
                
                // Thu thập tất cả các quyền (permissions) của các items trong nhóm này
                // Để hiển thị Header Nhóm nếu user có ít nhất 1 quyền trong nhóm
                $groupPermissions = $items->pluck('permission')->filter()->unique()->toArray();
            @endphp

            {{-- Nếu không có quyền nào được cấu hình, mặc định cho phép hiển thị, ngược lại kiểm tra quyền --}}
            @if(empty($groupPermissions) || auth()->user()->hasAnyPermission($groupPermissions))
                <div class="px-3 py-1" x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }">
                    <button type="button"
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-xl
                                   text-[11px] uppercase tracking-wider font-semibold
                                   {{ $isGroupActive ? 'text-white/80' : 'text-white/40' }}
                                   hover:text-white/70 transition-colors">
                        {{-- Mặc định dùng icon thư mục nếu item đầu tiên không có icon --}}
                        <span>{{ $items->first()->icon ?? '📁' }} {{ $groupName }}</span>
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
                        
                        @foreach ($items as $item)
                            {{-- Kiểm tra quyền cho từng item cụ thể --}}
                            @if(empty($item->permission) || auth()->user()->hasPermissionTo($item->permission))
                                @php
                                    $routeName = $item->route_name;
                                    $itemUrl = Route::has($routeName) ? route($routeName) : url('#');

                                    if ($routeName === 'admin.dashboard') {
                                        $isActive = request()->routeIs('admin.dashboard') || request()->path() === 'admin';
                                    } else {
                                        $isActive = Route::has($routeName) && request()->routeIs($routeName . '*');
                                    }
                                @endphp
                                <li>
                                    <a href="{{ $itemUrl }}"
                                       class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm transition-colors
                                              {{ $isActive ? 'bg-coral text-white font-semibold' : 'text-white/70 hover:bg-admin-sidebar-hover hover:text-white' }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60 shrink-0"></span>
                                        {{ $item->title }}
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