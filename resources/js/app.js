import './bootstrap';

/**
 * Calls the JSON cart API (see routes/api.php -> App\Http\Controllers\CartController)
 * and updates every cart-count badge on the page (desktop header + mobile floating button)
 * without a full page reload. Wired from resources/views/components/product-card.blade.php.
 */
window.mkAddToCart = async function mkAddToCart(productId, buttonEl) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    buttonEl?.setAttribute('disabled', 'true');

    try {
        const res = await fetch('/api/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ?? '',
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        });

        if (!res.ok) throw new Error('Request failed');
        const data = await res.json();

        document.querySelectorAll('.mk-cart-count').forEach((el) => {
            el.textContent = data.cart_count;
        });

        mkToast(data.message ?? 'Đã thêm vào giỏ hàng');
    } catch (err) {
        mkToast('Không thể thêm vào giỏ hàng, vui lòng thử lại');
        console.error(err);
    } finally {
        buttonEl?.removeAttribute('disabled');
    }
};

function mkToast(message) {
    const toast = document.getElementById('mk-toast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(window.__mkToastTimer);
    window.__mkToastTimer = setTimeout(() => toast.classList.add('hidden'), 2000);
}

// Toggle mobile category sidebar
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('mk-sidebar-toggle');
    const sidebar = document.getElementById('mk-sidebar');
    const overlay = document.getElementById('mk-sidebar-overlay');

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };

    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('-translate-x-full');
        overlay?.classList.toggle('hidden');
    });

    overlay?.addEventListener('click', closeSidebar);

    // Hero banner simple auto-slider
    const track = document.getElementById('mk-hero-track');
    if (track) {
        const slides = track.children.length;
        let current = 0;
        setInterval(() => {
            current = (current + 1) % slides;
            track.style.transform = `translateX(-${current * 100}%)`;
            document.querySelectorAll('[data-hero-dot]').forEach((dot, i) => {
                dot.classList.toggle('bg-coral', i === current);
                dot.classList.toggle('bg-white/60', i !== current);
            });
        }, 4000);
    }
});
