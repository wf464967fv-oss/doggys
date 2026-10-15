/*
 * Doggys service worker. Bump VERSION on every change: old caches are dropped on activate.
 * - HTML pages: network first, offline page as fallback.
 * - /build/assets (hashed files) and fonts: cache first.
 * - Never intercepted: non-GET, /admin, /api, account and login pages (HTML pages are never stored anyway).
 */
const VERSION = 'doggys-v1';
const OFFLINE_PAGES = ['/fr/hors-ligne', '/nl/offline', '/en/offline', '/de/offline'];
const FONTS = ['/fonts/fredoka-latin-wght-normal.woff2', '/fonts/nunito-latin-wght-normal.woff2'];
const PRIVATE = /^\/(admin|api)(\/|$)|^\/(fr|nl|en|de)\/(mon-compte|mijn-account|my-account|mein-konto|connexion|inloggen|login|anmelden)(\/|$)/;

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(VERSION);
        let assets = [];
        try {
            const manifest = await (await fetch('/build/manifest.json')).json();
            assets = Object.values(manifest).filter((entry) => entry.isEntry).flatMap((entry) => ['/build/' + entry.file, ...(entry.css || []).map((css) => '/build/' + css)]);
        } catch (error) { /* no manifest (dev): pages still work, assets are cached on first use */ }
        await cache.addAll([...OFFLINE_PAGES, ...FONTS, ...assets]);
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        await Promise.all((await caches.keys()).filter((key) => key !== VERSION).map((key) => caches.delete(key)));
        await self.clients.claim();
    })());
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin || PRIVATE.test(url.pathname)) {
        return;
    }

    if (url.pathname.startsWith('/build/assets/') || url.pathname.startsWith('/fonts/')) {
        event.respondWith(caches.match(request).then((hit) => hit || fetch(request).then((response) => {
            if (response.ok) {
                const copy = response.clone();
                caches.open(VERSION).then((cache) => cache.put(request, copy));
            }
            return response;
        })));
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(async () => {
            const locale = url.pathname.split('/')[1];
            return (await caches.match(OFFLINE_PAGES.find((page) => page.startsWith('/' + locale + '/')) || OFFLINE_PAGES[0])) || Response.error();
        }));
    }
});
