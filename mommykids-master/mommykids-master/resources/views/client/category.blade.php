@extends('client.layouts.app')

@section('title', $category->name . ' - MommyKids')

@section('content')
    <section class="card p-4 lg:p-6">
        <div class="flex items-center gap-2 mb-5">
            <span class="text-2xl">{{ $category->icon }}</span>
            <h1 class="font-display font-bold text-xl text-ink">{{ $category->name }}</h1>
        </div>

        @if ($products->isEmpty())
            <p class="text-ink-soft text-sm py-10 text-center">Chưa có sản phẩm trong danh mục này.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :product-id="$product['id']" />
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
