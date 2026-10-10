<div class="space-y-5">
    @error('cart')
        <div class="rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ $message }}</div>
    @enderror

    <section>
        <h2 class="font-bold text-brand-800 mb-2">Tipe Pembeli</h2>
        <div class="grid grid-cols-2 gap-2">
            <label @class(['rounded-xl border-2 p-3 cursor-pointer text-center font-semibold', $buyer_type === 'umum' ? 'border-brand-600 bg-brand-50' : 'border-stone-200'])>
                <input type="radio" name="buyer_type" wire:model.live="buyer_type" value="umum" class="sr-only"> Umum
            </label>
            <label @class(['rounded-xl border-2 p-3 cursor-pointer text-center font-semibold', $buyer_type === 'siswa' ? 'border-brand-600 bg-brand-50' : 'border-stone-200'])>
                <input type="radio" name="buyer_type" wire:model.live="buyer_type" value="siswa" class="sr-only"> Siswa
            </label>
        </div>
    </section>

    @if ($buyer_type === 'siswa')
        <section class="rounded-2xl border-2 border-accent-400 bg-orange-50/70 p-4 space-y-3">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-accent-700">Fitur Baru — Data Siswa</p>
            <div>
                <label class="block text-sm font-semibold mb-1">Asal Lembaga</label>
                <select wire:model="institution" class="w-full rounded-xl border-stone-200">
                    @foreach ($institutions as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
                @error('institution') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Kelas</label>
                <input type="text" wire:model="class_name" placeholder="Contoh: XI-IPA 2"
                       class="w-full rounded-xl border-stone-200">
                @error('class_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </section>
    @endif

    <section class="space-y-3">
        <div>
            <label class="block text-sm font-semibold mb-1">Nama Lengkap</label>
            <input type="text" wire:model="full_name" class="w-full rounded-xl border-stone-200" placeholder="Untuk notifikasi pesanan">
            @error('full_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold mb-1">No. HP / WhatsApp</label>
            <input type="text" wire:model="phone" class="w-full rounded-xl border-stone-200" placeholder="08xxxxxxxxxx" @required($buyer_type !== 'siswa')>
            @error('phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-stone-400 mt-1">
                @if ($buyer_type === 'siswa')
                    Opsional untuk siswa. Isi jika panitia perlu menghubungi lewat WhatsApp.
                @else
                    Wajib diisi agar panitia bisa menghubungi dan pesanan tidak hilang.
                @endif
            </p>
        </div>
    </section>

    <section>
        <h2 class="font-bold text-brand-800 mb-2">Metode Pengambilan</h2>
        <div class="space-y-2">
            @if ($canDeliver)
                <label @class(['flex items-start gap-3 rounded-xl border p-3 cursor-pointer', $pickup_method === 'kirim_alamat' ? 'border-brand-600 bg-brand-50' : 'border-stone-200'])>
                    <input type="radio" name="pickup_method" wire:model.live="pickup_method" value="kirim_alamat" class="mt-1 text-brand-600">
                    <span class="text-sm">
                        <span class="font-semibold">Kirim ke Alamat</span>
                        <span class="block text-xs text-stone-500 mt-0.5">Khusus Menu Bazar. Tanpa ongkir, diantar tim panitia.</span>
                    </span>
                </label>
            @else
                <label class="flex items-start gap-3 rounded-xl border border-stone-200 p-3 opacity-60 cursor-not-allowed">
                    <input type="radio" disabled class="mt-1 text-stone-400">
                    <span class="text-sm">
                        Kirim ke Alamat
                        <span class="block text-xs text-stone-400 mt-0.5">Hanya jika semua barang di keranjang dari Menu Bazar.</span>
                    </span>
                </label>
            @endif
            <label @class(['flex items-center gap-3 rounded-xl border p-3 cursor-pointer', $pickup_method === 'ambil_stand' ? 'border-brand-600 bg-brand-50' : 'border-stone-200'])>
                <input type="radio" name="pickup_method" wire:model.live="pickup_method" value="ambil_stand" class="text-brand-600">
                <span class="text-sm font-semibold">Ambil di Lokasi Bazar</span>
            </label>
        </div>
        @error('pickup_method') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

        @if ($canDeliver && $pickup_method === 'kirim_alamat')
            <div class="mt-3">
                <label class="block text-sm font-semibold mb-1">Alamat pengiriman</label>
                <textarea wire:model="delivery_address" rows="3"
                          placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, dan catatan untuk tim pengantar"
                          class="w-full rounded-xl border-stone-200"></textarea>
                @error('delivery_address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-stone-400 mt-1">Tidak ada biaya kirim. Tim panitia yang mengantar.</p>
            </div>
        @endif
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
