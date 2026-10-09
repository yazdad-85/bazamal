@extends('layouts.store')

@section('seo_title', ($siteName ?? 'Bazar Amal').' — Toko Amal untuk Siswa dan Masyarakat')
@section('canonical', route('home'))

@push('head')
    @php
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    'name' => $siteName ?? 'Bazar Amal',
                    'url' => route('home'),
                    'description' => \App\Models\Setting::metaDescription(),
                    'inLanguage' => 'id-ID',
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => route('products.index', ['bazar' => 'besar']).'&q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                array_filter([
                    '@type' => 'Organization',
                    'name' => $siteName ?? 'Bazar Amal',
                    'url' => route('home'),
                    'logo' => $siteLogoUrl,
                ]),
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 text-white px-5 py-8">
        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_20%_20%,#fff_0,transparent_45%),radial-gradient(circle_at_80%_70%,#ffb74d_0,transparent_40%)]"></div>
        <div class="relative max-w-xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70 mb-2">Bazar Amal Peduli</p>
            <h1 class="font-display text-2xl sm:text-3xl font-extrabold leading-tight">Belanja Sambil Berbagi!</h1>
            <p class="mt-2 text-sm text-white/85">Total Donasi <span class="font-extrabold text-accent-400">Rp {{ $donationTotal }}</span></p>
        </div>
    </section>

    <section class="px-4 -mt-5 relative z-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('products.index', ['bazar' => 'besar']) }}"
               class="rounded-2xl bg-accent-700 hover:bg-accent-600 text-white p-5 shadow-lg shadow-orange-900/10 transition flex items-center gap-4">
                <span class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <span>
                    <span class="block font-extrabold text-lg leading-tight">Menu Bazar Besar</span>
                    <span class="text-xs text-white/80">Barang utama & paket amal</span>
                </span>
            </a>
            <a href="{{ route('products.index', ['bazar' => 'kecil']) }}"
               class="rounded-2xl bg-accent-500 hover:bg-accent-400 text-white p-5 shadow-lg shadow-orange-900/10 transition flex items-center gap-4">
                <span class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </span>
                <span>
                    <span class="block font-extrabold text-lg leading-tight">Menu Bazar Kecil</span>
                    <span class="text-xs text-white/80">Jajanan & merchandise</span>
                </span>
            </a>
        </div>
    </section>

    <section class="px-4 mt-8">
        <div class="flex items-end justify-between mb-3">
            <h2 class="font-display font-bold text-lg text-brand-800">Produk Pilihan</h2>
            <a href="{{ route('products.index', ['bazar' => 'besar']) }}" class="text-sm font-semibold text-brand-600">Lihat semua</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-2 text-sm text-stone-500">Belum ada produk aktif.</p>
            @endforelse
        </div>
    </section>
@endsection
