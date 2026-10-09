@extends('layouts.store')

@section('title', 'Detail '.$order->order_code)

@section('content')
    <div class="px-4 py-5 space-y-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-brand-700">← Pesanan Saya</a>
                <h1 class="font-display font-extrabold text-xl text-brand-800 mt-2">Detail Pesanan</h1>
                <p class="font-mono text-sm text-stone-500 mt-1">{{ $order->order_code }}</p>
            </div>
            <span class="inline-flex text-xs font-bold px-3 py-1.5 rounded-full bg-amber-100 text-amber-800">
                {{ $order->status_label }}
            </span>
        </div>

        @if ($order->status !== 'batal')
            <div class="rounded-2xl border border-stone-100 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-stone-400 mb-3">Progress Status</p>
                <ol class="space-y-2">
                    @foreach ($statusSteps as $key => $label)
                        @php
                            $keys = array_keys($statusSteps);
                            $current = array_search($order->status, $keys, true);
                            $step = array_search($key, $keys, true);
                            $done = $current !== false && $step !== false && $step <= $current;
                        @endphp
                        <li class="flex items-center gap-2 text-sm {{ $done ? 'text-brand-700 font-semibold' : 'text-stone-400' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[11px] {{ $done ? 'bg-brand-600 text-white' : 'bg-stone-200' }}">
                                {{ $done ? '✓' : ($step + 1) }}
                            </span>
                            {{ $label }}
                        </li>
                    @endforeach
                </ol>
            </div>
        @else
            <div class="rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3 border border-red-100">
                Pesanan ini dibatalkan. Hubungi panitia jika ada pertanyaan.
            </div>
        @endif

        <div class="rounded-2xl border border-stone-100 p-4 space-y-2 text-sm">
            <p><span class="text-stone-500">Nama:</span> <strong>{{ $order->full_name }}</strong></p>
            <p><span class="text-stone-500">HP:</span> {{ $order->phone ?: '-' }}</p>
            <p><span class="text-stone-500">Tipe:</span>
                {{ $order->buyer_type === 'siswa' ? 'Siswa' : 'Umum' }}
                @if($order->institution) · {{ $order->institution }} {{ $order->class_name }} @endif
            </p>
            <p><span class="text-stone-500">Ambil:</span> {{ $order->pickup_method_label }}</p>
            @if ($order->delivery_address)
                <p><span class="text-stone-500">Alamat:</span> {{ $order->delivery_address }}</p>
                <p class="text-xs text-stone-400">Tanpa ongkir. Diantar tim panitia.</p>
            @endif
            <p><span class="text-stone-500">Bayar:</span> {{ $order->payment_method_label }}</p>
            @if ($qrisUrl)
                <div class="pt-2 text-center">
                    <p class="text-sm font-bold text-brand-800 mb-2">QRIS pembayaran</p>
                    <img src="{{ $qrisUrl }}" alt="QRIS panitia" class="mx-auto max-h-64 rounded-xl border bg-white p-2">
                    <p class="text-xs text-stone-400 mt-2">Bayar sebesar {{ $order->formatted_subtotal }} jika belum dibayar. Panitia mencocokkan pembayaran secara manual.</p>
                </div>
            @endif
            <p><span class="text-stone-500">Waktu:</span> {{ $order->created_at->format('d M Y H:i') }}</p>
        </div>

        <div class="rounded-2xl border border-stone-100 overflow-hidden">
            <div class="px-4 py-3 bg-stone-50 font-bold text-brand-800 text-sm">Barang yang dipesan</div>
            <ul class="divide-y divide-stone-100">
                @foreach ($order->items as $item)
                    <li class="px-4 py-3 flex justify-between gap-3 text-sm">
                        <div>
                            <p class="font-semibold">{{ $item->product_name }}</p>
                            <p class="text-stone-500">{{ $item->qty }} × Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                        </div>
                        <p class="font-bold whitespace-nowrap">Rp {{ number_format($item->line_total, 0, ',', '.') }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="px-4 py-3 border-t border-stone-100 flex justify-between font-extrabold text-brand-800">
                <span>Total</span>
                <span>{{ $order->formatted_subtotal }}</span>
            </div>
        </div>

        <div class="space-y-2">
            @foreach ($whatsappLinks as $link)
                <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"
                   class="inline-flex w-full justify-center rounded-xl bg-[#25D366] hover:bg-[#1ebe57] text-white font-bold py-3 text-sm">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endsection
