import './bootstrap';

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
