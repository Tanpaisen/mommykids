/**
 * =========================================================
 * CATEGORY PAGE
 * =========================================================
 *
 * Bao gồm:
 * - Auto submit brand / stage / attribute
 * - Price range slider 2 đầu
 * - Xem thêm / Thu gọn cẩm nang sữa
 */


/**
 * ---------------------------------------------------------
 * CẨM NANG SỮA CHO BÉ
 * ---------------------------------------------------------
 */
function initMilkGuide() {
    const button =
        document.getElementById('milk-guide-toggle');

    const more =
        document.getElementById('milk-guide-more');

    const label =
        document.getElementById('milk-guide-toggle-label');

    const icon =
        document.getElementById('milk-guide-toggle-icon');


    /*
     * Category khác "sua-cho-be"
     * sẽ không có các element này.
     */
    if (
        !button ||
        !more ||
        !label ||
        !icon
    ) {
        return;
    }


    button.addEventListener(
        'click',
        () => {
            const isOpen =
                button.getAttribute(
                    'aria-expanded'
                ) === 'true';


            button.setAttribute(
                'aria-expanded',
                String(!isOpen)
            );


            more.classList.toggle(
                'hidden',
                isOpen
            );


            label.textContent =
                isOpen
                    ? 'Xem thêm'
                    : 'Thu gọn';


            icon.textContent =
                isOpen
                    ? '↓'
                    : '↑';
        }
    );
}


/**
 * ---------------------------------------------------------
 * CATEGORY FILTER
 * ---------------------------------------------------------
 */
function initCategoryFilters() {
    const filterForm =
        document.getElementById(
            'category-filter-form'
        );


    /*
     * Không ở trang category
     * thì không làm gì.
     */
    if (!filterForm) {
        return;
    }


    /**
     * -----------------------------------------------------
     * BRAND / STAGE / ATTRIBUTE
     * -----------------------------------------------------
     *
     * Khi checkbox thay đổi
     * → submit form ngay.
     */
    filterForm
        .querySelectorAll(
            '.js-auto-filter'
        )
        .forEach(
            (input) => {
                input.addEventListener(
                    'change',
                    () => {
                        filterForm.submit();
                    }
                );
            }
        );


    /**
     * -----------------------------------------------------
     * PRICE SLIDER
     * -----------------------------------------------------
     */

    const slider =
        document.getElementById(
            'price-slider'
        );


    const minRange =
        document.getElementById(
            'price-range-min'
        );


    const maxRange =
        document.getElementById(
            'price-range-max'
        );


    const minHidden =
        document.getElementById(
            'min-price-input'
        );


    const maxHidden =
        document.getElementById(
            'max-price-input'
        );


    const minLabel =
        document.getElementById(
            'price-min-label'
        );


    const maxLabel =
        document.getElementById(
            'price-max-label'
        );


    const progress =
        document.getElementById(
            'price-range-progress'
        );


    /*
     * Nếu slider không tồn tại
     * thì kết thúc.
     */
    if (
        !slider ||
        !minRange ||
        !maxRange ||
        !minHidden ||
        !maxHidden ||
        !minLabel ||
        !maxLabel ||
        !progress
    ) {
        return;
    }


    const floor =
        Number(
            slider.dataset.floor
        );


    const ceiling =
        Number(
            slider.dataset.ceiling
        );


    /**
     * Format:
     *
     * 1000000
     * →
     * 1.000.000đ
     */
    const formatPrice =
        (value) =>
            new Intl.NumberFormat(
                'vi-VN'
            ).format(value)
            + 'đ';


    /**
     * -----------------------------------------------------
     * Đồng bộ slider
     * -----------------------------------------------------
     */
    const syncSlider =
        (changedInput = null) => {

            let minValue =
                Number(
                    minRange.value
                );


            let maxValue =
                Number(
                    maxRange.value
                );


            /*
             * Không cho min vượt max.
             */
            if (
                minValue >
                maxValue
            ) {

                if (
                    changedInput ===
                    minRange
                ) {

                    minValue =
                        maxValue;


                    minRange.value =
                        String(
                            minValue
                        );

                } else {

                    maxValue =
                        minValue;


                    maxRange.value =
                        String(
                            maxValue
                        );
                }
            }


            /*
             * Đồng bộ hidden input
             * gửi lên Laravel.
             */
            minHidden.value =
                String(minValue);


            maxHidden.value =
                String(maxValue);


            /**
             * Nếu slider vẫn ở:
             *
             * floor → ceiling
             *
             * thì coi như chưa lọc giá.
             */
            const hasPriceFilter =
                minValue > floor ||
                maxValue < ceiling;


            minHidden.disabled =
                !hasPriceFilter;


            maxHidden.disabled =
                !hasPriceFilter;


            /*
             * Update label.
             */
            minLabel.textContent =
                formatPrice(
                    minValue
                );


            maxLabel.textContent =
                formatPrice(
                    maxValue
                );


            /*
             * Update thanh màu coral.
             */
            const total =
                Math.max(
                    ceiling - floor,
                    1
                );


            const left =
                (
                    (
                        minValue -
                        floor
                    )
                    /
                    total
                )
                * 100;


            const right =
                (
                    (
                        maxValue -
                        floor
                    )
                    /
                    total
                )
                * 100;


            progress.style.left =
                left + '%';


            progress.style.width =
                Math.max(
                    right - left,
                    0
                )
                + '%';


            /**
             * Khi nút min gần cuối,
             * đưa nó lên trên nút max
             * để vẫn kéo được.
             */
            minRange.style.zIndex =
                minValue >=
                ceiling * 0.75
                    ? '5'
                    : '3';
        };


    /**
     * -----------------------------------------------------
     * Khi đang kéo
     * -----------------------------------------------------
     */
    minRange.addEventListener(
        'input',
        () => {
            syncSlider(
                minRange
            );
        }
    );


    maxRange.addEventListener(
        'input',
        () => {
            syncSlider(
                maxRange
            );
        }
    );


    /**
     * -----------------------------------------------------
     * Khi thả slider
     * → submit filter
     * -----------------------------------------------------
     */
    minRange.addEventListener(
        'change',
        () => {
            syncSlider(
                minRange
            );

            filterForm.submit();
        }
    );


    maxRange.addEventListener(
        'change',
        () => {
            syncSlider(
                maxRange
            );

            filterForm.submit();
        }
    );


    /*
     * Render trạng thái ban đầu.
     */
    syncSlider();
}


/**
 * =========================================================
 * INIT
 * =========================================================
 */
document.addEventListener(
    'DOMContentLoaded',
    () => {
        initMilkGuide();

        initCategoryFilters();
    }
);