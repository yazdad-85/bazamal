@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('heading', 'Pengaturan Website')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 max-w-3xl">
        @csrf
        @method('PUT')

        <section class="bg-white rounded-2xl border border-stone-200 p-5 space-y-4">
            <h2 class="font-bold text-brand-800">Identitas Website</h2>

            <div>
                <label class="block text-sm font-semibold mb-1">Nama Website</label>
                <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? 'Bazar Amal') }}" required
                       class="w-full rounded-xl border-stone-200">
                @error('site_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Logo</label>
                @if ($logoUrl)
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ $logoUrl }}" alt="Logo" class="h-16 w-16 rounded-xl object-contain bg-stone-50 border border-stone-100 p-1">
                        <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-stone-300 text-brand-600">
                            Hapus logo
                        </label>
                    </div>
                @endif
                <input type="file" name="site_logo" accept="image/*" class="block w-full text-sm">
                <p class="text-xs text-stone-400 mt-1">PNG/JPG, maks. 2 MB. Ditampilkan di header toko & admin.</p>
                @error('site_logo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Gambar QRIS</label>
                @if ($qrisUrl)
                    <div class="flex items-center gap-4 mb-3">
                        <img src="{{ $qrisUrl }}" alt="QRIS" class="h-28 w-28 rounded-xl object-contain bg-white border border-stone-100 p-1">
                        <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" name="remove_qris" value="1" class="rounded border-stone-300 text-brand-600">
                            Hapus QRIS
                        </label>
                    </div>
                @endif
                <input type="file" name="qris_image" accept="image/*" class="block w-full text-sm">
                <p class="text-xs text-stone-400 mt-1">PNG/JPG QRIS statis panitia, maks. 2 MB. Tampil saat pembeli memilih bayar QRIS. Panitia mencocokkan pembayaran secara manual.</p>
                @error('qris_image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold mb-1">Info Rekening Transfer</label>
                    <input type="text" name="bank_info" value="{{ old('bank_info', $settings['bank_info'] ?? '') }}"
                           class="w-full rounded-xl border-stone-200" placeholder="BSI 7123... a.n. Panitia">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Total Donasi (otomatis)</label>
                    <input type="text" value="Rp {{ $donationTotal }}" readonly
                           class="w-full rounded-xl border-stone-200 bg-stone-50 text-stone-700">
                    <p class="text-xs text-stone-400 mt-1">Dihitung dari semua pesanan yang belum dibatalkan. Berubah sendiri setiap ada checkout.</p>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-stone-200 p-5 space-y-4">
            <div>
                <h2 class="font-bold text-brand-800">Nomor WhatsApp</h2>
                <p class="text-sm text-stone-500 mt-1">Format: 628xxxxxxxxxx (tanpa +). Pesanan siswa dikirim ke WA lembaga; umum ke bendahara inti.</p>
            </div>

            <div class="rounded-xl bg-orange-50 border border-accent-400/40 p-4">
                <label class="block text-sm font-bold text-accent-700 mb-1">Bendahara Inti (utama)</label>
                <input type="text" name="wa_bendahara" value="{{ old('wa_bendahara', $settings['wa_bendahara'] ?? '') }}" required
                       class="w-full rounded-xl border-stone-200" placeholder="62812...">
                @error('wa_bendahara') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-stone-500 mt-1">Dipakai untuk pembeli Umum, dan fallback jika nomor lembaga kosong.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($institutions as $lembaga)
                    @php $key = 'wa_'.strtolower($lembaga); @endphp
                    <div>
                        <label class="block text-sm font-semibold mb-1">WA Panitia {{ $lembaga }}</label>
                        <input type="text" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}"
                               class="w-full rounded-xl border-stone-200" placeholder="62812...">
                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </section>

        <button type="submit" class="rounded-xl bg-brand-700 hover:bg-brand-600 text-white font-bold px-6 py-3">
            Simpan Pengaturan
        </button>
    </form>
@endsection
