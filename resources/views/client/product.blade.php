@extends('client.layouts.app')

@section('title', $product->name . ' - MommyKids')

@section('content')

@php
    $image = $product->image;

    if ($image) {
        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://')
        ) {
            $imageUrl = $image;
        } else {
            $imageUrl = asset(
                'storage/' . ltrim($image, '/')
            );
        }
    } else {
        $imageUrl = null;
    }
@endphp


<section
    class="card p-4 lg:p-6
           grid grid-cols-1 lg:grid-cols-2
           gap-8"
>

    {{-- =====================================================
        PRODUCT IMAGE
    ====================================================== --}}
    <div
        class="relative aspect-square
               bg-cream rounded-card
               overflow-hidden"
    >

        @if ($imageUrl)

            <img
                src="{{ $imageUrl }}"
                alt="{{ $product->name }}"
                class="w-full h-full
                       object-contain
                       bg-white
                       p-4"
                onerror="
                    this.style.display='none';
                    this.nextElementSibling.style.display='flex';
                "
            >

            {{-- Fallback khi ảnh lỗi --}}
            <div
                style="display:none;"
                class="absolute inset-0
                       items-center justify-center
                       bg-cream
                       text-center p-6"
            >

                <div>

                    <div class="text-5xl">
                        🖼️
                    </div>

                    <p
                        class="mt-3
                               text-sm text-ink-soft"
                    >
                        Không thể tải ảnh sản phẩm
                    </p>

                </div>

            </div>

        @else

            {{-- Chưa có ảnh --}}
            <div
                class="absolute inset-0
                       flex items-center justify-center
                       bg-cream
                       text-center p-6"
            >

                <div>

                    <div class="text-5xl">
                        🖼️
                    </div>

                    <p
                        class="mt-3
                               text-sm text-ink-soft"
                    >
                        Chưa có ảnh sản phẩm
                    </p>

                </div>

            </div>

        @endif

    </div>


    {{-- =====================================================
        PRODUCT INFO
    ====================================================== --}}
    <div>

        @if ($product->category)

            <a
                href="{{ route(
                    'category.show',
                    $product->category->slug
                ) }}"
                class="text-xs
                       font-semibold
                       text-coral
                       hover:underline"
            >
                {{ $product->category->name }}
            </a>

        @endif


        <h1
            class="font-display
                   font-bold
                   text-2xl
                   text-ink
                   mt-2"
        >
            {{ $product->name }}
        </h1>


        {{-- PRICE --}}
        <div
            class="flex items-center
                   gap-3 mt-4 flex-wrap"
        >

            <span
                class="price-tag text-2xl"
            >
                {{ number_format(
                    $product->price,
                    0,
                    ',',
                    '.'
                ) }}đ
            </span>


            @if ($product->old_price)

                <span
                    class="text-ink-soft
                           line-through"
                >
                    {{ number_format(
                        $product->old_price,
                        0,
                        ',',
                        '.'
                    ) }}đ
                </span>


                @if ($product->discount_percent)

                    <span
                        class="badge-discount"
                    >
                        -{{ $product->discount_percent }}%
                    </span>

                @endif

            @endif

        </div>


        {{-- DESCRIPTION --}}
        @if ($product->description)

            <div
                class="mt-5
                       text-sm
                       text-ink-soft
                       leading-6"
            >
                {{ $product->description }}
            </div>

        @endif


        {{-- STOCK --}}
        <div class="mt-5">

            @if ($product->stock > 0)

                <span
                    class="inline-flex
                           items-center
                           gap-2
                           rounded-full
                           bg-green-50
                           px-3 py-1.5
                           text-sm
                           font-medium
                           text-green-600"
                >

                    <span
                        class="w-2 h-2
                               rounded-full
                               bg-green-500"
                    ></span>

                    Còn hàng:
                    {{ $product->stock }}

                </span>

            @else

                <span
                    class="inline-flex
                           items-center
                           gap-2
                           rounded-full
                           bg-red-50
                           px-3 py-1.5
                           text-sm
                           font-medium
                           text-red-500"
                >

                    <span
                        class="w-2 h-2
                               rounded-full
                               bg-red-500"
                    ></span>

                    Hết hàng

                </span>

            @endif

        </div>


        {{-- ADD TO CART --}}
        @if ($product->stock > 0)

            <button
                type="button"
                onclick="mkAddToCart(
                    {{ $product->id }},
                    this
                )"
                class="btn-primary mt-6"
            >
                Thêm vào giỏ hàng
            </button>

        @else

            <button
                type="button"
                disabled
                class="mt-6
                       px-5 py-3
                       rounded-xl
                       bg-gray-200
                       text-gray-500
                       cursor-not-allowed
                       font-semibold"
            >
                Hết hàng
            </button>

        @endif

    </div>

</section>


{{-- =====================================================
    RELATED PRODUCTS
====================================================== --}}
@if ($related->isNotEmpty())

    <section
        class="card p-4 lg:p-6 mt-6"
    >

        <h2
            class="font-display
                   font-bold
                   text-lg
                   text-ink
                   mb-4"
        >
            Sản phẩm liên quan
        </h2>


        <div
            class="grid grid-cols-2
                   sm:grid-cols-3
                   lg:grid-cols-6
                   gap-4"
        >

            @foreach ($related as $item)

                <x-product-card
                    :product="$item"
                    :product-id="$item['id']"
                />

            @endforeach

        </div>

    </section>

@endif

@endsection