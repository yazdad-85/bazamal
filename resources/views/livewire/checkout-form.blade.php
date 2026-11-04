<div class="space-y-5">
    @error('cart')
        <div class="rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3">{{ $message }}</div>
    @enderror

    @if ($hasMenuItems)
        <section class="space-y-3">
            <div>
                <h2 class="font-bold text-brand-800">Lokasi Pengiriman</h2>
                <p class="text-sm text-stone-500">Menu Bazar diantar panitia ke Kabupaten Gresik, kecuali Pulau Bawean. Gratis ongkir untuk Kecamatan Manyar, Gresik, dan Bungah.</p>
            </div>
            <div>
                <label for="delivery_district" class="block text-sm font-semibold mb-1">Kecamatan</label>
                <select id="delivery_district" wire:model.live="delivery_district" class="w-full rounded-xl border-stone-200">
                    <option value="">Pilih kecamatan</option>
                    @foreach ($districts as $district)
                        <option wire:key="district-{{ $district }}" value="{{ $district }}">{{ $district }}{{ in_array($district, $freeDeliveryDistricts, true) ? ' (gratis ongkir)' : '' }}</option>
                    @endforeach
                    <option value="{{ \App\Livewire\CheckoutForm::BAWEAN_AREA }}">Pulau Bawean (Sangkapura, Tambak)</option>
                    <option value="{{ \App\Livewire\CheckoutForm::OUTSIDE_AREA }}">Di luar Kabupaten Gresik</option>
                </select>
            </div>

            @if ($villages->isNotEmpty())
                <div>
                    <label for="delivery_village_id" class="block text-sm font-semibold mb-1">Desa/Kelurahan</label>
                    <select id="delivery_village_id" wire:model.live="delivery_village_id" class="w-full rounded-xl border-stone-200">
                        <option value="">Pilih desa/kelurahan</option>
                        @foreach ($villages as $village)
                            <option wire:key="village-{{ $village->id }}" value="{{ $village->id }}">{{ $village->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @error('delivery_village_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            @if ($blockedAreaMessage)
                <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-bold">{{ $blockedAreaMessage }}</p>
                    <p class="mt-1">Keluarkan Menu Bazar dari keranjang, lalu lanjutkan dengan Infak &amp; Sedekah.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('products.index', ['bazar' => 'infak']) }}" class="rounded-lg bg-brand-700 px-3 py-2 text-xs font-bold text-white hover:bg-brand-600">Pilih Menu Infak</a>
                        <a href="{{ route('cart.index') }}" class="rounded-lg border border-amber-300 px-3 py-2 text-xs font-bold hover:bg-amber-100">Buka Keranjang</a>
                    </div>
                </div>
            @elseif ($selectedVillage && $deliveryFee === null)
                <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-900">
                    Ongkir untuk desa ini belum diatur panitia. Silakan hubungi panitia.
                </div>
            @elseif ($selectedVillage)
                <div class="flex items-center justify-between gap-3 rounded-xl bg-brand-50 p-3 text-sm text-brand-800">
                    <span>Ongkir ke {{ $selectedVillage->name }}</span>
                    <span class="font-extrabold whitespace-nowrap">{{ $deliveryFeeLabel }}</span>
                </div>
            @else
                <p class="text-xs text-stone-500">Pilih lokasi pengiriman untuk melanjutkan pengisian.</p>
            @endif
        </section>
    @endif

    <fieldset @disabled(! $canContinue) class="space-y-5 disabled:opacity-50">
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

    @if ($hasMenuItems)
    <section>
        <h2 class="font-bold text-brand-800 mb-2">Pengiriman</h2>
        <p class="text-sm text-stone-500 mb-3">Pesanan diantar ke alamat oleh tim panitia.</p>
        <label class="block text-sm font-semibold mb-1">Alamat pengiriman</label>
        <textarea wire:model="delivery_address" rows="3" required
                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, dan catatan untuk tim pengantar"
                  class="w-full rounded-xl border-stone-200"></textarea>
        @error('delivery_address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

        <div class="mt-3">
                <label class="block text-sm font-semibold mb-1">Hari pengiriman</label>
                <select wire:model="delivery_date" required class="w-full rounded-xl border-stone-200">
                    <option value="">Pilih hari pengiriman</option>
                    @foreach ($deliveryDates as $date => $label)
                        <option wire:key="delivery-date-{{ $date }}" value="{{ $date }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('delivery_date') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-stone-400 mt-1">Pilih hari yang diinginkan untuk pengantaran Menu Bazar.</p>
        </div>
    </section>
    @endif

    <section>
        <h2 class="font-bold text-brand-800 mb-2">Metode Pembayaran</h2>
        <div @class(['grid grid-cols-1 gap-2', 'sm:grid-cols-3' => count($paymentMethods) === 3, 'sm:grid-cols-2' => count($paymentMethods) !== 3])>
            @foreach ($paymentMethods as $value => $label)
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
                    <p class="text-xs text-stone-500 mt-2">Bayar sesuai total pesanan, lalu unggah tangkapan layar pembayaran berhasil.</p>
                @else
                    <p class="text-xs text-stone-500">Gambar QRIS belum diunggah di pengaturan panitia. Unggah bukti pembayaran tetap wajib.</p>
                @endif
            </div>
        @endif

        @if ($payment_method === 'transfer')
            <div class="mt-3 rounded-xl bg-stone-50 p-3">
                <p class="text-sm font-semibold">Transfer ke: {{ $bankInfo }}</p>
            </div>
        @endif

        @if (in_array($payment_method, ['online', 'transfer'], true))
            <div class="mt-3 space-y-2 rounded-xl bg-stone-50 p-3 text-left">
                <label class="block text-sm font-semibold">Bukti pembayaran berhasil</label>
                <p class="text-xs text-stone-500">Wajib. Panitia memakai gambar ini untuk mencocokkan pembayaran.</p>
                <div wire:ignore class="space-y-2">
                    <input id="payment-proof-input" type="file" accept="image/*" class="block w-full text-sm">
                    <p id="payment-proof-status" role="status" hidden class="text-xs font-semibold text-brand-700 data-[error=true]:text-red-600"></p>
                </div>
                @error('payment_proof') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif
    </section>

    <section class="space-y-1 rounded-xl border border-stone-100 p-3 text-sm">
        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
        @if ($hasMenuItems)
            <div class="flex justify-between"><span>Ongkir</span><span>{{ $deliveryFeeLabel }}</span></div>
        @endif
        <div class="flex justify-between border-t border-stone-100 pt-1 font-extrabold text-brand-800"><span>Total</span><span>Rp {{ number_format($total, 0, ',', '.') }}</span></div>
    </section>

    <button id="checkout-submit" type="button" wire:click="submit" wire:loading.attr="disabled"
            class="w-full rounded-xl bg-accent-600 hover:bg-accent-500 disabled:opacity-60 text-white font-extrabold py-3.5">
        <span wire:loading.remove wire:target="submit">Selesaikan Pesanan & Kirim ke Admin</span>
        <span wire:loading wire:target="submit">Memproses...</span>
    </button>
    </fieldset>
</div>
