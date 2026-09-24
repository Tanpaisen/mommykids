@extends('client.layouts.app')

@section('title', 'Tất cả danh mục - MommyKids')

@push('styles')
<style>
    .mk-categories-page {
        width: 100%;
        padding: .6rem 0 2.5rem;
    }

    .mk-categories-hero {
        position: relative;
        overflow: hidden;

        margin-bottom: 1.4rem;
        padding: 2rem 2.2rem;

        border-radius: 1.35rem;

        background:
            radial-gradient(
                circle at 88% 30%,
                rgba(255,255,255,.35),
                transparent 24%
            ),
            linear-gradient(
                135deg,
                #FFEEF2,
                #FFF8ED
            );

        border: 1px solid rgba(255,111,129,.15);
    }

    .mk-categories-hero span {
        display: inline-flex;

        margin-bottom: .55rem;
        padding: .36rem .68rem;

        border-radius: 999px;

        background: #FFE1E7;
        color: #F05D77;

        font-size: .7rem;
        font-weight: 800;
    }

    .mk-categories-hero h1 {
        margin: 0;

        color: #2B2530;

        font-family: 'Baloo 2', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .mk-categories-hero p {
        margin: .45rem 0 0;

        color: #756E78;

        font-size: .84rem;
        line-height: 1.55;
    }

    .mk-categories-grid {
        display: grid;

        grid-template-columns:
            repeat(5, minmax(0, 1fr));

        gap: 1rem;
    }

    .mk-category-card {
        position: relative;
        overflow: hidden;

        display: flex;
        flex-direction: column;

        min-height: 205px;

        padding: 1.25rem;

        border: 1px solid rgba(255,111,129,.12);
        border-radius: 1.15rem;

        background: #fff;

        color: #2B2530;
        text-decoration: none;

        box-shadow:
            0 6px 22px
            rgba(43,37,48,.055);

        transition:
            transform .18s ease,
            box-shadow .18s ease,
            border-color .18s ease;
    }

    .mk-category-card:hover {
        transform: translateY(-4px);

        border-color: rgba(255,111,129,.32);

        box-shadow:
            0 14px 32px
            rgba(43,37,48,.09);
    }

    .mk-category-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 3.4rem;
        height: 3.4rem;

        margin-bottom: 1rem;

        border-radius: 1rem;

        background:
            linear-gradient(
                145deg,
                #FFF0F3,
                #FFF7ED
            );

        font-size: 1.6rem;
    }

    .mk-category-card h2 {
        margin: 0;

        font-size: .98rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .mk-category-card p {
        margin: .35rem 0 0;

        color: #867E89;

        font-size: .72rem;
    }

    .mk-category-card-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: .5rem;

        margin-top: auto;
        padding-top: 1rem;
    }

    .mk-category-count {
        color: #958D97;

        font-size: .68rem;
    }

    .mk-category-open {
        color: #1DB8A0;

        font-size: .72rem;
        font-weight: 700;
    }

    .mk-categories-empty {
        padding: 4rem 1rem;

        border-radius: 1.2rem;
        background: #fff;

        text-align: center;

        color: #7B7480;
    }

    @media (max-width: 1200px) {
        .mk-categories-grid {
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .mk-categories-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .mk-categories-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .mk-categories-hero {
            padding: 1.4rem;
        }

        .mk-categories-hero h1 {
            font-size: 1.55rem;
        }
    }
</style>
@endpush


@section('content')

<div class="mk-categories-page">

    <section class="mk-categories-hero">

        <span>
            🛍️ MommyKids
        </span>

        <h1>
            Tất cả danh mục
        </h1>

        <p>
            Khám phá các nhóm sản phẩm dành cho mẹ và bé.
            Chọn một danh mục để xem sản phẩm, thương hiệu
            và các bộ lọc phù hợp.
        </p>

    </section>


    @if ($categories->isNotEmpty())

        <div class="mk-categories-grid">

            @foreach ($categories as $category)

                @php
                    $categoryIcon = match ($category->slug) {
                        'sua-cho-be' => '🍼',
                        'bim-ta-ve-sinh' => '🧷',
                        'binh-sua-phu-kien' => '🍶',
                        'an-dam-dinh-duong' => '🥣',
                        'vitamin-suc-khoe' => '💊',
                        'do-dung-me-be' => '🧴',
                        'do-so-sinh' => '👶',
                        'thoi-trang-phu-kien' => '👕',
                        'xe-cho-be' => '🚼',
                        'do-choi-hoc-tap' => '🧸',
                        'me-bau-sau-sinh' => '🤰',
                        'cham-soc-gia-dinh' => '🏠',

                        default =>
                            $category->icon ?: '🛍️',
                    };
                @endphp


                <a
                    href="{{ route('category.show', $category->slug) }}"
                    class="mk-category-card"
                >

                    <div class="mk-category-icon">
                        {{ $categoryIcon }}
                    </div>


                    <h2>
                        {{ $category->name }}
                    </h2>


                    <p>
                        Xem các sản phẩm thuộc
                        {{ $category->name }}.
                    </p>


                    <div class="mk-category-card-bottom">

                        <span class="mk-category-count">
                            {{ $category->products_count }}
                            sản phẩm
                        </span>

                        <span class="mk-category-open">
                            Khám phá →
                        </span>

                    </div>

                </a>

            @endforeach

        </div>

    @else

        <div class="mk-categories-empty">
            Hiện chưa có danh mục sản phẩm.
        </div>

    @endif

</div>

@endsection