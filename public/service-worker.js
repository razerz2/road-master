const CACHE_NAME = "roadmaster-cache-v1";

const STATIC_ASSETS = [
    "/",
    "/offline",
    "/css/app.css",
    "/js/app.js"
];

self.addEventListener("install", event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener("activate", event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.map(key => {
                if (key !== CACHE_NAME) return caches.delete(key);
            }))
        )
    );
});

self.addEventListener("fetch", event => {
    const request = event.request;
    if (request.method !== "GET") return;

    const url = new URL(request.url);

    const bypass =
        url.pathname.startsWith("/api") ||
        url.pathname.startsWith("/storage") ||
        url.pathname.includes("broadcasting");

    if (bypass) return;

    event.respondWith(
        fetch(request)
            .then(response => {
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(request, response.clone());
                    return response;
                });
            })
            .catch(() => caches.match(request).then(res => res || caches.match("/offline")))
    );
});

