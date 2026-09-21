{{-- MOMMYKIDS PRODUCT CARD --}}

@props([
    'product',
    'productId' => null,
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
    $price = max(0, (int) ($product['price'] ?? 0));

    $oldPrice = !empty($product['old_price'])
        ? (int) $product['old_price']
        : null;

    $discount = !empty($product['discount'])
        ? (int) $product['discount']
        : null;

    $rating = max(
        0,
        min(5, (float) ($product['rating'] ?? 0))
    );

    $reviewCount = max(
        0,
        (int) ($product['review_count'] ?? 0)
    );

    $filledStars = (int) round($rating);

    $soldCount = max(
        0,
        (int) ($product['sold_count'] ?? 0)
    );

    $url = $product['url'] ?? '#';
@endphp

<div
    class="card group relative overflow-hidden
           hover:-translate-y-1
           transition-transform duration-200"
>
    <a href="{{ $url }}" class="block">

        {{-- PRODUCT IMAGE --}}
        <div class="relative aspect-square bg-cream overflow-hidden">

            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $name }}"
                    loading="lazy"
                    decoding="async"
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

                <div
                    style="display:none;"
                    class="absolute inset-0
                           items-center justify-center
                           bg-cream text-center p-4"
                >
                    <div>
                        <div class="text-4xl">🖼️</div>

                        <p class="mt-2 text-xs text-ink-soft line-clamp-2">
                            Chưa có ảnh sản phẩm
                        </p>
                    </div>
                </div>
            @else
                <div
                    class="absolute inset-0
                           flex items-center justify-center
                           bg-cream text-center p-4"
                >
                    <div>
                        <div class="text-4xl">🖼️</div>

                        <p class="mt-2 text-xs text-ink-soft">
                            Chưa có ảnh sản phẩm
                        </p>
                    </div>
                </div>
            @endif

            {{-- DISCOUNT --}}
            @if ($discount)
                <span
                    class="badge-discount
                           absolute top-2 left-2 z-10"
                >
                    -{{ $discount }}%
                </span>
            @endif
        </div>

        {{-- PRODUCT INFO --}}
        <div class="p-3 pb-14">

            {{-- NAME --}}
            <p
                class="text-sm text-ink
                       line-clamp-2
                       min-h-[2.5rem]
                       group-hover:text-coral
                       transition-colors"
            >
                {{ $name }}
            </p>

            {{-- PRICE --}}
            <div class="mt-2 flex items-end gap-2 flex-wrap">

                <span class="price-tag">
                    {{ number_format($price, 0, ',', '.') }}đ
                </span>

                @if ($oldPrice && $oldPrice > $price)
                    <span class="text-xs text-ink-soft line-through">
                        {{ number_format($oldPrice, 0, ',', '.') }}đ
                    </span>
                @endif

            </div>

            {{-- REVIEW + SOLD COUNT: CÙNG MỘT HÀNG --}}
            <div
                class="mt-2
                       min-h-[1.25rem]
                       w-full
                       flex flex-nowrap
                       items-center
                       justify-between
                       gap-2"
            >
                {{-- REVIEW --}}
                <div
                    class="min-w-0
                           flex flex-nowrap
                           items-center
                           gap-1"
                >
                    @if ($reviewCount > 0)

                        <span
                            class="inline-flex
                                   shrink-0
                                   items-center
                                   gap-[1px]
                                   whitespace-nowrap"
                            aria-label="{{ number_format($rating, 1, ',', '.') }} trên 5 sao"
                        >
                            @for ($star = 1; $star <= 5; $star++)
                                <span
                                    class="text-[10px]
                                           sm:text-[11px]
                                           leading-none
                                           {{
                                               $star <= $filledStars
                                                   ? 'text-amber-400'
                                                   : 'text-gray-300'
                                           }}"
                                >
                                    ★
                                </span>
                            @endfor
                        </span>

                        <span
                            class="shrink-0
                                   text-[9px]
                                   sm:text-[10px]
                                   text-ink-soft
                                   whitespace-nowrap"
                        >
                            ({{ number_format($reviewCount, 0, ',', '.') }})
                        </span>

                    @else

                        <span
                            class="text-[9px]
                                   sm:text-[10px]
                                   text-ink-soft
                                   whitespace-nowrap"
                        >
                            Chưa có đánh giá
                        </span>

                    @endif
                </div>

                {{-- SOLD COUNT --}}
                @if ($soldCount > 0)
                    <div
                        class="shrink-0
                               inline-flex
                               items-center
                               gap-1
                               text-[9px]
                               sm:text-[10px]
                               font-medium
                               text-ink-soft
                               whitespace-nowrap"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            class="w-3 h-3 text-coral"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 6h2l2 9h10l2-6H6"
                            />
                            <circle cx="9" cy="19" r="1" />
                            <circle cx="17" cy="19" r="1" />
                        </svg>

                        <span>
                            Đã bán {{ number_format($soldCount, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </a>

    {{-- ADD TO CART --}}
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
                aria-hidden="true"
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
