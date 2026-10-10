<div class="space-y-5">
    @error('cart')
        <div class="rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ $message }}</div>
    @enderror

    <section class="space-y-3">
        <div>
            <label class="block text-sm font-semibold mb-1">Nama Lengkap</label>
            <input type="text" wire:model="full_name" class="w-full rounded-xl border-stone-200" placeholder="Untuk notifikasi pesanan">
            @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">No. HP / WhatsApp</label>
            <input type="text" wire:model="phone" class="w-full rounded-xl border-stone-200" placeholder="08xxxxxxxxxx" required>
            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-stone-400 mt-1">Wajib diisi agar panitia bisa menghubungi dan pesanan tidak hilang.</p>
        </div>
    </section>

    <section>
        <h2 class="font-bold text-brand-800 mb-2">Pengiriman</h2>
        <p class="text-sm text-stone-500 mb-3">Pesanan diantar ke alamat. Tidak ada biaya kirim. Tim panitia yang mengantar.</p>
        <label class="block text-sm font-semibold mb-1">Alamat pengiriman</label>
        <textarea wire:model="delivery_address" rows="3" required
                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, dan catatan untuk tim pengantar"
                  class="w-full rounded-xl border-stone-200"></textarea>
        @error('delivery_address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </section>

    <section>
        <h2 class="font-bold text-brand-800 mb-2">Metode Pembayaran</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            @foreach ([
                'online' => 'QRIS',
                'transfer' => 'Transfer Bank Manual',
                'tunai' => 'Tunai (Cash/COD)',
            ] as $value => $label)
                <label @class(['rounded-xl border-2 p-3 cursor-pointer text-center text-sm font-semibold', $payment_method === $value ? 'border-accent-600 bg-orange-50' : 'border-stone-200'])>
                    <input type="radio" name="payment_method" wire:model.live="payment_method" value="{{ $value }}" class="sr-only">
                    {{ $label }}
                </label>
            @endforeach
        </div>

        @if ($payment_method === 'online')
            <div class="mt-3 rounded-xl bg-stone-50 p-3 text-center">
                @if ($qrisUrl)
                    <p class="text-sm font-semibold text-brand-800">Scan QRIS untuk membayar</p>
                    <img src="{{ $qrisUrl }}" alt="QRIS panitia" class="mx-auto mt-3 max-h-64 rounded-xl border bg-white p-2">
                    <p class="text-xs text-stone-500 mt-2">Nominal sesuai total pesanan. Setelah bayar, selesaikan pesanan. Status masuk <strong>Menunggu Verifikasi</strong> sampai panitia mencocokkan pembayaran di aplikasi QRIS.</p>
                @else
                    <p class="text-xs text-stone-500">Gambar QRIS belum diunggah di pengaturan panitia. Pesanan tetap bisa disimpan dan masuk status <strong>Menunggu Verifikasi</strong>.</p>
                @endif
            </div>
        @endif

        @if ($payment_method === 'transfer')
            <div class="mt-3 space-y-2 rounded-xl bg-stone-50 p-3">
                <p class="text-sm font-semibold">Transfer ke: {{ $bankInfo }}</p>
                <input type="file" wire:model="payment_proof" accept="image/*" class="block w-full text-sm">
                @error('payment_proof') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="payment_proof" class="text-xs text-brand-600">Mengunggah...</div>
            </div>
        @endif
    </section>

    <button type="button" wire:click="submit" wire:loading.attr="disabled"
            class="w-full rounded-xl bg-accent-600 hover:bg-accent-500 disabled:opacity-60 text-white font-extrabold py-3.5">
        <span wire:loading.remove wire:target="submit">Selesaikan Pesanan & Kirim ke Admin</span>
        <span wire:loading wire:target="submit">Memproses...</span>
    </button>
</div>
