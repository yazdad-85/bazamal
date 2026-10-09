@extends('layouts.store')

@php
    $catalogLabel = $bazarType === 'besar' ? 'Bazar Besar' : 'Bazar Kecil';
    $catalogDescription = $bazarType === 'besar'
        ? 'Katalog Bazar Besar di '.($siteName ?? 'Bazar Amal').'. Produk pilihan panitia untuk siswa dan masyarakat, diambil di lokasi atau diantar tanpa ongkir.'
        : 'Katalog Bazar Kecil di '.($siteName ?? 'Bazar Amal').'. Jajanan dan merchandise bazar untuk siswa, diambil langsung di lokasi bazar.';
@endphp
@section('seo_title', $catalogLabel.' — '.($siteName ?? 'Bazar Amal'))
@section('meta_description', $catalogDescription)
@section('canonical', route('products.index', ['bazar' => $bazarType]))
@section('robots', request()->filled('q') ? 'noindex, follow' : 'index, follow')

@section('content')
    <div class="px-4 pt-5">
        <div class="flex gap-2 mb-4">
            <a href="{{ route('products.index', ['bazar' => 'besar']) }}"
               @class(['px-4 py-2 rounded-full text-sm font-bold', $bazarType === 'besar' ? 'bg-accent-700 text-white' : 'bg-stone-100 text-stone-600'])>
                Bazar Besar
            </a>
            <a href="{{ route('products.index', ['bazar' => 'kecil']) }}"
               @class(['px-4 py-2 rounded-full text-sm font-bold', $bazarType === 'kecil' ? 'bg-accent-500 text-white' : 'bg-stone-100 text-stone-600'])>
                Bazar Kecil
            </a>
        </div>

        <h1 class="font-display font-extrabold text-xl text-brand-800 mb-4">
            {{ $bazarType === 'besar' ? 'Bazar Besar' : 'Bazar Kecil' }}
        </h1>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-2 text-sm text-stone-500">Tidak ada produk di etalase ini.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
@endsection
