@extends('client.layouts.app')

@section('title', $product->name . ' - MommyKids')

@section('content')
    <section class="card p-4 lg:p-6 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="aspect-square bg-cream rounded-card overflow-hidden">
            <img src="{{ $product->image ?: 'https://via.placeholder.com/500' }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </div>
        <div>
            <a href="{{ route('category.show', $product->category->slug) }}" class="text-xs font-semibold text-coral">{{ $product->category->name }}</a>
            <h1 class="font-display font-bold text-2xl text-ink mt-2">{{ $product->name }}</h1>

            <div class="flex items-center gap-3 mt-4">
                <span class="price-tag text-2xl">{{ number_format($product->price) }}đ</span>
                @if ($product->old_price)
                    <span class="text-ink-soft line-through">{{ number_format($product->old_price) }}đ</span>
                    <span class="badge-discount">-{{ $product->discount_percent }}%</span>
                @endif
            </div>

            <button type="button" onclick="mkAddToCart({{ $product->id }}, this)" class="btn-primary mt-6">
                Thêm vào giỏ hàng
            </button>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="card p-4 lg:p-6">
            <h2 class="font-display font-bold text-lg text-ink mb-4">Sản phẩm liên quan</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($related as $item)
                    <x-product-card :product="$item" :product-id="$item['id']" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
