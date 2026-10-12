@extends('layouts.store')

@section('title', 'Konfirmasi Pesanan')
@section('robots', 'noindex, nofollow')

@section('content')
    <div class="px-4 py-5 max-w-lg mx-auto">
        <h1 class="font-display font-extrabold text-xl text-brand-800">Yakin memesan ini?</h1>
        <p class="text-sm text-stone-500 mt-2">Periksa menu di bawah. Kalau sudah benar, lanjutkan pembayaran. Kalau masih ragu, kembali untuk memilih menu.</p>

        <div class="mt-5 space-y-3">
            @foreach ($items as $item)
                <div class="rounded-2xl border border-stone-100 p-3 flex gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm">{{ $item['name'] }}</p>
                        <p class="text-stone-500 text-xs mt-0.5">{{ $item['qty'] }} × Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                    </div>
                    <p class="text-sm font-bold text-brand-800 whitespace-nowrap">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-5 rounded-2xl bg-brand-50 border border-brand-100 p-4">
            <div class="flex justify-between font-extrabold text-brand-800 mb-4">
                <span>Total</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <form method="POST" action="{{ route('cart.proceed') }}">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-accent-600 hover:bg-accent-500 text-white font-bold py-3.5">
                    Lanjutkan pembayaran
                </button>
            </form>
            <a href="{{ route('home') }}" class="mt-3 block text-center rounded-xl border border-stone-200 bg-white font-bold py-3 text-brand-800">
                Kembali pilih menu
            </a>
        </div>
    </div>
@endsection
