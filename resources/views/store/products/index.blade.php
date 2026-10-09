@extends('layouts.store')

@section('title', $bazarType === 'besar' ? 'Bazar Besar' : 'Bazar Kecil')

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
