@php
    /*
     * Template dùng chung cho toàn bộ sản phẩm thuộc danh mục "Sữa cho bé".
     * Layout luôn tồn tại; thiếu dữ liệu thì hiển thị "Đang cập nhật".
     */

    $milkUsageSlots = collect(range(0, 3))
        ->map(fn ($index) => $usageSteps->get($index));

    /*
     * Nội dung dùng chung cho toàn bộ nhóm "Sữa cho bé".
     * Nếu Admin đã nhập dữ liệu riêng cho sản phẩm thì ưu tiên dữ liệu riêng.
     * Nếu để trống thì tự dùng nội dung mặc định bên dưới.
     */
    $defaultStorageItems = collect([
        'Bảo quản tại nơi khô ráo, thoáng mát.',
        'Đậy kín nắp sau mỗi lần sử dụng.',
        'Tránh ánh nắng trực tiếp và nguồn nhiệt cao.',
    ]);

    $defaultWarningItems = collect([
        'Kiểm tra bao bì và hạn sử dụng trước khi dùng.',
        'Sử dụng và pha sản phẩm theo đúng hướng dẫn của nhà sản xuất.',
        'Tham khảo thông tin trên bao bì đối với các lưu ý riêng của từng sản phẩm.',
    ]);

    $displayStorageItems = $storageItems->isNotEmpty()
        ? $storageItems
        : $defaultStorageItems;

    $displayWarningItems = $warningItems->isNotEmpty()
        ? $warningItems
        : $defaultWarningItems;

    /*
     * Điểm nổi bật lấy trực tiếp từ Admin (products.highlights).
     * Nếu chưa đủ 4 điểm, bổ sung placeholder để bố cục sữa luôn đồng nhất.
     */
    $highlightData = is_array($product->highlights)
        ? $product->highlights
        : [];

    $milkBenefits = collect(data_get($highlightData, 'items', []))
        ->filter(fn ($item) => filled(data_get($item, 'title')))
        ->take(4)
        ->map(fn ($item) => [
            'title' => data_get($item, 'title'),
            'subtitle' => data_get($item, 'subtitle') ?: 'Thông tin nổi bật',
            'icon' => data_get($item, 'icon') ?: 'check',
        ])
        ->values();

    while ($milkBenefits->count() < 4) {
        $milkBenefits->push([
            'title' => 'Đang cập nhật',
            'subtitle' => 'Thông tin nổi bật',
            'icon' => 'check',
        ]);
    }

    $highlightMessage = data_get($highlightData, 'message')
        ?: 'Thông tin sản phẩm rõ ràng, dễ theo dõi';

    $highlightSubmessage = data_get($highlightData, 'submessage')
        ?: 'Nội dung được cập nhật từ thông tin sản phẩm trên MommyKids.';
@endphp

<section class="product-long-content">

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

                <div class="product-spec-row">
                    <strong>Danh mục</strong>
                    <span>{{ $product->category?->name ?: 'Đang cập nhật' }}</span>
                </div>

                <div class="product-spec-row">
                    <strong>Độ tuổi phù hợp</strong>
                    <span>{{ $ageText ?: 'Đang cập nhật' }}</span>
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
                    <strong>Kích thước</strong>
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

            {{-- 4 ô lợi ích luôn tồn tại để mọi sản phẩm sữa cùng bố cục --}}
            <div class="product-benefit-strip">
                @foreach ($milkBenefits as $benefit)
                    <div class="product-benefit-item">
                        <div class="product-benefit-icon">
                            @if ($benefit['icon'] === 'shield')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 5 6v5c0 4.7 2.8 8.3 7 10 4.2-1.7 7-5.3 7-10V6l-7-3Z"></path>
                                    <path d="m9 12 2 2 4-4"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'brain')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9.2 4.2A3.7 3.7 0 0 0 5.5 8v1.1A3.5 3.5 0 0 0 4 15.6V17a3 3 0 0 0 5.2 2"></path>
                                    <path d="M14.8 4.2A3.7 3.7 0 0 1 18.5 8v1.1a3.5 3.5 0 0 1 1.5 6.5V17a3 3 0 0 1-5.2 2"></path>
                                    <path d="M9.2 4.2V20M14.8 4.2V20"></path>
                                    <path d="M9.2 8H7M14.8 8H17M9.2 12H6M14.8 12H18M9.2 16H7M14.8 16H17"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'digest')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M9 4c0 4 1 5 3 5 2.5 0 3.2-2 3.2-4.5"></path>
                                    <path d="M15.2 4.5c2.8 1.2 4.8 4 4.8 7.3 0 4.5-3.4 8.2-7.6 8.2-4.3 0-7.4-3.1-7.4-7 0-2.2 1.1-4.3 3-5.6"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'bone')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M7.2 8.3a2.5 2.5 0 1 1-3.5-3.5 2.5 2.5 0 1 1 3.5 3.5l9.6 7.4a2.5 2.5 0 1 1 3.5 3.5 2.5 2.5 0 1 1-3.5-3.5L7.2 8.3Z"></path>
                                </svg>
                            @elseif ($benefit['icon'] === 'eye')
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                    <circle cx="12" cy="12" r="2.5"></circle>
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

            {{-- Banner luôn tồn tại --}}
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
        THÀNH PHẦN - LUÔN HIỆN
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-ingredients-card">
        <div class="product-section-heading">
            <h2>Thành phần</h2>
        </div>

        <div class="product-long-text">{!! nl2br(e(trim(
            $product->ingredients
                ?: 'Thông tin thành phần sản phẩm đang được cập nhật.'
        ))) !!}</div>
    </article>

    {{-- =========================================================
        HƯỚNG DẪN SỬ DỤNG - LUÔN GIỮ 4 VỊ TRÍ
    ========================================================== --}}
    <article class="product-content-card product-wide-card product-guide-card">
        <div class="product-section-heading">
            <h2>Hướng dẫn sử dụng</h2>
        </div>

        <div class="product-guide-steps">
            @foreach ($milkUsageSlots as $step)
                <div class="product-guide-step">

                    <div class="product-step-icon">
                        @if ($loop->iteration === 1)
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 11V6a1 1 0 0 1 2 0v4"></path>
                                <path d="M10 10V5a1 1 0 0 1 2 0v5"></path>
                                <path d="M12 10V6a1 1 0 0 1 2 0v5"></path>
                                <path d="M14 11V8a1 1 0 0 1 2 0v6c0 4-2 7-6 7-3 0-5-2-6-5l-1-3a1.4 1.4 0 0 1 2.5-1.2L7 14"></path>
                            </svg>
                        @elseif ($loop->iteration === 2)
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M9 3h6"></path>
                                <path d="M10 3v3h4V3"></path>
                                <rect x="7" y="6" width="10" height="15" rx="3"></rect>
                                <path d="M10 10h4M10 14h4"></path>
                            </svg>
                        @elseif ($loop->iteration === 3)
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 14c5 0 8-3 12-7"></path>
                                <path d="M4 14c0 4 3 6 6 6 4 0 6-3 6-6H4Z"></path>
                                <circle cx="18" cy="6" r="2"></circle>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M10 14.5V5a2 2 0 0 1 4 0v9.5"></path>
                                <circle cx="12" cy="17" r="4"></circle>
                                <path d="M12 14v3"></path>
                            </svg>
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
        BẢO QUẢN + LƯU Ý - LUÔN GIỮ 2 CARD
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

            <ul>
                @foreach ($displayStorageItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
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

            <ul>
                @foreach ($displayWarningItems as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </article>

    </div>

</section>
