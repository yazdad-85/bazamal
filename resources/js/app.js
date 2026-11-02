

import Alpine from 'alpinejs';
import { getApps, initializeApp } from 'firebase/app';
import { deleteToken, getMessaging, getToken, isSupported, onMessage } from 'firebase/messaging';

window.Alpine = Alpine;

Alpine.start();

let deferredInstallPrompt = null;
const installButton = document.getElementById('pwa-install-button');
const installBanner = document.getElementById('pwa-install-banner');
const installDescription = document.getElementById('pwa-install-description');
const installDismissButton = document.getElementById('pwa-install-dismiss');
const iosInstructions = document.getElementById('pwa-ios-instructions');
const iosCloseButton = document.getElementById('pwa-ios-close');
const iosDoneButton = document.getElementById('pwa-ios-done');

const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent)
    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
const isStandalone = window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;
const installDismissedRecently = () => {
    const dismissedAt = Number(window.localStorage.getItem('bazamal_install_dismissed_at') || 0);

    return dismissedAt > Date.now() - (7 * 24 * 60 * 60 * 1000);
};

const hideInstallUi = () => {
    installBanner?.setAttribute('hidden', '');
    iosInstructions?.setAttribute('hidden', '');
};

const showIosInstructions = () => {
    iosInstructions?.removeAttribute('hidden');
};

if (isIos && !isStandalone && !installDismissedRecently()) {
    if (installDescription) {
        installDescription.textContent = 'Tambahkan ke Layar Utama agar tampil seperti aplikasi dan dapat menerima notifikasi.';
    }

    if (installButton) {
        installButton.textContent = 'Lihat Cara Pasang';
    }

    installBanner?.removeAttribute('hidden');
}

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;

    if (!isStandalone && !installDismissedRecently()) {
        installBanner?.removeAttribute('hidden');
    }
});

installButton?.addEventListener('click', async () => {
    if (isIos && !isStandalone) {
        showIosInstructions();

        return;
    }

    if (!deferredInstallPrompt) {
        return;
    }

    await deferredInstallPrompt.prompt();
    deferredInstallPrompt = null;
    hideInstallUi();
});

installDismissButton?.addEventListener('click', () => {
    window.localStorage.setItem('bazamal_install_dismissed_at', String(Date.now()));
    hideInstallUi();
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    hideInstallUi();
});

iosCloseButton?.addEventListener('click', hideInstallUi);
iosDoneButton?.addEventListener('click', hideInstallUi);
iosInstructions?.addEventListener('click', (event) => {
    if (event.target === iosInstructions) {
        hideInstallUi();
    }
});

const adminMenuButton = document.getElementById('admin-menu-button');
const adminNavigation = document.getElementById('admin-navigation');

adminMenuButton?.addEventListener('click', () => {
    const isOpen = adminNavigation.classList.toggle('hidden') === false;

    adminMenuButton.setAttribute('aria-expanded', String(isOpen));
    adminMenuButton.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
    document.getElementById('admin-menu-open-icon').classList.toggle('hidden', isOpen);
    document.getElementById('admin-menu-close-icon').classList.toggle('hidden', !isOpen);
});

const firebaseConfigElement = document.getElementById('pwa-config');
const notificationButton = document.getElementById('push-notification-button');
const notificationToast = document.getElementById('push-notification-toast');
const notificationToastTitle = document.getElementById('push-notification-toast-title');
const notificationToastBody = document.getElementById('push-notification-toast-body');
const orderNotificationPrompt = document.getElementById('push-order-prompt');
const orderNotificationPromptDescription = document.getElementById('push-order-prompt-description');
const orderNotificationPromptButton = document.getElementById('push-order-prompt-button');

if (orderNotificationPrompt && isIos && !isStandalone) {
    orderNotificationPromptDescription.textContent = 'Di iPhone, pasang aplikasi ini lebih dulu. Setelah itu buka Pesanan Saya di aplikasi, lacak dengan kode di atas, lalu aktifkan notifikasi.';
    orderNotificationPromptButton.textContent = 'Lihat Cara Pasang';
    orderNotificationPromptButton.addEventListener('click', showIosInstructions);
    orderNotificationPrompt.removeAttribute('hidden');
}

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
const firebaseConfig = firebaseConfigElement ? {
    apiKey: firebaseConfigElement.dataset.apiKey,
    authDomain: firebaseConfigElement.dataset.authDomain,
    projectId: firebaseConfigElement.dataset.projectId,
    storageBucket: firebaseConfigElement.dataset.storageBucket,
    messagingSenderId: firebaseConfigElement.dataset.messagingSenderId,
    appId: firebaseConfigElement.dataset.appId,
} : null;

const requestJson = async (url, method, body) => {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    });

    if (!response.ok) {
        throw new Error(`Notification request failed with status ${response.status}`);
    }

    return response.json();
};

const platformName = () => {
    if (isIos) {
        return 'ios';
    }

    if (/android/i.test(navigator.userAgent)) {
        return 'android';
    }

    return 'desktop';
};

const setNotificationButtonState = (enabled) => {
    if (!notificationButton) {
        return;
    }

    notificationButton.textContent = enabled ? 'Aktif' : 'Aktifkan Notifikasi';
    notificationButton.dataset.enabled = String(enabled);
    notificationButton.setAttribute('aria-label', enabled ? 'Notifikasi aktif. Ketuk untuk mematikan.' : 'Aktifkan notifikasi');
};

const showNotificationToast = (title, body) => {
    notificationToastTitle.textContent = title;
    notificationToastBody.textContent = body;
    notificationToast?.removeAttribute('hidden');
    window.setTimeout(() => notificationToast?.setAttribute('hidden', ''), 6000);
};

const initializeNotifications = async () => {
    if (!firebaseConfig || !firebaseConfigElement?.dataset.vapidKey || !('serviceWorker' in navigator) || !window.isSecureContext) {
        return;
    }

    if (!await isSupported()) {
        return;
    }

    const serviceWorkerRegistration = await navigator.serviceWorker.register('/sw.js');
    const firebaseApp = getApps()[0] || initializeApp(firebaseConfig);
    const messaging = getMessaging(firebaseApp);
    let currentToken = null;

    const enableNotifications = async (shouldRequestPermission) => {
        if (isIos && !isStandalone) {
            showIosInstructions();

            return false;
        }

        let permission = Notification.permission;

        if (permission === 'default' && shouldRequestPermission) {
            permission = await Notification.requestPermission();
        }

        if (permission !== 'granted') {
            return false;
        }

        await navigator.serviceWorker.ready;

        currentToken = await getToken(messaging, {
            vapidKey: firebaseConfigElement.dataset.vapidKey,
            serviceWorkerRegistration,
        });

        if (!currentToken) {
            return false;
        }

        await requestJson(firebaseConfigElement.dataset.subscribeUrl, 'POST', {
            token: currentToken,
            platform: platformName(),
        });

        window.localStorage.setItem('bazamal_push_enabled', 'true');
        setNotificationButtonState(true);
        orderNotificationPrompt?.setAttribute('hidden', '');

        return true;
    };

    const disableNotifications = async () => {
        currentToken = currentToken || await getToken(messaging, {
            vapidKey: firebaseConfigElement.dataset.vapidKey,
            serviceWorkerRegistration,
        });

        if (currentToken) {
            await requestJson(firebaseConfigElement.dataset.unsubscribeUrl, 'DELETE', {
                token: currentToken,
            });
        }

        await deleteToken(messaging);
        currentToken = null;
        window.localStorage.removeItem('bazamal_push_enabled');
        setNotificationButtonState(false);
    };

    const enableNotificationsWithFeedback = async () => {
        const isEnabled = await enableNotifications(true);

        if (!isEnabled && !(isIos && !isStandalone)) {
            showNotificationToast(
                'Notifikasi belum aktif',
                Notification.permission === 'denied'
                    ? 'Izin notifikasi diblokir. Izinkan melalui pengaturan situs atau aplikasi.'
                    : 'Izin notifikasi belum diberikan.',
            );
        }

        return isEnabled;
    };

    notificationButton?.removeAttribute('hidden');
    setNotificationButtonState(
        Notification.permission === 'granted'
        && window.localStorage.getItem('bazamal_push_enabled') === 'true',
    );

    notificationButton?.addEventListener('click', async () => {
        notificationButton.disabled = true;

        try {
            if (window.localStorage.getItem('bazamal_push_enabled') === 'true') {
                if (window.confirm('Matikan notifikasi di perangkat ini?')) {
                    await disableNotifications();
                }
            } else {
                await enableNotificationsWithFeedback();
            }
        } catch (error) {
            console.error('Firebase notification update failed.', error);
            showNotificationToast('Notifikasi gagal diperbarui', 'Periksa koneksi internet, lalu coba lagi.');
        } finally {
            notificationButton.disabled = false;
        }
    });

    if (orderNotificationPrompt && !(isIos && !isStandalone) && window.localStorage.getItem('bazamal_push_enabled') !== 'true') {
        orderNotificationPrompt.removeAttribute('hidden');

        orderNotificationPromptButton.addEventListener('click', async () => {
            orderNotificationPromptButton.disabled = true;

            try {
                if (await enableNotificationsWithFeedback()) {
                    showNotificationToast('Notifikasi aktif', 'Kami akan mengabari perubahan status pesanan Anda.');
                }
            } catch (error) {
                console.error('Firebase notification update failed.', error);
                showNotificationToast('Notifikasi gagal diaktifkan', 'Periksa koneksi internet, lalu coba lagi.');
            } finally {
                orderNotificationPromptButton.disabled = false;
            }
        });
    }

    if (Notification.permission === 'granted' && window.localStorage.getItem('bazamal_push_enabled') === 'true') {
        enableNotifications(false).catch((error) => console.error('Firebase token refresh failed.', error));
    }

    onMessage(messaging, (payload) => {
        const title = payload.notification?.title || payload.data?.title || 'Bazar Amal';
        const body = payload.notification?.body || payload.data?.body || '';

        showNotificationToast(title, body);
    });
};

window.addEventListener('load', () => {
    initializeNotifications().catch((error) => console.error('Firebase notification setup failed.', error));
});
