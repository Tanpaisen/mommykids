@php
    /*
     * Template riêng cho category: binh-sua-phu-kien
     * Chỉ thay phần nội dung dài, không đụng hero / related / review.
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

    /*
     * Subtype riêng trong category Bình sữa & phụ kiện.
     * Thứ tự nhận diện quan trọng vì "Máy tiệt trùng bình sữa"
     * có chứa cụm "bình sữa" nhưng không phải bình sữa.
     */
    $isSterilizer =
        str_contains($productNameLower, 'máy tiệt trùng') ||
        $attributeNames->contains(
            fn ($name) => mb_stripos($name, 'máy tiệt trùng') !== false
        );

    $isTrainingCup =
        str_contains($productNameLower, 'cốc tập uống') ||
        $attributeNames->contains(
            fn ($name) => mb_stripos($name, 'cốc tập uống') !== false
        );

    $isFeedingSet =
        str_contains($productNameLower, 'bộ bát thìa') ||
        $attributeNames->contains(
            fn ($name) => mb_stripos($name, 'bộ bát thìa') !== false
        );

    $isFeedingBottle =
        !$isSterilizer &&
        !$isTrainingCup &&
        !$isFeedingSet &&
        (
            str_starts_with($productNameLower, 'bình sữa') ||
            $attributeNames->contains(
                fn ($name) => mb_strtolower(trim($name)) === 'bình sữa'
            )
        );

    $bottleType = $findAttribute([
        'bình sữa',
        'bình tập uống',
        'núm ti',
        'núm vú',
        'cọ rửa',
        'máy tiệt trùng',
        'túi trữ sữa',
        'phụ kiện',
        'cốc tập uống',
        'bộ bát thìa',
    ]);

    if (!$bottleType) {
        $bottleType = match (true) {
            $isSterilizer => 'Máy tiệt trùng',
            $isTrainingCup => 'Cốc tập uống',
            $isFeedingSet => 'Bộ bát thìa',
            $isFeedingBottle => 'Bình sữa',
            default => null,
        };
    }

    $bottleCapacity = $attributeNames->first(function ($name) {
        return preg_match('/\b\d+(?:[.,]\d+)?\s*(?:ml|mL|ML)\b/u', $name);
    });

    $bottleMaterial = $findAttribute([
        'silicone',
        'silicon',
        'nhựa pp',
        'polypropylene',
        'ppsu',
        'pes',
        'tritan',
        'thủy tinh',
        'thuỷ tinh',
        'nhựa',
    ]);

    $bottleNipple = $findAttribute([
        'núm ti',
        'núm vú',
        'dòng chảy',
        'flow',
        'natural response',
        'natural',
    ]);

    /*
     * Thông số riêng cho máy tiệt trùng.
     * Chỉ đọc từ attribute tags đang có; không tự dựng số liệu.
     */
    $sterilizerTechnology = $findAttribute([
        'uv-c',
        'uvc',
        'uv',
    ]);

    $sterilizerType = $findAttribute([
        'mini',
        'để bàn',
        'di động',
    ]);

    $sterilizerPower = $attributeNames->first(function ($name) {
        return preg_match('/\b\d+(?:[.,]\d+)?\s*w\b/iu', $name);
    });

    $sterilizerCapacity = $attributeNames->first(function ($name) {
        return preg_match(
            '/\b\d+(?:[.,]\d+)?\s*(?:l|lít|lit)\b/iu',
            $name
        );
    });

    $sterilizerVoltage = $attributeNames->first(function ($name) {
        return preg_match(
            '/\b\d+(?:[.,]\d+)?\s*v(?:olt)?\b/iu',
            $name
        );
    });

    /*
     * Cốc tập uống.
     */
    $trainingCupFeature = $findAttribute([
        'chống đổ',
        'chống tràn',
        '360',
    ]);

    /*
     * Bộ bát thìa ăn dặm.
     */
    $feedingSetPurpose = $findAttribute([
        'dụng cụ ăn dặm',
        'ăn dặm',
    ]);

    $feedingSetPieces = $attributeNames->first(function ($name) {
        return preg_match(
            '/\b\d+\s*(?:món|mon|chiếc|chiec)\b/iu',
            $name
        );
    });

    $bottleAgeAttribute = $attributeNames->first(function ($name) {
        return preg_match(
            '/(?:từ\s*)?\d+\s*(?:-|–|đến)\s*\d+\s*tháng|từ\s*\d+\s*tháng|sơ sinh/iu',
            $name
        );
    });

    $bottleAgeText =
        $ageText
        ?: (
            $product->stages->isNotEmpty()
                ? $product->stages
                    ->sortBy('sort_order')
                    ->pluck('name')
                    ->join(', ')
                : $bottleAgeAttribute
        );

    /*
     * Với sản phẩm generic không có mốc tháng chính xác,
     * dùng ngữ cảnh sử dụng thay vì bịa số tháng.
     */
    if (!$bottleAgeText && $isTrainingCup) {
        $bottleAgeText = 'Giai đoạn tập uống';
    }

    if (!$bottleAgeText && $isFeedingSet) {
        $bottleAgeText = 'Giai đoạn ăn dặm';
    }

    $highlightData = is_array($product->highlights)
        ? $product->highlights
        : [];

    $bottleBenefits = collect(data_get($highlightData, 'items', []))
        ->filter(fn ($item) => filled(data_get($item, 'title')))
        ->take(4)
        ->map(fn ($item) => [
            'title' => data_get($item, 'title'),
            'subtitle' => data_get($item, 'subtitle') ?: 'Thông tin nổi bật',
            'icon' => data_get($item, 'icon') ?: 'check',
        ])
        ->values();

    while ($bottleBenefits->count() < 4) {
        $bottleBenefits->push([
            'title' => 'Đang cập nhật',
            'subtitle' => 'Thông tin nổi bật',
            'icon' => 'check',
        ]);
    }

    $highlightMessage = data_get($highlightData, 'message')
        ?: 'Thông tin sản phẩm đang được cập nhật';

    $highlightSubmessage = data_get($highlightData, 'submessage')
        ?: 'MommyKids chỉ hiển thị những thông tin hiện có của sản phẩm.';

    $bottleStructureItems = collect(
        preg_split('/\r\n|\r|\n/', trim($product->ingredients ?? ''))
    )
        ->map(function ($line) {
            $line = trim($line);

            if ($line === '') {
                return null;
            }

            $parts = array_map('trim', explode('|', $line, 2));

            return [
                'title' => $parts[0] ?: 'Thông tin cấu tạo',
                'description' => $parts[1] ?? '',
            ];
        })
        ->filter()
        ->take(4)
        ->values();

    $bottleUsageSlots = collect(range(0, 3))
        ->map(fn ($index) => $usageSteps->get($index));

    $bottleCleaningItems = $usageSteps
        ->slice(4)
        ->filter()
        ->values();

    $stepTitles = collect(
        match (true) {
            $isSterilizer => [
                'Làm sạch vật dụng',
                'Xếp vào khoang',
                'Chạy chu trình',
                'Hoàn tất',
            ],
            $isTrainingCup => [
                'Rửa sạch',
                'Lắp cốc',
                'Cho nước',
                'Tập uống',
            ],
            $isFeedingSet => [
                'Rửa sạch',
                'Chuẩn bị thức ăn',
                'Cho bé ăn',
                'Vệ sinh',
            ],
            default => [
                'Rửa sạch',
                'Lắp bộ phận',
                'Chuẩn bị',
                'Kiểm tra',
            ],
        }
    );

    $cleaningTitle = match (true) {
        $isSterilizer => 'Vệ sinh thiết bị',
        $isTrainingCup => 'Vệ sinh sản phẩm',
        $isFeedingSet => 'Vệ sinh sản phẩm',
        default => 'Vệ sinh & tiệt trùng',
    };
@endphp

<section class="product-long-content product-bottle-detail product-bottle-pro">

    {{-- =========================================================
        CHI TIẾT + MÔ TẢ
    ========================================================== --}}
    <div class="product-content-grid product-bottle-top-grid">

        <article class="product-content-card product-bottle-spec-card">
            <div class="product-section-heading product-bottle-heading">
                <span class="product-bottle-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 3h6"></path>
                        <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                        <path d="M9 12h6"></path>
                    </svg>
                </span>
                <h2>Chi tiết sản phẩm</h2>
            </div>

            <div class="product-spec-table product-bottle-spec-table">
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

                @if ($isSterilizer)
                    {{-- MÁY TIỆT TRÙNG --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $bottleType ?: 'Máy tiệt trùng' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Công nghệ</strong>
                        <span>{{ $sterilizerTechnology ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Kiểu máy</strong>
                        <span>{{ $sterilizerType ?: 'Đang cập nhật' }}</span>
                    </div>

                    @if ($sterilizerPower)
                        <div class="product-spec-row">
                            <strong>Công suất</strong>
                            <span>{{ $sterilizerPower }}</span>
                        </div>
                    @endif

                    @if ($sterilizerCapacity)
                        <div class="product-spec-row">
                            <strong>Dung tích khoang</strong>
                            <span>{{ $sterilizerCapacity }}</span>
                        </div>
                    @endif

                    @if ($sterilizerVoltage)
                        <div class="product-spec-row">
                            <strong>Nguồn điện</strong>
                            <span>{{ $sterilizerVoltage }}</span>
                        </div>
                    @endif

                @elseif ($isTrainingCup)
                    {{-- CỐC TẬP UỐNG --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $bottleType ?: 'Cốc tập uống' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Dung tích</strong>
                        <span>{{ $bottleCapacity ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Đặc tính</strong>
                        <span>{{ $trainingCupFeature ?: 'Đang cập nhật' }}</span>
                    </div>

                    @if ($bottleMaterial)
                        <div class="product-spec-row">
                            <strong>Chất liệu</strong>
                            <span>{{ $bottleMaterial }}</span>
                        </div>
                    @endif

                    <div class="product-spec-row">
                        <strong>Độ tuổi phù hợp</strong>
                        <span>{{ $bottleAgeText }}</span>
                    </div>

                @elseif ($isFeedingSet)
                    {{-- BỘ BÁT THÌA ĂN DẶM --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $bottleType ?: 'Bộ bát thìa' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Chất liệu</strong>
                        <span>{{ $bottleMaterial ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Mục đích sử dụng</strong>
                        <span>{{ $feedingSetPurpose ?: 'Dụng cụ ăn dặm' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Thành phần bộ</strong>
                        <span>{{ $feedingSetPieces ?: 'Bát + thìa' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Độ tuổi phù hợp</strong>
                        <span>{{ $bottleAgeText }}</span>
                    </div>

                @elseif ($isFeedingBottle)
                    {{-- BÌNH SỮA --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $bottleType ?: 'Bình sữa' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Dung tích</strong>
                        <span>{{ $bottleCapacity ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Chất liệu</strong>
                        <span>{{ $bottleMaterial ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Loại núm ti</strong>
                        <span>{{ $bottleNipple ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Độ tuổi phù hợp</strong>
                        <span>{{ $bottleAgeText ?: 'Đang cập nhật' }}</span>
                    </div>

                @else
                    {{-- PHỤ KIỆN KHÁC --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $bottleType ?: 'Phụ kiện' }}</span>
                    </div>

                    @if ($bottleMaterial)
                        <div class="product-spec-row">
                            <strong>Chất liệu</strong>
                            <span>{{ $bottleMaterial }}</span>
                        </div>
                    @endif

                    @if ($product->weight_grams)
                        <div class="product-spec-row">
                            <strong>Khối lượng</strong>
                            <span>{{ number_format($product->weight_grams, 0, ',', '.') }} g</span>
                        </div>
                    @endif
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
        </article>

        <article class="product-content-card product-description-card product-bottle-description-card">
            <div class="product-section-heading product-bottle-heading">
                <span class="product-bottle-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 4h14v16H5z"></path>
                        <path d="M8 8h8M8 12h8M8 16h5"></path>
                    </svg>
                </span>
                <h2>Mô tả sản phẩm</h2>
            </div>

            <div class="product-description-text">
                {{ $product->description ?: 'Thông tin mô tả sản phẩm đang được cập nhật.' }}
            </div>

            <div class="product-bottle-benefit-grid">
                @foreach ($bottleBenefits as $benefit)
                    <div class="product-bottle-benefit-item">
                        <span class="product-bottle-benefit-icon">
                            @if ($benefit['icon'] === 'nipple')
                                <svg viewBox="0 0 24 24"><path d="M9 7c0-2 1.2-4 3-4s3 2 3 4"></path><path d="M7 9h10"></path><path d="M8 9c0 5-2 6-2 9h12c0-3-2-4-2-9"></path></svg>
                            @elseif ($benefit['icon'] === 'air')
                                <svg viewBox="0 0 24 24"><path d="M3 8h10c2.7 0 2.7-4 0-4"></path><path d="M3 12h15c3 0 3 4 0 4"></path><path d="M3 16h8"></path></svg>
                            @elseif ($benefit['icon'] === 'shield')
                                <svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path><path d="m9 12 2 2 4-4"></path></svg>
                            @elseif ($benefit['icon'] === 'wash')
                                <svg viewBox="0 0 24 24"><path d="M7 6h10v13H7z"></path><path d="M9 3h6v3H9z"></path><path d="M4 12c1.4-1.2 2.6-1.2 4 0"></path><path d="M16 12c1.4-1.2 2.6-1.2 4 0"></path></svg>
                            @elseif ($benefit['icon'] === 'heart')
                                <svg viewBox="0 0 24 24"><path d="M12 21S4 16.2 4 9.8A4.8 4.8 0 0 1 12 6a4.8 4.8 0 0 1 8 3.8C20 16.2 12 21 12 21Z"></path></svg>
                            @elseif ($benefit['icon'] === 'bottle')
                                <svg viewBox="0 0 24 24"><path d="M9 3h6"></path><path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path><path d="M9 12h6"></path></svg>
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

            <div class="product-trust-banner product-bottle-trust-banner">
                <div class="product-trust-banner-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 21S4 16.2 4 9.8A4.8 4.8 0 0 1 12 6a4.8 4.8 0 0 1 8 3.8C20 16.2 12 21 12 21Z"></path>
                    </svg>
                </div>

                <div class="product-trust-banner-content">
                    <strong>{{ $highlightMessage }}</strong>
                    <span>{{ $highlightSubmessage }}</span>
                </div>
            </div>
        </article>

    </div>

    {{-- =========================================================
        CHẤT LIỆU & CẤU TẠO - PROFESSIONAL
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-bottle-materials-pro">
        <div class="product-bottle-pro-heading">
            <div class="product-bottle-pro-heading-main">
                <span class="product-bottle-pro-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M9 3h6"></path>
                        <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                        <path d="M8 13h8"></path>
                    </svg>
                </span>

                <div>
                    <span class="product-bottle-pro-kicker">CẤU TẠO SẢN PHẨM</span>
                    <h2>Chất liệu &amp; cấu tạo</h2>
                </div>
            </div>

            <p>
                Các bộ phận và đặc điểm cấu tạo nổi bật giúp bạn hiểu rõ sản phẩm trước khi sử dụng.
            </p>
        </div>

        @if ($bottleStructureItems->isNotEmpty())
            <div class="product-bottle-materials-pro-grid">
                @foreach ($bottleStructureItems as $structure)
                    <article class="product-bottle-material-pro-item">
                        <div class="product-bottle-material-pro-top">
                            <span class="product-bottle-material-pro-index">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="product-bottle-material-pro-icon" aria-hidden="true">
                                @if ($loop->iteration === 1)
                                    <svg viewBox="0 0 24 24">
                                        <path d="M9 3h6"></path>
                                        <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                                        <path d="M8 13h8"></path>
                                    </svg>
                                @elseif ($loop->iteration === 2)
                                    <svg viewBox="0 0 24 24">
                                        <path d="M9 7c0-2 1.2-4 3-4s3 2 3 4"></path>
                                        <path d="M7 9h10"></path>
                                        <path d="M8 9c0 5-2 6-2 9h12c0-3-2-4-2-9"></path>
                                    </svg>
                                @elseif ($loop->iteration === 3)
                                    <svg viewBox="0 0 24 24">
                                        <path d="M3 8h10c2.7 0 2.7-4 0-4"></path>
                                        <path d="M3 12h15c3 0 3 4 0 4"></path>
                                        <path d="M3 16h8"></path>
                                    </svg>
                                @else
                                    <svg viewBox="0 0 24 24">
                                        <rect x="5" y="6" width="14" height="12" rx="2"></rect>
                                        <path d="M9 6V4h6v2"></path>
                                        <path d="m9 12 2 2 4-4"></path>
                                    </svg>
                                @endif
                            </span>
                        </div>

                        <div class="product-bottle-material-pro-content">
                            <strong>{{ $structure['title'] }}</strong>
                            <span>
                                {{ $structure['description'] ?: 'Thông tin chi tiết đang được cập nhật.' }}
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="product-empty-content">
                Thông tin chất liệu và cấu tạo đang được cập nhật.
            </p>
        @endif
    </article>

    {{-- =========================================================
        HƯỚNG DẪN SỬ DỤNG - PROFESSIONAL
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-bottle-usage-pro">
        <div class="product-bottle-pro-heading">
            <div class="product-bottle-pro-heading-main">
                <span class="product-bottle-pro-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M7 4h10v16H7z"></path>
                        <path d="M10 2h4v3h-4z"></path>
                        <path d="M10 10h4M10 14h4"></path>
                    </svg>
                </span>

                <div>
                    <span class="product-bottle-pro-kicker">QUY TRÌNH SỬ DỤNG</span>
                    <h2>Hướng dẫn sử dụng</h2>
                </div>
            </div>

            <p>
                Thực hiện theo từng bước để sử dụng sản phẩm đúng cách, thuận tiện và an toàn.
            </p>
        </div>

        <div class="product-bottle-usage-pro-grid">
            @foreach ($bottleUsageSlots as $step)
                <article class="product-bottle-usage-pro-step">
                    <div class="product-bottle-usage-pro-rail" aria-hidden="true">
                        <span class="product-bottle-usage-pro-number">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        @unless ($loop->last)
                            <span class="product-bottle-usage-pro-line"></span>
                        @endunless
                    </div>

                    <div class="product-bottle-usage-pro-card">
                        <span class="product-bottle-usage-pro-icon" aria-hidden="true">
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 3h6"></path>
                                    <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                                    <path d="M4 13c1.3-1.2 2.7-1.2 4 0"></path>
                                </svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 7c0-2 1.2-4 3-4s3 2 3 4"></path>
                                    <path d="M7 9h10"></path>
                                    <path d="M8 9c0 5-2 6-2 9h12c0-3-2-4-2-9"></path>
                                </svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 3s5 5.4 5 9a5 5 0 1 1-10 0c0-3.6 5-9 5-9Z"></path>
                                    <path d="M9.5 15c1 .6 4 .6 5 0"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="8"></circle>
                                    <path d="m8.5 12 2.2 2.2 4.8-5"></path>
                                </svg>
                            @endif
                        </span>

                        <div class="product-bottle-usage-pro-content">
                            <span class="product-bottle-usage-pro-label">
                                Bước {{ $loop->iteration }}
                            </span>
                            <strong>{{ $stepTitles->get($loop->index) }}</strong>
                            <p>{{ $step ?: 'Đang cập nhật hướng dẫn.' }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </article>

    {{-- =========================================================
        VỆ SINH / BẢO QUẢN / LƯU Ý
    ========================================================== --}}
    <div class="product-bottle-info-grid">

        <article class="product-bottle-info-card">
            <div class="product-bottle-info-title">
                <span class="product-bottle-info-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M7 6h10v13H7z"></path>
                        <path d="M9 3h6v3H9z"></path>
                        <path d="M4 12c1.4-1.2 2.6-1.2 4 0"></path>
                    </svg>
                </span>
                <h3>{{ $cleaningTitle }}</h3>
            </div>

            @if ($bottleCleaningItems->isNotEmpty())
                <ul>
                    @foreach ($bottleCleaningItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p class="product-empty-content">Thông tin vệ sinh và tiệt trùng đang được cập nhật.</p>
            @endif
        </article>

        <article class="product-bottle-info-card">
            <div class="product-bottle-info-title">
                <span class="product-bottle-info-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </span>
                <h3>Bảo quản sản phẩm</h3>
            </div>

            @if ($storageItems->isNotEmpty())
                <ul>
                    @foreach ($storageItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p class="product-empty-content">Thông tin bảo quản đang được cập nhật.</p>
            @endif
        </article>

        <article class="product-bottle-info-card is-warning">
            <div class="product-bottle-info-title">
                <span class="product-bottle-info-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 2.7 20h18.6L12 3Z"></path>
                        <path d="M12 9v5"></path>
                        <circle cx="12" cy="17" r=".8"></circle>
                    </svg>
                </span>
                <h3>Lưu ý khi sử dụng</h3>
            </div>

            @if ($warningItems->isNotEmpty())
                <ul>
                    @foreach ($warningItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @else
                <p class="product-empty-content">Thông tin lưu ý đang được cập nhật.</p>
            @endif
        </article>

    </div>

</section>
