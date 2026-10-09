@extends('layouts.store')

@section('title', 'Pesanan Saya')

@section('content')
    <div class="px-4 py-5 space-y-6">
        <div>
            <h1 class="font-display font-extrabold text-xl text-brand-800">Pesanan Saya</h1>
            <p class="text-sm text-stone-500 mt-1">Pesanan tersimpan di sistem, tetapi detailnya hanya terbuka di perangkat yang memesan, atau setelah kode dan nama lengkap cocok.</p>
        </div>

        <form method="POST" action="{{ route('orders.lookup') }}" class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
            @csrf
            <h2 class="font-bold text-brand-800 text-sm">Lacak Pesanan</h2>
            <div>
                <label class="block text-sm font-semibold mb-1">Kode Pesanan</label>
                <input type="text" name="order_code" value="{{ old('order_code') }}" required
                       placeholder="BA-xxxxxx-xxxxx" class="w-full rounded-xl border-stone-200 uppercase">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Nama Lengkap (saat checkout)</label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required
                       placeholder="Sesuai nama di pesanan" class="w-full rounded-xl border-stone-200">
            </div>
            <button class="w-full rounded-xl bg-brand-700 hover:bg-brand-600 text-white font-bold py-3">
                Lihat Status Pesanan
            </button>
        </form>

        @if ($orders->isNotEmpty())
            <section>
                <h2 class="font-bold text-brand-800 mb-3">Riwayat di perangkat ini</h2>
                <div class="space-y-3">
                    @foreach ($orders as $order)
                        <a href="{{ route('orders.show', ['orderCode' => $order->order_code]) }}"
                           class="block rounded-2xl border border-stone-100 p-4 hover:border-brand-300 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-mono text-xs text-stone-500">{{ $order->order_code }}</p>
                                    <p class="font-semibold mt-0.5">{{ $order->full_name }}</p>
                                    <p class="text-xs text-stone-500 mt-1">
                                        {{ $order->items->count() }} item · {{ $order->created_at->format('d M Y H:i') }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-extrabold text-brand-700 text-sm">{{ $order->formatted_subtotal }}</p>
                                    <span class="inline-flex mt-1 text-[11px] font-bold px-2 py-1 rounded-full bg-brand-50 text-brand-700">
                                        {{ $order->status_label }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @else
            <p class="text-sm text-stone-500 text-center py-4">Belum ada riwayat pesanan di perangkat ini. Gunakan form lacak di atas.</p>
        @endif
    </div>
@endsection
