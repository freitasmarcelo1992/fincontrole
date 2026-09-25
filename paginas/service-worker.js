const CACHE_NAME = "fincontrole-pwa-v46-landpage-pro-hero-20260917";
const APP_SHELL = [
  "/paginas/manifest.json",
  "/paginas/assets/img/icon-192.png",
  "/paginas/assets/img/icon-512.png",
  "/paginas/offline.html"
];

self.addEventListener("install", event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(APP_SHELL).catch(() => undefined))
  );
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  const url = new URL(event.request.url);
  const accept = event.request.headers.get("accept") || "";
  if (
    event.request.mode === "navigate" ||
    accept.includes("text/html") ||
    url.pathname.endsWith(".php") ||
    url.pathname.endsWith("/")
  ) {
    event.respondWith(
      fetch(event.request, { cache: "no-store" }).catch(() => caches.match("/paginas/offline.html"))
    );
    return;
  }

  event.respondWith(
    fetch(event.request, { cache: "no-store" })
      .then(response => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy)).catch(() => undefined);
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});



