/**
 * If the URL has no user_lat/user_lng, request geolocation and reload with coords
 * (+ sort=distance when the user has not chosen an explicit sort).
 */
export function initDirectoryGeo() {
    if (!document.querySelector('[data-directory-geo]')) {
        return;
    }
    if (!('geolocation' in navigator)) {
        return;
    }

    const params = new URLSearchParams(window.location.search);
    if (params.has('user_lat') && params.has('user_lng')) {
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const next = new URLSearchParams(window.location.search);
            next.set('user_lat', pos.coords.latitude.toFixed(7));
            next.set('user_lng', pos.coords.longitude.toFixed(7));
            if (!next.has('sort')) {
                next.set('sort', 'distance');
            }
            const qs = next.toString();
            window.location.replace(`${window.location.pathname}${qs ? `?${qs}` : ''}${window.location.hash}`);
        },
        () => {},
        { enableHighAccuracy: false, timeout: 12000, maximumAge: 600000 },
    );
}
