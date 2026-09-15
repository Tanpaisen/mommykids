@extends('client.layouts.app')

@section('title', $product->name . ' - MommyKids')

@section('content')

@php
    $imageUrl = function ($image) {
        if (!$image) {
            return null;
        }

        return str_starts_with($image, 'http://') ||
               str_starts_with($image, 'https://')
            ? $image
            : asset('storage/' . ltrim($image, '/'));
    };

    $galleryImages = collect([$product->image])
        ->merge($product->images ?? [])
        ->filter()
        ->unique()
        ->map($imageUrl)
        ->values();

    $mainImage = $galleryImages->first();

    $brandTag = $brand
        ?? $product->tags->firstWhere('type', 'brand');

    /*
     * Template chi tiết riêng cho nhóm Sữa cho bé.
     * Các nhóm Bỉm tã / Bình sữa / Vitamin... sẽ tách sau.
     */
    $isMilkProduct =
        ($product->category?->slug === 'sua-cho-be') ||
        (mb_strtolower(trim($product->category?->name ?? '')) === 'sữa cho bé');

    /*
     * Demo giao diện Aptamil theo đúng ảnh mẫu hiện tại.
     * Sau này có thể chuyển phần lợi ích này thành dữ liệu quản trị.
     */
    $isAptamilDemo =
        stripos($product->name, 'Aptamil') !== false ||
        stripos($brandTag?->name ?? '', 'Aptamil') !== false;

    $attributeTags = $attributes
        ?? $product->tags
            ->where('type', 'attribute')
            ->values();

    $usageSteps = collect(
        preg_split('/\r\n|\r|\n/', trim($product->usage_instructions ?? ''))
    )
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();

    $storageItems = collect(
        preg_split('/\r\n|\r|\n/', trim($product->storage_instructions ?? ''))
    )
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();

    $warningItems = collect(
        preg_split('/\r\n|\r|\n/', trim($product->warning ?? ''))
    )
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();

    $ageText = $product->stages->isNotEmpty()
        ? $product->stages
            ->sortBy('sort_order')
            ->pluck('name')
            ->join(', ')
        : null;

    /*
     * Highlight chỉ lấy dữ liệu thật đang có trong DB.
     * Không dựng rating / lượt bán / công dụng sức khỏe giả.
     */
    $highlightItems = collect();

    foreach ($attributeTags->take(2) as $attribute) {
        $highlightItems->push([
            'title' => $attribute->name,
            'value' => 'Thuộc tính sản phẩm',
            'icon' => 'check',
        ]);
    }

    if ($ageText) {
        $highlightItems->push([
            'title' => 'Độ tuổi phù hợp',
            'value' => $ageText,
            'icon' => 'baby',
        ]);
    }

    if ($product->weight_grams) {
        $highlightItems->push([
            'title' => 'Khối lượng',
            'value' => number_format($product->weight_grams, 0, ',', '.') . ' g',
            'icon' => 'weight',
        ]);
    }

    if ($product->origin) {
        $highlightItems->push([
            'title' => 'Xuất xứ',
            'value' => $product->origin,
            'icon' => 'globe',
        ]);
    }

    if ($product->manufacturer) {
        $highlightItems->push([
            'title' => 'Nhà sản xuất',
            'value' => $product->manufacturer,
            'icon' => 'factory',
        ]);
    }

    if ($product->category) {
        $highlightItems->push([
            'title' => 'Danh mục',
            'value' => $product->category->name,
            'icon' => 'category',
        ]);
    }

    $highlightItems = $highlightItems->take(4);

    // Chỉ hiển thị sản phẩm liên quan có dữ liệu bán hàng hợp lệ.
    // Tránh các bản ghi test / giá 0 làm vỡ giao diện storefront.
    $relatedItems = collect($related)
        ->filter(function ($item) {
            return !empty($item['id'])
                && !empty($item['name'])
                && !empty($item['url'])
                && (int) ($item['price'] ?? 0) > 0;
        })
        ->values();
@endphp

<div
    id="mk-product-page"
    class="product-page"
    data-product-id="{{ $product->id }}"
    data-stock="{{ $product->stock }}"
    data-images='@json($galleryImages->values())'
>
    {{-- =========================================================
        BREADCRUMB
    ========================================================== --}}
    <nav class="product-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Trang chủ</a>

        @if ($product->category)
            <span>›</span>
            <a href="{{ route('category.show', $product->category->slug) }}">
                {{ $product->category->name }}
            </a>
        @endif

        @if ($brandTag)
            <span>›</span>
            <span>{{ $brandTag->name }}</span>
        @endif

        <span>›</span>
        <strong>{{ $product->name }}</strong>
    </nav>

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <section class="product-hero">

        {{-- GALLERY CARD --}}
        <div class="product-gallery-card">
            <div class="product-gallery-shell">

                <div class="product-media">
                    @if ($mainImage)
                        <button
                            id="product-main-image-button"
                            class="product-main-button"
                            type="button"
                            aria-label="Phóng to ảnh sản phẩm"
                        >
                            <img
                                id="product-main-image"
                                class="product-main-image"
                                src="{{ $mainImage }}"
                                alt="{{ $product->name }}"
                                decoding="async"
                                fetchpriority="high"
                            >
                        </button>

                        <div
                            id="product-image-fallback"
                            class="product-image-fallback is-hidden"
                        >
                            <span>🖼️</span>
                            <p>Không thể tải ảnh sản phẩm</p>
                        </div>

                        @if ($galleryImages->count() > 1)
                            <button
                                id="product-gallery-prev"
                                class="product-gallery-arrow product-gallery-prev"
                                type="button"
                                aria-label="Ảnh trước"
                            >
                                ‹
                            </button>

                            <button
                                id="product-gallery-next"
                                class="product-gallery-arrow product-gallery-next"
                                type="button"
                                aria-label="Ảnh tiếp theo"
                            >
                                ›
                            </button>
                        @endif

                        <span class="product-zoom-hint" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="6"></circle>
                                <path d="m16 16 4 4M8 11h6M11 8v6"></path>
                            </svg>
                        </span>
                    @else
                        <div class="product-image-fallback">
                            <span>🖼️</span>
                            <p>Chưa có ảnh sản phẩm</p>
                        </div>
                    @endif
                </div>

                @if ($galleryImages->count() > 1)
                    <div class="product-thumbnails" aria-label="Ảnh sản phẩm">
                        @foreach ($galleryImages as $index => $galleryImage)
                            <button
                                type="button"
                                class="product-thumbnail {{ $index === 0 ? 'is-active' : '' }}"
                                data-index="{{ $index }}"
                                aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="Xem ảnh {{ $index + 1 }}"
                            >
                                <img
                                    src="{{ $galleryImage }}"
                                    alt="{{ $product->name }} - ảnh {{ $index + 1 }}"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </button>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>

        {{-- INFO CARD --}}
        <div class="product-info-card">

            @if ($product->category || $brandTag)
                <div class="product-brand-badge">
                    <span>{{ $product->category?->name ?? $brandTag?->name }}</span>
                </div>
            @endif

            <h1 class="product-title">
                {{ $product->name }}
            </h1>

            <div class="product-prices">
                <strong class="product-price">
                    {{ number_format($product->price, 0, ',', '.') }}đ
                </strong>

                @if ($product->old_price && $product->old_price > $product->price)
                    <span class="product-old-price">
                        {{ number_format($product->old_price, 0, ',', '.') }}đ
                    </span>
                @endif

                @if ($product->discount_percent)
                    <span class="product-price-discount">
                        -{{ $product->discount_percent }}%
                    </span>
                @endif
            </div>
            <div class="product-info-row product-stock-row">
                <strong>Tình trạng:</strong>

                @if ($product->stock > 0)
                    <span class="product-stock-inline">
                        <i></i>
                        Còn hàng
                        <small>({{ $product->stock }} sản phẩm)</small>
                    </span>
                @else
                    <span class="product-stock-inline is-out">
                        <i></i>
                        Hết hàng
                    </span>
                @endif
            </div>

            @if ($product->stock > 0)
                <div class="product-quantity-label">Số lượng</div>

                <div class="product-buy-row">
                    <div class="product-quantity">
                        <button
                            id="product-quantity-minus"
                            type="button"
                            aria-label="Giảm số lượng"
                        >
                            −
                        </button>

                        <input
                            id="product-quantity"
                            type="number"
                            value="1"
                            min="1"
                            max="{{ $product->stock }}"
                            aria-label="Số lượng sản phẩm"
                        >

                        <button
                            id="product-quantity-plus"
                            type="button"
                            aria-label="Tăng số lượng"
                        >
                            +
                        </button>
                    </div>

                    <button
                        id="product-add-to-cart"
                        class="product-cart-button"
                        type="button"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="9" cy="20" r="1.2"></circle>
                            <circle cx="18" cy="20" r="1.2"></circle>
                            <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L21 7H6"></path>
                        </svg>
                        Thêm vào giỏ hàng
                    </button>
                </div>
            @else
                <button
                    class="product-disabled-button"
                    type="button"
                    disabled
                >
                    Hết hàng
                </button>
            @endif


            {{-- =========================================================
                CAM KẾT MUA HÀNG
            ========================================================== --}}
            <div class="product-promise-strip">
                <div class="product-promise-item">
                    <span class="product-promise-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M3 7h11v10H3z"></path>
                            <path d="M14 10h4l3 3v4h-7z"></path>
                            <circle cx="7" cy="19" r="1.5"></circle>
                            <circle cx="18" cy="19" r="1.5"></circle>
                        </svg>
                    </span>

                    <div>
                        <strong>Giao hàng toàn quốc</strong>
                        <span>Nhanh chóng, tiện lợi</span>
                    </div>
                </div>

                <div class="product-promise-item">
                    <span class="product-promise-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>

                    <div>
                        <strong>Hàng chính hãng</strong>
                        <span>Cam kết minh bạch</span>
                    </div>
                </div>

                <div class="product-promise-item">
                    <span class="product-promise-icon">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 13v-1a8 8 0 0 1 16 0v1"></path>
                            <path d="M4 13h3v6H5a1 1 0 0 1-1-1v-5Z"></path>
                            <path d="M20 13h-3v6h2a1 1 0 0 0 1-1v-5Z"></path>
                        </svg>
                    </span>

                    <div>
                        <strong>Tư vấn tận tâm</strong>
                        <span>Hotline: 1800 6886</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- =========================================================
        RELATED PRODUCTS
    ========================================================== --}}
    @if ($relatedItems->isNotEmpty())
        <section class="product-related">
            <div class="product-related-header">
                <h2>Sản phẩm tương tự</h2>

                @if ($product->category)
                    <a href="{{ route('category.show', $product->category->slug) }}">
                        Xem tất cả →
                    </a>
                @endif
            </div>

            <div class="product-related-wrap">
                <button
                    id="product-related-prev"
                    class="product-related-arrow product-related-prev"
                    type="button"
                    aria-label="Xem sản phẩm trước"
                >
                    ‹
                </button>

                <div id="product-related-track" class="product-related-track">
                    @foreach ($relatedItems as $item)
                        @php
                            $relatedImage = $imageUrl($item['image'] ?? null);
                        @endphp

                        <article class="product-related-card">
                            @if (!empty($item['discount']))
                                <span class="product-related-discount">
                                    -{{ $item['discount'] }}%
                                </span>
                            @endif

                            <a
                                class="product-related-image"
                                href="{{ $item['url'] }}"
                            >
                                @if ($relatedImage)
                                    <img
                                        src="{{ $relatedImage }}"
                                        alt="{{ $item['name'] }}"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                @else
                                    <span class="product-related-fallback">🖼️</span>
                                @endif
                            </a>

                            <a
                                class="product-related-name"
                                href="{{ $item['url'] }}"
                            >
                                {{ $item['name'] }}
                            </a>

                            <div class="product-related-price-row">
                                <div>
                                    <strong>
                                        {{ number_format($item['price'], 0, ',', '.') }}đ
                                    </strong>

                                    @if (
                                        !empty($item['old_price']) &&
                                        $item['old_price'] > $item['price']
                                    )
                                        <span>
                                            {{ number_format($item['old_price'], 0, ',', '.') }}đ
                                        </span>
                                    @endif
                                </div>

                                <button
                                    class="product-related-add"
                                    type="button"
                                    data-product-id="{{ $item['id'] }}"
                                    aria-label="Thêm {{ $item['name'] }} vào giỏ hàng"
                                >
                                    +
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <button
                    id="product-related-next"
                    class="product-related-arrow product-related-next"
                    type="button"
                    aria-label="Xem sản phẩm tiếp theo"
                >
                    ›
                </button>
            </div>
        </section>
    @endif

    {{-- =========================================================
        LONG CONTENT
        Sữa cho bé dùng partial riêng.
        Các danh mục khác tạm giữ giao diện chung, sẽ tách tiếp sau.
    ========================================================== --}}
    @if ($isMilkProduct)
        @include('client.product-details.milk', [
            'brandTag' => $brandTag,
            'attributeTags' => $attributeTags,
            'usageSteps' => $usageSteps,
            'storageItems' => $storageItems,
            'warningItems' => $warningItems,
            'ageText' => $ageText,
        ])
    @else
        <section class="product-long-content">
            <div class="product-content-grid">

                <article class="product-content-card">
                    <div class="product-section-heading">
                        <h2>Chi tiết sản phẩm</h2>
                    </div>

                    <div class="product-spec-table">
                        <div class="product-spec-row">
                            <strong>Tên sản phẩm</strong>
                            <span>{{ $product->name }}</span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Thương hiệu</strong>
                            <span>{{ $brandTag?->name ?: 'Đang cập nhật' }}</span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Danh mục</strong>
                            <span>{{ $product->category?->name ?: 'Đang cập nhật' }}</span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Khối lượng</strong>
                            <span>
                                {{ $product->weight_grams
                                    ? number_format($product->weight_grams, 0, ',', '.') . ' g'
                                    : 'Đang cập nhật' }}
                            </span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Xuất xứ</strong>
                            <span>{{ $product->origin ?: 'Đang cập nhật' }}</span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Nhà sản xuất</strong>
                            <span>{{ $product->manufacturer ?: 'Đang cập nhật' }}</span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Thuộc tính</strong>

                            <span class="product-spec-tags">
                                @forelse ($attributeTags as $attribute)
                                    <em>{{ $attribute->name }}</em>
                                @empty
                                    Đang cập nhật
                                @endforelse
                            </span>
                        </div>

                        <div class="product-spec-row">
                            <strong>Tình trạng</strong>
                            <span class="{{ $product->stock > 0 ? 'is-stock' : 'is-out' }}">
                                {{ $product->stock > 0
                                    ? 'Còn ' . $product->stock . ' sản phẩm'
                                    : 'Hết hàng' }}
                            </span>
                        </div>
                    </div>
                </article>

                <article class="product-content-card product-description-card">
                    <div class="product-section-heading">
                        <h2>Mô tả sản phẩm</h2>
                    </div>

                    <div class="product-description-text">
                        {{ $product->description ?: 'Thông tin mô tả sản phẩm đang được cập nhật.' }}
                    </div>

                    @if ($highlightItems->isNotEmpty())
                        <div class="product-highlight-grid">
                            @foreach ($highlightItems as $highlight)
                                <div class="product-highlight-item">
                                    <div class="product-highlight-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9"></circle>
                                            <path d="m8 12 2.5 2.5L16 9"></path>
                                        </svg>
                                    </div>

                                    <div>
                                        <strong>{{ $highlight['title'] }}</strong>
                                        <span>{{ $highlight['value'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-description-note">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 21s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 11c0 5.6-7 10-7 10Z"></path>
                        </svg>

                        <div>
                            <strong>Thông tin sản phẩm rõ ràng, dễ theo dõi</strong>
                            <span>Nội dung được cập nhật từ thông tin sản phẩm trên MommyKids.</span>
                        </div>
                    </div>
                </article>
            </div>

            <article class="product-content-card product-wide-card product-ingredients-card">
                <div class="product-section-heading">
                    <h2>Thành phần</h2>
                </div>

                <div class="product-long-text">
                    {{ trim($product->ingredients ?: 'Thông tin thành phần sản phẩm đang được cập nhật.') }}
                </div>
            </article>
        </section>
    @endif

    {{-- =========================================================
        PRODUCT REVIEWS
    ========================================================== --}}
    <section id="product-reviews" class="product-reviews-section">

        <div class="product-reviews-heading">
            <div>
                <span class="product-reviews-eyebrow">
                    Ý kiến khách hàng
                </span>

                <h2>Đánh giá sản phẩm</h2>

                <p>
                    Chia sẻ trải nghiệm của bạn về
                    {{ $product->name }}.
                </p>
            </div>

            @if ($reviewCount > 0)
                <div class="product-reviews-heading-count">
                    {{ $reviewCount }} đánh giá
                </div>
            @endif
        </div>

        {{-- ================================================
            REVIEW SUMMARY
        ================================================= --}}
        <div class="product-review-summary">

            <div class="product-review-score-card">
                <strong class="product-review-score">
                    {{ number_format($averageRating, 1, ',', '.') }}
                </strong>

                <span class="product-review-score-max">
                    / 5
                </span>

                <div class="product-review-summary-stars"
                     aria-label="{{ $averageRating }} trên 5 sao">

                    @for ($star = 1; $star <= 5; $star++)
                        <span class="{{ $star <= round($averageRating) ? 'is-active' : '' }}">
                            ★
                        </span>
                    @endfor
                </div>

                <p>
                    @if ($reviewCount > 0)
                        Dựa trên {{ $reviewCount }} đánh giá
                    @else
                        Chưa có đánh giá nào
                    @endif
                </p>
            </div>

            <div class="product-rating-distribution">
                @foreach ($ratingDistribution as $rating => $distribution)
                    <div class="product-rating-row">
                        <div class="product-rating-label">
                            <strong>{{ $rating }}</strong>
                            <span>★</span>
                        </div>

                        <div class="product-rating-track">
                            <div
                                class="product-rating-fill"
                                style="width: {{ $distribution['percentage'] }}%"
                            ></div>
                        </div>

                        <span class="product-rating-percent">
                            {{ $distribution['percentage'] }}%
                        </span>

                        <span class="product-rating-count">
                            ({{ $distribution['count'] }})
                        </span>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ================================================
            SUCCESS / ERROR
        ================================================= --}}
        @if (session('success'))
            <div class="product-review-alert is-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('review'))
            <div class="product-review-alert is-error">
                {{ $errors->first('review') }}
            </div>
        @endif

        {{-- ================================================
            WRITE REVIEW
        ================================================= --}}
        <div class="product-review-form-card">

            @guest
                <div class="product-review-login">
                    <div class="product-review-login-icon">
                        ★
                    </div>

                    <div>
                        <strong>Đăng nhập để viết đánh giá</strong>

                        <p>
                            Bạn cần đăng nhập tài khoản MommyKids
                            trước khi đánh giá sản phẩm.
                        </p>
                    </div>
                </div>
            @else
                {{-- Chưa từng mua hoặc đơn chưa giao thành công --}}
                @if (!$hasPurchasedProduct)
                    <div class="product-review-purchase-required">
                        <div class="product-review-purchase-icon">
                            🛒
                        </div>

                        <div>
                           <strong>
    Chỉ khách hàng đã mua và nhận sản phẩm mới có thể đánh giá
</strong>

<p>
    Bạn có thể viết đánh giá sau khi đơn hàng
    chứa sản phẩm này được giao thành công.
</p>
                        </div>
                    </div>

                {{-- Đã mua và đã có review: cho phép chỉnh sửa review hiện tại --}}
                @elseif ($userReview)
                    <div class="product-review-already">
                        <div class="product-review-already-icon">
                            ✓
                        </div>

                        <div>
                            <strong>
                                Bạn đã đánh giá sản phẩm này
                            </strong>

                            <p>
                                Cảm ơn bạn đã chia sẻ trải nghiệm về sản phẩm.
                            </p>
                        </div>
                    </div>

                    @php
                        $reviewEditOpen =
                            $errors->has('rating') ||
                            $errors->has('comment') ||
                            $errors->has('images') ||
                            $errors->has('images.*');
                    @endphp

                    <details
                        class="product-review-edit"
                        {{ $reviewEditOpen ? 'open' : '' }}
                    >
                        <summary class="product-review-edit-toggle">
                            <span class="product-review-edit-toggle-icon" aria-hidden="true">
                                ✎
                            </span>

                            <span>Chỉnh sửa đánh giá</span>
                        </summary>

                        <div class="product-review-edit-panel">
                            <div class="product-review-form-heading">
                                <div>
                                    <h3>Chỉnh sửa đánh giá</h3>

                                    <p>
                                        Cập nhật số sao, nội dung hoặc hình ảnh
                                        dựa trên trải nghiệm hiện tại của bạn.
                                    </p>
                                </div>
                            </div>

                            <form
                                action="{{ route('products.reviews.update', [$product, $userReview]) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="product-review-form"
                            >
                                @csrf
                                @method('PATCH')

                                {{-- Rating hiện tại --}}
                                <div class="product-review-field">
                                    <label class="product-review-field-label">
                                        Đánh giá của bạn
                                        <span>*</span>
                                    </label>

                                    <div class="product-review-star-input">
                                        @for ($rating = 5; $rating >= 1; $rating--)
                                            <input
                                                type="radio"
                                                id="review-edit-rating-{{ $rating }}"
                                                name="rating"
                                                value="{{ $rating }}"
                                                {{
                                                    (int) old('rating', $userReview->rating) === $rating
                                                        ? 'checked'
                                                        : ''
                                                }}
                                            >

                                            <label
                                                for="review-edit-rating-{{ $rating }}"
                                                title="{{ $rating }} sao"
                                                aria-label="{{ $rating }} sao"
                                            >
                                                ★
                                            </label>
                                        @endfor
                                    </div>

                                    @error('rating')
                                        <p class="product-review-field-error">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Comment hiện tại --}}
                                <div class="product-review-field">
                                    <label
                                        class="product-review-field-label"
                                        for="review-edit-comment"
                                    >
                                        Nội dung đánh giá
                                        <small>Không bắt buộc</small>
                                    </label>

                                    <textarea
                                        id="review-edit-comment"
                                        name="comment"
                                        rows="5"
                                        maxlength="2000"
                                        placeholder="Chia sẻ trải nghiệm thực tế của bạn về sản phẩm..."
                                    >{{ old('comment', $userReview->comment) }}</textarea>

                                    <div class="product-review-field-help">
                                        Tối đa 2000 ký tự.
                                    </div>

                                    @error('comment')
                                        <p class="product-review-field-error">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Ảnh review hiện tại --}}
                                @if (!empty($userReview->images))
                                    <div class="product-review-field">
                                        <span class="product-review-field-label">
                                            Hình ảnh hiện tại
                                        </span>

                                        <div class="product-review-edit-current-images">
                                            @foreach ($userReview->images as $reviewImage)
                                                <a
                                                    href="{{ $imageUrl($reviewImage) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                >
                                                    <img
                                                        src="{{ $imageUrl($reviewImage) }}"
                                                        alt="Ảnh đánh giá hiện tại"
                                                        loading="lazy"
                                                        decoding="async"
                                                    >
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Ảnh mới --}}
                                <div class="product-review-field">
                                    <label
                                        class="product-review-field-label"
                                        for="review-edit-images"
                                    >
                                        Thay hình ảnh
                                        <small>Không bắt buộc</small>
                                    </label>

                                    <label
                                        class="product-review-upload"
                                        for="review-edit-images"
                                    >
                                        <span class="product-review-upload-icon">
                                            +
                                        </span>

                                        <span>
                                            <strong>Chọn hình ảnh mới</strong>

                                            <small>
                                                Không chọn ảnh mới thì giữ nguyên ảnh hiện tại.
                                                Nếu chọn, bộ ảnh hiện tại sẽ được thay thế.
                                                Tối đa 5 ảnh · 4MB/ảnh.
                                            </small>
                                        </span>
                                    </label>

                                    <input
                                        id="review-edit-images"
                                        class="product-review-file-input"
                                        type="file"
                                        name="images[]"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        multiple
                                    >

                                    @error('images')
                                        <p class="product-review-field-error">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                    @error('images.*')
                                        <p class="product-review-field-error">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="product-review-edit-actions">
                                    <button
                                        type="button"
                                        class="product-review-edit-cancel"
                                        onclick="this.closest('details').removeAttribute('open')"
                                    >
                                        Hủy
                                    </button>

                                    <button
                                        type="submit"
                                        class="product-review-submit"
                                    >
                                        Cập nhật đánh giá
                                    </button>
                                </div>
                            </form>
                        </div>
                    </details>

                {{-- Đã mua + delivered + chưa review --}}
                @elseif ($canReview)
                    <div class="product-review-form-heading">
                        <div>
                            <h3>Viết đánh giá của bạn</h3>

                            <p>
                                Chọn số sao và chia sẻ trải nghiệm
                                thực tế của bạn.
                            </p>
                        </div>
                    </div>

                    <form
                        action="{{ route('products.reviews.store', $product) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="product-review-form"
                    >
                        @csrf

                        {{-- Rating --}}
                        <div class="product-review-field">
                            <label class="product-review-field-label">
                                Đánh giá của bạn
                                <span>*</span>
                            </label>

                            <div class="product-review-star-input">
                                @for ($rating = 5; $rating >= 1; $rating--)
                                    <input
                                        type="radio"
                                        id="review-rating-{{ $rating }}"
                                        name="rating"
                                        value="{{ $rating }}"
                                        {{ (int) old('rating') === $rating ? 'checked' : '' }}
                                    >

                                    <label
                                        for="review-rating-{{ $rating }}"
                                        title="{{ $rating }} sao"
                                        aria-label="{{ $rating }} sao"
                                    >
                                        ★
                                    </label>
                                @endfor
                            </div>

                            @error('rating')
                                <p class="product-review-field-error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Comment --}}
                        <div class="product-review-field">
                            <label
                                class="product-review-field-label"
                                for="review-comment"
                            >
                                Nội dung đánh giá
                                <small>Không bắt buộc</small>
                            </label>

                            <textarea
                                id="review-comment"
                                name="comment"
                                rows="5"
                                maxlength="2000"
                                placeholder="Chia sẻ trải nghiệm thực tế của bạn về sản phẩm..."
                            >{{ old('comment') }}</textarea>

                            <div class="product-review-field-help">
                                Tối đa 2000 ký tự.
                            </div>

                            @error('comment')
                                <p class="product-review-field-error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Images --}}
                        <div class="product-review-field">
                            <label
                                class="product-review-field-label"
                                for="review-images"
                            >
                                Hình ảnh
                                <small>Không bắt buộc</small>
                            </label>

                            <label
                                class="product-review-upload"
                                for="review-images"
                            >
                                <span class="product-review-upload-icon">
                                    +
                                </span>

                                <span>
                                    <strong>Thêm hình ảnh</strong>

                                    <small>
                                        JPG, JPEG, PNG, WEBP · tối đa
                                        5 ảnh · 4MB/ảnh
                                    </small>
                                </span>
                            </label>

                            <input
                                id="review-images"
                                class="product-review-file-input"
                                type="file"
                                name="images[]"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                multiple
                            >

                            @error('images')
                                <p class="product-review-field-error">
                                    {{ $message }}
                                </p>
                            @enderror

                            @error('images.*')
                                <p class="product-review-field-error">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="product-review-form-actions">
                            <button
                                type="submit"
                                class="product-review-submit"
                            >
                                Gửi đánh giá
                            </button>
                        </div>
                    </form>
                @endif
            @endguest

        </div>

        {{-- ================================================
            REVIEW LIST
        ================================================= --}}
        <div class="product-review-list">

            <div class="product-review-list-heading">
                <h3>
                    Đánh giá từ khách hàng
                </h3>

                @if ($reviewCount > 0)
                    <span>
                        {{ $reviewCount }} đánh giá
                    </span>
                @endif
            </div>

            @forelse ($reviews as $review)
                <article class="product-review-item">

                    <div class="product-review-avatar">
                        {{
                            mb_strtoupper(
                                mb_substr(
                                    $review->user?->name ?? 'K',
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>

                    <div class="product-review-body">

                        <div class="product-review-user-row">
                            <div>
                                <strong class="product-review-user-name">
                                    {{ $review->user?->name ?? 'Khách hàng' }}
                                </strong>

                                <div
                                    class="product-review-item-stars"
                                    aria-label="{{ $review->rating }} trên 5 sao"
                                >
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $review->rating ? 'is-active' : '' }}">
                                            ★
                                        </span>
                                    @endfor
                                </div>
                            </div>

                            <time
                                datetime="{{ $review->created_at?->toDateString() }}"
                            >
                                {{ $review->created_at?->format('d/m/Y') }}
                            </time>
                        </div>

                        @if ($review->comment)
                            <p class="product-review-comment">
                                {{ $review->comment }}
                            </p>
                        @endif

                        @if (!empty($review->images))
                            <div class="product-review-images">
                                @foreach ($review->images as $reviewImage)
                                    <a
                                        href="{{ $imageUrl($reviewImage) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        <img
                                            src="{{ $imageUrl($reviewImage) }}"
                                            alt="Ảnh đánh giá của {{ $review->user?->name ?? 'khách hàng' }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </a>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </article>
            @empty
                <div class="product-review-empty">
                    <span>☆</span>

                    <strong>
                        Chưa có đánh giá nào
                    </strong>

                    <p>
                        Hãy là người đầu tiên chia sẻ trải nghiệm
                        về sản phẩm này.
                    </p>
                </div>
            @endforelse

            @if ($reviews->hasPages())
                <div class="product-review-pagination">
                    {{ $reviews->fragment('product-reviews')->links() }}
                </div>
            @endif

        </div>
    </section>

    {{-- =========================================================
        IMAGE MODAL
    ========================================================== --}}
    @if ($mainImage)
        <div
            id="product-image-modal"
            class="product-image-modal is-hidden"
            aria-hidden="true"
        >
            <button
                id="product-image-modal-close"
                class="product-modal-close"
                type="button"
                aria-label="Đóng"
            >
                ×
            </button>

            <img
                id="product-image-modal-image"
                src="{{ $mainImage }}"
                alt="{{ $product->name }}"
            >
        </div>
    @endif
</div>

@endsection
