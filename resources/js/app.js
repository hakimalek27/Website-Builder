import './bootstrap';

// Tanda JS aktif — elemen `.reveal` hanya disembunyikan apabila JS ada (elak kandungan halimunan tanpa-JS).
document.documentElement.classList.add('js');

/*
 | Reveal-on-scroll — menambah `.is-revealed` pada elemen `.reveal` apabila masuk viewport.
 | CSP-safe (fail Vite, bukan skrip inline). Dipintas jika pengguna mahu kurang gerakan.
 */
function initReveal() {
    const items = document.querySelectorAll('.reveal');
    if (!items.length) return;

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    items.forEach((el) => observer.observe(el));
}

/*
 | Header — menambah `.is-scrolled` selepas skrol sedikit supaya bar jadi lebih pekat/berbayang.
 */
function initHeader() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function boot() {
    initReveal();
    initHeader();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
