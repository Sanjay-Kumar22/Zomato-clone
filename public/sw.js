// ZomatoClone service worker
// Kept deliberately minimal: this app is order/checkout/payment heavy, so
// aggressively caching pages could show stale menus, prices, or cart state.
// This worker only exists to (a) make the site installable as a PWA and
// (b) show a friendly offline page instead of the browser's default error
// when there's no connection — it does not cache or serve app content.

const CACHE_NAME = 'zomatoclone-shell-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll([
            OFFLINE_URL,
            '/pwa-icons/icon-192.png',
        ]))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only intercept page navigations (not API calls, POSTs, assets, etc.)
    // so cart/checkout/payment requests always go straight to the network.
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});
