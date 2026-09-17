@php
    /*
     * Template riêng cho category: bim-ta-ve-sinh
     *
     * Dữ liệu:
     * - Product fields hiện có.
     * - Attribute tags cho: loại sản phẩm / size / cân nặng / số miếng.
     * - products.highlights cho 4 điểm nổi bật.
     * - products.ingredients lưu "Tiêu đề|Mô tả" theo từng dòng
     *   để dựng phần Chất liệu & cấu tạo.
     *
     * Không fake dữ liệu: thiếu gì thì hiển thị "Đang cập nhật".
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

    $diaperType = $findAttribute([
        'tã dán',
        'tã quần',
        'bỉm dán',
        'bỉm quần',
        'dạng dán',
        'dạng quần',
    ]);

    $diaperSize = $findAttribute([
        'size',
        'kích cỡ',
    ]);

    $diaperWeightRange = $attributeNames->first(function ($name) {
        return preg_match(
            '/\d+(?:[.,]\d+)?\s*(?:-|–|—|đến|~)\s*\d+(?:[.,]\d+)?\s*kg/iu',
            $name
        ) || preg_match('/\b\d+(?:[.,]\d+)?\s*kg\b/iu', $name);
    });

    $diaperPieces = $attributeNames->first(function ($name) {
        return preg_match('/\b\d+\s*(?:miếng|mieng)\b/iu', $name);
    });

    /*
     * Subtype riêng: khăn ướt.
     * Hiện tại category "Bỉm tã & vệ sinh" có Mamamy cùng với các sản phẩm tã,
     * nên cần bảng thông số phù hợp ngữ cảnh thay vì Size / Cân nặng.
     */
    $isWetWipeProduct =
        $product->slug === 'khan-uot-mamamy-khong-mui-100-to' ||
        $attributeNames->contains(
            fn ($name) => mb_stripos($name, 'khăn ướt') !== false
        );

    $wetWipeType = $findAttribute([
        'khăn ướt',
    ]);

    $wetWipeScent = $findAttribute([
        'không mùi',
        'có mùi',
        'mùi',
    ]);

    $wetWipeCount = $attributeNames->first(function ($name) {
        return preg_match('/\b\d+\s*(?:tờ|to)\b/iu', $name);
    });

    $wetWipeSheetSize = $attributeNames->first(function ($name) {
        return preg_match(
            '/\b\d+(?:[.,]\d+)?\s*(?:x|×)\s*\d+(?:[.,]\d+)?\s*cm\b/iu',
            $name
        );
    });

    /*
     * 4 điểm nổi bật.
     */
    $highlightData = is_array($product->highlights)
        ? $product->highlights
        : [];

    $diaperBenefits = collect(data_get($highlightData, 'items', []))
        ->filter(fn ($item) => filled(data_get($item, 'title')))
        ->take(4)
        ->map(fn ($item) => [
            'title' => data_get($item, 'title'),
            'subtitle' => data_get($item, 'subtitle') ?: 'Thông tin nổi bật',
            'icon' => data_get($item, 'icon') ?: 'check',
        ])
        ->values();

    while ($diaperBenefits->count() < 4) {
        $diaperBenefits->push([
            'title' => 'Đang cập nhật',
            'subtitle' => 'Thông tin nổi bật',
            'icon' => 'check',
        ]);
    }

    $highlightMessage = data_get($highlightData, 'message')
        ?: 'Thông tin sản phẩm đang được cập nhật';

    $highlightSubmessage = data_get($highlightData, 'submessage')
        ?: 'MommyKids chỉ hiển thị những thông tin hiện có của sản phẩm.';

    /*
     * Chất liệu & cấu tạo:
     * mỗi dòng ingredients có dạng:
     * Tiêu đề|Mô tả
     */
    $diaperMaterialItems = collect(
        preg_split('/\r\n|\r|\n/', trim($product->ingredients ?? ''))
    )
        ->map(function ($line) {
            $line = trim($line);

            if ($line === '') {
                return null;
            }

            $parts = array_map(
                'trim',
                explode('|', $line, 2)
            );

            return [
                'title' => $parts[0] ?: 'Thông tin cấu tạo',
                'description' => $parts[1] ?? '',
            ];
        })
        ->filter()
        ->take(6)
        ->values();

    /*
     * Hướng dẫn sử dụng giữ tối đa 4 bước.
     */
    $diaperUsageSlots = collect(range(0, 3))
        ->map(fn ($index) => $usageSteps->get($index));
@endphp

<section class="product-long-content product-diaper-detail">

    {{-- =========================================================
        CHI TIẾT + MÔ TẢ
    ========================================================== --}}
    <div class="product-content-grid">

        {{-- CHI TIẾT SẢN PHẨM --}}
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

                @if ($isWetWipeProduct)
                    {{-- KHĂN ƯỚT: dùng thông số đúng ngữ cảnh --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $wetWipeType ?: 'Khăn ướt' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Mùi hương</strong>
                        <span>{{ $wetWipeScent ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Quy cách</strong>
                        <span>{{ $wetWipeCount ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Kích thước mỗi tờ</strong>
                        <span>{{ $wetWipeSheetSize ?: 'Đang cập nhật' }}</span>
                    </div>
                @else
                    {{-- BỈM/TÃ: giữ nguyên các thông số Size / cân nặng / số miếng --}}
                    <div class="product-spec-row">
                        <strong>Loại sản phẩm</strong>
                        <span>{{ $diaperType ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Size</strong>
                        <span>{{ $diaperSize ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Cân nặng phù hợp</strong>
                        <span>{{ $diaperWeightRange ?: 'Đang cập nhật' }}</span>
                    </div>

                    <div class="product-spec-row">
                        <strong>Số miếng</strong>
                        <span>{{ $diaperPieces ?: 'Đang cập nhật' }}</span>
                    </div>
                @endif

                <div class="product-spec-row">
                    <strong>Xuất xứ</strong>
                    <span>{{ $product->origin ?: 'Đang cập nhật' }}</span>
                </div>

                <div class="product-spec-row">
                    <strong>Nhà sản xuất</strong>
                    <span>{{ $product->manufacturer ?: 'Đang cập nhật' }}</span>
                </div>

                @unless ($isWetWipeProduct)
                    <div class="product-spec-row">
                        <strong>Kích thước / đóng gói</strong>
                        <span>
                            @if ($product->length_cm && $product->width_cm && $product->height_cm)
                                {{ $product->length_cm }}
                                × {{ $product->width_cm }}
                                × {{ $product->height_cm }} cm
                            @else
                                Đang cập nhật
                            @endif
                        </span>
                    </div>
                @endunless

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
                        @if ($product->stock > 0)
                            Còn {{ $product->stock }} sản phẩm
                        @else
                            Hết hàng
                        @endif
                    </span>
                </div>

            </div>
        </article>

        {{-- MÔ TẢ SẢN PHẨM --}}
        <article class="product-content-card product-description-card">
            <div class="product-section-heading">
                <h2>Mô tả sản phẩm</h2>
            </div>

            <div class="product-description-text">
                {{ $product->description
                    ?: 'Thông tin mô tả sản phẩm đang được cập nhật.' }}
            </div>

            {{-- 4 ĐIỂM NỔI BẬT --}}
            <div class="product-benefit-strip">
                @foreach ($diaperBenefits as $benefit)
                    <div class="product-benefit-item">

                        <div class="product-benefit-icon">
                            @if ($benefit['icon'] === 'drop')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3s6 6.4 6 11a6 6 0 1 1-12 0c0-4.6 6-11 6-11Z"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'air')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 8h10c2.7 0 2.7-4 0-4"></path>
                                    <path d="M3 12h15c3 0 3 4 0 4"></path>
                                    <path d="M3 16h8"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'shield')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'heart')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 21S4 16.2 4 9.8A4.8 4.8 0 0 1 12 6a4.8 4.8 0 0 1 8 3.8C20 16.2 12 21 12 21Z"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="m8 12 2.5 2.5L16 9"></path>
                                </svg>
                            @endif
                        </div>

                        <strong>{{ $benefit['title'] }}</strong>
                        <span>{{ $benefit['subtitle'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="product-trust-banner">
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
        CHẤT LIỆU & CẤU TẠO
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-diaper-material-card">
        <div class="product-section-heading">
            <h2>{{ $isWetWipeProduct ? 'Chất liệu & thành phần' : 'Chất liệu & cấu tạo' }}</h2>
        </div>

        @if ($diaperMaterialItems->isNotEmpty())
            <div class="product-diaper-material-grid">
                @foreach ($diaperMaterialItems as $material)
                    <div class="product-diaper-material-item">
                        <span class="product-diaper-material-icon">
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 8h14v10H5z"></path>
                                    <path d="M8 8V5h8v3M8 12h8M8 15h6"></path>
                                </svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="7" cy="7" r="2"></circle>
                                    <circle cx="15" cy="6" r="2"></circle>
                                    <circle cx="11" cy="13" r="2"></circle>
                                    <circle cx="18" cy="15" r="2"></circle>
                                    <circle cx="5" cy="17" r="2"></circle>
                                </svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            @elseif ($loop->iteration === 4)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3s6 6.4 6 11a6 6 0 1 1-12 0c0-4.6 6-11 6-11Z"></path>
                                    <path d="M9 15c1.2.9 4.8.9 6 0"></path>
                                </svg>
                            @elseif ($loop->iteration === 5)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 8h10c2.7 0 2.7-4 0-4"></path>
                                    <path d="M3 12h15c3 0 3 4 0 4"></path>
                                    <path d="M3 16h8"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 7h14v10H5z"></path>
                                    <path d="M8 10h8M8 14h8"></path>
                                    <path d="m17 5 2 2-2 2"></path>
                                </svg>
                            @endif
                        </span>

                        <div>
                            <strong>{{ $material['title'] }}</strong>

                            @if ($material['description'] !== '')
                                <span>{{ $material['description'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="product-empty-content">
                {{ $isWetWipeProduct
                    ? 'Thông tin chất liệu và thành phần đang được cập nhật.'
                    : 'Thông tin chất liệu và cấu tạo đang được cập nhật.' }}
            </p>
        @endif
    </article>

    {{-- =========================================================
        HƯỚNG DẪN SỬ DỤNG
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-guide-card">
        <div class="product-section-heading">
            <h2>Hướng dẫn sử dụng</h2>
        </div>

        <div class="product-guide-steps">
            @foreach ($diaperUsageSlots as $step)
                <div class="product-guide-step">

                    <div class="product-step-icon">
                        @if ($isWetWipeProduct)
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4" y="6" width="16" height="12" rx="2"></rect>
                                    <path d="M8 6V4h8v2"></path>
                                </svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="5" y="7" width="14" height="10" rx="2"></rect>
                                    <path d="M9 7c.8-2 5.2-2 6 0"></path>
                                    <path d="M9 12h6"></path>
                                </svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 15c2-4 4-6 7-6 2.8 0 4.6 1.6 7 5"></path>
                                    <path d="M7 17h10"></path>
                                    <path d="M9 12c1.2 1 4.8 1 6 0"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <rect x="4" y="6" width="16" height="12" rx="2"></rect>
                                    <path d="M7 9h10"></path>
                                    <path d="m9 13 2 2 4-4"></path>
                                </svg>
                            @endif
                        @else
                            @if ($loop->iteration === 1)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 8c2-2 10-2 12 0"></path>
                                    <path d="M5 9 7 19h10l2-10"></path>
                                    <path d="M8 12h8"></path>
                                </svg>
                            @elseif ($loop->iteration === 2)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 7h14v10H5z"></path>
                                    <path d="M8 10h8M8 14h8"></path>
                                </svg>
                            @elseif ($loop->iteration === 3)
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 12h12"></path>
                                    <path d="m9 9-3 3 3 3M15 9l3 3-3 3"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8"></circle>
                                    <path d="m8.5 12 2.2 2.2 4.8-5"></path>
                                </svg>
                            @endif
                        @endif
                    </div>

                    <strong>Bước {{ $loop->iteration }}</strong>

                    <p>
                        {{ $step ?: 'Đang cập nhật hướng dẫn.' }}
                    </p>
                </div>

                @unless ($loop->last)
                    <span class="product-step-arrow" aria-hidden="true">→</span>
                @endunless
            @endforeach
        </div>
    </article>

    {{-- =========================================================
        BẢO QUẢN + LƯU Ý
    ========================================================== --}}
    <div class="product-bottom-info-grid">

        <article class="product-info-box">
            <div class="product-info-box-title">
                <span class="product-info-box-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
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
                <p class="product-empty-content">
                    Thông tin bảo quản đang được cập nhật.
                </p>
            @endif
        </article>

        <article class="product-info-box product-warning-box">
            <div class="product-info-box-title">
                <span class="product-info-box-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
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
                <p class="product-empty-content">
                    Thông tin lưu ý đang được cập nhật.
                </p>
            @endif
        </article>

    </div>

</section>
