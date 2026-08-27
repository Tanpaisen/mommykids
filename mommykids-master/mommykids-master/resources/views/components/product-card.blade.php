{{--
    Usage:
    <x-product-card :product="$product" :product-id="$product['id']" />
    Expected $product shape (see App\Models\Product::toCardArray): name, image, price, old_price(optional), discount(optional), url
    productId is optional — when given, renders an "add to cart" button wired to POST /api/cart (see resources/js/app.js -> mkAddToCart).
--}}
@props(['product', 'productId' => null])

<div class="card group relative overflow-hidden hover:-translate-y-1 transition-transform duration-200">
    <a href="{{ $product['url'] ?? '#' }}" class="block">
        <div class="relative aspect-square bg-cream overflow-hidden">
            <img src="{{ $product['image'] ?? 'https://via.placeholder.com/300' }}"
                 alt="{{ $product['name'] }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
            @if (!empty($product['discount']))
                <span class="badge-discount absolute top-2 left-2">-{{ $product['discount'] }}%</span>
            @endif
        </div>
        <div class="p-3 pb-1">
            <p class="text-sm text-ink line-clamp-2 min-h-[2.5rem] group-hover:text-coral">{{ $product['name'] }}</p>
            <div class="mt-2 flex items-center gap-2">
                <span class="price-tag">{{ number_format($product['price'] ?? 0) }}đ</span>
                @if (!empty($product['old_price']))
                    <span class="text-xs text-ink-soft line-through">{{ number_format($product['old_price']) }}đ</span>
                @endif
            </div>
        </div>
    </a>

    @if ($productId)
        <button type="button"
                onclick="mkAddToCart({{ $productId }}, this)"
                class="absolute bottom-3 right-3 w-8 h-8 rounded-full bg-coral text-white flex items-center justify-center
                       shadow-pop hover:bg-coral-dark transition-colors"
                aria-label="Thêm vào giỏ hàng">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    @endif
</div>
