@extends('client.layouts.app')

@section('title', 'Giỏ hàng - MommyKids')

@section('content')
    <section class="card p-4 lg:p-6">
        <h1 class="font-display font-bold text-xl text-ink mb-5">Giỏ hàng của bạn</h1>

        @if ($items->isEmpty())
            <p class="text-ink-soft text-sm py-10 text-center">Giỏ hàng đang trống.</p>
        @else
            <div class="divide-y divide-cream" id="mk-cart-list">
                @foreach ($items as $item)
                    <div class="flex items-center gap-4 py-4" data-cart-item="{{ $item->id }}">
                        <img src="{{ $item->product->image ?: 'https://via.placeholder.com/80' }}" class="w-16 h-16 rounded-xl object-cover bg-cream" alt="{{ $item->product->name }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-ink truncate">{{ $item->product->name }}</p>
                            <p class="price-tag text-sm mt-1">{{ number_format($item->product->price) }}đ</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="mkUpdateCartItem({{ $item->id }}, {{ $item->quantity - 1 }})" class="w-7 h-7 rounded-full border border-coral-light hover:border-coral">−</button>
                            <span class="w-6 text-center text-sm" id="mk-qty-{{ $item->id }}">{{ $item->quantity }}</span>
                            <button onclick="mkUpdateCartItem({{ $item->id }}, {{ $item->quantity + 1 }})" class="w-7 h-7 rounded-full border border-coral-light hover:border-coral">+</button>
                        </div>
                        <button onclick="mkUpdateCartItem({{ $item->id }}, 0)" class="text-ink-soft hover:text-coral text-xs">Xóa</button>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-coral-light">
                <span class="text-ink-soft">Tổng cộng</span>
                <span class="font-display font-bold text-xl text-coral" id="mk-cart-total">{{ number_format($total) }}đ</span>
            </div>
            <a href="{{ route('checkout.index') }}" class="btn-primary w-full mt-4">Tiến hành thanh toán</a>
        @endif
    </section>

    @push('scripts')
    <script>
        async function mkUpdateCartItem(cartItemId, quantity) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch(`/api/cart/${cartItemId}`, {
                method: quantity <= 0 ? 'DELETE' : 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken ?? '' },
                body: quantity <= 0 ? null : JSON.stringify({ quantity }),
            });
            if (!res.ok) return;
            const data = await res.json();
            document.querySelectorAll('.mk-cart-count').forEach(el => el.textContent = data.cart_count);
            if (quantity <= 0) {
                document.querySelector(`[data-cart-item="${cartItemId}"]`)?.remove();
            } else {
                document.getElementById(`mk-qty-${cartItemId}`).textContent = quantity;
            }
        }
    </script>
    @endpush
@endsection
