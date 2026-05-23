/**
 * Service Worker - Copam Metall PWA
 *
 * Strategia:
 * - Build assets (/build/*) → Cache First (file con hash, immutabili)
 * - Font esterni (fonts.bunny.net) → Cache First
 * - Navigazione (pagine HTML) → Network First con fallback a cache
 * - API (/api/*) → Network Only (sempre freschi)
 */

const VERSION = 'v1';
const CACHE_NAME = `copam-${VERSION}`;

/** File precached all'installazione (app shell) */
const APP_SHELL = [
    '/',
    '/manifest.webmanifest',
    '/favicon.ico',
    '/apple-touch-icon.png',
    '/pwa-192x192.png',
    '/pwa-512x512.png',
];

// ─── Install ────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting()),
    );
});

// ─── Activate ───────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))),
            )
            .then(() => self.clients.claim()),
    );
});

// ─── Fetch ──────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Solo GET
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Font esterni → Cache First
    if (url.hostname === 'fonts.bunny.net' || url.hostname === 'fonts.googleapis.com') {
        event.respondWith(cacheFirst(req));
        return;
    }

    // Solo same-origin da qui in poi
    if (url.origin !== self.location.origin) return;

    // API → Network Only (non intercettare)
    if (url.pathname.startsWith('/api/')) return;

    // Build assets (hashed, immutabili) → Cache First
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(req));
        return;
    }

    // Icone e asset statici → Cache First
    if (/\.(png|jpg|jpeg|svg|ico|webp|woff2?|ttf|eot)$/i.test(url.pathname)) {
        event.respondWith(cacheFirst(req));
        return;
    }

    // Navigazione HTML → Network First con fallback offline
    if (req.mode === 'navigate') {
        event.respondWith(networkFirstWithOfflineFallback(req));
        return;
    }
});

// ─── Strategie ──────────────────────────────────────────────────────────────

/** Cache First: serve dalla cache; se mancante scarica e memorizza */
async function cacheFirst(req) {
    const cached = await caches.match(req);
    if (cached) return cached;

    try {
        const response = await fetch(req);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        return new Response('Risorsa non disponibile offline.', { status: 503 });
    }
}

/** Network First: prova la rete; se fallisce serve dalla cache */
async function networkFirstWithOfflineFallback(req) {
    try {
        const response = await fetch(req);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(req, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(req);
        if (cached) return cached;

        // Fallback alla home cached (app shell)
        const shell = await caches.match('/');
        if (shell) return shell;

        return new Response(
            `<!DOCTYPE html>
<html lang="it">
<head><meta charset="utf-8"><title>Copam - Offline</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f3f4f6}
  .box{text-align:center;padding:2rem;background:#fff;border-radius:1rem;box-shadow:0 4px 24px rgba(0,0,0,.1)}
  h1{color:#044585;margin-bottom:.5rem}p{color:#6b7280}
</style></head>
<body><div class="box"><h1>Copam Metall</h1><p>Nessuna connessione internet disponibile.</p><p>Verifica la rete e riprova.</p></div></body>
</html>`,
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } },
        );
    }
}
