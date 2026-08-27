@extends('client.layouts.app')

@section('title', "Kết quả tìm kiếm: {$keyword} - MommyKids")

@section('content')
    <section class="card p-4 lg:p-6">
        <h1 class="font-display font-bold text-xl text-ink mb-5">
            Kết quả cho “{{ $keyword }}” <span class="text-ink-soft font-body text-sm">({{ $products->total() }} sản phẩm)</span>
        </h1>

        @if ($products->isEmpty())
            <p class="text-ink-soft text-sm py-10 text-center">Không tìm thấy sản phẩm phù hợp.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :product-id="$product['id']" />
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
