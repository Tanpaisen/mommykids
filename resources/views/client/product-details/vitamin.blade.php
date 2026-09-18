@php
    /*
     * Template riêng cho category: vitamin-suc-khoe
     * Dùng dữ liệu Product + brand/attribute tags hiện có.
     * Không dựng rating, lượt bán hoặc tuyên bố điều trị.
     */

    $vitaminAttributeNames = $attributeTags
        ->pluck('name')
        ->filter()
        ->map(fn ($name) => trim($name))
        ->values();

    $vitaminAttributeSlugs = $attributeTags
        ->pluck('slug')
        ->filter()
        ->map(fn ($slug) => trim($slug))
        ->values();

    $hasVitaminAttribute = function (array $slugs) use ($vitaminAttributeSlugs) {
        return $vitaminAttributeSlugs->contains(
            fn ($slug) => in_array($slug, $slugs, true)
        );
    };

    $findVitaminAttribute = function (array $keywords) use ($vitaminAttributeNames) {
        return $vitaminAttributeNames->first(function ($name) use ($keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($name, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        });
    };

    $vitaminProductName = mb_strtolower(trim($product->name ?? ''));

    /*
     * Nhận diện subtype.
     * Thứ tự quan trọng vì một sản phẩm có thể có nhiều dưỡng chất.
     */
    $isVitaminD3K2 =
        $hasVitaminAttribute(['vitamin-d3', 'vitamin-k2'])
        || str_contains($vitaminProductName, 'd3')
        || str_contains($vitaminProductName, 'k2');

    $isVitaminProbiotic =
        $hasVitaminAttribute(['men-vi-sinh', 'l-reuteri-dsm-17938'])
        || str_contains($vitaminProductName, 'biogaia')
        || str_contains($vitaminProductName, 'men vi sinh');

    $isVitaminMulti =
        $hasVitaminAttribute(['vitamin-tong-hop'])
        || str_contains($vitaminProductName, 'multi-vitamin')
        || str_contains($vitaminProductName, 'multivitamin');

    $isVitaminDha =
        $hasVitaminAttribute(['dha', 'omega-3'])
        || str_contains($vitaminProductName, 'dha')
        || str_contains($vitaminProductName, 'omega');

    $isVitaminIron =
        $hasVitaminAttribute(['sat'])
        || str_contains($vitaminProductName, 'sắt');

    $isVitaminCalcium =
        $hasVitaminAttribute(['canxi'])
        || str_contains($vitaminProductName, 'calcium')
        || str_contains($vitaminProductName, 'canxi');

    $isVitaminNutritionSupport =
        !$isVitaminD3K2
        && !$isVitaminProbiotic
        && !$isVitaminMulti
        && !$isVitaminDha
        && !$isVitaminIron
        && !$isVitaminCalcium;

    /*
     * Thông tin chung từ attribute.
     */
    $vitaminForm = $findVitaminAttribute([
        'dạng nhỏ giọt',
        'dạng xịt',
        'dạng siro',
        'viên nhai',
        'viên nang mềm',
        'dạng lỏng',
    ]);

    $vitaminPack = $vitaminAttributeNames->first(function ($name) {
        return preg_match(
            '/\b\d+(?:[.,]\d+)?\s*(?:ml|mL|ML|viên|vien)\b/u',
            $name
        );
    });

    $vitaminAgeAttribute = $findVitaminAttribute([
        '0M+',
        '4M+',
        '2Y+',
    ]);

    $vitaminAgeText =
        $ageText
        ?: $vitaminAgeAttribute;

    $vitaminPrimaryNutrient = match (true) {
        $isVitaminD3K2 => collect([
            $findVitaminAttribute(['Vitamin D3']),
            $findVitaminAttribute(['Vitamin K2']),
        ])->filter()->join(' + ') ?: 'Vitamin D3 + K2',

        $isVitaminProbiotic =>
            $findVitaminAttribute(['L. reuteri DSM 17938', 'Men vi sinh'])
            ?: 'Men vi sinh',

        $isVitaminMulti =>
            $findVitaminAttribute(['Vitamin tổng hợp'])
            ?: 'Vitamin tổng hợp',

        $isVitaminDha =>
            $findVitaminAttribute(['DHA'])
            ?: 'DHA',

        $isVitaminIron =>
            $findVitaminAttribute(['Sắt'])
            ?: 'Sắt',

        $isVitaminCalcium =>
            $findVitaminAttribute(['Canxi'])
            ?: 'Canxi',

        default =>
            $findVitaminAttribute(['Hỗ trợ dinh dưỡng'])
            ?: 'Hỗ trợ dinh dưỡng',
    };

    $vitaminTypeLabel = match (true) {
        $isVitaminD3K2 => 'Vitamin D3 / K2',
        $isVitaminProbiotic => 'Men vi sinh',
        $isVitaminMulti => 'Vitamin tổng hợp',
        $isVitaminDha => 'DHA / Omega-3',
        $isVitaminIron => 'Sắt',
        $isVitaminCalcium => 'Canxi',
        default => 'Dinh dưỡng bổ sung',
    };


    /*
     * Subtitle ngắn cho khối Tổng quan.
     * Chỉ mô tả nhóm sản phẩm, không thay thế hướng dẫn trên nhãn.
     */
    $vitaminOverviewKicker = match (true) {
        $isVitaminIron => 'Bổ sung sắt theo hướng dẫn phù hợp với từng độ tuổi',
        $isVitaminD3K2 => 'Bổ sung vitamin D3 và K2 theo hướng dẫn sử dụng',
        $isVitaminProbiotic => 'Men vi sinh dạng tiện lợi cho trẻ nhỏ',
        $isVitaminMulti => 'Vitamin và khoáng chất cho giai đoạn phát triển',
        $isVitaminDha => 'Bổ sung DHA và Omega-3 theo nhu cầu dinh dưỡng',
        $isVitaminCalcium => 'Bổ sung canxi và vitamin D3 theo hướng dẫn sử dụng',
        default => 'Dinh dưỡng bổ sung theo hướng dẫn của sản phẩm',
    };

    /*
     * Highlight: ưu tiên dữ liệu highlights thật nếu có,
     * thiếu thì dùng mô tả trung tính theo subtype.
     */
    $vitaminHighlightData = is_array($product->highlights)
        ? $product->highlights
        : [];

    $vitaminBenefits = collect(data_get($vitaminHighlightData, 'items', []))
        ->filter(fn ($item) => filled(data_get($item, 'title')))
        ->take(4)
        ->map(fn ($item) => [
            'title' => data_get($item, 'title'),
            'subtitle' => data_get($item, 'subtitle') ?: 'Thông tin sản phẩm',
            'icon' => data_get($item, 'icon') ?: 'check',
        ])
        ->values();

    $vitaminFallbackBenefits = collect(
        match (true) {
            $isVitaminD3K2 => [
                ['title' => 'D3 & K2', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'drop'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
                ['title' => $vitaminPack ?: 'Theo quy cách', 'subtitle' => 'Quy cách', 'icon' => 'box'],
            ],

            $isVitaminProbiotic => [
                ['title' => 'Men vi sinh', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'microbe'],
                ['title' => $findVitaminAttribute(['L. reuteri DSM 17938']) ?: 'Chủng lợi khuẩn', 'subtitle' => 'Thông tin chủng', 'icon' => 'shield'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
            ],

            $isVitaminMulti => [
                ['title' => 'Vitamin tổng hợp', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'spark'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
                ['title' => $vitaminPack ?: 'Theo quy cách', 'subtitle' => 'Quy cách', 'icon' => 'box'],
            ],

            $isVitaminDha => [
                ['title' => 'DHA', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'drop'],
                ['title' => $findVitaminAttribute(['Omega-3']) ?: 'Omega-3', 'subtitle' => 'Nhóm dưỡng chất', 'icon' => 'wave'],
                ['title' => $vitaminForm ?: 'Viên nang mềm', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'capsule'],
                ['title' => $vitaminPack ?: 'Theo quy cách', 'subtitle' => 'Quy cách', 'icon' => 'box'],
            ],

            $isVitaminIron => [
                ['title' => 'Sắt', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'drop'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Độ tuổi sử dụng', 'icon' => 'user'],
                ['title' => 'Đúng liều lượng', 'subtitle' => 'Theo hướng dẫn trên nhãn', 'icon' => 'check'],
            ],

            $isVitaminCalcium => [
                ['title' => 'Canxi', 'subtitle' => 'Dưỡng chất chính', 'icon' => 'spark'],
                ['title' => 'Sản phẩm bổ sung', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'shield'],
                ['title' => $vitaminForm ?: 'Theo sản phẩm', 'subtitle' => 'Dạng sử dụng', 'icon' => 'bottle'],
                ['title' => 'Đúng liều lượng', 'subtitle' => 'Theo hướng dẫn trên nhãn', 'icon' => 'check'],
            ],

            default => [
                ['title' => 'Dinh dưỡng bổ sung', 'subtitle' => 'Nhóm sản phẩm', 'icon' => 'spark'],
                ['title' => $vitaminForm ?: 'Dạng dùng tiện lợi', 'subtitle' => 'Dạng sản phẩm', 'icon' => 'bottle'],
                ['title' => $vitaminAgeText ?: 'Theo nhãn sản phẩm', 'subtitle' => 'Đối tượng sử dụng', 'icon' => 'user'],
                ['title' => 'Dùng đúng hướng dẫn', 'subtitle' => 'Theo thông tin trên bao bì', 'icon' => 'check'],
            ],
        }
    );

    foreach ($vitaminFallbackBenefits as $benefit) {
        if ($vitaminBenefits->count() >= 4) {
            break;
        }

        $vitaminBenefits->push($benefit);
    }

    $vitaminBenefits = $vitaminBenefits->take(4)->values();

    $vitaminHighlightMessage = data_get($vitaminHighlightData, 'message')
        ?: $product->name;

    $vitaminHighlightSubmessage = data_get($vitaminHighlightData, 'submessage')
        ?: 'Thông tin sử dụng cần được đối chiếu với nhãn và hướng dẫn của sản phẩm.';

    /*
     * Thành phần / dưỡng chất:
     * ưu tiên từng dòng ingredients đã lưu trong DB.
     */
    $vitaminIngredientLines = collect(
        preg_split('/\r\n|\r|\n/', trim($product->ingredients ?? ''))
    )
        ->map(fn ($item) => trim($item))
        ->filter()
        ->take(4)
        ->values();

    $vitaminIngredientFallback = collect(
        match (true) {
            $isVitaminD3K2 => [
                'Vitamin D3',
                'Vitamin K2',
                'Chất nền theo công bố sản phẩm',
                'Phụ liệu theo nhãn sản phẩm',
            ],
            $isVitaminProbiotic => [
                'Chủng lợi khuẩn',
                'Thành phần nền',
                'Phụ liệu theo công bố sản phẩm',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminMulti => [
                'Vitamin',
                'Khoáng chất',
                'Thành phần nền',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminDha => [
                'DHA',
                'Omega-3',
                'Thành phần viên nang',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminIron => [
                'Sắt',
                'Thành phần nền',
                'Phụ liệu theo công bố sản phẩm',
                'Thông tin chi tiết trên bao bì',
            ],
            $isVitaminCalcium => [
                'Canxi',
                'Thành phần nền',
                'Phụ liệu theo công bố sản phẩm',
                'Thông tin chi tiết trên bao bì',
            ],
            default => [
                'Thành phần chính',
                'Dưỡng chất',
                'Thành phần nền',
                'Thông tin chi tiết trên bao bì',
            ],
        }
    );

    while ($vitaminIngredientLines->count() < 4) {
        $fallback = $vitaminIngredientFallback->get($vitaminIngredientLines->count());

        if (!$fallback) {
            break;
        }

        $vitaminIngredientLines->push($fallback);
    }

    /*
     * 4 bước sử dụng.
     * Nếu DB có ít hơn 4 dòng thì bổ sung hướng dẫn trung tính,
     * không tự dựng liều lượng.
     */
    $vitaminUsageFallback = collect(
        match (true) {
            $isVitaminProbiotic => [
                'Đọc kỹ hướng dẫn và kiểm tra hạn sử dụng.',
                'Chuẩn bị sản phẩm đúng theo dạng dùng.',
                'Sử dụng đúng liều lượng ghi trên nhãn.',
                'Đậy kín và bảo quản đúng hướng dẫn sau khi dùng.',
            ],
            $isVitaminD3K2 || $isVitaminIron => [
                'Đọc kỹ hướng dẫn và kiểm tra hạn sử dụng.',
                'Lấy sản phẩm theo đúng dạng nhỏ giọt hoặc dạng xịt.',
                'Sử dụng đúng liều lượng theo nhóm tuổi trên nhãn.',
                'Đậy kín và bảo quản đúng hướng dẫn sau khi dùng.',
            ],
            $isVitaminMulti => [
                'Lắc sản phẩm nếu hướng dẫn trên nhãn yêu cầu.',
                'Đo lượng sử dụng bằng dụng cụ phù hợp.',
                'Dùng đúng liều lượng theo độ tuổi ghi trên nhãn.',
                'Đậy kín và bảo quản đúng hướng dẫn sau khi dùng.',
            ],
            $isVitaminDha => [
                'Kiểm tra bao bì và hạn sử dụng.',
                'Xác định liều dùng phù hợp theo độ tuổi trên nhãn.',
                'Sử dụng sản phẩm theo đúng hướng dẫn.',
                'Bảo quản sản phẩm đúng điều kiện sau khi mở.',
            ],
            default => [
                'Đọc kỹ hướng dẫn và kiểm tra bao bì.',
                'Chuẩn bị sản phẩm theo đúng dạng sử dụng.',
                'Dùng đúng liều lượng được ghi trên nhãn.',
                'Bảo quản đúng hướng dẫn sau khi sử dụng.',
            ],
        }
    );

    $vitaminUsageSlots = collect(range(0, 3))
        ->map(function ($index) use ($usageSteps, $vitaminUsageFallback) {
            return $usageSteps->get($index)
                ?: $vitaminUsageFallback->get($index);
        });

    $vitaminStepTitles = collect(
        match (true) {
            $isVitaminMulti => [
                'Chuẩn bị',
                'Đo lượng dùng',
                'Sử dụng',
                'Bảo quản',
            ],
            $isVitaminDha => [
                'Kiểm tra',
                'Xác định liều',
                'Sử dụng',
                'Bảo quản',
            ],
            default => [
                'Kiểm tra',
                'Chuẩn bị',
                'Sử dụng đúng liều',
                'Bảo quản',
            ],
        }
    );

    $vitaminStorageList = $storageItems->isNotEmpty()
        ? $storageItems
        : collect([
            'Bảo quản theo đúng điều kiện được ghi trên bao bì sản phẩm.',
            'Đậy kín sau khi sử dụng và để xa tầm tay trẻ nhỏ.',
        ]);

    $vitaminWarningList = $warningItems->isNotEmpty()
        ? $warningItems
        : collect([
            'Không tự ý vượt quá liều lượng được khuyến nghị trên nhãn.',
            'Ngưng sử dụng và tham khảo ý kiến chuyên môn nếu xuất hiện phản ứng bất thường.',
        ]);

    $vitaminAudienceItems = collect([
        $vitaminAgeText
            ? 'Độ tuổi tham khảo: ' . $vitaminAgeText . '.'
            : 'Đối tượng sử dụng theo hướng dẫn cụ thể trên bao bì.',
        'Đọc kỹ thành phần trước khi sử dụng nếu trẻ có tiền sử dị ứng hoặc đang dùng sản phẩm bổ sung khác.',
    ]);
@endphp

<section class="product-long-content product-vitamin-detail">

    {{-- =========================================================
         OVERVIEW
    ========================================================== --}}
    <article class="product-vitamin-overview">

        <div class="product-vitamin-overview-grid">

            {{-- SPEC --}}
            <div class="product-vitamin-overview-spec">

                <div class="product-vitamin-section-heading">
                    <span class="product-vitamin-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M9 3h6"></path>
                            <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                            <path d="M9 12h6"></path>
                        </svg>
                    </span>

                    <div>
                        <span>Thông tin sản phẩm</span>
                        <h2>Chi tiết Vitamin &amp; sức khỏe</h2>
                    </div>
                </div>

                <div class="product-spec-table product-vitamin-spec-table">

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
                        <span>{{ $vitaminTypeLabel }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Dưỡng chất chính</strong>
                        <span>{{ $vitaminPrimaryNutrient }}</span>
                    </div>

                    @if ($vitaminForm)
                        <div class="product-spec-row">
                            <strong>Dạng sản phẩm</strong>
                            <span>{{ $vitaminForm }}</span>
                        </div>
                    @endif

                    @if ($vitaminPack)
                        <div class="product-spec-row">
                            <strong>Quy cách</strong>
                            <span>{{ $vitaminPack }}</span>
                        </div>
                    @endif

                    @if ($vitaminAgeText)
                        <div class="product-spec-row">
                            <strong>Độ tuổi</strong>
                            <span>{{ $vitaminAgeText }}</span>
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
                            {{ $product->stock > 0
                                ? 'Còn ' . $product->stock . ' sản phẩm'
                                : 'Hết hàng' }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- DESCRIPTION --}}
            <div class="product-vitamin-overview-description">

                <div class="product-vitamin-section-heading">
                    <span class="product-vitamin-heading-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 4h14v16H5z"></path>
                            <path d="M8 8h8M8 12h8M8 16h5"></path>
                        </svg>
                    </span>

                    <div>
                        <span>Tổng quan</span>
                        <h2>Mô tả sản phẩm</h2>
                    </div>
                </div>

                <div class="product-vitamin-overview-intro">
                    <p class="product-vitamin-overview-kicker">
                        {{ $vitaminOverviewKicker }}
                    </p>

                    <div class="product-vitamin-description-text">
                        {{ $product->description ?: 'Thông tin mô tả sản phẩm đang được cập nhật.' }}
                    </div>
                </div>

                <div class="product-vitamin-benefit-grid">
                    @foreach ($vitaminBenefits as $benefit)
                        <div
                            class="product-vitamin-benefit-item"
                            data-index="{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}"
                        >

                            <span class="product-vitamin-benefit-icon" aria-hidden="true">

    @if ($benefit['icon'] === 'drop')

        {{-- Dưỡng chất dạng giọt --}}
        <svg viewBox="0 0 24 24">
            <path d="M12 3s5 5.5 5 9a5 5 0 1 1-10 0c0-3.5 5-9 5-9Z"></path>
        </svg>

    @elseif ($benefit['icon'] === 'bottle')

        {{-- Chai / dạng nhỏ giọt / siro --}}
        <svg viewBox="0 0 24 24">
            <path d="M9 3h6"></path>
            <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
            <path d="M9 12h6"></path>
        </svg>

    @elseif ($benefit['icon'] === 'spark')

        {{-- Thành phần nổi bật / vitamin --}}
        <svg viewBox="0 0 24 24">
            <path d="M12 3l1.4 4.1L17.5 8.5l-4.1 1.4L12 14l-1.4-4.1L6.5 8.5l4.1-1.4L12 3Z"></path>
            <path d="M18 14l.8 2.2L21 17l-2.2.8L18 20l-.8-2.2L15 17l2.2-.8L18 14Z"></path>
        </svg>

    @elseif ($benefit['icon'] === 'wave')

        {{-- DHA / Omega --}}
        <svg viewBox="0 0 24 24">
            <path d="M3 9c2.2-2 4.3-2 6.5 0s4.3 2 6.5 0 4.3-2 5 0"></path>
            <path d="M3 15c2.2-2 4.3-2 6.5 0s4.3 2 6.5 0 4.3-2 5 0"></path>
        </svg>

    @elseif ($benefit['icon'] === 'microbe')

        {{-- Men vi sinh --}}
        <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="5"></circle>
            <path d="M12 3v4M12 17v4M3 12h4M17 12h4"></path>
            <path d="M5.5 5.5l3 3M15.5 15.5l3 3"></path>
            <path d="M18.5 5.5l-3 3M8.5 15.5l-3 3"></path>
        </svg>

    @elseif ($benefit['icon'] === 'capsule')

        {{-- Viên nang --}}
        <svg viewBox="0 0 24 24">
            <path d="M8 4a4 4 0 0 1 5.7 0l6.3 6.3a4 4 0 0 1-5.7 5.7L8 9.7A4 4 0 0 1 8 4Z"></path>
            <path d="m10 12 5-5"></path>
        </svg>

    @elseif ($benefit['icon'] === 'user')

        {{-- Độ tuổi / đối tượng --}}
        <svg viewBox="0 0 24 24">
            <circle cx="12" cy="8" r="3"></circle>
            <path d="M6 20c.8-4 3-6 6-6s5.2 2 6 6"></path>
        </svg>

    @elseif ($benefit['icon'] === 'box')

        {{-- Quy cách --}}
        <svg viewBox="0 0 24 24">
            <path d="m4 7 8-4 8 4-8 4-8-4Z"></path>
            <path d="M4 7v10l8 4 8-4V7"></path>
            <path d="M12 11v10"></path>
        </svg>

    @elseif ($benefit['icon'] === 'shield')

        {{-- An toàn --}}
        <svg viewBox="0 0 24 24">
            <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
            <path d="m9 12 2 2 4-4"></path>
        </svg>

    @else

        {{-- Fallback --}}
        <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="m8 12 2.5 2.5L16 9"></path>
        </svg>

    @endif

</span>

                            <div>
                                <strong>{{ $benefit['title'] }}</strong>
                                <span>{{ $benefit['subtitle'] }}</span>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="product-vitamin-trust-banner">
                    <span class="product-vitamin-trust-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                            <path d="m9 12 2 2 4-4"></path>
                        </svg>
                    </span>

                    <div class="product-vitamin-trust-copy">
                        <span class="product-vitamin-trust-label">Thông tin sử dụng</span>
                        <strong>{{ $vitaminHighlightMessage }}</strong>
                        <span>{{ $vitaminHighlightSubmessage }}</span>
                    </div>
                </div>

            </div>

        </div>

    </article>

    {{-- =========================================================
         THÀNH PHẦN / DƯỠNG CHẤT
    ========================================================== --}}
    <article class="product-vitamin-section-card">

        <div class="product-vitamin-card-heading">

            <div class="product-vitamin-section-heading">
                <span class="product-vitamin-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3v18"></path>
                        <path d="M7 8c0-2 2-4 5-4"></path>
                        <path d="M17 8c0-2-2-4-5-4"></path>
                        <path d="M7 14c0 2 2 4 5 4"></path>
                        <path d="M17 14c0 2-2 4-5 4"></path>
                    </svg>
                </span>

                <div>
                    <span>Thành phần</span>
                    <h2>Dưỡng chất &amp; thành phần chính</h2>
                </div>
            </div>

            <p>Thông tin tóm tắt từ dữ liệu hiện có của sản phẩm.</p>

        </div>

        <div class="product-vitamin-ingredient-grid">

            @foreach ($vitaminIngredientLines as $ingredient)
                <div class="product-vitamin-ingredient-item">

                    <span class="product-vitamin-ingredient-number">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <span class="product-vitamin-ingredient-icon" aria-hidden="true">
                        @if ($loop->iteration === 1)
                            <svg viewBox="0 0 24 24">
                                <path d="M12 3s5 5.5 5 9a5 5 0 1 1-10 0c0-3.5 5-9 5-9Z"></path>
                            </svg>
                        @elseif ($loop->iteration === 2)
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8"></circle>
                                <path d="M8 12h8M12 8v8"></path>
                            </svg>
                        @elseif ($loop->iteration === 3)
                            <svg viewBox="0 0 24 24">
                                <path d="m4 7 8-4 8 4-8 4-8-4Z"></path>
                                <path d="M4 7v10l8 4 8-4V7"></path>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24">
                                <path d="M5 4h14v16H5z"></path>
                                <path d="M8 8h8M8 12h8M8 16h5"></path>
                            </svg>
                        @endif
                    </span>

                    <div>
                        <strong>
                            @if ($loop->iteration === 1)
                                Dưỡng chất chính
                            @elseif ($loop->iteration === 2)
                                Thành phần bổ sung
                            @elseif ($loop->iteration === 3)
                                Thành phần nền
                            @else
                                Thông tin thành phần
                            @endif
                        </strong>

                        <span>{{ $ingredient }}</span>
                    </div>

                </div>
            @endforeach

        </div>

    </article>

    {{-- =========================================================
         HƯỚNG DẪN SỬ DỤNG
    ========================================================== --}}
    <article class="product-vitamin-section-card product-vitamin-guide-card">

        <div class="product-vitamin-card-heading">

            <div class="product-vitamin-section-heading">
                <span class="product-vitamin-heading-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M7 4h10v16H7z"></path>
                        <path d="M10 2h4v3h-4z"></path>
                        <path d="m9 12 2 2 4-4"></path>
                    </svg>
                </span>

                <div>
                    <span>Sử dụng đúng cách</span>
                    <h2>Hướng dẫn sử dụng</h2>
                </div>
            </div>

            <p>Luôn ưu tiên liều lượng và hướng dẫn cụ thể trên nhãn sản phẩm.</p>

        </div>

        <div class="product-vitamin-steps">

            @foreach ($vitaminUsageSlots as $step)

                <div class="product-vitamin-step">

                    <div class="product-vitamin-step-head">
                        <span class="product-vitamin-step-number">
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="product-vitamin-step-icon" aria-hidden="true">
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24">
                                    <path d="M5 4h14v16H5z"></path>
                                    <path d="M8 8h8M8 12h8M8 16h5"></path>
                                </svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24">
                                    <path d="M9 3h6"></path>
                                    <path d="M10 3v3l-2 2v11h8V8l-2-2V3"></path>
                                </svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="8"></circle>
                                    <path d="M12 8v4l3 2"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            @endif
                        </span>
                    </div>

                    <strong>{{ $vitaminStepTitles->get($loop->index) }}</strong>
                    <p>{{ $step }}</p>

                </div>

            @endforeach

        </div>

    </article>

    {{-- =========================================================
         BẢO QUẢN / LƯU Ý / ĐỐI TƯỢNG
    ========================================================== --}}
    <div class="product-vitamin-info-grid">

        <article class="product-vitamin-info-card">

            <div class="product-vitamin-info-title">
                <span class="product-vitamin-info-icon" aria-hidden="true">
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

            <ul>
                @foreach ($vitaminStorageList as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

        </article>

        <article class="product-vitamin-info-card is-warning">

            <div class="product-vitamin-info-title">
                <span class="product-vitamin-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 2.8 20h18.4L12 3Z"></path>
                        <path d="M12 9v5"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </span>

                <div>
                    <span>Lưu ý</span>
                    <h3>An toàn khi sử dụng</h3>
                </div>
            </div>

            <ul>
                @foreach ($vitaminWarningList as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

        </article>

        <article class="product-vitamin-info-card is-audience">

            <div class="product-vitamin-info-title">
                <span class="product-vitamin-info-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="3"></circle>
                        <path d="M6 20c.8-4 3-6 6-6s5.2 2 6 6"></path>
                    </svg>
                </span>

                <div>
                    <span>Đối tượng</span>
                    <h3>{{ $vitaminAgeText ?: 'Theo hướng dẫn sản phẩm' }}</h3>
                </div>
            </div>

            <ul>
                @foreach ($vitaminAudienceItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>

        </article>

    </div>

</section>
