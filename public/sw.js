const CACHE_NAME = 'bazamal-static-v3';
const PRECACHE_URLS = [
    '/offline.html',
    '/icons/icon-192.png?v=2',
    '/icons/icon-512.png?v=2',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((names) => Promise.all(
                names
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name)),
            ))
            .then(() => self.clients.claim()),
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

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html')),
        );

        return;
    }

    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => cachedResponse || fetch(request).then((networkResponse) => {
                const responseToCache = networkResponse.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, responseToCache));

                return networkResponse;
            })),
        );
    }
});

self.addEventListener('push', (event) => {
    let payload = {};

    try {
        payload = event.data?.json() || {};
    } catch {
        payload = { data: { body: event.data?.text() || '' } };
    }

    const notification = payload.notification || {};
    const data = payload.data || {};
    const title = notification.title || data.title || 'Bazar Amal';
    const options = {
        body: notification.body || data.body || '',
        icon: notification.icon || '/icons/icon-192.png?v=2',
        badge: '/icons/icon-192.png?v=2',
        data: {
            url: data.url || payload.fcmOptions?.link || '/',
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = new URL(event.notification.data?.url || '/', self.location.origin).href;

    const isAdminUrl = (url) => new URL(url).pathname.startsWith('/admin');

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(async (clients) => {
            const exactClient = clients.find((client) => client.url === targetUrl);

            if (exactClient) {
                return exactClient.focus();
            }

            const sameAreaClient = clients.find((client) => isAdminUrl(client.url) === isAdminUrl(targetUrl));

            if (sameAreaClient) {
                try {
                    const navigatedClient = await sameAreaClient.navigate(targetUrl);

                    return (navigatedClient || sameAreaClient).focus();
                } catch {
                    // Windows the worker does not control cannot navigate, so open a new one.
                }
            }

            return self.clients.openWindow(targetUrl);
        }),
    );
});
