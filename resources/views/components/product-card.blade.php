{{--
    Usage:
    <x-product-card :product="$product" />
    Expected $product shape: name, image, price, old_price(optional), discount(optional), url
--}}
@props(['product'])

<a href="{{ $product['url'] ?? '#' }}" class="card group block overflow-hidden hover:-translate-y-1 transition-transform duration-200">
    <div class="relative aspect-square bg-cream overflow-hidden">
        <img src="{{ $product['image'] ?? 'https://via.placeholder.com/300' }}"
             alt="{{ $product['name'] }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
        @if (!empty($product['discount']))
            <span class="badge-discount absolute top-2 left-2">-{{ $product['discount'] }}%</span>
        @endif
    </div>
    <div class="p-3">
        <p class="text-sm text-ink line-clamp-2 min-h-[2.5rem] group-hover:text-coral">{{ $product['name'] }}</p>
        <div class="mt-2 flex items-center gap-2">
            <span class="price-tag">{{ number_format($product['price'] ?? 0) }}đ</span>
            @if (!empty($product['old_price']))
                <span class="text-xs text-ink-soft line-through">{{ number_format($product['old_price']) }}đ</span>
            @endif
        </div>
    </div>
</a>
