@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Tổng quan')
@section('page_subtitle', 'Số liệu hôm nay & cảnh báo vận hành')

@section('content')

    {{-- ============ STAT CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Doanh thu hôm nay</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">{{ number_format($revenue['today']) }}đ</p>
            <p class="text-xs text-ink-soft mt-1">QR: {{ number_format($revenue['today_qr']) }}đ · VNPay: {{ number_format($revenue['today_vnpay']) }}đ</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Doanh thu tuần này</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">{{ number_format($revenue['week']) }}đ</p>
            <p class="text-xs text-ink-soft mt-1">QR: {{ number_format($revenue['week_qr']) }}đ · VNPay: {{ number_format($revenue['week_vnpay']) }}đ</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Đơn hàng hôm nay</p>
            <p class="font-display font-bold text-2xl text-ink mt-1">{{ $orders['today'] }}</p>
            <p class="text-xs text-ink-soft mt-1">{{ $orders['week'] }} đơn trong tuần</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-ink-soft uppercase tracking-wide">Bình luận chưa trả lời</p>
            <p class="font-display font-bold text-2xl text-coral mt-1">{{ $pendingComments->count() }}</p>
            <a href="{{ route('admin.comments.index') }}" class="text-xs text-coral font-semibold hover:underline">Xử lý ngay →</a>
        </div>
    </div>

    {{-- ============ REVENUE BY PAYMENT METHOD ============ --}}
    <div class="card p-5 lg:p-6">
        <h2 class="font-display font-bold text-ink mb-4">Doanh thu theo hình thức thanh toán (hôm nay)</h2>
        @php
            $qrPercent = $revenue['today'] > 0 ? round($revenue['today_qr'] / $revenue['today'] * 100) : 0;
            $vnpayPercent = 100 - $qrPercent;
        @endphp
        <div class="space-y-3">
            <div>
                <div class="flex justify-between text-sm mb-1"><span class="font-medium text-ink">QR Chuyển khoản</span><span class="text-ink-soft">{{ number_format($revenue['today_qr']) }}đ ({{ $qrPercent }}%)</span></div>
                <div class="h-2.5 rounded-pill bg-admin-bg overflow-hidden"><div class="h-full bg-coral rounded-pill" style="width: {{ $qrPercent }}%"></div></div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1"><span class="font-medium text-ink">VNPay</span><span class="text-ink-soft">{{ number_format($revenue['today_vnpay']) }}đ ({{ $vnpayPercent }}%)</span></div>
                <div class="h-2.5 rounded-pill bg-admin-bg overflow-hidden"><div class="h-full bg-mint rounded-pill" style="width: {{ $vnpayPercent }}%"></div></div>
            </div>
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
                <p class="text-sm text-ink-soft py-6 text-center">Không có sản phẩm nào sắp hết hàng.</p>
            @else
                <ul class="divide-y divide-admin-border text-sm">
                    @foreach ($lowStockProducts as $p)
                        <li class="py-2.5 flex items-center justify-between">
                            <span class="text-ink">{{ $p->name }}</span>
                            <span class="text-coral font-semibold bg-coral-light px-2.5 py-0.5 rounded-pill text-xs">Còn {{ $p->stock }}</span>
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
