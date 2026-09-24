@extends('client.layouts.app')

@section('title', 'Deal Hot - MommyKids')

@section(
    'meta_description',
    'Khám phá các Deal Hot và sản phẩm đang được áp dụng chương trình ưu đãi tại MommyKids.'
)

@push('styles')
<style>
    /* =========================================================
       DEAL HOT PAGE
    ========================================================= */
    .mk-deal-page {
        width: 100%;
        padding: .4rem 0 2.5rem;
    }


    /* =========================================================
       HERO
    ========================================================= */
    .mk-deal-hero {
        position: relative;
        overflow: hidden;

        display: flex;
        align-items: center;
        justify-content: space-between;

        min-height: 230px;

        margin-bottom: 1.35rem;

        padding: 2rem 2.4rem;

        border-radius: 1.45rem;

        background:
            radial-gradient(
                circle at 84% 32%,
                rgba(255,255,255,.22),
                transparent 23%
            ),
            linear-gradient(
                120deg,
                #FF637C 0%,
                #FF7B73 50%,
                #F5A623 100%
            );

        box-shadow:
            0 16px 38px
            rgba(255,111,129,.16);
    }

    .mk-deal-hero-content {
        position: relative;
        z-index: 2;

        max-width: 680px;
    }

    .mk-deal-eyebrow {
        display: inline-flex;
        align-items: center;

        gap: .42rem;

        padding: .42rem .78rem;

        margin-bottom: .75rem;

        border: 1px solid
            rgba(255,255,255,.32);

        border-radius: 999px;

        background:
            rgba(255,255,255,.13);

        color: #fff;

        font-size: .73rem;

        font-weight: 800;

        letter-spacing: .035em;

        text-transform: uppercase;
    }

    .mk-deal-hero h1 {
        margin: 0;

        color: #fff;

        font-family:
            'Baloo 2',
            sans-serif;

        font-size: 2.15rem;

        font-weight: 800;

        line-height: 1.08;
    }

    .mk-deal-hero p {
        max-width: 590px;

        margin: .55rem 0 0;

        color:
            rgba(255,255,255,.91);

        font-size: .88rem;

        line-height: 1.6;
    }

    .mk-deal-summary {
        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: .55rem;

        margin-top: 1rem;
    }

    .mk-deal-summary-item {
        display: inline-flex;
        align-items: center;

        gap: .38rem;

        padding: .44rem .72rem;

        border-radius: 999px;

        background:
            rgba(255,255,255,.16);

        color: #fff;

        font-size: .74rem;

        font-weight: 700;
    }

    .mk-deal-hero-fire {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 145px;

        width: 145px;
        height: 145px;

        margin-right: 3rem;

        border: 1px solid
            rgba(255,255,255,.18);

        border-radius: 50%;

        background:
            rgba(255,255,255,.08);

        font-size: 4.6rem;

        transform: rotate(-5deg);
    }

    .mk-deal-hero-fire::before {
        content: "";

        position: absolute;

        width: 105px;
        height: 105px;

        border-radius: 50%;

        background:
            rgba(255,255,255,.07);
    }

    .mk-deal-hero-fire span {
        position: relative;
        z-index: 2;
    }


    /* =========================================================
       CAMPAIGN WRAPPER
    ========================================================= */
    .mk-deal-campaign {
        overflow: hidden;

        margin-bottom: 1.35rem;

        border:
            1px solid
            rgba(255,111,129,.13);

        border-radius: 1.35rem;

        background: #fff;

        box-shadow:
            0 8px 28px
            rgba(43,37,48,.055);
    }


    /* =========================================================
       CAMPAIGN HEADER
    ========================================================= */
    .mk-deal-campaign-top {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 1rem;

        padding: .95rem 1.15rem;

        border-bottom:
            1px solid #F4E8EB;

        background:
            linear-gradient(
                90deg,
                #FFF8F9 0%,
                #FFFFFF 55%,
                #FFF8F1 100%
            );
    }

    .mk-deal-campaign-heading {
        display: flex;
        align-items: center;

        gap: .72rem;

        min-width: 0;
    }

    .mk-deal-campaign-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        flex: 0 0 2.55rem;

        width: 2.55rem;
        height: 2.55rem;

        border-radius: .8rem;

        background:
            linear-gradient(
                145deg,
                #FFF0F3,
                #FFE0E6
            );

        font-size: 1.2rem;
    }

    .mk-deal-campaign-heading > div {
        min-width: 0;
    }

    .mk-deal-campaign-title {
        margin: 0;

        color: #2B2530;

        font-family:
            'Baloo 2',
            sans-serif;

        font-size: 1.12rem;

        font-weight: 800;

        line-height: 1.2;
    }

    .mk-deal-campaign-description {
        margin: .15rem 0 0;

        color: #7B7380;

        font-size: .73rem;

        line-height: 1.4;
    }

    .mk-deal-campaign-meta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;

        gap: .45rem;

        flex-shrink: 0;
    }

    .mk-deal-badge {
        display: inline-flex;
        align-items: center;

        gap: .32rem;

        padding: .4rem .68rem;

        border-radius: 999px;

        background: #FFF0F3;

        color: #EF5E78;

        font-size: .69rem;

        font-weight: 700;

        white-space: nowrap;
    }

    .mk-deal-badge-time {
        background: #FFF7E7;

        color: #C77700;
    }


    /* =========================================================
       CAMPAIGN BODY
    ========================================================= */
    .mk-deal-campaign-body {
        display: grid;

        grid-template-columns:
            minmax(0, 1fr)
            300px;

        align-items: stretch;

        gap: 1.25rem;

        padding: 1.25rem;
    }


    /* =========================================================
       PRODUCT AREA
    ========================================================= */
    .mk-deal-products-wrap {
        min-width: 0;
    }

    .mk-deal-products-title {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 1rem;

        margin-bottom: .8rem;
    }

    .mk-deal-products-title strong {
        color: #2B2530;

        font-size: .82rem;

        font-weight: 800;
    }

    .mk-deal-products-title span {
        color: #8A828C;

        font-size: .69rem;

        white-space: nowrap;
    }

    .mk-deal-products {
        display: grid;

        grid-template-columns:
            repeat(
                auto-fill,
                minmax(190px, 220px)
            );

        align-items: start;

        justify-content: start;

        gap: 1rem;
    }

    .mk-deal-products > * {
        min-width: 0;
    }


    /* =========================================================
       CAMPAIGN SUMMARY CARD
    ========================================================= */
    .mk-deal-summary-card {
        position: relative;

        min-height: 270px;

        padding: 1.35rem;

        overflow: hidden;

        border:
            1px solid
            rgba(255,111,129,.1);

        border-radius: 1rem;

        background:
            linear-gradient(
                145deg,
                #FFF1F4 0%,
                #FFF8F8 55%,
                #FFF4E6 100%
            );
    }

    .mk-deal-summary-card::after {
        content: "🔥";

        position: absolute;

        right: -.8rem;
        bottom: -1.3rem;

        font-size: 7rem;

        opacity: .055;

        pointer-events: none;
    }

    .mk-deal-status {
        position: relative;
        z-index: 2;

        display: inline-flex;
        align-items: center;

        width: fit-content;

        padding: .38rem .65rem;

        border-radius: 999px;

        background: #FF6F81;

        color: #fff;

        font-size: .65rem;

        font-weight: 800;
    }

    .mk-deal-summary-big {
        position: relative;
        z-index: 2;

        margin-top: 1rem;

        color: #2B2530;

        font-family:
            'Baloo 2',
            sans-serif;

        font-size: 1.45rem;

        font-weight: 800;

        line-height: 1.15;
    }

    .mk-deal-summary-card p {
        position: relative;
        z-index: 2;

        margin: .4rem 0 1.1rem;

        color: #756E78;

        font-size: .76rem;

        line-height: 1.55;
    }

    .mk-deal-summary-list {
        position: relative;
        z-index: 2;

        display: flex;
        flex-direction: column;

        gap: .7rem;
    }

    .mk-deal-summary-list > div {
        display: flex;
        align-items: center;
        justify-content: space-between;

        gap: 1rem;

        padding-top: .7rem;

        border-top:
            1px dashed
            rgba(255,111,129,.24);
    }

    .mk-deal-summary-list span {
        color: #817A84;

        font-size: .7rem;
    }

    .mk-deal-summary-list strong {
        max-width: 150px;

        color: #FF5F78;

        font-size: .74rem;

        font-weight: 800;

        line-height: 1.35;

        text-align: right;
    }


    /* =========================================================
       EMPTY STATE
    ========================================================= */
    .mk-deal-empty {
        padding: 4rem 1.5rem;

        border:
            1px solid
            rgba(255,111,129,.13);

        border-radius: 1.35rem;

        background: #fff;

        text-align: center;

        box-shadow:
            0 8px 28px
            rgba(43,37,48,.05);
    }

    .mk-deal-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;

        width: 4rem;
        height: 4rem;

        margin: 0 auto 1rem;

        border-radius: 50%;

        background: #FFF0F3;

        font-size: 1.8rem;
    }

    .mk-deal-empty h2 {
        margin: 0;

        color: #2B2530;

        font-family:
            'Baloo 2',
            sans-serif;

        font-size: 1.3rem;

        font-weight: 800;
    }

    .mk-deal-empty p {
        margin: .45rem 0 0;

        color: #77717B;

        font-size: .82rem;
    }

    .mk-deal-empty a {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        margin-top: 1.1rem;

        padding: .62rem 1rem;

        border-radius: 999px;

        background: #FF6F81;

        color: #fff;

        font-size: .78rem;

        font-weight: 700;

        text-decoration: none;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */
    @media (max-width: 1100px) {
        .mk-deal-campaign-body {
            grid-template-columns:
                minmax(0, 1fr)
                260px;
        }

        .mk-deal-hero-fire {
            margin-right: 1rem;
        }
    }

    @media (max-width: 850px) {
        .mk-deal-hero-fire {
            display: none;
        }

        .mk-deal-campaign-body {
            grid-template-columns: 1fr;
        }

        .mk-deal-summary-card {
            min-height: 0;
        }

        .mk-deal-campaign-top {
            align-items: flex-start;
            flex-direction: column;
        }

        .mk-deal-campaign-meta {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .mk-deal-page {
            padding-top: 0;
        }

        .mk-deal-hero {
            min-height: 0;

            padding:
                1.4rem
                1.25rem;
        }

        .mk-deal-hero h1 {
            font-size: 1.72rem;
        }

        .mk-deal-products {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }

        .mk-deal-campaign-body {
            padding: 1rem;
        }

        .mk-deal-campaign-top {
            padding: .9rem 1rem;
        }
    }
</style>
@endpush


@section('content')

<div class="mk-deal-page">

    {{-- =====================================================
        HERO
    ====================================================== --}}
    <section class="mk-deal-hero">

        <div class="mk-deal-hero-content">

            <span class="mk-deal-eyebrow">
                🔥 Deal Hot MommyKids
            </span>

            <h1>
                Săn deal tốt cho mẹ & bé
            </h1>

            <p>
                Tổng hợp những sản phẩm đang được áp dụng
                chương trình ưu đãi tại MommyKids.
                Giá Deal Hot được lấy trực tiếp từ Campaign
                đang có hiệu lực.
            </p>


            <div class="mk-deal-summary">

                <span class="mk-deal-summary-item">
                    🎁
                    {{ $dealCampaigns->count() }}
                    chương trình
                </span>

                <span class="mk-deal-summary-item">
                    🛍️
                    {{ $dealProductCount }}
                    sản phẩm đang giảm
                </span>

            </div>

        </div>


        <div class="mk-deal-hero-fire">
            <span>
                🔥
            </span>
        </div>

    </section>


    {{-- =====================================================
        ACTIVE CAMPAIGNS
    ====================================================== --}}
    @forelse ($dealCampaigns as $campaign)

        <section class="mk-deal-campaign">

            {{-- =================================================
                CAMPAIGN HEADER
            ================================================== --}}
            <div class="mk-deal-campaign-top">

                <div class="mk-deal-campaign-heading">

                    <span class="mk-deal-campaign-icon">
                        🔥
                    </span>


                    <div>

                        <h2 class="mk-deal-campaign-title">
                            {{ $campaign['name'] }}
                        </h2>

                        <p class="mk-deal-campaign-description">

                            @if (!empty($campaign['description']))

                                {{ $campaign['description'] }}

                            @else

                                Ưu đãi đặc biệt đang được áp dụng
                                tại MommyKids.

                            @endif

                        </p>

                    </div>

                </div>


                <div class="mk-deal-campaign-meta">

                    @if (!empty($campaign['type_name']))

                        <span class="mk-deal-badge">
                            🎯
                            {{ $campaign['type_name'] }}
                        </span>

                    @endif


                    @if ($campaign['ends_at'])

                        <span
                            class="
                                mk-deal-badge
                                mk-deal-badge-time
                            "
                        >
                            ⏰
                            Kết thúc
                            {{ $campaign['ends_at']->format('d/m/Y H:i') }}
                        </span>

                    @else

                        <span
                            class="
                                mk-deal-badge
                                mk-deal-badge-time
                            "
                        >
                            ⏰
                            Không giới hạn thời gian
                        </span>

                    @endif

                </div>

            </div>


            {{-- =================================================
                CAMPAIGN BODY
            ================================================== --}}
            <div class="mk-deal-campaign-body">

                {{-- =============================================
                    PRODUCTS
                ============================================== --}}
                <div class="mk-deal-products-wrap">

                    <div class="mk-deal-products-title">

                        <strong>
                            Sản phẩm đang ưu đãi
                        </strong>

                        <span>
                            {{ $campaign['products']->count() }}
                            sản phẩm
                        </span>

                    </div>


                    <div class="mk-deal-products">

                        @foreach (
                            $campaign['products']
                            as $product
                        )

                            <x-product-card
                                :product="$product"
                                :product-id="$product['id']"
                            />

                        @endforeach

                    </div>

                </div>


                {{-- =============================================
                    CAMPAIGN SUMMARY
                ============================================== --}}
                <aside class="mk-deal-summary-card">

                    <span class="mk-deal-status">
                        🔥 ĐANG DIỄN RA
                    </span>


                    <div class="mk-deal-summary-big">
                        Deal đang áp dụng
                    </div>


                    <p>
                        Các sản phẩm bên cạnh đang được áp dụng
                        mức giá Campaign trực tiếp từ MommyKids.
                    </p>


                    <div class="mk-deal-summary-list">

                        <div>

                            <span>
                                Sản phẩm ưu đãi
                            </span>

                            <strong>
                                {{ $campaign['products']->count() }}
                            </strong>

                        </div>


                        @if ($campaign['ends_at'])

                            <div>

                                <span>
                                    Kết thúc
                                </span>

                                <strong>
                                    {{ $campaign['ends_at']->format('d/m/Y') }}
                                </strong>

                            </div>

                        @endif


                        @if (!empty($campaign['type_name']))

                            <div>

                                <span>
                                    Loại chương trình
                                </span>

                                <strong>
                                    {{ $campaign['type_name'] }}
                                </strong>

                            </div>

                        @endif

                    </div>

                </aside>

            </div>

        </section>


    @empty

        {{-- =================================================
            NO ACTIVE DEAL
        ================================================== --}}
        <section class="mk-deal-empty">

            <div class="mk-deal-empty-icon">
                🔥
            </div>


            <h2>
                Hiện chưa có Deal Hot
            </h2>


            <p>
                Hiện chưa có Campaign giảm giá nào đang hoạt động
                hoặc sản phẩm trong Campaign đã hết lượt ưu đãi.
            </p>


            <a href="{{ route('home') }}">
                ← Quay về trang chủ
            </a>

        </section>

    @endforelse

</div>

@endsection