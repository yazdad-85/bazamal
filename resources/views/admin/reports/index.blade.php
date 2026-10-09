@extends('layouts.admin')

@section('title', 'Laporan')
@section('heading', 'Laporan Transaksi')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex flex-wrap gap-2">
            @if ($lockedInstitution)
                <span class="px-3 py-1.5 rounded-full text-xs font-bold border bg-brand-700 text-white border-brand-700">{{ $institution }}</span>
            @else
            @foreach (array_merge(['semua' => 'Semua'], array_combine($institutions, $institutions), ['Umum' => 'Umum']) as $value => $label)
                <a href="{{ route('admin.reports.index', ['lembaga' => $value]) }}"
                   @class(['px-3 py-1.5 rounded-full text-xs font-bold border', $institution === $value ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-stone-600 border-stone-200'])>
                    {{ $label }}
                </a>
            @endforeach
            @endif
        </div>
        <a href="{{ route('admin.reports.export', ['lembaga' => $institution]) }}"
           class="inline-flex justify-center rounded-xl bg-accent-600 text-white font-bold px-4 py-2.5 text-sm">
            Export CSV
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Lembaga</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $order->order_code }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $order->full_name }}</td>
                        <td class="px-4 py-3">{{ $order->buyer_badge }}</td>
                        <td class="px-4 py-3 font-bold">{{ $order->formatted_subtotal }}</td>
                        <td class="px-4 py-3 text-xs">{{ $order->status_label }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-stone-500">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
