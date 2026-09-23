@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Tổng quan')
@section('page_subtitle', 'Số liệu hôm nay & cảnh báo vận hành')

@section('content')

    {{-- ============ STAT CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- ========== THẺ DOANH THU HÔM NAY ========== --}}
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Doanh thu thực tế (Hôm nay)</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">
                {{ number_format($revenue['today']) }}đ
            </p>

            <div class="mt-3 pt-3 border-t border-admin-border space-y-1">
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Tiền hàng (trước giảm):</span>
                    <span class="font-medium text-ink">{{ number_format($revenue['today_subtotal']) }}đ</span>
                </div>
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Đã khuyến mãi:</span>
                    <span class="font-medium text-red-500">-{{ number_format($revenue['today_discount']) }}đ</span>
                </div>
                <div class="flex justify-between text-xs text-ink-soft mt-1 pt-1 border-t border-dashed">
                    <span>Thực nhận:</span>
                    <span class="font-bold text-green-600">{{ number_format($revenue['today']) }}đ</span>
                </div>
            </div>
        </div>

        {{-- ========== THẺ DOANH THU TUẦN NÀY ========== --}}
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Doanh thu thực tế (Tuần này)</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">
                {{ number_format($revenue['week']) }}đ
            </p>

            <div class="mt-3 pt-3 border-t border-admin-border space-y-1">
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Tiền hàng (trước giảm):</span>
                    <span class="font-medium text-ink">{{ number_format($revenue['week_subtotal']) }}đ</span>
                </div>
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Đã khuyến mãi:</span>
                    <span class="font-medium text-red-500">-{{ number_format($revenue['week_discount']) }}đ</span>
                </div>
                <div class="flex justify-between text-xs text-ink-soft mt-1 pt-1 border-t border-dashed">
                    <span>Thực nhận:</span>
                    <span class="font-bold text-green-600">{{ number_format($revenue['week']) }}đ</span>
                </div>
            </div>
        </div>
        {{-- Card Doanh thu tháng này --}}
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Doanh thu tháng này</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">{{ number_format($revenue['month']) }}đ</p>
            
            <div class="mt-3 pt-3 border-t border-admin-border space-y-1">
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Tiền hàng:</span>
                    <span class="font-medium text-ink">{{ number_format($revenue['month_subtotal']) }}đ</span>
                </div>
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Khuyến mãi:</span>
                    <span class="font-medium text-coral">-{{ number_format($revenue['month_discount']) }}đ</span>
                </div>
            </div>
        </div>
        {{-- Card Tổng doanh thu toàn hệ thống --}}
        <div class="card p-5 bg-gradient-to-br from-admin-bg to-white">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Tổng doanh thu (All-time)</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">{{ number_format($revenue['all_time']) }}đ</p>
            
            <div class="mt-3 pt-3 border-t border-admin-border space-y-1">
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Tiền hàng:</span>
                    <span class="font-medium text-ink">{{ number_format($revenue['all_time_subtotal']) }}đ</span>
                </div>
                <div class="flex justify-between text-xs text-ink-soft">
                    <span>Khuyến mãi:</span>
                    <span class="font-medium text-coral">-{{ number_format($revenue['all_time_discount']) }}đ</span>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Đơn hàng hôm nay</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">{{ $orders['today'] }}</p>
            <p class="text-xs text-ink-soft mt-1">{{ $orders['week'] }} đơn trong tuần</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Bình luận chưa trả lời</p>
            <p class="font-display font-bold text-2xl text-coral mt-1">{{ $pendingComments->count() }}</p>
            @can('handbook.view')
                <a href="{{ route('admin.comments.index') }}" class="text-xs text-coral font-semibold hover:underline">Xử lý ngay →</a>
            @endcan
        </div>
    </div>

    {{-- ============ REVENUE BY PAYMENT METHOD ============ --}}
    <div class="card p-5 lg:p-6">
        <h2 class="font-display font-bold text-ink mb-4">Doanh thu theo hình thức thanh toán (hôm nay)</h2>
        <div class="space-y-4">
            @php
                $methods = [
                    ['name' => 'COD (Tiền mặt)', 'key' => 'today_cod', 'color' => 'bg-gray-500'],
                    ['name' => 'QR Chuyển khoản', 'key' => 'today_qr', 'color' => 'bg-blue-500'],
                    ['name' => 'VNPay', 'key' => 'today_vnpay', 'color' => 'bg-mint'],
                    ['name' => 'ZaloPay', 'key' => 'today_zalopay', 'color' => 'bg-blue-400'],
                    ['name' => 'Chuyển khoản (Bank)', 'key' => 'today_bank', 'color' => 'bg-indigo-500'],
                    ['name' => 'PayPal', 'key' => 'today_paypal', 'color' => 'bg-yellow-500'],
                    ['name' => 'Stripe', 'key' => 'today_stripe', 'color' => 'bg-purple-500'],
                ];
            @endphp

            @foreach ($methods as $method)
                @if (($revenue[$method['key']] ?? 0) > 0)
                    @php
                        $percent = $revenue['today'] > 0 ? round(($revenue[$method['key']] / $revenue['today']) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-ink">{{ $method['name'] }}</span>
                            <span class="text-ink-soft">{{ number_format($revenue[$method['key']]) }}đ ({{ $percent }}%)</span>
                        </div>
                        <div class="h-2.5 rounded-pill bg-admin-bg overflow-hidden">
                            <div class="h-full {{ $method['color'] }} rounded-pill" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($revenue['today'] == 0)
                <p class="text-sm text-ink-soft text-center py-4 italic">Chưa có doanh thu trong hôm nay.</p>
            @endif
        </div>
    </div>

    {{-- ============ OPERATIONAL ALERTS ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Low stock (red) --}}
        <div class="card p-5 lg:p-6">
            <h2 class="font-display font-bold text-ink mb-3 flex items-center gap-2">
                <span class="text-coral">⚠️</span> Sản phẩm sắp hết hàng
            </h2>
            @if ($lowStockProducts->isEmpty())
                <p class="text-sm text-ink-soft py-6 text-center">Tồn kho ổn định. Không có sản phẩm nào chạm mức cảnh báo.</p>
            @else
                <ul class="divide-y divide-admin-border text-sm">
                    @foreach ($lowStockProducts as $p)
                        <li class="py-2.5 flex items-center justify-between">
                            <span class="text-ink truncate pr-4">{{ $p->name }}</span>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-ink-soft text-xs">Cảnh báo: {{ $p->low_stock_alert }}</span>
                                <span class="text-coral font-semibold bg-coral-light px-2.5 py-0.5 rounded-pill text-xs">
                                    Còn {{ $p->stock }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Pending comments / questions --}}
        <div class="card p-5 lg:p-6">
            <h2 class="font-display font-bold text-ink mb-3 flex items-center gap-2">
                💬 Câu hỏi / bình luận chưa trả lời
            </h2>
            <ul class="divide-y divide-admin-border">
                @foreach ($pendingComments as $c)
                    <li class="py-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-coral">{{ $c['type'] }} · {{ $c['target'] }}</span>
                            <span class="text-xs text-ink-soft">{{ $c['minutes_ago'] }} phút trước</span>
                        </div>
                        <p class="text-sm text-ink mt-0.5"><span class="font-medium">{{ $c['author'] }}:</span> {{ $c['excerpt'] }}</p>
                        <a href="{{ route('admin.comments.index') }}" class="text-xs text-coral font-semibold hover:underline">Trả lời →</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ============ TOP LISTS ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="card p-5 lg:p-6">
            <h2 class="font-display font-bold text-ink mb-3">📚 Top bài viết Cẩm nang đọc nhiều nhất</h2>
            <ol class="space-y-2.5">
                @foreach ($topArticles as $i => $article)
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-coral-light text-coral text-xs font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                        <span class="flex-1 text-sm text-ink">{{ $article['title'] }}</span>
                        <span class="text-xs text-ink-soft shrink-0">{{ number_format($article['views']) }} lượt đọc</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="card p-5 lg:p-6">
            <h2 class="font-display font-bold text-ink mb-3">📦 Top sản phẩm bán chạy nhất</h2>
            <ol class="space-y-2.5">
                @foreach ($topProducts as $i => $product)
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-mint-light text-mint text-xs font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                        <span class="flex-1 text-sm text-ink">{{ $product['name'] }}</span>
                        <span class="text-xs text-ink-soft shrink-0">Đã bán {{ $product['sold'] }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

@endsection
