

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

let installPrompt = null;
const installButton = document.getElementById('pwa-install-button');

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    installButton?.removeAttribute('hidden');
});

installButton?.addEventListener('click', async () => {
    if (!installPrompt) {
        return;
    }

    await installPrompt.prompt();
    installPrompt = null;
    installButton.setAttribute('hidden', '');
});

window.addEventListener('appinstalled', () => {
    installPrompt = null;
    installButton?.setAttribute('hidden', '');
});

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}
