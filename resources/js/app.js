import './bootstrap';

import './category';
import '../css/category.css';

import './product';
import '../css/product.css';


/**
 * Thêm sản phẩm vào giỏ hàng.
 *
 * quantity mặc định = 1 nên những chỗ cũ đang gọi:
 * mkAddToCart(productId, button)
 * vẫn hoạt động bình thường.
 */
window.mkAddToCart = async function mkAddToCart(
    productId,
    buttonEl,
    quantity = 1
) {
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.content;

    const safeQuantity = Math.max(
        1,
        Number(quantity) || 1
    );

    buttonEl?.setAttribute('disabled', 'true');

    try {
        const res = await fetch('/api/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: safeQuantity,
            }),
        });

        if (!res.ok) {
            throw new Error('Request failed');
        }

        const data = await res.json();

        document.querySelectorAll('.mk-cart-count').forEach((el) => {
            el.textContent = data.cart_count;
        });

        mkToast(
            data.message ?? 'Đã thêm vào giỏ hàng'
        );
    } catch (err) {
        mkToast(
            'Không thể thêm vào giỏ hàng, vui lòng thử lại'
        );

        console.error(err);
    } finally {
        buttonEl?.removeAttribute('disabled');
    }
};


/**
 * Toast thông báo.
 */
function mkToast(message) {
    const toast = document.getElementById('mk-toast');

    if (!toast) return;

    toast.textContent = message;
    toast.classList.remove('hidden');

    clearTimeout(window.__mkToastTimer);

    window.__mkToastTimer = setTimeout(() => {
        toast.classList.add('hidden');
    }, 2000);
}


/**
 * Sidebar mobile + Hero slider.
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById(
        'mk-sidebar-toggle'
    );

    const sidebar = document.getElementById(
        'mk-sidebar'
    );

    const overlay = document.getElementById(
        'mk-sidebar-overlay'
    );


    /**
     * Đóng sidebar mobile.
     */
    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };


    /**
     * Toggle sidebar mobile.
     */
    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        overlay?.classList.toggle('hidden');
    });


    /**
     * Click overlay để đóng sidebar.
     */
    overlay?.addEventListener(
        'click',
        closeSidebar
    );


    /**
     * Hero banner auto slider.
     */
    const track = document.getElementById(
        'mk-hero-track'
    );

    if (!track) {
        return;
    }

    const slides = track.children.length;

    if (slides <= 1) {
        return;
    }

    let current = 0;

    setInterval(() => {
        current = (current + 1) % slides;

        track.style.transform =
            `translateX(-${current * 100}%)`;

        document.querySelectorAll(
            '[data-hero-dot]'
        ).forEach((dot, index) => {
            dot.classList.toggle(
                'bg-coral',
                index === current
            );

            dot.classList.toggle(
                'bg-white/60',
                index !== current
            );
        });
    }, 4000);
});