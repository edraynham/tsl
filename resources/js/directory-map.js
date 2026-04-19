import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl,
    iconRetinaUrl,
    shadowUrl,
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

function showMapError(el, message) {
    el.replaceChildren();
    const p = document.createElement('p');
    p.className = 'flex min-h-[320px] items-center justify-center p-8 text-center text-sm text-[#50606f]';
    p.textContent = message;
    el.appendChild(p);
}

function runInit(el) {
    if (el._tslLeafletInitDone) {
        return;
    }

    const dataEl = document.getElementById('directory-map-data');
    let markers;
    try {
        const raw = dataEl?.textContent?.trim() ?? '[]';
        markers = JSON.parse(raw);
    } catch {
        showMapError(el, 'Could not load map data. Please refresh the page.');

        return;
    }

    const valid = markers.filter(
        (m) =>
            m.lat != null &&
            m.lng != null &&
            !Number.isNaN(Number(m.lat)) &&
            !Number.isNaN(Number(m.lng)),
    );

    if (valid.length === 0) {
        showMapError(
            el,
            'No grounds with coordinates match your filters. Try clearing search or filters.',
        );

        return;
    }

    el._tslLeafletInitDone = true;

    const map = L.map(el, {
        scrollWheelZoom: true,
        attributionControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    const latLngs = [];

    for (const m of valid) {
        const lat = Number(m.lat);
        const lng = Number(m.lng);
        latLngs.push([lat, lng]);

        const marker = L.marker([lat, lng]).addTo(map);

        const place = [m.city, m.county].filter(Boolean).join(', ');
        const popupHtml = `
            <div class="min-w-[200px] font-sans text-[#1c1c19]">
                <a href="${escapeHtml(m.url)}" class="font-semibold text-[#173124] hover:underline">${escapeHtml(m.name)}</a>
                ${place ? `<p class="mt-1 text-xs text-[#50606f]">${escapeHtml(place)}</p>` : ''}
            </div>
        `;
        marker.bindPopup(popupHtml);
    }

    const refit = () => {
        map.invalidateSize({ animate: false });
    };

    const scheduleRefit = () => {
        refit();
        requestAnimationFrame(() => {
            refit();
            setTimeout(refit, 200);
        });
    };

    /** Fit map to grounds only (until geo resolves). */
    function viewGroundsOnly() {
        if (latLngs.length === 1) {
            map.setView(latLngs[0], 12);
        } else {
            map.fitBounds(latLngs, { padding: [48, 48], maxZoom: 11 });
        }
    }

    /** User location + grounds: fit everyone in view, or centre on user if the span would be huge (e.g. abroad). */
    function viewWithUser(userLatLng) {
        const bounds = L.latLngBounds(latLngs);
        bounds.extend(userLatLng);
        const sw = bounds.getSouthWest();
        const ne = bounds.getNorthEast();
        const spanM = sw.distanceTo(ne);
        if (spanM > 800000) {
            map.setView(userLatLng, 9);
        } else {
            map.fitBounds(bounds, { padding: [52, 52], maxZoom: 12 });
        }
    }

    viewGroundsOnly();

    map.whenReady(() => {
        scheduleRefit();
    });

    window.addEventListener('resize', refit);

    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const userLatLng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                L.circleMarker(userLatLng, {
                    radius: 10,
                    color: '#1d4ed8',
                    weight: 2,
                    fillColor: '#3b82f6',
                    fillOpacity: 0.35,
                })
                    .addTo(map)
                    .bindPopup('Your location');
                viewWithUser(userLatLng);
                scheduleRefit();
            },
            () => {
                /* keep grounds-only view */
            },
            { enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 },
        );
    }
}

export function initDirectoryMap() {
    const el = document.getElementById('directory-map');
    if (!el) {
        return;
    }

    const start = () => {
        if (el.offsetWidth < 2 || el.offsetHeight < 2) {
            return false;
        }
        runInit(el);

        return true;
    };

    if (start()) {
        return;
    }

    const ro = new ResizeObserver(() => {
        if (start()) {
            ro.disconnect();
        }
    });
    ro.observe(el);

    setTimeout(() => {
        ro.disconnect();
        if (el.querySelector('.leaflet-container') === null) {
            start();
        }
    }, 1500);
}
