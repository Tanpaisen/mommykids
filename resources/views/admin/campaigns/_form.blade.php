@php
    $isEdit = $campaign->exists;

    $campaignTypeValue = old(
        'campaign_type_name',
        $campaign->campaignType?->name ?? ''
    );
@endphp

@if ($errors->any())
    <div class="card mb-5 border border-red-200 bg-red-50">
        <p class="font-semibold text-red-600">
            Vui lòng kiểm tra lại dữ liệu.
        </p>

        <ul class="mt-2 text-sm text-red-500 list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    id="campaignForm"
    method="POST"
    action="{{ $isEdit
        ? route('admin.campaigns.update', $campaign)
        : route('admin.campaigns.store') }}"
    class="space-y-5"
>
    @csrf

    @if (!$isEdit)
        <input
            type="hidden"
            name="submission_token"
            value="{{ $submissionToken }}"
        >
    @endif

    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="card">
        <div class="mb-5">
            <h3 class="font-semibold text-ink">
                Thông tin chiến dịch
            </h3>

            <p class="text-sm text-ink-soft mt-1">
                Loại chiến dịch lấy từ dữ liệu. Có thể chọn loại đã có hoặc nhập loại mới.
                Campaign và Voucher được áp dụng độc lập nếu Voucher đủ điều kiện.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-ink mb-2">
                    Tên chiến dịch *
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $campaign->name) }}"
                    class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-ink mb-2">
                    Loại chiến dịch *
                </label>

                <input
                    type="text"
                    name="campaign_type_name"
                    list="campaignTypeOptions"
                    value="{{ $campaignTypeValue }}"
                    placeholder="VD: Khuyến mãi sản phẩm"
                    autocomplete="off"
                    class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                    required
                >

                <datalist id="campaignTypeOptions">
                    @foreach ($campaignTypes as $type)
                        <option value="{{ $type->name }}"></option>
                    @endforeach
                </datalist>

                <p class="text-xs text-ink-soft mt-1">
                    Nhập loại mới thì hệ thống tự lưu để dùng cho lần sau.
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ink mb-2">
                    Priority *
                </label>

                <input
                    type="number"
                    min="0"
                    name="priority"
                    value="{{ old('priority', $campaign->priority ?? 0) }}"
                    class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                    required
                >

                <p class="text-xs text-ink-soft mt-1">
                    Khi một sản phẩm nằm trong nhiều campaign, priority cao hơn được xét trước.
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ink mb-2">
                    Bắt đầu
                </label>

                <input
                    type="datetime-local"
                    name="starts_at"
                    value="{{ old(
                        'starts_at',
                        $campaign->starts_at?->format('Y-m-d\TH:i')
                    ) }}"
                    class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-ink mb-2">
                    Kết thúc
                </label>

                <input
                    type="datetime-local"
                    name="ends_at"
                    value="{{ old(
                        'ends_at',
                        $campaign->ends_at?->format('Y-m-d\TH:i')
                    ) }}"
                    class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                >
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-ink mb-2">
                    Mô tả
                </label>

                <textarea
                    name="description"
                    rows="3"
                    class="w-full border border-admin-border rounded-xl px-4 py-3 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                >{{ old('description', $campaign->description) }}</textarea>
            </div>

            <div class="lg:col-span-2">
                <label class="flex items-center gap-3 border border-admin-border rounded-xl px-4 py-3 bg-white cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="accent-coral"
                        @checked(old('is_active', $campaign->is_active))
                    >

                    <span>
                        <strong class="block text-sm text-ink">
                            Kích hoạt chiến dịch
                        </strong>

                        <small class="text-ink-soft">
                            Campaign chỉ áp dụng khi bật và nằm trong thời gian chạy.
                        </small>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="mb-5">
            <h3 class="font-semibold text-ink">
                Sản phẩm áp dụng
            </h3>

            <p class="text-sm text-ink-soft mt-1">
                Tìm sản phẩm rồi chọn một cách giảm: giá khuyến mãi cố định hoặc theo phần trăm.
            </p>
        </div>

        <div class="relative">
            <label class="block text-sm font-semibold text-ink mb-2">
                Tìm và thêm sản phẩm
            </label>

            <div class="relative">
                <input
                    id="campaignProductSearch"
                    type="text"
                    autocomplete="off"
                    placeholder="VD: Aptamil, Merries, bình sữa..."
                    class="w-full border border-admin-border rounded-xl px-4 py-3 pr-12 bg-white outline-none focus:border-coral focus:ring-2 focus:ring-coral/10"
                >

                <span
                    id="campaignProductSearchSpinner"
                    class="hidden absolute right-4 top-1/2 -translate-y-1/2 text-ink-soft text-sm"
                >
                    ...
                </span>
            </div>

            <p class="text-xs text-ink-soft mt-2">
                Gõ ít nhất 2 ký tự. Hệ thống chỉ trả tối đa 10 kết quả mỗi lần tìm.
            </p>

            <div
                id="campaignProductResults"
                class="hidden absolute z-30 left-0 right-0 mt-2 max-h-80 overflow-y-auto rounded-xl border border-admin-border bg-white shadow-xl"
            ></div>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            <div>
                <h4 class="font-semibold text-ink">
                    Sản phẩm đã chọn
                </h4>

                <p class="text-xs text-ink-soft mt-1">
                    Mỗi sản phẩm chỉ nhập một giá trị giảm.
                </p>
            </div>

            <span
                id="campaignSelectedCount"
                class="inline-flex min-w-8 h-8 items-center justify-center rounded-full bg-coral/10 text-coral text-sm font-semibold px-2"
            >
                {{ $selectedProductRows->count() }}
            </span>
        </div>

        <div
            id="campaignSelectedEmpty"
            class="{{ $selectedProductRows->isEmpty() ? '' : 'hidden' }} mt-4 rounded-xl border border-dashed border-admin-border py-10 text-center"
        >
            <div class="text-3xl">🔎</div>

            <p class="font-semibold text-ink mt-2">
                Chưa chọn sản phẩm
            </p>

            <p class="text-sm text-ink-soft mt-1">
                Tìm sản phẩm ở ô phía trên rồi bấm “Thêm”.
            </p>
        </div>

        <div
            id="campaignSelectedTableWrap"
            class="{{ $selectedProductRows->isEmpty() ? 'hidden' : '' }} mt-4 overflow-x-auto border border-admin-border rounded-xl"
        >
            <table class="w-full min-w-[1180px] text-sm">
                <thead class="bg-admin-bg text-xs uppercase text-ink-soft">
                    <tr>
                        <th class="text-left px-4 py-3 min-w-[300px]">Sản phẩm</th>
                        <th class="text-left px-4 py-3">Giá hiện tại</th>
                        <th class="text-left px-4 py-3">Kiểu giảm</th>
                        <th class="text-left px-4 py-3">Giá trị</th>
                        <th class="text-left px-4 py-3">Giá sau KM</th>
                        <th class="text-left px-4 py-3">Kho campaign</th>
                        <th class="text-left px-4 py-3">Tối đa/user</th>
                        <th class="text-center px-4 py-3">Bỏ</th>
                    </tr>
                </thead>

                <tbody
                    id="campaignSelectedProducts"
                    class="divide-y divide-admin-border"
                >
                    @foreach ($selectedProductRows as $row)
                        <tr
                            data-product-id="{{ $row['id'] }}"
                            data-base-price="{{ $row['price'] }}"
                            class="campaign-selected-row"
                        >
                            <td class="px-4 py-4">
                                <input
                                    type="hidden"
                                    name="products[{{ $row['id'] }}][product_id]"
                                    value="{{ $row['id'] }}"
                                >

                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg border border-admin-border bg-white overflow-hidden shrink-0 flex items-center justify-center">
                                        @if ($row['image_url'])
                                            <img
                                                src="{{ $row['image_url'] }}"
                                                alt="{{ $row['name'] }}"
                                                class="w-full h-full object-contain"
                                            >
                                        @else
                                            <span>🖼️</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink">
                                            {{ $row['name'] }}
                                        </p>

                                        <p class="text-xs text-ink-soft mt-1">
                                            {{ $row['category'] ?: 'Chưa phân loại' }}
                                            · Tồn kho {{ $row['stock'] }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4 font-semibold text-ink">
                                {{ number_format($row['price'], 0, ',', '.') }}đ
                            </td>

                            <td class="px-4 py-4">
                                <select
                                    name="products[{{ $row['id'] }}][discount_method]"
                                    class="campaign-discount-method w-36 border border-admin-border rounded-lg px-3 py-2 bg-white"
                                >
                                    <option
                                        value="percent"
                                        @selected($row['discount_method'] === 'percent')
                                    >
                                        Theo %
                                    </option>

                                    <option
                                        value="fixed"
                                        @selected($row['discount_method'] === 'fixed')
                                    >
                                        Giá cố định
                                    </option>
                                </select>
                            </td>

                            <td class="px-4 py-4">
                                <input
                                    type="number"
                                    min="0"
                                    name="products[{{ $row['id'] }}][discount_value]"
                                    value="{{ $row['discount_value'] }}"
                                    class="campaign-discount-value w-32 border border-admin-border rounded-lg px-3 py-2"
                                    required
                                >
                            </td>

                            <td class="px-4 py-4">
                                <span class="campaign-final-price font-semibold text-coral">—</span>
                            </td>

                            <td class="px-4 py-4">
                                <input
                                    type="number"
                                    min="1"
                                    name="products[{{ $row['id'] }}][stock_limit]"
                                    value="{{ $row['stock_limit'] }}"
                                    placeholder="Không giới hạn"
                                    class="w-36 border border-admin-border rounded-lg px-3 py-2"
                                >
                            </td>

                            <td class="px-4 py-4">
                                <input
                                    type="number"
                                    min="1"
                                    name="products[{{ $row['id'] }}][max_per_user]"
                                    value="{{ $row['max_per_user'] }}"
                                    placeholder="Không giới hạn"
                                    class="w-36 border border-admin-border rounded-lg px-3 py-2"
                                >
                            </td>

                            <td class="px-4 py-4 text-center">
                                <button
                                    type="button"
                                    class="campaign-remove-product inline-flex items-center justify-center px-3 py-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                                >
                                    Bỏ
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
        <a
            href="{{ route('admin.campaigns.index') }}"
            class="inline-flex items-center justify-center h-11 px-5 rounded-xl border border-admin-border bg-white text-sm font-semibold text-ink hover:bg-admin-bg transition"
        >
            Hủy
        </a>

        <button
            id="campaignSubmitButton"
            type="submit"
            class="inline-flex items-center justify-center h-11 px-6 rounded-xl bg-coral text-white text-sm font-semibold hover:opacity-90 transition"
        >
            {{ $isEdit ? 'Lưu thay đổi' : 'Tạo chiến dịch' }}
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('campaignProductSearch');
    const resultsBox = document.getElementById('campaignProductResults');
    const spinner = document.getElementById('campaignProductSearchSpinner');
    const selectedBody = document.getElementById('campaignSelectedProducts');
    const selectedWrap = document.getElementById('campaignSelectedTableWrap');
    const selectedEmpty = document.getElementById('campaignSelectedEmpty');
    const selectedCount = document.getElementById('campaignSelectedCount');

    const searchUrl = @json(
        route('admin.campaigns.products.search')
    );

    let timer = null;
    let requestController = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function money(value) {
        return new Intl.NumberFormat('vi-VN')
            .format(Number(value || 0)) + 'đ';
    }

    function selectedIds() {
        return new Set(
            Array.from(
                selectedBody.querySelectorAll('[data-product-id]')
            ).map(row => String(row.dataset.productId))
        );
    }

    function refreshSelectedState() {
        const count = selectedBody.querySelectorAll(
            '[data-product-id]'
        ).length;

        selectedCount.textContent = count;
        selectedEmpty.classList.toggle('hidden', count > 0);
        selectedWrap.classList.toggle('hidden', count === 0);
    }

    function productImage(product) {
        if (!product.image_url) {
            return `
                <div class="w-12 h-12 rounded-lg border border-admin-border bg-white flex items-center justify-center shrink-0">
                    🖼️
                </div>
            `;
        }

        return `
            <div class="w-12 h-12 rounded-lg border border-admin-border bg-white overflow-hidden shrink-0">
                <img
                    src="${escapeHtml(product.image_url)}"
                    alt=""
                    class="w-full h-full object-contain"
                >
            </div>
        `;
    }

    function updateDiscountInput(row) {
        const method = row.querySelector(
            '.campaign-discount-method'
        );

        const input = row.querySelector(
            '.campaign-discount-value'
        );

        if (!method || !input) {
            return;
        }

        if (method.value === 'percent') {
            input.min = '1';
            input.max = '100';
            input.placeholder = 'VD: 20';
        } else {
            input.min = '0';
            input.removeAttribute('max');
            input.placeholder = 'VD: 399000';
        }
    }

    function updateFinalPrice(row) {
        const basePrice = Number(row.dataset.basePrice || 0);

        const method = row.querySelector(
            '.campaign-discount-method'
        )?.value;

        const rawValue = row.querySelector(
            '.campaign-discount-value'
        )?.value;

        const output = row.querySelector(
            '.campaign-final-price'
        );

        if (!output) {
            return;
        }

        if (rawValue === '' || rawValue === null) {
            output.textContent = '—';
            return;
        }

        const value = Number(rawValue || 0);
        let finalPrice = basePrice;

        if (method === 'percent') {
            finalPrice = Math.max(
                0,
                Math.round(
                    basePrice * (100 - value) / 100
                )
            );
        } else {
            finalPrice = Math.max(0, value);
        }

        output.textContent = money(finalPrice);
    }

    function bindRow(row) {
        const method = row.querySelector(
            '.campaign-discount-method'
        );

        const value = row.querySelector(
            '.campaign-discount-value'
        );

        method?.addEventListener('change', function () {
            updateDiscountInput(row);
            updateFinalPrice(row);
        });

        value?.addEventListener('input', function () {
            updateFinalPrice(row);
        });

        updateDiscountInput(row);
        updateFinalPrice(row);
    }

    function addProduct(product) {
        if (selectedIds().has(String(product.id))) {
            return;
        }

        const id = Number(product.id);
        const row = document.createElement('tr');

        row.dataset.productId = String(id);
        row.dataset.basePrice = String(Number(product.price || 0));
        row.className = 'campaign-selected-row';

        row.innerHTML = `
            <td class="px-4 py-4">
                <input
                    type="hidden"
                    name="products[${id}][product_id]"
                    value="${id}"
                >

                <div class="flex items-center gap-3">
                    ${productImage(product)}

                    <div class="min-w-0">
                        <p class="font-semibold text-ink">
                            ${escapeHtml(product.name)}
                        </p>

                        <p class="text-xs text-ink-soft mt-1">
                            ${escapeHtml(product.category || 'Chưa phân loại')}
                            · Tồn kho ${Number(product.stock || 0)}
                        </p>
                    </div>
                </div>
            </td>

            <td class="px-4 py-4 font-semibold text-ink">
                ${money(product.price)}
            </td>

            <td class="px-4 py-4">
                <select
                    name="products[${id}][discount_method]"
                    class="campaign-discount-method w-36 border border-admin-border rounded-lg px-3 py-2 bg-white"
                >
                    <option value="percent">Theo %</option>
                    <option value="fixed">Giá cố định</option>
                </select>
            </td>

            <td class="px-4 py-4">
                <input
                    type="number"
                    min="1"
                    max="100"
                    name="products[${id}][discount_value]"
                    placeholder="VD: 20"
                    class="campaign-discount-value w-32 border border-admin-border rounded-lg px-3 py-2"
                    required
                >
            </td>

            <td class="px-4 py-4">
                <span class="campaign-final-price font-semibold text-coral">—</span>
            </td>

            <td class="px-4 py-4">
                <input
                    type="number"
                    min="1"
                    name="products[${id}][stock_limit]"
                    placeholder="Không giới hạn"
                    class="w-36 border border-admin-border rounded-lg px-3 py-2"
                >
            </td>

            <td class="px-4 py-4">
                <input
                    type="number"
                    min="1"
                    name="products[${id}][max_per_user]"
                    placeholder="Không giới hạn"
                    class="w-36 border border-admin-border rounded-lg px-3 py-2"
                >
            </td>

            <td class="px-4 py-4 text-center">
                <button
                    type="button"
                    class="campaign-remove-product inline-flex items-center justify-center px-3 py-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition"
                >
                    Bỏ
                </button>
            </td>
        `;

        selectedBody.appendChild(row);
        bindRow(row);
        refreshSelectedState();

        renderSearchResults(
            window.__campaignLastProducts || []
        );
    }

    function renderSearchResults(products) {
        window.__campaignLastProducts = products;

        if (!products.length) {
            resultsBox.innerHTML = `
                <div class="px-4 py-5 text-center text-sm text-ink-soft">
                    Không tìm thấy sản phẩm phù hợp.
                </div>
            `;

            resultsBox.classList.remove('hidden');
            return;
        }

        const ids = selectedIds();

        resultsBox.innerHTML = products
            .map(product => {
                const added = ids.has(String(product.id));

                return `
                    <div class="flex items-center justify-between gap-4 px-4 py-3 border-b border-admin-border last:border-b-0 hover:bg-admin-bg/50">
                        <div class="flex items-center gap-3 min-w-0">
                            ${productImage(product)}

                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-ink truncate">
                                    ${escapeHtml(product.name)}
                                </p>

                                <p class="text-xs text-ink-soft mt-1">
                                    ${escapeHtml(product.category || 'Chưa phân loại')}
                                    · ${money(product.price)}
                                    · Tồn ${Number(product.stock || 0)}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="campaign-add-search-product shrink-0 px-4 py-2 rounded-lg text-sm font-semibold transition
                                ${added
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'bg-coral/10 text-coral hover:bg-coral hover:text-white'}"
                            data-product-id="${Number(product.id)}"
                            ${added ? 'disabled' : ''}
                        >
                            ${added ? 'Đã thêm' : 'Thêm'}
                        </button>
                    </div>
                `;
            })
            .join('');

        resultsBox.classList.remove('hidden');
    }

    async function searchProducts() {
        const keyword = searchInput.value.trim();

        if (keyword.length < 2) {
            return;
        }

        if (requestController) {
            requestController.abort();
        }

        requestController = new AbortController();
        spinner.classList.remove('hidden');

        try {
            const response = await fetch(
                `${searchUrl}?q=${encodeURIComponent(keyword)}`,
                {
                    headers: {
                        'Accept': 'application/json',
                    },
                    signal: requestController.signal,
                }
            );

            if (!response.ok) {
                throw new Error('Không tìm được sản phẩm.');
            }

            const payload = await response.json();
            renderSearchResults(payload.data || []);
        } catch (error) {
            if (error.name !== 'AbortError') {
                resultsBox.innerHTML = `
                    <div class="px-4 py-5 text-center text-sm text-red-500">
                        Không thể tải danh sách sản phẩm.
                    </div>
                `;

                resultsBox.classList.remove('hidden');
            }
        } finally {
            spinner.classList.add('hidden');
        }
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);

        const keyword = searchInput.value.trim();

        if (keyword.length < 2) {
            if (requestController) {
                requestController.abort();
            }

            resultsBox.innerHTML = `
                <div class="px-4 py-4 text-center text-sm text-ink-soft">
                    Gõ ít nhất 2 ký tự để tìm sản phẩm.
                </div>
            `;

            resultsBox.classList.toggle(
                'hidden',
                keyword.length === 0
            );

            spinner.classList.add('hidden');
            return;
        }

        timer = setTimeout(searchProducts, 350);
    });

    resultsBox.addEventListener('click', function (event) {
        const button = event.target.closest(
            '.campaign-add-search-product'
        );

        if (!button || button.disabled) {
            return;
        }

        const product = (
            window.__campaignLastProducts || []
        ).find(
            item =>
                String(item.id)
                ===
                String(button.dataset.productId)
        );

        if (product) {
            addProduct(product);
        }
    });

    selectedBody.addEventListener('click', function (event) {
        const button = event.target.closest(
            '.campaign-remove-product'
        );

        if (!button) {
            return;
        }

        const row = button.closest('[data-product-id]');

        if (row) {
            row.remove();
            refreshSelectedState();

            renderSearchResults(
                window.__campaignLastProducts || []
            );
        }
    });

    document.addEventListener('click', function (event) {
        if (
            !resultsBox.contains(event.target)
            && event.target !== searchInput
        ) {
            resultsBox.classList.add('hidden');
        }
    });

    selectedBody
        .querySelectorAll('[data-product-id]')
        .forEach(bindRow);

    refreshSelectedState();

    /*
     * Chặn double-submit ở phía trình duyệt.
     * Backend cũng có submission_token nên vẫn an toàn
     * nếu trình duyệt gửi POST lặp lại.
     */
    const campaignForm = document.getElementById(
        'campaignForm'
    );

    const submitButton = document.getElementById(
        'campaignSubmitButton'
    );

    let isSubmitting = false;

    campaignForm?.addEventListener(
        'submit',
        function (event) {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            isSubmitting = true;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add(
                    'opacity-60',
                    'cursor-not-allowed'
                );

                submitButton.textContent =
                    @json(
                        $isEdit
                            ? 'Đang lưu...'
                            : 'Đang tạo...'
                    );
            }
        }
    );
});
</script>
