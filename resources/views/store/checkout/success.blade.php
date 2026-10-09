@extends('layouts.store')

@section('title', 'Pesanan Berhasil')

@section('content')
    <div class="px-4 py-8 text-center max-w-md mx-auto">
        <div class="w-16 h-16 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-3xl mx-auto mb-4">✓</div>
        <h1 class="font-display font-extrabold text-2xl text-brand-800">Pesanan Tersimpan</h1>
        <p class="text-sm text-stone-600 mt-2">Pesanan Anda sudah tercatat di sistem dan tidak akan hilang.</p>

        <div class="mt-4 rounded-2xl bg-brand-50 border border-brand-100 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-brand-600">Simpan kode ini</p>
            <p class="font-mono text-xl font-extrabold text-brand-900 mt-1 select-all">{{ $order->order_code }}</p>
            <p class="text-xs text-stone-500 mt-2">Status saat ini: <strong>{{ $order->status_label }}</strong> · {{ $order->formatted_subtotal }}</p>
        </div>

        <div class="mt-6 space-y-3 text-left rounded-2xl border border-stone-100 p-4">
            <p class="text-sm"><span class="text-stone-500">Nama:</span> <strong>{{ $order->full_name }}</strong></p>
            <p class="text-sm"><span class="text-stone-500">HP:</span> {{ $order->phone ?: '-' }}</p>
            <p class="text-sm"><span class="text-stone-500">Tipe:</span> {{ $order->buyer_type === 'siswa' ? 'Siswa' : 'Umum' }}
                @if($order->institution) ({{ $order->institution }} {{ $order->class_name }}) @endif
            </p>
            <p class="text-sm"><span class="text-stone-500">Ambil:</span> {{ $order->pickup_method_label }}</p>
            @if ($order->delivery_address)
                <p class="text-sm"><span class="text-stone-500">Alamat:</span> {{ $order->delivery_address }}</p>
                <p class="text-xs text-stone-400">Tanpa ongkir. Diantar tim panitia.</p>
            @endif
            <p class="text-sm"><span class="text-stone-500">Bayar:</span> {{ $order->payment_method_label }}</p>
        </div>

        @if ($qrisUrl)
            <div class="mt-4 rounded-2xl border border-stone-100 p-4 text-center">
                <p class="text-sm font-bold text-brand-800">Scan QRIS untuk membayar</p>
                <img src="{{ $qrisUrl }}" alt="QRIS panitia" class="mx-auto mt-3 max-h-64 rounded-xl border bg-white p-2">
                <p class="text-xs text-stone-500 mt-2">Bayar sebesar {{ $order->formatted_subtotal }}. Cantumkan kode pesanan di catatan transfer jika aplikasi bank menyediakan kolom itu. Panitia akan mencocokkan pembayaran.</p>
            </div>
        @endif

        <div class="mt-4 text-left rounded-2xl border border-stone-100 overflow-hidden">
            <div class="px-4 py-2 bg-stone-50 text-sm font-bold text-brand-800">Item pesanan</div>
            <ul class="divide-y divide-stone-100">
                @foreach ($order->items as $item)
                    <li class="px-4 py-2.5 flex justify-between text-sm gap-2">
                        <span>{{ $item->product_name }} × {{ $item->qty }}</span>
                        <span class="font-semibold">Rp {{ number_format($item->line_total, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <a href="{{ route('orders.show', ['orderCode' => $order->order_code]) }}"
           class="mt-6 inline-flex w-full justify-center rounded-xl bg-brand-700 hover:bg-brand-600 text-white font-bold py-3.5">
            Pantau Status Pesanan
        </a>

        <div class="mt-3 space-y-2">
            @foreach ($whatsappLinks as $link)
                <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" referrerpolicy="no-referrer"
                   class="inline-flex w-full justify-center rounded-xl bg-[#25D366] hover:bg-[#1ebe57] text-white font-bold py-3 text-sm">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <a href="{{ route('home') }}" class="mt-3 inline-flex w-full justify-center rounded-xl border border-stone-200 font-bold py-3 text-brand-800">
            Kembali ke Beranda
        </a>
    </div>
@endsection
