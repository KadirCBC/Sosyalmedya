const CACHE_NAME = 'meydan-pwa-v1';
const OFFLINE_URL = '/offline.html';

const ASSETS_TO_PRECACHE = [
    '/',
    OFFLINE_URL,
    '/manifest.json',
    '/icons/icon-192x192.svg',
    '/icons/icon-512x512.svg'
];

// 1. Kurulum (Install Event) - Temel Varlıkları Önbelleğe Al
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_PRECACHE);
        })
    );
    self.skipWaiting();
});

// 2. Etkinleştirme (Activate Event) - Eski Önbellekleri Temizle
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// 3. İstek Yakalama (Fetch Event) - Cache-First & Offline Fallback
self.addEventListener('fetch', (event) => {
    // Yalnızca GET isteklerini önbellekle
    if (event.request.method !== 'GET') {
        return;
    }

    // Sayfa navigasyonu (HTML istekleri) için Network-First, Offline ise offline.html
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // Statik dosyalar (Görsel, Font, CSS, JS) için Stale-While-Revalidate / Cache-First
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).then((networkResponse) => {
                // Başarılı statik istekleri önbelleğe ekle
                if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                    const responseToCache = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return networkResponse;
            });
        })
    );
});

// 4. WebPush Bildirim Yakalayıcı (Push Event)
self.addEventListener('push', (event) => {
    let data = {
        title: 'Meydan Bildirimi',
        body: 'Yeni bir etkileşim veya mahalle duyurusu var.',
        icon: '/icons/icon-192x192.svg',
        badge: '/icons/icon-192x192.svg',
        url: '/'
    };

    if (event.data) {
        try {
            data = Object.assign(data, event.data.json());
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/icons/icon-192x192.svg',
        badge: data.badge || '/icons/icon-192x192.svg',
        vibrate: data.vibrate || [200, 100, 200],
        tag: data.tag || 'meydan-general',
        renotify: true,
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// 5. Bildirime Tıklanma Olayı (Notification Click Event)
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) 
        ? event.notification.data.url 
        : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Eğer açık bir sekme varsa oraya odaklan ve yönlendir
            for (const client of clientList) {
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            // Açık sekme yoksa yeni pencere aç
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

