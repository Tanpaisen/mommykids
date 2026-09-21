@extends('client.layouts.app')

@section('title', 'Sản phẩm nổi bật - MommyKids')

@section('content')

    {{-- ============ PAGE HEADER ============ --}}
    <section class="rounded-card overflow-hidden bg-gradient-to-r from-coral-light via-peach-light to-gold-light p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>
                <div class="flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-white shadow-soft flex items-center justify-center text-2xl">
                        ⭐
                    </span>

                    <div>
                        <h1 class="font-display font-extrabold text-2xl lg:text-3xl text-ink">
                            Sản phẩm nổi bật
                        </h1>

                        <p class="text-sm text-ink-soft mt-1">
                            Những sản phẩm được MommyKids lựa chọn dành cho ba mẹ
                        </p>
                    </div>
                </div>
            </div>

            <a
                href="{{ route('home') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-coral"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-4 h-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>

                Về trang chủ
            </a>

        </div>
    </section>

    {{-- ============ PRODUCT LIST ============ --}}
    <section class="card p-4 lg:p-6">

        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-display font-bold text-lg lg:text-xl text-ink">
                    Danh sách sản phẩm
                </h2>

                <p class="text-sm text-ink-soft mt-1">
                    Có {{ $products->total() }} sản phẩm nổi bật
                </p>
            </div>
        </div>

        @forelse ($products as $product)

            @if ($loop->first)
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @endif

                    <x-product-card
                        :product="$product"
                        :product-id="$product['id']"
                    />

            @if ($loop->last)
                </div>
            @endif

        @empty

            <div class="py-16 text-center">

                <div class="text-5xl mb-4">
                    ⭐
                </div>

                <h3 class="font-display font-bold text-lg text-ink">
                    Chưa có sản phẩm nổi bật
                </h3>

                <p class="text-sm text-ink-soft mt-2">
                    Hiện chưa có sản phẩm nào được đánh dấu là sản phẩm nổi bật.
                </p>

                <a
                    href="{{ route('home') }}"
                    class="btn-primary inline-flex mt-5"
                >
                    Về trang chủ
                </a>

            </div>

        @endforelse

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="mt-8 pt-6 border-t border-admin-border">
                {{ $products->links() }}
            </div>
        @endif

    </section>

@endsection