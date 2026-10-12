@extends('layouts.store')

@section('title', 'Checkout')
@section('robots', 'noindex, nofollow')

@section('content')
    <div class="px-4 py-5">
        <h1 class="font-display font-extrabold text-xl text-brand-800 mb-4">Checkout</h1>

        <div class="rounded-2xl border border-stone-100 p-4 mb-5 space-y-3">
            @foreach ($items as $item)
                @php
                    $photo = ! empty($item['image'])
                        ? asset('storage/'.$item['image'])
                        : 'https://placehold.co/160x160/e8f5e9/1b5e20?text='.urlencode($item['name']);
                @endphp
                <div class="flex items-center gap-3">
                    <img src="{{ $photo }}" alt="{{ $item['name'] }}" class="w-20 h-20 rounded-xl object-cover bg-stone-100 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-stone-800">{{ $item['name'] }}</p>
                        <p class="text-xs text-stone-500 mt-0.5">{{ $item['qty'] }} × Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                    </div>
                    <span class="font-semibold text-sm whitespace-nowrap">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
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
