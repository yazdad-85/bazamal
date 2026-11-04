<dialog id="payment-proof-viewer" aria-labelledby="payment-proof-viewer-title" class="h-full max-h-none w-full max-w-none bg-stone-950 p-0">
    <div class="flex h-full flex-col">
        <div class="flex shrink-0 items-center justify-between gap-3 p-3">
            <p id="payment-proof-viewer-title" class="min-w-0 truncate font-mono text-xs text-white/70"></p>
            <form method="dialog" class="shrink-0">
                <button class="rounded-full bg-white/15 px-4 py-2 text-sm font-bold text-white hover:bg-white/25">✕ Tutup</button>
            </form>
        </div>
        <div class="min-h-0 flex-1 overflow-auto px-3 pb-3">
            <img id="payment-proof-viewer-image" alt="Bukti pembayaran" class="mx-auto h-full w-full object-contain">
            <p id="payment-proof-viewer-error" hidden class="pt-10 text-center text-sm text-white/70">Foto bukti tidak ditemukan di server.</p>
        </div>
    </div>
</dialog>
