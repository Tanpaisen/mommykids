@extends('client.layouts.app')

@section('title', 'Giỏ hàng - MommyKids')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Helper xử lý ảnh
    |--------------------------------------------------------------------------
    |
    | Cloudinary / URL ngoài:
    | https://...
    |
    | Ảnh local cũ:
    | products/main/abc.jpg
    |
    */
    $resolveImage = function (?string $image) {
        if (!$image) {
            return null;
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://')
        ) {
            return $image;
        }

        return asset(
            'storage/' . ltrim($image, '/')
        );
    };
@endphp


<section class="card p-4 lg:p-6">

    <h1
        class="font-display
               font-bold
               text-xl
               text-ink
               mb-5"
    >
        Giỏ hàng của bạn
    </h1>


    @if ($items->isEmpty())

        <div
            class="py-14
                   text-center"
        >

            <div class="text-5xl mb-3">
                🛒
            </div>

            <p
                class="text-ink
                       font-semibold"
            >
                Giỏ hàng đang trống
            </p>

            <p
                class="text-ink-soft
                       text-sm mt-1"
            >
                Hãy thêm sản phẩm để tiếp tục mua sắm.
            </p>

            <a
                href="{{ route('home') }}"
                class="btn-primary inline-flex mt-5"
            >
                Tiếp tục mua hàng
            </a>

        </div>

    @else

        <div
            class="divide-y divide-cream"
            id="mk-cart-list"
        >

            @foreach ($items as $item)

                @php
                    $product = $item->product;

                    /*
                     * Trường hợp product đã bị xóa mềm / không còn tồn tại.
                     */
                    $imageUrl = $product
                        ? $resolveImage($product->image)
                        : null;
                @endphp


                @if ($product)

                    <div
                        class="flex items-center
                               gap-4 py-4"
                        data-cart-item="{{ $item->id }}"
                    >

                        {{-- IMAGE --}}
                        <div
                            class="relative
                                   w-20 h-20
                                   shrink-0
                                   rounded-xl
                                   overflow-hidden
                                   bg-cream"
                        >

                            @if ($imageUrl)

                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full
                                           object-contain
                                           bg-white
                                           p-1"
                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling.style.display='flex';
                                    "
                                >

                                <div
                                    style="display:none;"
                                    class="absolute inset-0
                                           items-center
                                           justify-center
                                           bg-cream
                                           text-xl"
                                >
                                    🖼️
                                </div>

                            @else

                                <div
                                    class="absolute inset-0
                                           flex items-center
                                           justify-center
                                           bg-cream
                                           text-xl"
                                >
                                    🖼️
                                </div>

                            @endif

                        </div>


                        {{-- PRODUCT INFO --}}
                        <div
                            class="flex-1
                                   min-w-0"
                        >

                            <a
                                href="{{ route(
                                    'product.show',
                                    $product->slug
                                ) }}"
                                class="text-sm
                                       font-medium
                                       text-ink
                                       hover:text-coral
                                       line-clamp-2"
                            >
                                {{ $product->name }}
                            </a>

                            <p
                                class="price-tag
                                       text-sm
                                       mt-1"
                            >
                                {{ number_format(
                                    $product->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}đ
                            </p>

                        </div>


                        {{-- QUANTITY --}}
                        <div
                            class="flex
                                   items-center
                                   gap-2"
                        >

                            <button
                                type="button"
                                onclick="mkUpdateCartItem(
                                    {{ $item->id }},
                                    {{ $item->quantity - 1 }}
                                )"
                                class="w-8 h-8
                                       rounded-full
                                       border border-coral-light
                                       hover:border-coral
                                       hover:text-coral
                                       transition"
                            >
                                −
                            </button>


                            <span
                                class="w-7
                                       text-center
                                       text-sm"
                                id="mk-qty-{{ $item->id }}"
                            >
                                {{ $item->quantity }}
                            </span>


                            <button
                                type="button"
                                onclick="mkUpdateCartItem(
                                    {{ $item->id }},
                                    {{ $item->quantity + 1 }}
                                )"
                                class="w-8 h-8
                                       rounded-full
                                       border border-coral-light
                                       hover:border-coral
                                       hover:text-coral
                                       transition"
                            >
                                +
                            </button>

                        </div>


                        {{-- REMOVE --}}
                        <button
                            type="button"
                            onclick="mkUpdateCartItem(
                                {{ $item->id }},
                                0
                            )"
                            class="text-ink-soft
                                   hover:text-coral
                                   text-xs
                                   transition"
                        >
                            Xóa
                        </button>

                    </div>

                @endif

            @endforeach

        </div>


        {{-- TOTAL --}}
        <div
            class="flex items-center
                   justify-between
                   mt-6 pt-4
                   border-t border-coral-light"
        >

            <span class="text-ink-soft">
                Tổng cộng
            </span>

            <span
                class="font-display
                       font-bold
                       text-xl
                       text-coral"
                id="mk-cart-total"
            >
                {{ number_format(
                    $total,
                    0,
                    ',',
                    '.'
                ) }}đ
            </span>

        </div>


        {{-- CHECKOUT --}}
        @auth

            <a
                href="{{ route('checkout.index') }}"
                class="btn-primary
                       w-full
                       mt-4
                       text-center"
            >
                Tiến hành thanh toán
            </a>

        @else

            <button
                type="button"
                onclick="
                    if (typeof window.mkOpenLoginModal === 'function') {
                        window.mkOpenLoginModal();
                    } else if (typeof window.openLoginModal === 'function') {
                        window.openLoginModal();
                    } else {
                        window.location.href='{{ route('login') }}';
                    }
                "
                class="btn-primary
                       w-full
                       mt-4"
            >
                Đăng nhập để thanh toán
            </button>

        @endauth

    @endif

</section>

@endsection


@push('scripts')

<script>
    async function mkUpdateCartItem(
        cartItemId,
        quantity
    ) {
        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;

        try {
            const response = await fetch(
                `/api/cart/${cartItemId}`,
                {
                    method:
                        quantity <= 0
                            ? 'DELETE'
                            : 'PATCH',

                    headers: {
                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            csrfToken ?? '',
                    },

                    body:
                        quantity <= 0
                            ? null
                            : JSON.stringify({
                                quantity
                            }),
                }
            );

            if (!response.ok) {
                return;
            }

            const data =
                await response.json();


            /*
            |--------------------------------------------------------------------------
            | Cart badge
            |--------------------------------------------------------------------------
            */

            document
                .querySelectorAll(
                    '.mk-cart-count'
                )
                .forEach(
                    el => {
                        el.textContent =
                            data.cart_count ?? 0;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Remove item
            |--------------------------------------------------------------------------
            */

            if (quantity <= 0) {

                document
                    .querySelector(
                        `[data-cart-item="${cartItemId}"]`
                    )
                    ?.remove();

            } else {

                const qtyElement =
                    document.getElementById(
                        `mk-qty-${cartItemId}`
                    );

                if (qtyElement) {
                    qtyElement.textContent =
                        quantity;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Update total nếu API trả cart_total
            |--------------------------------------------------------------------------
            */

            if (
                typeof data.cart_total !==
                'undefined'
            ) {
                const totalElement =
                    document.getElementById(
                        'mk-cart-total'
                    );

                if (totalElement) {
                    totalElement.textContent =
                        new Intl.NumberFormat(
                            'vi-VN'
                        ).format(
                            data.cart_total
                        ) + 'đ';
                }
            }


            /*
             * Nếu giỏ hàng hết sản phẩm,
             * reload để hiện empty state.
             */
            if (
                Number(
                    data.cart_count ?? 0
                ) === 0
            ) {
                window.location.reload();
            }

        } catch (error) {
            console.error(
                'Cart update error:',
                error
            );
        }
    }
</script>

@endpush