<div wire:poll.10s class="space-y-4">
    <div class="flex flex-wrap gap-2">
        @if ($lockedInstitution)
            <span class="px-3 py-1.5 rounded-full text-xs font-bold border bg-brand-700 text-white border-brand-700">{{ $institution }}</span>
        @else
        @foreach (array_merge(['semua' => 'Semua'], array_combine($institutions, $institutions), ['Umum' => 'Umum']) as $value => $label)
            <button type="button" wire:click="setInstitution('{{ $value }}')"
                    @class(['px-3 py-1.5 rounded-full text-xs font-bold border transition', $institution === $value ? 'bg-brand-700 text-white border-brand-700' : 'bg-white text-stone-600 border-stone-200 hover:border-brand-400'])>
                {{ $label }}
            </button>
        @endforeach
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari kode / nama..."
               class="rounded-xl border-stone-200 text-sm">
        <select wire:model.live="bazar" class="rounded-xl border-stone-200 text-sm">
            <option value="semua">Semua Kategori</option>
            <option value="menu">Menu Bazar</option>
            <option value="infak">Infak & Sedekah</option>
        </select>
        <select wire:model.live="status" class="rounded-xl border-stone-200 text-sm">
            <option value="semua">Semua Status</option>
            @foreach ($statuses as $s)
                <option value="{{ $s }}">{{ str_replace('_', ' ', ucfirst($s)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500">
                <tr>
                    <th class="px-4 py-3 font-semibold">ID</th>
                    <th class="px-4 py-3 font-semibold">Nama Pembeli</th>
                    <th class="px-4 py-3 font-semibold">Produk</th>
                    <th class="px-4 py-3 font-semibold">Total</th>
                    <th class="px-4 py-3 font-semibold">Bayar</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($orders as $order)
                    <tr class="align-top">
                        <td class="px-4 py-3 font-mono text-xs">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-700 font-bold hover:underline">{{ $order->order_code }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $order->full_name }}</div>
                            <span class="inline-flex mt-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full
                                {{ $order->buyer_type === 'siswa' ? 'bg-orange-100 text-accent-700' : 'bg-stone-100 text-stone-600' }}">
                                {{ $order->buyer_badge }}
                            </span>
                            @if($order->class_name)
                                <div class="text-xs text-stone-500 mt-1">{{ $order->class_name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-stone-600">
                            {{ $order->items->take(2)->pluck('product_name')->implode(', ') }}
                            @if($order->items->count() > 2)
                                <span class="text-stone-400">+{{ $order->items->count() - 2 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold whitespace-nowrap">{{ $order->formatted_subtotal }}</td>
                        <td class="px-4 py-3 text-xs">{{ $order->payment_method_label }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = match($order->status) {
                                    'menunggu_verifikasi' => 'bg-amber-100 text-amber-800',
                                    'diproses' => 'bg-sky-100 text-sky-800',
                                    'siap_diantar' => 'bg-indigo-100 text-indigo-800',
                                    'selesai' => 'bg-emerald-100 text-emerald-800',
                                    'batal' => 'bg-red-100 text-red-700',
                                    default => 'bg-stone-100 text-stone-600',
                                };
                            @endphp
                            <span class="inline-flex text-[11px] font-bold px-2 py-1 rounded-full {{ $badge }}">{{ $order->status_label }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <select wire:change="updateStatus({{ $order->id }}, $event.target.value)"
                                    class="rounded-lg border-stone-200 text-xs py-1.5">
                                @foreach ($statuses as $s)
                                    <option value="{{ $s }}" @selected($order->status === $s)>{{ str_replace('_', ' ', $s) }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-stone-500">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $orders->links() }}</div>
</div>
