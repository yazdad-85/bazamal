@extends('layouts.store')

@php
    $catalogLabel = $bazarType === 'infak' ? 'Infak & Sedekah' : 'Menu Bazar';
    $catalogDescription = $bazarType === 'infak'
        ? 'Infak dan sedekah di '.($siteName ?? 'Bazar Amal').'. Kategori ini tetap dapat dipesan selama website aktif.'
        : 'Menu Bazar di '.($siteName ?? 'Bazar Amal').'. Pemesanan dibuka sampai '.$menuDeadlineLabel.', diambil di lokasi atau diantar tanpa ongkir.';
@endphp
@section('seo_title', $catalogLabel.' — '.($siteName ?? 'Bazar Amal'))
@section('meta_description', $catalogDescription)
@section('canonical', route('products.index', ['bazar' => $bazarType]))
@section('robots', request()->filled('q') ? 'noindex, follow' : 'index, follow')

@section('content')
    <div class="px-4 pt-5">
        <div class="flex gap-2 mb-4">
            <a href="{{ route('products.index', ['bazar' => 'menu']) }}"
               @class(['px-4 py-2 rounded-full text-sm font-bold', $bazarType === 'menu' ? 'bg-accent-700 text-white' : 'bg-stone-100 text-stone-600'])>
                Menu Bazar
            </a>
            <a href="{{ route('products.index', ['bazar' => 'infak']) }}"
               @class(['px-4 py-2 rounded-full text-sm font-bold', $bazarType === 'infak' ? 'bg-accent-500 text-white' : 'bg-stone-100 text-stone-600'])>
                Infak & Sedekah
            </a>
        </div>

        <h1 class="font-display font-extrabold text-xl text-brand-800 mb-2">{{ $catalogLabel }}</h1>
        <p class="text-sm text-stone-500 mb-4">
            @if ($bazarType === 'infak')
                Kategori ini tetap dapat dipesan selama website aktif.
            @elseif ($menuOrderingOpen)
                Pemesanan menu dibuka sampai {{ $menuDeadlineLabel }}.
            @else
                Pemesanan menu sudah ditutup pada {{ $menuDeadlineLabel }}.
            @endif
        </p>

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
