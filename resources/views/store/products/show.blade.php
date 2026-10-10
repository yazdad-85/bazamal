@extends('layouts.store')

@php
    $productDescription = trim(preg_replace('/\s+/', ' ', strip_tags((string) $product->description)) ?? '');
    if ($productDescription === '') {
        $productDescription = $product->name.' di '.($siteName ?? 'Bazar Amal').'. Harga '.$product->formatted_price.'. '.$product->bazar_label.' untuk siswa dan masyarakat.';
    }
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $productDescription,
        'image' => $product->image_url,
        'url' => route('products.show', $product),
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'IDR',
            'price' => (string) $product->price,
            'availability' => $product->stock > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'url' => route('products.show', $product),
        ],
    ];
@endphp
@section('seo_title', $product->name.' — '.($siteName ?? 'Bazar Amal'))
@section('meta_description', $productDescription)
@section('canonical', route('products.show', $product))
@section('og_type', 'product')
@section('og_image', $product->image_url)

@push('head')
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
    <div class="relative">
        <a href="{{ url()->previous() }}" class="absolute top-3 left-3 z-10 w-10 h-10 rounded-full bg-white/90 shadow flex items-center justify-center text-brand-800">←</a>
        <div class="aspect-[4/3] bg-stone-100">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </div>
        <div class="absolute bottom-3 left-3 flex flex-wrap gap-2">
            <span class="rounded-full bg-amber-300 text-amber-950 text-[11px] font-bold px-3 py-1">
                Kategori: {{ $product->bazar_label }}
            </span>
            <span class="rounded-full bg-violet-600 text-white text-[11px] font-bold px-3 py-1">
                Bisa dibeli Siswa & Umum
            </span>
        </div>
    </div>

    <div class="px-4 py-5 space-y-4">
        <div>
            <h1 class="font-display text-2xl font-extrabold text-brand-900">{{ $product->name }}</h1>
            <p class="text-sm text-stone-500 mt-1">Bazar Amal · Sisa {{ $product->stock }} pcs</p>
            <p class="text-2xl font-extrabold text-brand-700 mt-2">{{ $product->formatted_price }}</p>
            <p class="text-sm text-stone-600 mt-3">{{ $product->description }}</p>
            <p class="text-xs text-brand-600 font-semibold mt-2">100% keuntungan untuk donasi panti asuhan.</p>
            @if ($product->orderingClosed())
                <p class="text-sm text-red-600 font-semibold mt-2">Pemesanan menu sudah ditutup pada {{ $product->menuDeadlineLabel() }}.</p>
            @elseif (! $product->isInfak())
                <p class="text-sm text-stone-500 mt-2">Pemesanan menu dibuka sampai {{ $product->menuDeadlineLabel() }}.</p>
            @endif
        </div>

        <form action="{{ route('cart.store') }}" method="POST" class="space-y-3" x-data="{ qty: 1 }">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div class="flex items-center gap-3">
                <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-10 h-10 rounded-full border border-stone-200 font-bold">−</button>
                <input type="number" name="qty" x-model.number="qty" min="1" max="{{ max($product->stock, 1) }}"
                       class="w-16 text-center rounded-xl border-stone-200 font-bold">
                <button type="button" @click="qty = Math.min({{ max($product->stock, 1) }}, qty + 1)" class="w-10 h-10 rounded-full border border-stone-200 font-bold">+</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button type="submit" @disabled($product->stock < 1 || $product->orderingClosed())
                        class="w-full rounded-xl border-2 border-brand-600 text-brand-700 font-bold py-3 disabled:opacity-40">
                    Masukkan Keranjang
                </button>
                <button type="submit" name="buy_now" value="1" @disabled($product->stock < 1 || $product->orderingClosed())
                        class="w-full rounded-xl bg-accent-600 hover:bg-accent-500 text-white font-bold py-3 disabled:opacity-40">
                    Beli Sekarang
                </button>
            </div>
        </form>
    </div>
@endsection
