/*
 * Leaflet maps for elements rendered by <x-map> ([data-map]). Imported dynamically by app.js,
 * so Leaflet (JS + CSS) is only downloaded on pages that show a map.
 */
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const PIN = (fill, inner) => L.divIcon({
    className: 'doggys-pin',
    html: `<svg viewBox="0 0 36 46" width="36" height="46" aria-hidden="true"><path d="M18 45s15-15.6 15-27A15 15 0 0 0 3 18c0 11.4 15 27 15 27Z" fill="${fill}" stroke="#4a2812" stroke-width="2"/>${inner}</svg>`,
    iconSize: [36, 46],
    iconAnchor: [18, 44],
    popupAnchor: [0, -40],
});

/* Orange pin with a brown paw for providers, rose pin with a white dot for the search origin. */
const PROVIDER_ICON = PIN('#f5a043', '<g fill="#4a2812" transform="translate(8 7) scale(.83)"><ellipse cx="5.5" cy="10" rx="2.2" ry="2.7"/><ellipse cx="9.5" cy="5.6" rx="2.2" ry="2.8"/><ellipse cx="14.5" cy="5.6" rx="2.2" ry="2.8"/><ellipse cx="18.5" cy="10" rx="2.2" ry="2.7"/><path d="M12 11.2c-2.7 0-5.8 3.7-5.8 6.4 0 2 1.5 2.9 3.1 2.9 1.1 0 1.7-.5 2.7-.5s1.6.5 2.7.5c1.6 0 3.1-.9 3.1-2.9 0-2.7-3.1-6.4-5.8-6.4Z"/></g>');
const ORIGIN_ICON = PIN('#ff5c77', '<circle cx="18" cy="18" r="6" fill="#fff"/>');

/* Popup built with DOM nodes (textContent), never HTML strings: names come from open data. */
function popup(marker, openLabel) {
    const box = document.createElement('div');
    box.className = 'doggys-popup';

    const title = document.createElement('p');
    title.className = 'doggys-popup-title';
    title.textContent = marker.name;
    box.append(title);

    if (marker.meta) {
        const meta = document.createElement('p');
        meta.className = 'doggys-popup-meta';
        meta.textContent = marker.meta;
        box.append(meta);
    }

    if (marker.url) {
        const link = document.createElement('a');
        link.href = marker.url;
        link.className = 'doggys-popup-link';
        link.textContent = openLabel;
        box.append(link);
    }

    return box;
}

function mount(element) {
    const markers = JSON.parse(element.dataset.markers || '[]');
    const center = JSON.parse(element.dataset.center || 'null');
    const circleKm = Number(element.dataset.circleKm || 0);

    element.replaceChildren();

    const map = L.map(element, { scrollWheelZoom: false, zoomControl: true });
    map.attributionControl.setPrefix('<a href="https://leafletjs.com">Leaflet</a>');
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    const points = [];

    if (center) {
        L.marker([center.lat, center.lng], { icon: ORIGIN_ICON, title: center.label || element.dataset.pinLabel, alt: element.dataset.pinLabel, zIndexOffset: -100 }).addTo(map);
        points.push([center.lat, center.lng]);
    }

    markers.forEach((marker) => {
        L.marker([marker.lat, marker.lng], { icon: PROVIDER_ICON, title: marker.name, alt: marker.name })
            .bindPopup(popup(marker, element.dataset.openLabel))
            .addTo(map);
        points.push([marker.lat, marker.lng]);
    });

    if (circleKm > 0 && markers.length > 0) {
        L.circle([markers[0].lat, markers[0].lng], {
            radius: circleKm * 1000, color: '#d4995c', weight: 2, fillColor: '#f5a043', fillOpacity: 0.12,
        }).addTo(map);
        map.fitBounds(L.latLng(markers[0].lat, markers[0].lng).toBounds(circleKm * 2000), { padding: [16, 16] });
    } else if (points.length > 1) {
        map.fitBounds(points, { padding: [36, 36], maxZoom: 15 });
    } else if (points.length === 1) {
        map.setView(points[0], 15);
    } else {
        map.setView([50.5, 4.47], 7);
    }
}

export function mountMaps() {
    document.querySelectorAll('[data-map]').forEach(mount);
}
