@extends('client.layouts.app')

@section('title', 'Sản phẩm yêu thích - MommyKids')

@push('styles')
<style>
    .mk-wishlist-page {
        width: 100%;
        max-width: 1220px;
        margin: 0 auto 56px;
    }

    .mk-wishlist-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;

        margin-bottom: 22px;
        padding: 6px 2px 18px;

        border-bottom: 1px solid #eadfe3;
    }

    .mk-wishlist-breadcrumb {
        display: flex;
        align-items: center;
        gap: 7px;

        margin-bottom: 8px;

        color: #958c97;
        font-size: 12px;
    }

    .mk-wishlist-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .mk-wishlist-breadcrumb a:hover {
        color: #FF6F81;
    }

    .mk-wishlist-title {
        margin: 0;

        color: #241E28;

        font-family: 'Baloo 2', cursive;
        font-size: 30px;
        font-weight: 800;
        line-height: 1.08;
    }

    .mk-wishlist-subtitle {
        margin: 5px 0 0;

        color: #776f79;
        font-size: 13px;
        line-height: 1.55;
    }

    .mk-wishlist-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .mk-wishlist-link,
    .mk-wishlist-count {
        min-height: 38px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0 14px;

        border-radius: 999px;

        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .mk-wishlist-link {
        border: 1px solid #e6dfe3;

        background: #fff;
        color: #49424d;

        text-decoration: none;

        transition:
            border-color .15s ease,
            color .15s ease,
            background .15s ease;
    }

    .mk-wishlist-link:hover {
        border-color: #FF6F81;
        background: #fff8fa;
        color: #FF6F81;
    }

    .mk-wishlist-count {
        border: 1px solid #ffd8df;

        background: #fff5f7;
        color: #FF6F81;
    }

    .mk-wishlist-list {
        padding: 18px 20px 22px;

        border: 1px solid #eee6e9;
        border-radius: 18px;

        background: #fff;

        box-shadow:
            0 7px 24px rgba(43, 37, 48, .045);
    }

    .mk-wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, 218px);
        justify-content: start;
        gap: 16px;
    }

    .mk-wishlist-card-wrap {
        width: 218px;
        min-width: 0;
    }

    .mk-wishlist-card-wrap > * {
        width: 100%;
    }

    .mk-wishlist-empty {
        padding: 52px 24px;

        text-align: center;
    }

    .mk-wishlist-empty-icon {
        width: 58px;
        height: 58px;

        margin: 0 auto 15px;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 50%;

        background: #FFF0F3;
        color: #FF6F81;
    }

    .mk-wishlist-empty h2 {
        margin: 0 0 7px;

        color: #241E28;

        font-family: 'Baloo 2', cursive;
        font-size: 23px;
        font-weight: 800;
    }

    .mk-wishlist-empty p {
        max-width: 430px;

        margin: 0 auto 20px;

        color: #776f79;
        font-size: 13px;
        line-height: 1.65;
    }

    .mk-wishlist-empty a {
        min-height: 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0 18px;

        border-radius: 999px;

        background: #FF6F81;
        color: #fff;

        font-size: 13px;
        font-weight: 700;
        text-decoration: none;

        box-shadow:
            0 8px 18px rgba(255, 111, 129, .16);
    }

    .mk-wishlist-pagination {
        margin-top: 22px;
    }

    @media (max-width: 760px) {
        .mk-wishlist-page {
            margin-bottom: 36px;
        }

        .mk-wishlist-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .mk-wishlist-actions {
            width: 100%;
            justify-content: space-between;
        }

        .mk-wishlist-list {
            padding: 16px;
        }

        .mk-wishlist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            justify-content: stretch;
            gap: 12px;
        }

        .mk-wishlist-card-wrap {
            width: auto;
        }
    }

    @media (max-width: 480px) {
        .mk-wishlist-title {
            font-size: 25px;
        }

        .mk-wishlist-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .mk-wishlist-link,
        .mk-wishlist-count {
            width: 100%;
        }

        .mk-wishlist-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="mk-wishlist-page">

    <div class="mk-wishlist-head">
        <div>
            <div class="mk-wishlist-breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>›</span>
                <span>Yêu thích</span>
            </div>

            <h1 class="mk-wishlist-title">
                Sản phẩm yêu thích
            </h1>

            <p class="mk-wishlist-subtitle">
                Những sản phẩm bạn đã lưu để xem lại sau.
            </p>
        </div>

        <div class="mk-wishlist-actions">
            <a
                href="{{ route('categories.index') }}"
                class="mk-wishlist-link"
            >
                ← Tiếp tục mua sắm
            </a>

            <span
                class="mk-wishlist-count"
                data-wishlist-page-count
            >
                {{ $items->total() }} sản phẩm
            </span>
        </div>
    </div>


    <div class="mk-wishlist-list">

        @if ($items->count())

            <div class="mk-wishlist-grid">

                @foreach ($items as $item)

                    @php
                        $product = $item->product;

                        if (!$product) {
                            continue;
                        }

                        $cardProduct = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'price' => $product->price ?? 0,
                            'sale_price' => $product->sale_price ?? null,
                            'image' => $product->image_url
                                ?? $product->image
                                ?? null,
                            'url' => route(
                                'product.show',
                                $product->slug
                            ),
                            'is_wishlisted' => true,
                        ];
                    @endphp

                    <div
                        class="mk-wishlist-card-wrap"
                        data-wishlist-card="{{ $product->id }}"
                    >
                        <x-product-card
                            :product="$cardProduct"
                            :product-id="$product->id"
                        />
                    </div>

                @endforeach

            </div>

            @if ($items->hasPages())
                <div class="mk-wishlist-pagination">
                    {{ $items->links() }}
                </div>
            @endif

        @else

            <div class="mk-wishlist-empty">

                <div class="mk-wishlist-empty-icon">
                    <svg
                        width="26"
                        height="26"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 21s-7-4.35-9.33-8.42C.91 9.5 2.06 5.5 5.72 4.32A5.2 5.2 0 0 1 12 6.09a5.2 5.2 0 0 1 6.28-1.77c3.66 1.18 4.81 5.18 3.05 8.26C19 16.65 12 21 12 21Z"
                        />
                    </svg>
                </div>

                <h2>Chưa có sản phẩm yêu thích</h2>

                <p>
                    Bấm biểu tượng trái tim ở sản phẩm bạn quan tâm.
                    MommyKids sẽ lưu lại để bạn xem lại nhanh hơn.
                </p>

                <a href="{{ route('categories.index') }}">
                    Khám phá sản phẩm
                </a>

            </div>

        @endif

    </div>

</section>
@endsection
