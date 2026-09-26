// Service Worker para Storefront D2C PWA Multiverso
// Politica estricta de Cero Emojis
// Soporte Web Push Notifications VAPID (RFC 8292 - TASK-STR-06)

const CACHE_NAME = 'multiverse-store-v1';
const STATIC_ASSETS = [
  '/',
  '/css/store-pwa.css',
  '/js/storefront/store-pwa.js',
  '/manifest.json',
  '/user-avatar.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch(() => {
        // En entorno local o archivo sin servidor no bloquea
      });
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      return fetch(event.request).catch(() => {
        return caches.match('/');
      });
    })
  );
});

// ============================================================================
// MOTOR DE NOTIFICACIONES WEB PUSH PWA (TASK-STR-06)
// ============================================================================

// Ingesta de evento Push desde Servidor / VAPID Backend
self.addEventListener('push', (event) => {
  let payload = {};
  if (event.data) {
    try {
      payload = event.data.json();
    } catch (e) {
      payload = { title: 'Multiverso Store', body: event.data.text() };
    }
  }

  const title = payload.title || 'Multiverso Store // Aviso Oficial';
  const options = {
    body: payload.body || 'Tienes una nueva actualización en tu pedido o catálogo.',
    icon: payload.icon || '/user-avatar.png',
    badge: payload.badge || '/user-avatar.png',
    tag: payload.tag || 'multiverse-general',
    renotify: payload.renotify !== false,
    requireInteraction: payload.requireInteraction || false,
    data: payload.data || { url: '/', action: 'open_store' }
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Interacción al hacer Clic en la Notificación en Dispositivo Móvil o Desktop
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const notificationData = event.notification.data || {};
  const targetUrl = notificationData.url || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // 1. Si hay una ventana abierta de la tienda, enfocarla y enviar postMessage
      for (const client of clientList) {
        if (client.url && 'focus' in client) {
          client.postMessage({
            type: 'NOTIFICATION_CLICK',
            data: notificationData
          });
          return client.focus();
        }
      }
      // 2. Si no hay ventanas activas, abrir una nueva ventana
      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});
