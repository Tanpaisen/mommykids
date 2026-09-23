@extends('admin.layouts.app')

@section('page_title', 'Quản lý khách hàng')
@section('page_subtitle', 'Danh sách khách hàng, hạng thành viên và tổng chi tiêu MommyKids')

@section('content')
<div class="space-y-5">
    <!-- Thống báo thành công -->
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200/80 rounded-xl text-emerald-800 text-sm shadow-sm">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Container -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        
        <!-- Bộ lọc nâng cao -->
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.customers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                
                <!-- Tìm kiếm từ khóa -->
                <div class="lg:col-span-4 relative">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" 
                           placeholder="Tìm theo tên, email, SĐT..." 
                           class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Lọc theo Hạng -->
                <div class="lg:col-span-2">
                    <select name="rank" onchange="this.form.submit()" 
                            class="w-full py-2 px-3 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-slate-700">
                        <option value="">Tất cả Hạng</option>
                        <option value="diamond" {{ request('rank') == 'diamond' ? 'selected' : '' }}>Kim Cương (≥10M)</option>
                        <option value="gold" {{ request('rank') == 'gold' ? 'selected' : '' }}>Vàng (5M - 10M)</option>
                        <option value="silver" {{ request('rank') == 'silver' ? 'selected' : '' }}>Bạc (2M - 5M)</option>
                        <option value="bronze" {{ request('rank') == 'bronze' ? 'selected' : '' }}>Đồng (<2M)</option>
                    </select>
                </div>

                <!-- Lọc theo Trạng thái -->
                <div class="lg:col-span-2">
                    <select name="status" onchange="this.form.submit()" 
                            class="w-full py-2 px-3 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-slate-700">
                        <option value="">Trạng thái TK</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đang bị khóa</option>
                    </select>
                </div>

                <!-- Lọc Mức chi tiêu -->
                <div class="lg:col-span-2">
                    <select name="min_spent" onchange="this.form.submit()" 
                            class="w-full py-2 px-3 text-sm bg-white border border-slate-200 rounded-lg focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition text-slate-700">
                        <option value="">Mức chi tiêu</option>
                        <option value="10000000" {{ request('min_spent') == '10000000' ? 'selected' : '' }}>Trên 10 triệu</option>
                        <option value="5000000" {{ request('min_spent') == '5000000' ? 'selected' : '' }}>Trên 5 triệu</option>
                        <option value="2000000" {{ request('min_spent') == '2000000' ? 'selected' : '' }}>Trên 2 triệu</option>
                    </select>
                </div>

                <!-- Nút Xóa lọc -->
                @if(request()->hasAny(['keyword', 'rank', 'status', 'min_spent']))
                    <div class="lg:col-span-2">
                        <a href="{{ route('admin.customers.index') }}" 
                           class="w-full flex items-center justify-center gap-1.5 py-2 px-3 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Xóa lọc</span>
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Bảng danh sách -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/80">
                        <th class="py-3.5 px-4 w-12 text-center">STT</th>
                        <th class="py-3.5 px-4">Khách hàng</th>
                        <th class="py-3.5 px-4">Liên hệ</th>
                        <th class="py-3.5 px-4 text-center">Số đơn</th>
                        <th class="py-3.5 px-4 text-right">Tổng chi tiêu</th>
                        <th class="py-3.5 px-4 text-center">Hạng</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4 text-center">Ngày ĐK</th>
                        <th class="py-3.5 px-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($customers as $index => $customer)
                        @php
                            // Tải mức chi tiêu & tính Rank
                            $spent = $customer->total_spent ?? 0;
                            if ($spent >= 10000000) {
                                $rankName = 'Kim Cương';
                                $rankBadge = 'bg-cyan-50 text-cyan-700 border-cyan-200/80';
                            } elseif ($spent >= 5000000) {
                                $rankName = 'Vàng';
                                $rankBadge = 'bg-amber-50 text-amber-700 border-amber-200/80';
                            } elseif ($spent >= 2000000) {
                                $rankName = 'Bạc';
                                $rankBadge = 'bg-slate-100 text-slate-700 border-slate-200/80';
                            } else {
                                $rankName = 'Đồng';
                                $rankBadge = 'bg-orange-50 text-orange-700 border-orange-200/80';
                            }

                            // Tạo Avatar Initials ngẫu nhiên màu nhã nhặn
                            $displayName = $customer->name ?? $customer->email ?? 'K';
                            $initial = mb_strtoupper(mb_substr($displayName, 0, 1, 'UTF-8'));
                            $colorIndex = abs(crc32($customer->id ?? $index)) % 5;
                            $avatarStyles = [
                                'bg-rose-100 text-rose-700',
                                'bg-indigo-100 text-indigo-700',
                                'bg-emerald-100 text-emerald-700',
                                'bg-amber-100 text-amber-700',
                                'bg-sky-100 text-sky-700'
                            ];
                            $avatarClass = $avatarStyles[$colorIndex];
                            
                            $stt = method_exists($customers, 'firstItem') ? ($customers->firstItem() + $index) : ($index + 1);
                            $isActive = $customer->is_active ?? true;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 text-center text-xs font-semibold text-slate-400">{{ $stt }}</td>
                            
                            <!-- Cột Khách hàng -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full {{ $avatarClass }} font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800">{{ $customer->name ?? 'Chưa cập nhật' }}</div>
                                        <div class="text-xs text-slate-400 font-mono mt-0.5">ID: #{{ $customer->id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Cột Liên hệ -->
                            <td class="py-3.5 px-4">
                                <div class="text-slate-700 font-medium text-xs">{{ $customer->email }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $customer->phone ?? 'Chưa có SĐT' }}</div>
                                @if(!empty($customer->address))
                                    <div class="text-[11px] text-slate-400 truncate max-w-[200px] mt-0.5" title="{{ $customer->address }}">
                                        {{ $customer->address }}
                                    </div>
                                @endif
                            </td>

                            <!-- Cột Số đơn -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/60">
                                    {{ $customer->orders_count ?? 0 }} đơn
                                </span>
                            </td>

                            <!-- Cột Tổng chi tiêu -->
                            <td class="py-3.5 px-4 text-right font-semibold text-slate-900">
                                {{ number_format($spent, 0, ',', '.') }}đ
                            </td>

                            <!-- Cột Hạng -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold border {{ $rankBadge }}">
                                    {{ $rankName }}
                                </span>
                            </td>

                            <!-- Cột Trạng thái -->
                            <td class="py-3.5 px-4 text-center">
                                @if($isActive)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Đã khóa
                                    </span>
                                @endif
                            </td>

                            <!-- Cột Ngày ĐK -->
                            <td class="py-3.5 px-4 text-center text-xs text-slate-500">
                                {{ $customer->created_at ? $customer->created_at->format('d/m/Y') : 'N/A' }}
                            </td>

                            <!-- Cột Hành động -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Xem chi tiết -->
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" 
                                       title="Xem chi tiết" 
                                       class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <!-- Khóa / Mở khóa -->
                                    <form action="{{ route('admin.customers.toggleStatus', $customer->id) }}" method="POST" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này?')" class="inline">
                                        @csrf
                                        @if($isActive)
                                            <button type="submit" title="Khóa tài khoản" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="submit" title="Mở khóa tài khoản" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-sm">Không tìm thấy khách hàng nào thỏa mãn điều kiện.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        @if(method_exists($customers, 'hasPages') && $customers->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                {{ $customers->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection