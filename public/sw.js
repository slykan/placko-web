const CACHE = 'placko-static-v2';
const ASSETS = [
    '/img/icon-192.png',
    '/img/icon-512.png',
    '/img/apple-touch-icon.png',
    '/img/placko-logo.svg',
    '/img/placko-icon.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(ASSETS)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    if (!ASSETS.includes(url.pathname)) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
});

// Push obavijest (novosti): naslov, tekst i poveznica sa servera.
self.addEventListener('push', (event) => {
    let d = {};
    try { d = event.data ? event.data.json() : {}; } catch (e) { /* neispravan sadržaj: ipak pokaži obavijest */ }
    event.waitUntil(self.registration.showNotification(d.naslov || 'Plačko', {
        body: d.tekst || '',
        icon: '/img/icon-192.png',
        badge: '/img/icon-192.png',
        data: { url: d.url || '/admin' },
        lang: 'hr',
    }));
});

// Klik otvara poveznicu, samo unutar placko.app.
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    let cilj = new URL('/admin', self.location.origin).href;
    try {
        const u = new URL(event.notification.data?.url || '', self.location.origin);
        if (u.origin === self.location.origin) cilj = u.href;
    } catch (e) { /* ostaje /admin */ }
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((prozori) => {
        const otvoren = prozori.find((p) => p.url === cilj);
        return otvoren ? otvoren.focus() : self.clients.openWindow(cilj);
    }));
});
