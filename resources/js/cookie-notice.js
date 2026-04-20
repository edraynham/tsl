export function initCookieNotice() {
    const notice = document.querySelector('[data-cookie-notice]');
    if (!notice) {
        return;
    }

    const acceptBtn = notice.querySelector('[data-cookie-accept]');
    if (!acceptBtn) {
        return;
    }

    const COOKIE_KEY = 'tsl_cookie_notice_accepted';
    const COOKIE_NAME = 'tsl_cookie_consent';
    const ONE_YEAR_SECONDS = 60 * 60 * 24 * 365;

    const accepted = (() => {
        try {
            return window.localStorage.getItem(COOKIE_KEY) === '1'
                || document.cookie.split('; ').some((c) => c.startsWith(`${COOKIE_NAME}=accepted`));
        } catch {
            return document.cookie.split('; ').some((c) => c.startsWith(`${COOKIE_NAME}=accepted`));
        }
    })();

    if (accepted) {
        notice.remove();
        return;
    }

    requestAnimationFrame(() => {
        notice.classList.remove('pointer-events-none', 'translate-x-4', 'opacity-0');
    });

    acceptBtn.addEventListener('click', () => {
        try {
            window.localStorage.setItem(COOKIE_KEY, '1');
        } catch {
            // Ignore storage failures; cookie below still persists consent.
        }

        document.cookie = `${COOKIE_NAME}=accepted; Max-Age=${ONE_YEAR_SECONDS}; Path=/; SameSite=Lax`;

        notice.classList.add('pointer-events-none', 'translate-x-4', 'opacity-0');
        window.setTimeout(() => notice.remove(), 240);
    });
}
