@extends('layouts.store')

@section('title', 'Keranjang')

@section('content')
    <div class="px-4 py-5">
        <h1 class="font-display font-extrabold text-xl text-brand-800 mb-4">Keranjang</h1>

        @if ($items->isEmpty())
            <div class="rounded-2xl border border-dashed border-stone-200 p-8 text-center">
                <p class="text-stone-500 mb-4">Keranjang masih kosong.</p>
                <a href="{{ route('home') }}" class="inline-flex rounded-xl bg-brand-600 text-white font-bold px-4 py-2.5">Mulai Belanja</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($items as $item)
                    <div class="rounded-2xl border border-stone-100 p-3 flex gap-3">
                        <div class="w-16 h-16 rounded-xl bg-stone-100 overflow-hidden shrink-0">
                            @if (!empty($item['image']))
                                <img src="{{ asset('storage/'.$item['image']) }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $item['name'] }}</p>
                            <p class="text-brand-700 font-bold text-sm">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                    <input type="number" name="qty" value="{{ $item['qty'] }}" min="1" max="99"
                                           class="w-16 rounded-lg border-stone-200 text-sm py-1">
                                    <button class="text-xs font-bold text-brand-700">Ubah</button>
                                </form>
                                <form action="{{ route('cart.destroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                                    <button class="text-xs font-bold text-red-600">Hapus</button>
                                </form>
                            </div>
                        </div>
                        <div class="text-sm font-bold text-stone-700">
                            Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 rounded-2xl bg-brand-50 border border-brand-100 p-4">
                <div class="flex justify-between font-extrabold text-brand-800 mb-4">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <a href="{{ route('checkout.show') }}"
                   class="block text-center rounded-xl bg-accent-600 hover:bg-accent-500 text-white font-bold py-3">
                    Lanjut Checkout
                </a>
            </div>
        @endif
    </div>
@endsection
