/**
 * "Near me" uses the browser geolocation API and reloads with user_lat / user_lng
 * (other query params are preserved).
 */
export function initCompetitionsGeo() {
    const root = document.querySelector('[data-competitions-geo]');
    if (!root) {
        return;
    }

    function applyPosition(lat, lng) {
        const next = new URLSearchParams(window.location.search);
        next.set('user_lat', lat.toFixed(7));
        next.set('user_lng', lng.toFixed(7));
        const qs = next.toString();
        window.location.replace(`${window.location.pathname}${qs ? `?${qs}` : ''}${window.location.hash}`);
    }

    const nearBtn = document.getElementById('competitions-near-me');
    if (!nearBtn) {
        return;
    }

    if (!('geolocation' in navigator)) {
        nearBtn.disabled = true;
        nearBtn.title = 'Location is not available in this browser';

        return;
    }

    nearBtn.addEventListener('click', () => {
        nearBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                applyPosition(pos.coords.latitude, pos.coords.longitude);
            },
            () => {
                nearBtn.disabled = false;
            },
            { enableHighAccuracy: false, timeout: 15000, maximumAge: 600000 },
        );
    });
}
