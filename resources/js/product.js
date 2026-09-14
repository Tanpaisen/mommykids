document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('mk-product-page');
    if (!page) return;

    // =========================================================
    // PRODUCT PAGE LAYOUT
    // =========================================================
    document.documentElement.classList.add('product-detail-active');
    document.body.classList.add('product-detail-active');

    // Giữ sidebar danh mục ở desktop.
    const sidebar = document.getElementById('mk-sidebar');

    if (sidebar) {
        sidebar.classList.remove('product-page-sidebar-hidden');

        const layout = sidebar.parentElement;
        layout?.classList.remove('product-page-layout-wide');
    }

    // =========================================================
    // PRODUCT DATA
    // =========================================================
    let images = [];

    try {
        const parsedImages = JSON.parse(page.dataset.images || '[]');

        if (Array.isArray(parsedImages)) {
            images = parsedImages.filter(
                (image) => typeof image === 'string' && image.trim() !== ''
            );
        }
    } catch (error) {
        console.error('Gallery JSON error:', error);
    }

    const productId = Number(page.dataset.productId) || 0;
    const stock = Math.max(0, Number(page.dataset.stock) || 0);

    // =========================================================
    // GALLERY
    // =========================================================
    const mainImage = document.getElementById('product-main-image');
    const fallback = document.getElementById('product-image-fallback');

    const thumbnails = [
        ...document.querySelectorAll('.product-thumbnail'),
    ];

    let currentImage = 0;
    if (mainImage) {
    mainImage.classList.add('is-primary-product-image');
}

    function getModalImage() {
        return document.getElementById('product-image-modal-image');
    }

    function setMainImageVisible() {
        if (mainImage) {
            mainImage.style.display = '';
        }

        fallback?.classList.add('is-hidden');
    }

    function setMainImageFallback() {
        if (mainImage) {
            mainImage.style.display = 'none';
        }

        fallback?.classList.remove('is-hidden');
    }

    function showImage(index) {
        if (!mainImage || images.length === 0) return;

        if (index < 0) {
            index = images.length - 1;
        }

        if (index >= images.length) {
            index = 0;
        }

        currentImage = index;
        mainImage.classList.toggle('is-primary-product-image', index === 0);
        const imageUrl = images[currentImage];

        mainImage.src = imageUrl;
        setMainImageVisible();

        thumbnails.forEach((thumbnail) => {
            const active =
                Number(thumbnail.dataset.index) === currentImage;

            thumbnail.classList.toggle('is-active', active);

            thumbnail.setAttribute(
                'aria-current',
                active ? 'true' : 'false'
            );
        });

        const modalImage = getModalImage();

        if (modalImage) {
            modalImage.src = imageUrl;
        }
    }

    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            const index = Number(thumbnail.dataset.index);

            if (!Number.isInteger(index)) return;

            showImage(index);
        });
    });

    document
        .getElementById('product-gallery-prev')
        ?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            showImage(currentImage - 1);
        });

    document
        .getElementById('product-gallery-next')
        ?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            showImage(currentImage + 1);
        });

    mainImage?.addEventListener('load', () => {
        setMainImageVisible();
    });

    mainImage?.addEventListener('error', () => {
        setMainImageFallback();
    });

    // =========================================================
    // IMAGE MODAL
    // =========================================================
    const modal = document.getElementById('product-image-modal');

    function openModal() {
        if (!modal || images.length === 0) return;

        const modalImage = getModalImage();

        if (modalImage && images[currentImage]) {
            modalImage.src = images[currentImage];
        }

        modal.classList.remove('is-hidden');
        modal.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;

        modal.classList.add('is-hidden');
        modal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';
    }

    document
        .getElementById('product-main-image-button')
        ?.addEventListener('click', openModal);

    document
        .getElementById('product-image-modal-close')
        ?.addEventListener('click', closeModal);

    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
            return;
        }

        if (!modal || modal.classList.contains('is-hidden')) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            showImage(currentImage - 1);
        }

        if (event.key === 'ArrowRight') {
            showImage(currentImage + 1);
        }
    });

    // =========================================================
    // QUANTITY
    // =========================================================
    const quantityInput = document.getElementById('product-quantity');

    function getQuantity() {
        if (!quantityInput) {
            return 1;
        }

        let value = Math.max(
            1,
            Number(quantityInput.value) || 1
        );

        if (stock > 0) {
            value = Math.min(value, stock);
        }

        quantityInput.value = value;

        return value;
    }

    document
        .getElementById('product-quantity-minus')
        ?.addEventListener('click', () => {
            if (!quantityInput) return;

            quantityInput.value = Math.max(
                1,
                getQuantity() - 1
            );
        });

    document
        .getElementById('product-quantity-plus')
        ?.addEventListener('click', () => {
            if (!quantityInput || stock <= 0) return;

            const value = getQuantity();

            if (value < stock) {
                quantityInput.value = value + 1;
            }
        });

    quantityInput?.addEventListener('input', () => {
        if (quantityInput.value === '') return;

        getQuantity();
    });

    quantityInput?.addEventListener('change', getQuantity);
    quantityInput?.addEventListener('blur', getQuantity);

    // =========================================================
    // MAIN ADD TO CART
    // =========================================================
    const cartButton = document.getElementById(
        'product-add-to-cart'
    );

    cartButton?.addEventListener('click', () => {
        if (!productId || stock <= 0) return;

        window.mkAddToCart?.(
            productId,
            cartButton,
            getQuantity()
        );
    });

    // =========================================================
    // RELATED PRODUCTS
    // =========================================================
    const relatedTrack = document.getElementById(
        'product-related-track'
    );

    const relatedPrev = document.getElementById(
        'product-related-prev'
    );

    const relatedNext = document.getElementById(
        'product-related-next'
    );

    function updateRelatedArrows() {
        if (!relatedTrack) return;

        const maxScrollLeft =
            relatedTrack.scrollWidth - relatedTrack.clientWidth;

        if (relatedPrev) {
            relatedPrev.disabled = relatedTrack.scrollLeft <= 2;
        }

        if (relatedNext) {
            relatedNext.disabled =
                relatedTrack.scrollLeft >= maxScrollLeft - 2;
        }
    }

    function scrollRelated(direction) {
        if (!relatedTrack) return;

        relatedTrack.scrollBy({
            left:
                direction *
                Math.max(
                    relatedTrack.clientWidth * 0.8,
                    280
                ),
            behavior: 'smooth',
        });
    }

    relatedPrev?.addEventListener('click', () => {
        scrollRelated(-1);
    });

    relatedNext?.addEventListener('click', () => {
        scrollRelated(1);
    });

    relatedTrack?.addEventListener(
        'scroll',
        updateRelatedArrows,
        { passive: true }
    );

    window.addEventListener(
        'resize',
        updateRelatedArrows,
        { passive: true }
    );

    requestAnimationFrame(updateRelatedArrows);

    // =========================================================
    // RELATED PRODUCT ADD TO CART
    // =========================================================
    document
        .querySelectorAll('.product-related-add')
        .forEach((button) => {
            button.addEventListener('click', () => {
                const relatedProductId = Number(
                    button.dataset.productId
                );

                if (!relatedProductId) return;

                window.mkAddToCart?.(
                    relatedProductId,
                    button,
                    1
                );
            });
        });
});