document.addEventListener('DOMContentLoaded', () => {

    /* =========================================================
       CONFIG
    ========================================================= */
    const config = JSON.parse(
        document.getElementById('checkout-config').textContent
    );

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content || '';


    /* =========================================================
       CHECKOUT HIDDEN ADDRESS
    ========================================================= */
    const province = document.getElementById('province');
    const district = document.getElementById('district');
    const ward = document.getElementById('ward');
    const fullName = document.getElementById('full_name');
    const phone = document.getElementById('phone');
    const address = document.getElementById('address');


    /* =========================================================
       PRICING ELEMENTS
    ========================================================= */
    const shippingText = document.getElementById('checkout-shipping');
    const shippingMirrors = document.querySelectorAll('[data-checkout-shipping]');
    const totalText = document.getElementById('checkout-total');
    const totalMobile = document.getElementById('checkout-total-mobile');
    const orderVoucherRow = document.getElementById('checkout-order-voucher-row');
    const orderVoucherText = document.getElementById('checkout-order-voucher');
    const shippingVoucherRow = document.getElementById('checkout-shipping-voucher-row');
    const shippingVoucherText = document.getElementById('checkout-shipping-voucher');
    const pointsRow = document.getElementById('checkout-points-row');
    const pointsText = document.getElementById('checkout-points-discount');


    /* =========================================================
       VOUCHER
    ========================================================= */
    const voucherToggle = document.getElementById('voucher-toggle');
    const voucherPanel = document.getElementById('voucher-panel');
    const voucherTriggerValue = document.getElementById('voucher-trigger-value');


    /* =========================================================
       POPUP THÊM ĐỊA CHỈ
    ========================================================= */
    const newAddressEditor = document.getElementById('new-address-editor');
    const newAddressForm = document.getElementById('checkout-new-address-form');
    const newProvince = document.getElementById('new-province');
    const newDistrict = document.getElementById('new-district');
    const newWard = document.getElementById('new-ward');
    const newAddressError = document.getElementById('new-address-error');
    const saveNewAddressButton = document.getElementById('save-new-address');


    /* =========================================================
       STATE
    ========================================================= */
    let addressReady = false;

    const pricing = {
        subtotal: Number(config.subtotal) || 0,
        shippingFee: 0,
        orderVoucherDiscount: Number(config.orderVoucherDiscount) || 0,
        shippingVoucherDiscount: Number(config.shippingVoucherDiscount) || 0,
        pointsDiscount: Number(config.pointsDiscount) || 0,
    };


    /* =========================================================
       HELPERS
    ========================================================= */
    const formatMoney = amount =>
        Number(amount || 0).toLocaleString('vi-VN') + 'đ';

    function setCheckoutButtonsDisabled(disabled) {
        document
            .querySelectorAll('#checkout-form button[type="submit"]')
            .forEach(button => {
                button.disabled = disabled;
            });
    }

    function setShippingMessage(message) {
        if (shippingText) {
            shippingText.textContent = message;
        }
        shippingMirrors.forEach(element => {
            element.textContent = message;
        });
    }

    function showNewAddressError(message = '') {
        if (!newAddressError) return;
        newAddressError.textContent = message;
        newAddressError.hidden = !message;
    }


    /* =========================================================
       POPUP THÊM ĐỊA CHỈ
    ========================================================= */
    function openNewAddressEditor() {
        if (!newAddressEditor) return;
        showNewAddressError('');
        newAddressEditor.hidden = false;
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            document.getElementById('new-recipient-name')?.focus();
        }, 50);
    }

    function closeNewAddressEditor() {
        if (!newAddressEditor) return;
        newAddressEditor.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-open-new-address]').forEach(button => {
        button.addEventListener('click', openNewAddressEditor);
    });

    document.getElementById('close-new-address')?.addEventListener('click', closeNewAddressEditor);
    document.getElementById('cancel-new-address')?.addEventListener('click', closeNewAddressEditor);

    newAddressEditor?.addEventListener('click', event => {
        if (event.target === newAddressEditor) {
            closeNewAddressEditor();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && newAddressEditor && !newAddressEditor.hidden) {
            closeNewAddressEditor();
        }
    });


    /* =========================================================
       THÊM ĐỊA CHỈ: TỈNH -> HUYỆN CÓ TUYẾN GHN
    ========================================================= */
    newProvince?.addEventListener('change', async () => {
        const provinceName = document.getElementById('new-province-name');
        if (provinceName) {
            provinceName.value = newProvince.selectedOptions[0]?.textContent.trim() || '';
        }

        newDistrict.innerHTML = '<option value="">Đang tải...</option>';
        newDistrict.disabled = true;
        newWard.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        newWard.disabled = true;
        showNewAddressError('');

        if (!newProvince.value) return;

        try {
            const response = await fetch(`${config.routes.districts}?province_id=${encodeURIComponent(newProvince.value)}`, {
                headers: { 'Accept': 'application/json' }
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Không tải được Quận/Huyện.');
            }

            const rows = Array.isArray(payload) ? payload : (payload.data || []);

            newDistrict.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';

            if (rows.length === 0) {
                newDistrict.innerHTML = '<option value="">GHN chưa hỗ trợ khu vực này</option>';
                showNewAddressError('Hiện chưa có Quận/Huyện được GHN hỗ trợ giao hàng trong tỉnh này.');
                return;
            }

            rows.forEach(item => {
                const option = document.createElement('option');
                option.value = item.DistrictID;
                option.textContent = item.DistrictName;
                newDistrict.appendChild(option);
            });

            newDistrict.disabled = false;

        } catch (error) {
            console.error(error);
            newDistrict.innerHTML = '<option value="">Không tải được Quận/Huyện</option>';
            showNewAddressError(error.message || 'Không tải được Quận/Huyện.');
        }
    });


    /* =========================================================
       HUYỆN -> XÃ CÓ KHẢ NĂNG GIAO
    ========================================================= */
    newDistrict?.addEventListener('change', async () => {
        const districtName = document.getElementById('new-district-name');
        if (districtName) {
            districtName.value = newDistrict.selectedOptions[0]?.textContent.trim() || '';
        }

        newWard.innerHTML = '<option value="">Đang tải...</option>';
        newWard.disabled = true;
        showNewAddressError('');

        if (!newDistrict.value) return;

        try {
            const response = await fetch(`${config.routes.wards}?district_id=${encodeURIComponent(newDistrict.value)}`, {
                headers: { 'Accept': 'application/json' }
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Không tải được Phường/Xã.');
            }

            const rows = Array.isArray(payload) ? payload : (payload.data || []);

            newWard.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';

            if (rows.length === 0) {
                newWard.innerHTML = '<option value="">GHN chưa hỗ trợ Phường/Xã</option>';
                showNewAddressError('Không có Phường/Xã phù hợp để GHN giao hàng.');
                return;
            }

            rows.forEach(item => {
                const option = document.createElement('option');
                option.value = item.WardCode;
                option.textContent = item.WardName;
                newWard.appendChild(option);
            });

            newWard.disabled = false;

        } catch (error) {
            console.error(error);
            newWard.innerHTML = '<option value="">Không tải được Phường/Xã</option>';
            showNewAddressError(error.message || 'Không tải được Phường/Xã.');
        }
    });


    newWard?.addEventListener('change', () => {
        const wardName = document.getElementById('new-ward-name');
        if (wardName) {
            wardName.value = newWard.selectedOptions[0]?.textContent.trim() || '';
        }
    });


    /*
     * Không preventDefault.
     * Form vẫn submit bình thường tới AddressController.
     * Chỉ khóa nút để tránh bấm 2 lần.
     */
    newAddressForm?.addEventListener('submit', () => {
        showNewAddressError('');
        if (saveNewAddressButton) {
            saveNewAddressButton.disabled = true;
            saveNewAddressButton.textContent = 'Đang lưu...';
        }
    });


    /* =========================================================
       VOUCHER UI
    ========================================================= */
    voucherToggle?.addEventListener('click', () => {
        if (!voucherPanel) return;
        voucherPanel.hidden = !voucherPanel.hidden;
    });

    function updateVoucherTrigger() {
        if (!voucherTriggerValue) return;
        const totalVoucher = pricing.orderVoucherDiscount + pricing.shippingVoucherDiscount;
        voucherTriggerValue.textContent = totalVoucher > 0 ? '-' + formatMoney(totalVoucher) : 'Chọn voucher';
    }


    /* =========================================================
       RENDER GIÁ
    ========================================================= */
    function renderPricing(serverTotal = null) {
        const shippingLabel = pricing.shippingFee > 0 ? formatMoney(pricing.shippingFee) : 'Chưa tính';

        if (shippingText) shippingText.textContent = shippingLabel;
        shippingMirrors.forEach(element => element.textContent = shippingLabel);

        if (orderVoucherRow && orderVoucherText) {
            orderVoucherRow.hidden = pricing.orderVoucherDiscount <= 0;
            orderVoucherText.textContent = '-' + formatMoney(pricing.orderVoucherDiscount);
        }

        if (shippingVoucherRow && shippingVoucherText) {
            shippingVoucherRow.hidden = pricing.shippingVoucherDiscount <= 0;
            shippingVoucherText.textContent = '-' + formatMoney(pricing.shippingVoucherDiscount);
        }

        if (pointsRow && pointsText) {
            pointsRow.hidden = pricing.pointsDiscount <= 0;
            pointsText.textContent = '-' + formatMoney(pricing.pointsDiscount);
        }

        const total = serverTotal !== null
            ? Number(serverTotal)
            : Math.max(0, pricing.subtotal - pricing.orderVoucherDiscount + pricing.shippingFee - pricing.shippingVoucherDiscount - pricing.pointsDiscount);

        const totalLabel = formatMoney(total);

        if (totalText) totalText.textContent = totalLabel;
        if (totalMobile) totalMobile.textContent = totalLabel;

        updateVoucherTrigger();
    }


    function applyPricingPayload(data) {
        if (!data) return;
        pricing.shippingFee = Number(data.shipping_fee ?? pricing.shippingFee) || 0;
        pricing.orderVoucherDiscount = Number(data.order_voucher_discount ?? pricing.orderVoucherDiscount) || 0;
        pricing.shippingVoucherDiscount = Number(data.shipping_voucher_discount ?? pricing.shippingVoucherDiscount) || 0;
        pricing.pointsDiscount = Number(data.points_discount ?? pricing.pointsDiscount) || 0;
        renderPricing(data.total ?? null);
    }

    function resetShippingPricing() {
        pricing.shippingFee = 0;
        pricing.shippingVoucherDiscount = 0;
        renderPricing();
    }


    /* =========================================================
       XÁC THỰC DISTRICT ĐÃ LƯU
    ========================================================= */
    async function loadDistricts(provinceId, districtId) {
        district.value = '';
        ward.value = '';
        resetShippingPricing();

        if (!provinceId || !districtId) return false;

        try {
            const response = await fetch(`${config.routes.districts}?province_id=${encodeURIComponent(provinceId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Không tải được Quận/Huyện.');
            }

            const rows = Array.isArray(payload) ? payload : (payload.data || []);
            const found = rows.some(item => String(item.DistrictID) === String(districtId));

            if (found) {
                district.value = String(districtId);
            }
            return found;

        } catch (error) {
            console.error(error);
            return false;
        }
    }


    /* =========================================================
       XÁC THỰC WARD ĐÃ LƯU
    ========================================================= */
    async function loadWards(districtId, wardCode) {
        ward.value = '';
        resetShippingPricing();

        if (!districtId || !wardCode) return false;

        try {
            const response = await fetch(`${config.routes.wards}?district_id=${encodeURIComponent(districtId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Không tải được Phường/Xã.');
            }

            const rows = Array.isArray(payload) ? payload : (payload.data || []);
            const found = rows.some(item => String(item.WardCode) === String(wardCode));

            if (found) {
                ward.value = String(wardCode);
            }
            return found;

        } catch (error) {
            console.error(error);
            return false;
        }
    }


    /* =========================================================
       TÍNH PHÍ GHN THẬT
    ========================================================= */
    async function calculateShippingFee() {
        if (!district.value || !ward.value) return false;

        setShippingMessage('Đang tính...');

        try {
            const response = await fetch(config.routes.shippingFee, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    district_id: Number(district.value),
                    ward_code: String(ward.value),
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = data.message || 'Không tính được phí vận chuyển GHN.';
                setShippingMessage(message);
                console.error('GHN shipping error:', data);
                return false;
            }

            applyPricingPayload(data);
            return true;

        } catch (error) {
            console.error(error);
            setShippingMessage('Không kết nối được GHN');
            return false;
        }
    }


    /* =========================================================
       VOUCHER API
    ========================================================= */
    const appliedVoucherIds = {
        order: document.getElementById('voucher-order-id')?.value || '',
        shipping: document.getElementById('voucher-shipping-id')?.value || '',
    };

    const voucherPending = {
        order: false,
        shipping: false,
    };


    async function changeVoucher(type, voucherId) {
        const select = document.getElementById(`voucher-${type}-id`);
        const status = document.getElementById(`voucher-${type}-status`);
        const removeButton = document.querySelector(`[data-voucher-remove="${type}"]`);

        if (!select || !status || voucherPending[type]) return;
        if (voucherId === appliedVoucherIds[type]) return;

        voucherPending[type] = true;
        select.disabled = true;
        if (removeButton) removeButton.disabled = true;

        status.classList.remove('is-error');
        status.textContent = voucherId ? 'Đang áp dụng mã...' : 'Đang gỡ mã...';

        try {
            const response = await fetch(
                voucherId ? config.routes.voucherApply : config.routes.voucherRemove,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(
                        voucherId ? { type, voucher_id: voucherId } : { type }
                    ),
                }
            );

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Không thể cập nhật voucher.');
            }

            const acceptedId = voucherId ? String(data.voucher?.id ?? voucherId) : '';
            appliedVoucherIds[type] = acceptedId;
            select.value = acceptedId;
            status.textContent = data.message || (acceptedId ? 'Đã áp dụng mã.' : 'Đã gỡ mã.');

            if (removeButton) {
                removeButton.hidden = !acceptedId;
            }

            applyPricingPayload(data.pricing);

        } catch (error) {
            select.value = appliedVoucherIds[type];
            status.textContent = error.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
            status.classList.add('is-error');
        } finally {
            voucherPending[type] = false;
            select.disabled = false;
            if (removeButton) removeButton.disabled = false;
        }
    }


    ['order', 'shipping'].forEach(type => {
        const select = document.getElementById(`voucher-${type}-id`);

        select?.addEventListener('change', () => {
            changeVoucher(type, select.value);
        });

        document.querySelector(`[data-voucher-remove="${type}"]`)?.addEventListener('click', () => {
            changeVoucher(type, '');
        });
    });


    /* =========================================================
       CHECKOUT SUBMIT GUARD
    ========================================================= */
    document.getElementById('checkout-form')?.addEventListener('submit', event => {
        if (addressReady) return;
        event.preventDefault();

        if (config.hasDefaultAddress) {
            alert('Địa chỉ hiện tại chưa tính được phí vận chuyển GHN.\n\nVui lòng bấm vào khối địa chỉ nhận hàng để chọn hoặc sửa địa chỉ khác.');
        } else {
            openNewAddressEditor();
        }
    });


    /* =========================================================
       KHỞI TẠO CHECKOUT
    ========================================================= */
    renderPricing();

    if (config.hasDefaultAddress) {
        /*
         * ĐÃ CÓ ĐỊA CHỈ
         */
        setCheckoutButtonsDisabled(true);

        (async () => {
            try {
                const provinceId = String(config.oldProvinceId || '');
                const districtId = String(config.oldDistrictId || '');
                const wardCode = String(config.oldWardCode || '');

                if (!provinceId || !districtId || !wardCode || !address.value) {
                    throw new Error('Địa chỉ mặc định chưa đầy đủ thông tin GHN.');
                }

                province.value = provinceId;

                const districtLoaded = await loadDistricts(provinceId, districtId);
                if (!districtLoaded) {
                    throw new Error('Địa chỉ này hiện chưa nằm trong tuyến GHN hỗ trợ. Vui lòng chọn địa chỉ khác.');
                }

                const wardLoaded = await loadWards(districtId, wardCode);
                if (!wardLoaded) {
                    throw new Error('Phường/Xã của địa chỉ hiện không được GHN hỗ trợ. Vui lòng chọn địa chỉ khác.');
                }

                const feeLoaded = await calculateShippingFee();
                if (!feeLoaded) {
                    throw new Error('Không tính được phí vận chuyển cho địa chỉ này.');
                }

                addressReady = true;
                setCheckoutButtonsDisabled(false);

            } catch (error) {
                console.error('Checkout address error:', error);
                addressReady = false;
                setCheckoutButtonsDisabled(true);
                setShippingMessage(error.message || 'Địa chỉ chưa được GHN hỗ trợ');
            }
        })();

    } else {
        /*
         * CHƯA CÓ ĐỊA CHỈ
         */
        addressReady = false;
        setCheckoutButtonsDisabled(true);
        setTimeout(() => { openNewAddressEditor(); }, 150);
    }
});