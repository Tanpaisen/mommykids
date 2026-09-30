@extends('client.layouts.app')

@section('sidebar')
    <div class="hidden"></div>
@endsection

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

    {{-- THANH THÔNG TIN BẢO HÀNH & GIAO HÀNG --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-100 p-4 lg:p-5 flex flex-col md:flex-row items-center justify-between gap-4">
        
        {{-- Nhóm các tính năng --}}
        <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-8 w-full md:w-auto">
            
            {{-- Miễn phí giao hàng --}}
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="text-2xl text-[#1DB8A0]">
                    <i class="fas fa-truck"></i> {{-- Thay bằng icon SVG tương ứng nếu dùng hệ thống icon khác --}}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Miễn phí giao hàng</p>
                    <p class="text-xs text-slate-500">Nội thành đơn từ 2,990đ</p>
                </div>
            </div>

            {{-- Tặng quà cho đơn --}}
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="text-2xl text-amber-500">
                    <i class="fas fa-gift"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Tặng quà cho đơn</p>
                    <p class="text-xs text-slate-500">Hàng từ 4,990đ</p>
                </div>
            </div>

            {{-- Tích điểm thành viên --}}
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="text-2xl text-amber-500">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">Tích điểm thành viên</p>
                    <p class="text-xs text-slate-500">Cho mọi đơn hàng</p>
                </div>
            </div>
        </div>

        {{-- Cột Hotline --}}
        <div class="flex items-center gap-3 w-full md:w-auto md:border-l border-slate-100 md:pl-8 mt-2 md:mt-0 pt-4 md:pt-0 border-t md:border-t-0">
            <div class="text-2xl text-[#1DB8A0]">
                <i class="fas fa-phone-alt"></i>
            </div>
            <div class="text-left md:text-right w-full">
                <p class="text-xs text-slate-500">Hotline hỗ trợ</p>
                <p class="text-lg font-bold text-slate-800 tracking-wide">1800 1234</p>
            </div>
        </div>

    </div>


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

                    /*
                     * Tính phần trăm giảm của Campaign từ giá gốc
                     * và giá hiệu lực hiện tại trong CartItem.
                     */
                    $campaignPercent = 0;

                    if (
                        isset($item->base_price) &&
                        (int) $item->base_price > 0 &&
                        (int) $item->price < (int) $item->base_price
                    ) {
                        $campaignPercent = (int) round(
                            (
                                (
                                    (int) $item->base_price
                                    - (int) $item->price
                                )
                                / (int) $item->base_price
                            ) * 100
                        );
                    }
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

                            <div class="mt-1">
                                <p class="price-tag text-sm">
                                    {{ number_format(
                                        (int) $item->price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}đ
                                </p>

                                @if (
                                    isset($item->base_price)
                                    && (int) $item->base_price > (int) $item->price
                                )
                                    <div class="mt-0.5 flex items-center gap-2 text-xs">
                                        <span class="text-ink-soft line-through">
                                            {{ number_format(
                                                (int) $item->base_price,
                                                0,
                                                ',',
                                                '.'
                                            ) }}đ
                                        </span>

                                        <span
                                            class="inline-flex items-center
                                                   rounded-full
                                                   bg-coral-light
                                                   px-2 py-0.5
                                                   text-[11px]
                                                   font-semibold
                                                   text-coral"
                                        >
                                            Khuyến mãi -{{ $campaignPercent }}%
                                        </span>
                                    </div>
                                @endif
                            </div>

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