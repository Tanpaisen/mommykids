{{--
    Usage:
    <x-product-card
        :product="$product"
        :product-id="$product['id']"
    />

    Expected product shape:
    - id
    - name
    - image
    - price
    - old_price
    - discount
    - url

    productId optional:
    dùng cho nút thêm vào giỏ hàng.
--}}

@props([
    'product',
    'productId' => null
])

@php
    $image = $product['image'] ?? null;

    if ($image) {
        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://')
        ) {
            $imageUrl = $image;
        } else {
            $imageUrl = asset('storage/' . ltrim($image, '/'));
        }
    } else {
        $imageUrl = null;
    }

    $name = $product['name'] ?? 'Sản phẩm';

    $price = (int) ($product['price'] ?? 0);

    $oldPrice = !empty($product['old_price'])
        ? (int) $product['old_price']
        : null;

    $discount = !empty($product['discount'])
        ? (int) $product['discount']
        : null;

    $url = $product['url'] ?? '#';
@endphp


<div
    class="card group relative overflow-hidden
           hover:-translate-y-1
           transition-transform duration-200"
>

    <a
        href="{{ $url }}"
        class="block"
    >

        {{-- =====================================================
            PRODUCT IMAGE
        ====================================================== --}}
        <div
            class="relative aspect-square
                   bg-cream overflow-hidden"
        >

            @if ($imageUrl)

                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $name }}"
                    loading="lazy"
                    class="w-full h-full
                           object-contain
                           bg-white
                           p-3
                           group-hover:scale-105
                           transition-transform duration-300"
                    onerror="
                        this.style.display='none';
                        this.nextElementSibling.style.display='flex';
                    "
                >

                {{-- Fallback nếu ảnh lỗi --}}
                <div
                    style="display:none;"
                    class="absolute inset-0
                           items-center justify-center
                           bg-cream
                           text-center p-4"
                >
                    <div>

                        <div class="text-4xl">
                            🖼️
                        </div>

                        <p
                            class="mt-2
                                   text-xs text-ink-soft
                                   line-clamp-2"
                        >
                            Chưa có ảnh sản phẩm
                        </p>

                    </div>
                </div>

            @else

                {{-- Chưa có ảnh --}}
                <div
                    class="absolute inset-0
                           flex items-center justify-center
                           bg-cream
                           text-center p-4"
                >

                    <div>

                        <div class="text-4xl">
                            🖼️
                        </div>

                        <p
                            class="mt-2
                                   text-xs text-ink-soft"
                        >
                            Chưa có ảnh sản phẩm
                        </p>

                    </div>

                </div>

            @endif


            {{-- DISCOUNT --}}
            @if ($discount)

                <span
                    class="badge-discount
                           absolute top-2 left-2
                           z-10"
                >
                    -{{ $discount }}%
                </span>

            @endif

        </div>


        {{-- =====================================================
            PRODUCT INFO
        ====================================================== --}}
        <div class="p-3 pb-3">

            <p
                class="text-sm text-ink
                       line-clamp-2
                       min-h-[2.5rem]
                       group-hover:text-coral
                       transition-colors"
            >
                {{ $name }}
            </p>


            <div
                class="mt-2
                       flex items-end
                       gap-2
                       flex-wrap"
            >

                <span class="price-tag">
                    {{ number_format($price, 0, ',', '.') }}đ
                </span>


                @if ($oldPrice)

                    <span
                        class="text-xs
                               text-ink-soft
                               line-through"
                    >
                        {{ number_format($oldPrice, 0, ',', '.') }}đ
                    </span>

                @endif

            </div>

        </div>

    </a>


    {{-- =====================================================
        ADD TO CART
    ====================================================== --}}
    @if ($productId)

        <button
            type="button"
            onclick="mkAddToCart({{ $productId }}, this)"
            aria-label="Thêm {{ $name }} vào giỏ hàng"
            title="Thêm vào giỏ hàng"
            class="absolute
                   bottom-3 right-3
                   z-20
                   w-9 h-9
                   rounded-full
                   bg-coral text-white
                   flex items-center justify-center
                   shadow-pop
                   hover:bg-coral-dark
                   hover:scale-105
                   active:scale-95
                   transition"
        >

            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2.5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4v16m8-8H4"
                />
            </svg>

        </button>

    @endif

</div>