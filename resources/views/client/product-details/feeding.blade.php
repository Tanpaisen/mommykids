@php
    /*
     * Template riêng cho category: an-dam-dinh-duong
     * Có 3 subtype chính: bột/ngũ cốc, bánh/snack và cháo/bữa ăn sẵn.
     */

    $attributeNames = $attributeTags
        ->pluck('name')
        ->filter()
        ->map(fn ($name) => trim($name))
        ->values();

    $findAttribute = function (array $keywords) use ($attributeNames) {
        return $attributeNames->first(function ($name) use ($keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($name, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        });
    };

    $productNameLower = mb_strtolower(trim($product->name ?? ''));

    $isPorridge =
        str_contains($productNameLower, 'cháo') ||
        $attributeNames->contains(
            fn ($name) => mb_stripos($name, 'cháo') !== false
        );

    $isSnack =
        !$isPorridge &&
        (
            str_contains($productNameLower, 'bánh') ||
            str_contains($productNameLower, 'snack') ||
            $attributeNames->contains(function ($name) {
                $value = mb_strtolower(trim($name));

                return str_contains($value, 'bánh') ||
                    str_contains($value, 'snack');
            })
        );

    $isCereal =
        !$isPorridge &&
        !$isSnack &&
        (
            str_contains($productNameLower, 'bột ăn dặm') ||
            str_contains($productNameLower, 'ngũ cốc') ||
            $attributeNames->contains(function ($name) {
                $value = mb_strtolower(trim($name));

                return str_contains($value, 'bột ăn dặm') ||
                    str_contains($value, 'ngũ cốc');
            })
        );

    $feedingType = $findAttribute([
        'bột ăn dặm',
        'ngũ cốc ăn dặm',
        'bánh ăn dặm',
        'bánh gạo',
        'snack ăn dặm',
        'cháo tươi',
        'cháo ăn dặm',
    ]);

    if (!$feedingType) {
        $feedingType = match (true) {
            $isPorridge => 'Cháo / bữa ăn sẵn',
            $isSnack => 'Bánh / snack ăn dặm',
            $isCereal => 'Bột / ngũ cốc ăn dặm',
            default => 'Thực phẩm ăn dặm',
        };
    }

    $feedingFlavor = $findAttribute([
        'vị ngũ cốc',
        'vị chuối',
        'cá hồi rau củ',
        'gạo sữa',
        'vị bí đỏ',
        'vị rau củ',
        'bí đỏ',
        'chuối',
        'rau củ',
    ]);

    $feedingFeature = $findAttribute([
        'organic',
        'hữu cơ',
        'tự cầm ăn',
        'tập nhai',
        'ăn liền',
        'tiện lợi',
    ]);

    $feedingAgeAttribute = $attributeNames->first(function ($name) {
        return preg_match(
            '/(?:từ\s*)?\d+\s*(?:-|–|đến)\s*\d+\s*tháng|từ\s*\d+\s*tháng|\d+\+\s*tháng|giai đoạn ăn dặm/iu',
            $name
        );
    });

    $feedingAgeText =
        $ageText
        ?: (
            $product->stages->isNotEmpty()
                ? $product->stages
                    ->sortBy('sort_order')
                    ->pluck('name')
                    ->join(', ')
                : $feedingAgeAttribute
        );

    if (!$feedingAgeText) {
        $feedingAgeText = $isSnack
            ? 'Giai đoạn tập nhai / tự ăn'
            : 'Giai đoạn ăn dặm';
    }

    $highlightData = is_array($product->highlights)
        ? $product->highlights
        : [];

    $feedingBenefits = collect(data_get($highlightData, 'items', []))
        ->filter(fn ($item) => filled(data_get($item, 'title')))
        ->take(4)
        ->map(fn ($item) => [
            'title' => data_get($item, 'title'),
            'subtitle' => data_get($item, 'subtitle') ?: 'Đặc điểm sản phẩm',
            'icon' => data_get($item, 'icon') ?: 'check',
        ])
        ->values();

    $fallbackBenefits = collect(
        match (true) {
            $isSnack => [
                ['title' => 'Dễ cầm nắm', 'subtitle' => 'Hỗ trợ bé tự ăn', 'icon' => 'hand'],
                ['title' => 'Khẩu phần nhỏ', 'subtitle' => 'Dễ chia theo nhu cầu', 'icon' => 'portion'],
                ['title' => 'Tiện mang theo', 'subtitle' => 'Phù hợp bữa phụ', 'icon' => 'bag'],
                ['title' => 'Giai đoạn tập nhai', 'subtitle' => $feedingAgeText, 'icon' => 'baby'],
            ],
            $isPorridge => [
                ['title' => 'Bữa ăn tiện lợi', 'subtitle' => 'Chuẩn bị nhanh', 'icon' => 'bowl'],
                ['title' => 'Hương vị dễ ăn', 'subtitle' => $feedingFlavor ?: 'Theo sản phẩm', 'icon' => 'leaf'],
                ['title' => 'Dễ sử dụng', 'subtitle' => 'Phù hợp bữa ăn dặm', 'icon' => 'spoon'],
                ['title' => 'Giai đoạn ăn dặm', 'subtitle' => $feedingAgeText, 'icon' => 'baby'],
            ],
            default => [
                ['title' => 'Dễ chuẩn bị', 'subtitle' => 'Phù hợp bữa ăn dặm', 'icon' => 'bowl'],
                ['title' => 'Ngũ cốc', 'subtitle' => $feedingFlavor ?: 'Theo sản phẩm', 'icon' => 'grain'],
                ['title' => 'Khẩu phần linh hoạt', 'subtitle' => 'Pha theo hướng dẫn bao bì', 'icon' => 'spoon'],
                ['title' => 'Giai đoạn ăn dặm', 'subtitle' => $feedingAgeText, 'icon' => 'baby'],
            ],
        }
    );

    foreach ($fallbackBenefits as $benefit) {
        if ($feedingBenefits->count() >= 4) {
            break;
        }

        $feedingBenefits->push($benefit);
    }

    $highlightMessage = data_get($highlightData, 'message')
        ?: 'Thông tin được trình bày theo đúng nhóm sản phẩm ăn dặm';

    $highlightSubmessage = data_get($highlightData, 'submessage')
        ?: 'Luôn ưu tiên hướng dẫn trên bao bì của đúng sản phẩm khi pha, dùng và bảo quản.';

    $feedingIngredientItems = collect(
        preg_split('/\r\n|\r|\n/', trim($product->ingredients ?? ''))
    )
        ->map(function ($line) {
            $line = trim($line);

            if ($line === '') {
                return null;
            }

            $parts = array_map('trim', explode('|', $line, 2));

            return [
                'title' => $parts[0] ?: 'Thành phần',
                'description' => $parts[1] ?? '',
            ];
        })
        ->filter()
        ->take(6)
        ->values();

    $feedingUsageSlots = collect(range(0, 3))
        ->map(fn ($index) => $usageSteps->get($index));

    $feedingExtraUsage = $usageSteps
        ->slice(4)
        ->filter()
        ->values();

    $stepTitles = collect(
        match (true) {
            $isPorridge => [
                'Kiểm tra bao bì',
                'Chuẩn bị khẩu phần',
                'Làm ấm nếu cần',
                'Kiểm tra trước khi ăn',
            ],
            $isSnack => [
                'Rửa tay sạch',
                'Cho bé ngồi thẳng',
                'Chia lượng phù hợp',
                'Quan sát khi bé ăn',
            ],
            default => [
                'Chuẩn bị dụng cụ',
                'Pha theo hướng dẫn',
                'Khuấy đều',
                'Kiểm tra trước khi ăn',
            ],
        }
    );

    $ingredientTitle = match (true) {
        $isPorridge => 'Thành phần chính',
        $isSnack => 'Thành phần & đặc điểm',
        default => 'Thành phần & dinh dưỡng',
    };

    $usageTitle = match (true) {
        $isPorridge => 'Hướng dẫn sử dụng',
        $isSnack => 'Cách dùng an toàn',
        default => 'Hướng dẫn pha / chế biến',
    };
@endphp

<section class="product-long-content product-feeding-detail">

    {{-- =========================================================
        TỔNG QUAN SẢN PHẨM
        Gộp chi tiết + mô tả trong một shell để bố cục cân đối hơn.
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-feeding-overview-card">
        <div class="product-feeding-overview-grid">

            <section class="product-feeding-overview-spec">
                <div class="product-section-heading product-feeding-heading">
                    <span class="product-feeding-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 12h14"></path>
                            <path d="M7 12c0 4 2 7 5 7s5-3 5-7"></path>
                            <path d="M9 8c0-2 1.4-4 3-4s3 2 3 4"></path>
                        </svg>
                    </span>
                    <div>
                        <span class="product-feeding-eyebrow">Thông tin sản phẩm</span>
                        <h2>Chi tiết ăn dặm &amp; dinh dưỡng</h2>
                    </div>
                </div>

                <div class="product-spec-table product-feeding-spec-table">
                    <div class="product-spec-row">
                        <strong>Tên sản phẩm</strong>
                        <span>{{ $product->name }}</span>
                    </div>

                    @if ($brandTag?->name)
                        <div class="product-spec-row">
                            <strong>Thương hiệu</strong>
                            <span>{{ $brandTag->name }}</span>
                        </div>
                    @endif

                    <div class="product-spec-row">
                        <strong>Nhóm sản phẩm</strong>
                        <span>{{ $feedingType }}</span>
                    </div>

                    @if ($feedingFlavor)
                        <div class="product-spec-row">
                            <strong>Hương vị</strong>
                            <span>{{ $feedingFlavor }}</span>
                        </div>
                    @endif

                    @if ($feedingFeature)
                        <div class="product-spec-row">
                            <strong>Đặc điểm</strong>
                            <span>{{ $feedingFeature }}</span>
                        </div>
                    @endif

                    <div class="product-spec-row">
                        <strong>Độ tuổi phù hợp</strong>
                        <span>{{ $feedingAgeText }}</span>
                    </div>

                    @if ($product->weight_grams)
                        <div class="product-spec-row">
                            <strong>Khối lượng</strong>
                            <span>{{ number_format($product->weight_grams, 0, ',', '.') }} g</span>
                        </div>
                    @endif

                    @if ($product->origin)
                        <div class="product-spec-row">
                            <strong>Xuất xứ</strong>
                            <span>{{ $product->origin }}</span>
                        </div>
                    @endif

                    @if ($product->manufacturer)
                        <div class="product-spec-row">
                            <strong>Nhà sản xuất</strong>
                            <span>{{ $product->manufacturer }}</span>
                        </div>
                    @endif

                    <div class="product-spec-row">
                        <strong>Tình trạng</strong>
                        <span class="{{ $product->stock > 0 ? 'is-stock' : 'is-out' }}">
                            {{ $product->stock > 0 ? 'Còn ' . $product->stock . ' sản phẩm' : 'Hết hàng' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="product-feeding-overview-description">
                <div class="product-section-heading product-feeding-heading">
                    <span class="product-feeding-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 4h12v16H6z"></path>
                            <path d="M9 8h6M9 12h6M9 16h4"></path>
                        </svg>
                    </span>
                    <div>
                        <span class="product-feeding-eyebrow">Tổng quan</span>
                        <h2>Mô tả sản phẩm</h2>
                    </div>
                </div>

                <div class="product-description-text product-feeding-description-text">
                    {{ $product->description ?: 'Thông tin mô tả sản phẩm đang được bổ sung.' }}
                </div>

                <div class="product-feeding-benefit-grid">
                    @foreach ($feedingBenefits as $benefit)
                        <div class="product-feeding-benefit-item">
                            <span class="product-feeding-benefit-icon" aria-hidden="true">
                                @if ($benefit['icon'] === 'grain')
                                    <svg viewBox="0 0 24 24"><path d="M12 20V5"></path><path d="M12 8c-3 0-5-2-5-5 3 0 5 2 5 5Z"></path><path d="M12 12c3 0 5-2 5-5-3 0-5 2-5 5Z"></path><path d="M12 16c-3 0-5-2-5-5 3 0 5 2 5 5Z"></path></svg>
                                @elseif ($benefit['icon'] === 'baby')
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"></circle><path d="M7 20c.5-4 2-6 5-6s4.5 2 5 6"></path><path d="M9 5c1-2 4-2 5 0"></path></svg>
                                @elseif ($benefit['icon'] === 'hand')
                                    <svg viewBox="0 0 24 24"><path d="M8 12V7a1.5 1.5 0 0 1 3 0v4"></path><path d="M11 11V6a1.5 1.5 0 0 1 3 0v5"></path><path d="M14 11V8a1.5 1.5 0 0 1 3 0v5"></path><path d="M8 11 6.5 9.5a1.5 1.5 0 0 0-2 2L9 18c1 1.4 2.3 2 4 2h1c3 0 5-2 5-5v-3"></path></svg>
                                @elseif ($benefit['icon'] === 'bowl')
                                    <svg viewBox="0 0 24 24"><path d="M4 11h16c0 5-3 8-8 8s-8-3-8-8Z"></path><path d="M8 21h8"></path><path d="M15 4c-2 1-3 3-3 5"></path></svg>
                                @elseif ($benefit['icon'] === 'spoon')
                                    <svg viewBox="0 0 24 24"><ellipse cx="8" cy="6" rx="3" ry="4"></ellipse><path d="m10 9 8 11"></path></svg>
                                @elseif ($benefit['icon'] === 'leaf')
                                    <svg viewBox="0 0 24 24"><path d="M19 4C11 4 6 8 6 14c0 3 2 5 5 5 6 0 8-7 8-15Z"></path><path d="M6 20c2-5 5-8 10-11"></path></svg>
                                @elseif ($benefit['icon'] === 'bag')
                                    <svg viewBox="0 0 24 24"><path d="M6 8h12l1 12H5L6 8Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path></svg>
                                @elseif ($benefit['icon'] === 'portion')
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M12 4v8l6 4"></path></svg>
                                @else
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg>
                                @endif
                            </span>

                            <div>
                                <strong>{{ $benefit['title'] }}</strong>
                                <span>{{ $benefit['subtitle'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="product-feeding-trust-banner">
                    <span class="product-feeding-trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>

                    <div>
                        <strong>{{ $highlightMessage }}</strong>
                        <span>{{ $highlightSubmessage }}</span>
                    </div>
                </div>
            </section>

        </div>
    </article>

    {{-- =========================================================
        THÀNH PHẦN
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-feeding-ingredient-card">
        <div class="product-feeding-section-head">
            <div class="product-feeding-section-title">
                <span class="product-feeding-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20V5"></path>
                        <path d="M12 8c-3 0-5-2-5-5 3 0 5 2 5 5Z"></path>
                        <path d="M12 12c3 0 5-2 5-5-3 0-5 2-5 5Z"></path>
                    </svg>
                </span>
                <div>
                    <span class="product-feeding-eyebrow">Thành phần</span>
                    <h2>{{ $ingredientTitle }}</h2>
                </div>
            </div>

            <p>Thông tin tóm tắt theo dữ liệu hiện có của sản phẩm.</p>
        </div>

        @if ($feedingIngredientItems->isNotEmpty())
            <div class="product-feeding-ingredient-grid">
                @foreach ($feedingIngredientItems as $ingredient)
                    <div class="product-feeding-ingredient-item">
                        <span class="product-feeding-ingredient-index">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="product-feeding-ingredient-icon" aria-hidden="true">
                            @if ($loop->iteration % 3 === 1)
                                <svg viewBox="0 0 24 24"><path d="M12 20V5"></path><path d="M12 9c-3 0-5-2-5-5 3 0 5 2 5 5Z"></path><path d="M12 13c3 0 5-2 5-5-3 0-5 2-5 5Z"></path></svg>
                            @elseif ($loop->iteration % 3 === 2)
                                <svg viewBox="0 0 24 24"><path d="M5 12h14"></path><path d="M7 12c0 4 2 7 5 7s5-3 5-7"></path><path d="M9 9c1-2 2-3 3-5"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M8 12h8M12 8v8"></path></svg>
                            @endif
                        </span>

                        <div>
                            <strong>{{ $ingredient['title'] }}</strong>
                            <span>{{ $ingredient['description'] ?: 'Xem thông tin chi tiết trên bao bì sản phẩm.' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="product-feeding-empty-state">
                <strong>Thành phần chi tiết theo bao bì</strong>
                <span>Vui lòng đối chiếu nhãn của đúng biến thể sản phẩm trước khi sử dụng.</span>
            </div>
        @endif
    </article>

    {{-- =========================================================
        HƯỚNG DẪN
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-feeding-guide-card">
        <div class="product-feeding-section-head">
            <div class="product-feeding-section-title">
                <span class="product-feeding-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M7 4h10v16H7z"></path>
                        <path d="M10 2h4v3h-4z"></path>
                        <path d="M10 10h4M10 14h4"></path>
                    </svg>
                </span>
                <div>
                    <span class="product-feeding-eyebrow">Sử dụng đúng cách</span>
                    <h2>{{ $usageTitle }}</h2>
                </div>
            </div>

            <p>Thực hiện theo từng bước và ưu tiên hướng dẫn trên bao bì.</p>
        </div>

        <div class="product-feeding-steps">
            @foreach ($feedingUsageSlots as $step)
                <div class="product-feeding-step">
                    <div class="product-feeding-step-head">
                        <span class="product-feeding-step-number">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <span class="product-feeding-step-line" aria-hidden="true"></span>
                        <span class="product-feeding-step-icon" aria-hidden="true">
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24"><path d="M8 5h8"></path><path d="M7 8h10v11H7z"></path><path d="M9 3h6v5H9z"></path></svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24"><path d="M5 12h14"></path><path d="M7 12c0 4 2 7 5 7s5-3 5-7"></path><path d="M15 4c-2 1-3 3-3 5"></path></svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24"><ellipse cx="8" cy="6" rx="3" ry="4"></ellipse><path d="m10 9 8 11"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="m8.5 12 2.2 2.2 4.8-5"></path></svg>
                            @endif
                        </span>
                    </div>

                    <strong>{{ $stepTitles->get($loop->index) }}</strong>
                    <p>{{ $step ?: 'Thực hiện theo hướng dẫn ghi trên bao bì của đúng sản phẩm.' }}</p>
                </div>
            @endforeach
        </div>
    </article>

    {{-- =========================================================
        BẢO QUẢN / LƯU Ý / THÔNG TIN THÊM
    ========================================================== --}}
    <div class="product-feeding-info-grid">
        <article class="product-feeding-info-card">
            <div class="product-feeding-info-title">
                <span class="product-feeding-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </span>
                <div>
                    <span>Bảo quản</span>
                    <h3>Giữ sản phẩm đúng cách</h3>
                </div>
            </div>

            @if ($storageItems->isNotEmpty())
                <ul>
                    @foreach ($storageItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p>Bảo quản nơi khô ráo, thoáng mát và làm theo hướng dẫn trên bao bì.</p>
            @endif
        </article>

        <article class="product-feeding-info-card is-warning">
            <div class="product-feeding-info-title">
                <span class="product-feeding-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 22 20H2L12 3Z"></path>
                        <path d="M12 9v5"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </span>
                <div>
                    <span>Lưu ý</span>
                    <h3>An toàn khi sử dụng</h3>
                </div>
            </div>

            @if ($warningItems->isNotEmpty())
                <ul>
                    @foreach ($warningItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p>Kiểm tra độ tuổi, thành phần và cảnh báo dị ứng trên bao bì trước khi cho bé sử dụng.</p>
            @endif
        </article>

        <article class="product-feeding-info-card is-guide">
            <div class="product-feeding-info-title">
                <span class="product-feeding-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3"></circle>
                        <path d="M7 20c.5-4 2-6 5-6s4.5 2 5 6"></path>
                    </svg>
                </span>
                <div>
                    <span>Giai đoạn</span>
                    <h3>{{ $feedingAgeText }}</h3>
                </div>
            </div>

            <ul>
                @if ($isSnack)
                    <li>Cho bé ăn khi ngồi thẳng và luôn có người lớn quan sát.</li>
                    <li>Chia lượng nhỏ, phù hợp khả năng nhai và tự ăn của bé.</li>
                @elseif ($isPorridge)
                    <li>Kiểm tra nhiệt độ trước khi cho bé ăn.</li>
                    <li>Phần đã mở hoặc đã làm nóng nên xử lý theo hướng dẫn trên bao bì.</li>
                @else
                    <li>Pha lượng vừa đủ cho một bữa ăn.</li>
                    <li>Không tự thay đổi tỷ lệ pha nếu bao bì có hướng dẫn cụ thể.</li>
                @endif

                @foreach ($feedingExtraUsage->take(2) as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>
    </div>
</section>
