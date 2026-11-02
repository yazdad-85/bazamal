<div
    id="pwa-config"
    data-api-key="{{ config('services.firebase.web.api_key') }}"
    data-auth-domain="{{ config('services.firebase.web.auth_domain') }}"
    data-project-id="{{ config('services.firebase.web.project_id') }}"
    data-storage-bucket="{{ config('services.firebase.web.storage_bucket') }}"
    data-messaging-sender-id="{{ config('services.firebase.web.messaging_sender_id') }}"
    data-app-id="{{ config('services.firebase.web.app_id') }}"
    data-vapid-key="{{ config('services.firebase.web.vapid_key') }}"
    data-subscribe-url="{{ route('push-subscriptions.store') }}"
    data-unsubscribe-url="{{ route('push-subscriptions.destroy') }}"
></div>

<button
    id="push-notification-button"
    type="button"
    hidden
    class="fixed bottom-4 right-4 z-40 rounded-full bg-brand-700 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-900/20 hover:bg-brand-600 data-[enabled=true]:bottom-2 data-[enabled=true]:right-2 data-[enabled=true]:bg-white/60 data-[enabled=true]:px-2 data-[enabled=true]:py-0.5 data-[enabled=true]:text-[10px] data-[enabled=true]:font-semibold data-[enabled=true]:text-brand-800/70 data-[enabled=true]:shadow-none data-[enabled=true]:backdrop-blur-sm data-[enabled=true]:hover:bg-white/90"
>
    Aktifkan Notifikasi
</button>

<div id="pwa-install-banner" hidden class="fixed inset-x-4 bottom-4 z-50 mx-auto max-w-md rounded-2xl border border-brand-100 bg-white p-4 shadow-2xl">
    <div class="flex items-start gap-3">
        <img src="/icons/icon-192.png?v=2" alt="" class="h-12 w-12 shrink-0 rounded-xl">
        <div class="min-w-0 flex-1">
            <p id="pwa-install-title" class="font-extrabold text-brand-800">Pasang Bazar Amal</p>
            <p id="pwa-install-description" class="mt-1 text-xs leading-5 text-stone-500">Akses lebih cepat langsung dari layar utama perangkat Anda.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button id="pwa-install-button" type="button" class="rounded-full bg-brand-700 px-4 py-2 text-xs font-bold text-white hover:bg-brand-600">
                    Pasang Aplikasi
                </button>
                <button id="pwa-install-dismiss" type="button" class="rounded-full px-3 py-2 text-xs font-semibold text-stone-500 hover:bg-stone-100">
                    Nanti
                </button>
            </div>
        </div>
    </div>
</div>

<div id="pwa-ios-instructions" hidden class="fixed inset-0 z-[60] bg-stone-950/50 p-4">
    <div role="dialog" aria-modal="true" aria-labelledby="pwa-ios-title" class="mx-auto mt-[12vh] max-w-sm rounded-2xl bg-white p-5 shadow-2xl">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 id="pwa-ios-title" class="font-extrabold text-brand-800">Pasang di iPhone</h2>
                <p class="mt-1 text-sm text-stone-500">Gunakan menu Safari untuk memasang aplikasi.</p>
            </div>
            <button id="pwa-ios-close" type="button" aria-label="Tutup" class="rounded-full p-2 text-stone-400 hover:bg-stone-100">✕</button>
        </div>
        <ol class="mt-4 space-y-3 text-sm text-stone-700">
            <li class="flex gap-3"><span class="font-extrabold text-brand-700">1.</span><span>Tekan tombol <strong>Bagikan</strong> di Safari.</span></li>
            <li class="flex gap-3"><span class="font-extrabold text-brand-700">2.</span><span>Pilih <strong>Tambahkan ke Layar Utama</strong>.</span></li>
            <li class="flex gap-3"><span class="font-extrabold text-brand-700">3.</span><span>Aktifkan <strong>Buka sebagai App Web</strong>, lalu tekan Tambah.</span></li>
        </ol>
        <button id="pwa-ios-done" type="button" class="mt-5 w-full rounded-xl bg-brand-700 py-3 text-sm font-bold text-white hover:bg-brand-600">Mengerti</button>
    </div>
</div>

<div id="push-notification-toast" hidden class="fixed left-4 right-4 top-4 z-[70] mx-auto max-w-md rounded-2xl bg-brand-800 p-4 text-white shadow-2xl">
    <p id="push-notification-toast-title" class="font-extrabold"></p>
    <p id="push-notification-toast-body" class="mt-1 text-sm text-white/80"></p>
</div>
