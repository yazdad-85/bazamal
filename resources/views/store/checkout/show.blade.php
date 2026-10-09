@extends('layouts.store')

@section('title', 'Checkout')

@section('content')
    <div class="px-4 py-5">
        <h1 class="font-display font-extrabold text-xl text-brand-800 mb-4">Checkout</h1>

        <div class="rounded-2xl border border-stone-100 p-4 mb-5 space-y-2">
            @foreach ($items as $item)
                <div class="flex justify-between text-sm gap-3">
                    <span class="text-stone-700">{{ $item['name'] }} × {{ $item['qty'] }}</span>
                    <span class="font-semibold">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                </div>
            @endforeach
            <div class="border-t border-stone-100 pt-2 flex justify-between font-extrabold text-brand-800">
                <span>Subtotal</span>
                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
        </div>

        <livewire:checkout-form />
    </div>
@endsection
