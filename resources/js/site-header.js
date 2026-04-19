/**
 * Mobile navigation drawer: toggle, escape/resize close, focus-friendly labels.
 * Header: subtle drop shadow when the page is scrolled.
 */
export function initSiteHeader() {
    const header = document.querySelector('[data-site-header]');
    if (header) {
        const thresholdPx = 6;
        let scrollTicking = false;

        function syncHeaderScroll() {
            const y = window.scrollY || document.documentElement.scrollTop;
            header.classList.toggle('is-scrolled', y > thresholdPx);
            scrollTicking = false;
        }

        window.addEventListener(
            'scroll',
            () => {
                if (!scrollTicking) {
                    scrollTicking = true;
                    requestAnimationFrame(syncHeaderScroll);
                }
            },
            { passive: true },
        );
        syncHeaderScroll();
    }

    const toggle = document.querySelector('[data-mobile-nav-toggle]');
    const panel = document.querySelector('[data-mobile-nav-panel]');
    const backdrop = document.querySelector('[data-mobile-nav-backdrop]');
    const iconOpen = document.querySelector('[data-mobile-nav-icon-open]');
    const iconClose = document.querySelector('[data-mobile-nav-icon-close]');

    if (!toggle || !panel) {
        return;
    }

    const mq = window.matchMedia('(min-width: 1024px)');

    function setOpen(open) {
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.classList.toggle('hidden', !open);
        panel.setAttribute('aria-hidden', open ? 'false' : 'true');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        document.body.classList.toggle('overflow-hidden', open);
        if (backdrop) {
            backdrop.classList.toggle('hidden', !open);
            backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
        if (iconOpen && iconClose) {
            iconOpen.classList.toggle('hidden', open);
            iconClose.classList.toggle('hidden', !open);
        }
    }

    function closeIfNarrow() {
        if (mq.matches) {
            setOpen(false);
        }
    }

    toggle.addEventListener('click', () => {
        const next = toggle.getAttribute('aria-expanded') !== 'true';
        setOpen(next);
    });

    backdrop?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            setOpen(false);
        }
    });

    mq.addEventListener('change', closeIfNarrow);
    window.addEventListener('resize', closeIfNarrow);

    panel.querySelectorAll('a[href]').forEach((a) => {
        a.addEventListener('click', () => setOpen(false));
    });

    setOpen(false);
}
