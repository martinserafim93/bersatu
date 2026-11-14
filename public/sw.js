const CACHE_NAME = 'bersatu-pwa-v1';
const APP_SHELL = [
    '/',
    '/offline',
    '/manifest.json',
    '/assets/css/app.css',
    '/assets/js/pwa.js',
    '/assets/icons/icon-192.svg',
    '/assets/icons/icon-512.svg'
];
const PUBLIC_NAV_PATHS = new Set([
    '/',
    '/offline',
    '/peta-kerukunan',
    '/media-hub',
    '/tanya',
    '/informasi-kerukunan'
]);

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    // Never cache sensitive auth or admin endpoints.
    if (url.pathname.startsWith('/api/auth/') || url.pathname.startsWith('/api/admin/')) {
        return;
    }

    if (request.mode === 'navigate') {
        const isPublicNav = PUBLIC_NAV_PATHS.has(url.pathname);
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response && response.ok && isPublicNav) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(() => (
                    isPublicNav
                        ? caches.match(request).then((cached) => cached || caches.match('/offline'))
                        : caches.match('/offline')
                ))
        );
        return;
    }

    if (url.pathname.startsWith('/assets/') || url.pathname === '/manifest.json') {
        event.respondWith(
            caches.match(request).then((cached) => {
                const networkUpdate = fetch(request)
                    .then((response) => {
                        if (response && response.ok) {
                            const copy = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        }
                        return response;
                    })
                    .catch(() => cached);

                return cached || networkUpdate;
            })
        );
    }
});
