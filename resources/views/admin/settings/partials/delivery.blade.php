<section id="ongkir" class="mt-5 max-w-3xl space-y-4 rounded-2xl border border-stone-200 bg-white p-5">
    <div>
        <h2 class="font-bold text-brand-800">Ongkos Kirim Menu Bazar</h2>
        <p class="mt-1 text-sm text-stone-500">Berlaku untuk Kecamatan Manyar dan Bungah. Ongkir = jarak desa × tarif per km, dibulatkan ke atas, lalu minimal sebesar biaya minimum. Pesanan Infak tanpa ongkir.</p>
    </div>

    <form id="delivery-settings-form" method="POST" action="{{ route('admin.settings.delivery.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="delivery_origin" class="mb-1 block text-sm font-semibold">Titik asal pengantaran (lembaga/panitia)</label>
            <input id="delivery_origin" type="text" name="delivery_origin" required inputmode="text" placeholder="-7.1019, 112.5950"
                   value="{{ old('delivery_origin', $settings['delivery_origin'] ?? '') }}" class="w-full rounded-xl border-stone-200">
            <p class="mt-1 text-xs text-stone-400">Di Google Maps, tekan lokasi lembaga lalu salin koordinatnya. Saat titik ini berubah, jarak semua desa dihitung ulang.</p>
            @error('delivery_origin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div>
                <label for="delivery_rate_per_km" class="mb-1 block text-sm font-semibold">Tarif per km (Rp)</label>
                <input id="delivery_rate_per_km" type="number" name="delivery_rate_per_km" min="0" required
                       value="{{ old('delivery_rate_per_km', $settings['delivery_rate_per_km'] ?? '') }}" class="w-full rounded-xl border-stone-200">
                @error('delivery_rate_per_km') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="delivery_min_fee" class="mb-1 block text-sm font-semibold">Biaya minimum (Rp)</label>
                <input id="delivery_min_fee" type="number" name="delivery_min_fee" min="0" required
                       value="{{ old('delivery_min_fee', $settings['delivery_min_fee'] ?? '') }}" class="w-full rounded-xl border-stone-200">
                @error('delivery_min_fee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="delivery_fee_rounding" class="mb-1 block text-sm font-semibold">Pembulatan ke atas (Rp)</label>
                <input id="delivery_fee_rounding" type="number" name="delivery_fee_rounding" min="0" required
                       value="{{ old('delivery_fee_rounding', $settings['delivery_fee_rounding'] ?? '1000') }}" class="w-full rounded-xl border-stone-200">
                <p class="mt-1 text-xs text-stone-400">Isi 0 tanpa pembulatan.</p>
                @error('delivery_fee_rounding') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <details @if ($deliveryVillages->contains('distance_km', null) || $errors->has('distances.*')) open @endif class="rounded-xl border border-stone-200">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold">Jarak tiap desa (km)</summary>
            <div class="space-y-4 border-t border-stone-100 p-4">
                @foreach ($deliveryVillages->groupBy('district') as $district => $villages)
                    <div>
                        <h3 class="mb-2 text-sm font-bold text-brand-800">Kecamatan {{ $district }}</h3>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($villages as $village)
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span class="min-w-0 truncate">{{ $village->name }}</span>
                                    <input type="number" name="distances[{{ $village->id }}]" min="0" max="200" step="0.01"
                                           value="{{ old('distances.'.$village->id, $village->distance_km) }}"
                                           aria-label="Jarak {{ $village->name }} dalam km" class="w-24 shrink-0 rounded-lg border-stone-200 py-1.5 text-sm">
                                </label>
                                @error('distances.'.$village->id) <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <p class="text-xs text-stone-400">Jarak jalan dihitung dengan OSRM, data peta © OpenStreetMap contributors. Angka bisa dikoreksi manual. Desa tanpa jarak belum bisa dipilih pembeli.</p>
            </div>
        </details>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="rounded-xl bg-brand-700 px-6 py-3 font-bold text-white hover:bg-brand-600">Simpan Ongkir</button>
            <button type="submit" form="delivery-recalculate-form" class="rounded-xl border border-brand-700 px-6 py-3 font-bold text-brand-700 hover:bg-brand-50">Hitung Ulang Jarak</button>
        </div>
    </form>

    <form id="delivery-recalculate-form" method="POST" action="{{ route('admin.settings.delivery.recalculate') }}">
        @csrf
    </form>
</section>
