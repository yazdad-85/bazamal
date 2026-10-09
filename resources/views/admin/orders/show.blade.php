@extends('layouts.admin')

@section('title', $order->order_code)
@section('heading', 'Detail Pesanan')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="font-mono text-sm text-stone-500">{{ $order->order_code }}</p>
                        <h2 class="font-extrabold text-xl text-brand-800">{{ $order->full_name }}</h2>
                    </div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-amber-100 text-amber-800">{{ $order->status_label }}</span>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-stone-400">Tipe</dt><dd class="font-semibold">{{ $order->buyer_type }} {{ $order->institution ? '· '.$order->institution : '' }}</dd></div>
                    <div><dt class="text-stone-400">Kelas</dt><dd class="font-semibold">{{ $order->class_name ?: '-' }}</dd></div>
                    <div><dt class="text-stone-400">HP</dt><dd class="font-semibold">{{ $order->phone ?: '-' }}</dd></div>
                    <div><dt class="text-stone-400">Ambil</dt><dd class="font-semibold">{{ $order->pickup_method_label }}</dd></div>
                    @if ($order->delivery_address)
                        <div class="sm:col-span-2"><dt class="text-stone-400">Alamat</dt><dd class="font-semibold">{{ $order->delivery_address }}</dd><dd class="text-xs text-stone-400">Tanpa ongkir. Diantar tim panitia.</dd></div>
                    @endif
                    <div><dt class="text-stone-400">Bayar</dt><dd class="font-semibold">{{ $order->payment_method_label }}</dd></div>
                    <div><dt class="text-stone-400">Tanggal</dt><dd class="font-semibold">{{ $order->created_at->format('d M Y H:i') }}</dd></div>
                </dl>
                @if ($order->payment_proof)
                    <div class="mt-4">
                        <p class="text-sm font-semibold mb-2">Bukti Transfer</p>
                        <a href="{{ route('admin.orders.proof', $order) }}" target="_blank" rel="noopener">
                            <img src="{{ route('admin.orders.proof', $order) }}" alt="Bukti" class="max-h-48 rounded-xl border">
                        </a>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-stone-50 text-left text-stone-500">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Qty</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $item->product_name }}</td>
                                <td class="px-4 py-3">{{ $item->qty }}</td>
                                <td class="px-4 py-3">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 font-bold">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-stone-100 flex justify-between font-extrabold text-brand-800">
                    <span>Subtotal</span>
                    <span>{{ $order->formatted_subtotal }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200 p-5 h-fit">
            <h3 class="font-bold mb-3">Ubah Status</h3>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status" class="w-full rounded-xl border-stone-200">
                    @foreach (\App\Models\Order::STATUSES as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
                <button class="w-full rounded-xl bg-brand-700 text-white font-bold py-2.5">Simpan Status</button>
            </form>
            <a href="{{ route('admin.orders.index') }}" class="mt-3 block text-center text-sm font-semibold text-brand-600">← Kembali</a>
        </div>
    </div>
@endsection
