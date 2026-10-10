@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="flex flex-wrap gap-2 mb-5">
        @if ($lockedInstitution)
            <span class="px-3 py-1.5 rounded-full text-xs font-bold border bg-brand-700 text-white border-brand-700">{{ $institution }}</span>
        @else
        @foreach (array_merge(['semua' => 'Semua'], array_combine($institutions, $institutions), ['Umum' => 'Umum']) as $value => $label)
            <a href="{{ route('admin.dashboard', ['lembaga' => $value]) }}"
               @class(['px-3 py-1.5 rounded-full text-xs font-bold border', $institution === $value ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-stone-600 border-stone-200'])>
                {{ $label }}
            </a>
        @endforeach
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-stone-200 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Menu Bazar</p>
            <p class="text-3xl font-extrabold text-brand-800 mt-1">{{ $stats['total_menu'] }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-stone-200 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Infak & Sedekah</p>
            <p class="text-3xl font-extrabold text-accent-700 mt-1">{{ $stats['total_infak'] }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-stone-200 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Menunggu Verifikasi</p>
            <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $stats['menunggu'] }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-stone-200 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-400">Omzet (filter)</p>
            <p class="text-2xl font-extrabold text-brand-700 mt-1">Rp {{ number_format($stats['omzet'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-bold text-brand-800">Pesanan Masuk (Terbaru)</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand-600">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-50 text-left text-stone-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-700 font-bold">{{ $order->order_code }}</a>
                            </td>
                            <td class="px-4 py-3">
                                {{ $order->full_name }}
                                <span class="ml-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-orange-100 text-accent-700">{{ $order->buyer_badge }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold">{{ $order->formatted_subtotal }}</td>
                            <td class="px-4 py-3 text-xs font-semibold">{{ $order->status_label }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-stone-500">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
