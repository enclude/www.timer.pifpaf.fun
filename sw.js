// Service worker — makes the app usable with no internet at the range.
//
// Everything the app needs at a shooting stage is local: Web Bluetooth, the
// live session view, the localStorage session cache and the ID-tone player.
// Only the page itself and the calculator API need the network, so the page
// shell is cached here and the calculator is never cached (a stale answer
// about "what is already in the database" would create duplicate entries).
//
// Cache busting: index.php registers this worker as /sw.js?v=<commit hash>,
// so a deploy changes the worker URL, the browser re-fetches it and the new
// version precaches the fresh shell under a new cache name.

const VERSION = new URL(self.location.href).searchParams.get('v') || 'dev';
const CACHE = `sgtimer-${VERSION}`;

// The shell: the page itself plus the files an installed app starts from.
const SHELL = ['/', '/manifest.json', '/icons/icon-192.png', '/icons/icon-512.png',
               '/icons/icon-maskable-512.png'];

self.addEventListener('install', event => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE);
        // One missing file must not abort the whole install (the app still
        // works offline without an icon).
        await Promise.all(SHELL.map(url => cache.add(url).catch(() => {})));
        await self.skipWaiting();
    })());
});

self.addEventListener('activate', event => {
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names.filter(n => n !== CACHE && n.startsWith('sgtimer-'))
                               .map(n => caches.delete(n)));
        await self.clients.claim();
    })());
});

// Cache-first with background refresh. The app must open instantly and work
// with no network; a deploy is picked up by the next load (or by the new
// worker's precache), which is fast enough for a version-in-the-footer page.
async function cacheFirst(request, cacheKey) {
    const cache = await caches.open(CACHE);
    const key = cacheKey || request;
    const hit = await cache.match(key);
    const network = fetch(request).then(resp => {
        if (resp && resp.ok && resp.type === 'basic') cache.put(key, resp.clone());
        return resp;
    }).catch(() => null);
    if (hit) return hit;
    const fresh = await network;
    if (fresh) return fresh;
    // Offline and never cached: for a navigation, fall back to the shell.
    if (request.mode === 'navigate') {
        const shell = await cache.match('/');
        if (shell) return shell;
    }
    return new Response('Offline', { status: 503, statusText: 'Offline' });
}

self.addEventListener('fetch', event => {
    const request = event.request;
    if (request.method !== 'GET') return;             // POST to the calculator: never touch
    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;  // calculator API and links: network only

    if (request.mode === 'navigate') {
        // Every navigation within scope resolves to the same PHP page — keep
        // one shell entry under "/" instead of one per query string.
        event.respondWith(cacheFirst(request, '/'));
        return;
    }
    event.respondWith(cacheFirst(request));
});
