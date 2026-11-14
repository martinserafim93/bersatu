(function () {
    const navToggle = document.getElementById('nav-toggle-btn');
    const navPanel = document.getElementById('site-nav-panel');
    if (navToggle && navPanel) {
        navToggle.addEventListener('click', function () {
            const expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            navPanel.classList.toggle('is-open', !expanded);
        });
    }

    const networkBanner = document.getElementById('network-status-banner');
    const syncNetworkBanner = function () {
        if (!networkBanner) {
            return;
        }
        networkBanner.hidden = navigator.onLine;
    };

    syncNetworkBanner();
    window.addEventListener('online', syncNetworkBanner);
    window.addEventListener('offline', syncNetworkBanner);

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {
                // Abaikan gagal registrasi SW pada browser lama atau konteks non-HTTPS lokal.
            });
        });
    }

    const reloadBtn = document.getElementById('btn-reload-page');
    if (reloadBtn) {
        reloadBtn.addEventListener('click', function () {
            window.location.reload();
        });
    }

    const clearCachedNavigationHtml = function () {
        if (!('caches' in window)) {
            return Promise.resolve();
        }
        return caches.open('bersatu-pwa-v1')
            .then(function (cache) {
                return cache.keys().then(function (requests) {
                    return Promise.all(requests.map(function (req) {
                        const pathname = new URL(req.url).pathname;
                        const keepStatic = pathname.startsWith('/assets/')
                            || pathname === '/manifest.json'
                            || pathname === '/offline';
                        return keepStatic ? Promise.resolve(false) : cache.delete(req);
                    }));
                });
            })
            .catch(function () {
                // Jangan menghalangi proses keluar bila Cache Storage tidak tersedia.
            });
    };

    const logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function () {
            logoutBtn.disabled = true;
            fetch('/api/auth/logout', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: '{}'
            })
                .then(clearCachedNavigationHtml)
                .then(function () {
                    window.location.href = '/';
                })
                .catch(function () {
                    logoutBtn.disabled = false;
                });
        });
    }
})();
