const CACHE_NAME = 'trafikcams-shell-v1';
const SHELL_ASSETS = ['/trafikcams.webmanifest', '/favicon.svg'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)),
        )),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) return;
    if (request.headers.get('accept')?.includes('text/event-stream')) return;

    if (request.mode === 'navigate') {
        // Never cache authenticated HTML: the console can contain private
        // camera data and must always be revalidated by the server.
        event.respondWith(fetch(request));
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).then((response) => {
            if (response.ok && response.type === 'basic') {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
            }
            return response;
        })),
    );
});
