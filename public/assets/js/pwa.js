/**
 * Prowave Management — Progressive Web App (PWA) Handler
 */
(function () {
    'use strict';

    let deferredPrompt = null;
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js')
                .then(function (registration) {
                    console.log('PWA ServiceWorker registered with scope: ', registration.scope);
                })
                .catch(function (err) {
                    console.log('PWA ServiceWorker registration failed: ', err);
                });
        });
    }

    // Capture install prompt
    window.addEventListener('beforeinstallprompt', function (e) {
        // Prevent default mini-infobar or automatic prompt
        e.preventDefault();
        deferredPrompt = e;

        // Show install buttons
        updateInstallUI(true);
    });

    window.addEventListener('appinstalled', function () {
        console.log('PWA was installed successfully');
        deferredPrompt = null;
        updateInstallUI(false);
    });

    function updateInstallUI(canInstall) {
        if (isStandalone) {
            canInstall = false;
        }

        const installButtons = document.querySelectorAll('[data-pwa-install]');
        installButtons.forEach(function (btn) {
            if (canInstall) {
                btn.classList.remove('d-none');
                btn.style.display = '';
            } else {
                btn.classList.add('d-none');
                btn.style.display = 'none';
            }
        });
    }

    // Global install click handler
    document.addEventListener('click', async function (e) {
        const target = e.target.closest('[data-pwa-install]');
        if (!target) return;

        e.preventDefault();

        if (deferredPrompt) {
            deferredPrompt.prompt();
            const choiceResult = await deferredPrompt.userChoice;
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the PWA install prompt');
            } else {
                console.log('User dismissed the PWA install prompt');
            }
            deferredPrompt = null;
            updateInstallUI(false);
        } else {
            // If already installed or on iOS where beforeinstallprompt isn't fired
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (isIOS) {
                alert('To install this app on your iPhone/iPad: tap the Share button (square with arrow) at the bottom and select "Add to Home Screen".');
            } else {
                alert('App is already installed or your browser already has installation ready in the browser address bar (look for the install icon in the URL bar).');
            }
        }
    });

    // Initial check: if standalone, hide all install buttons immediately
    document.addEventListener('DOMContentLoaded', function () {
        if (isStandalone) {
            updateInstallUI(false);
        }
    });
})();
