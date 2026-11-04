@if ($order->payment_proof)
    <button type="button" data-proof-url="{{ route('admin.orders.proof', $order) }}" data-proof-title="{{ $order->order_code }}"
            class="inline-flex items-center gap-1 whitespace-nowrap rounded-lg border border-brand-100 bg-brand-50 px-2.5 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-100">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Lihat Bukti
    </button>
@else
    <span class="text-xs text-stone-400">Tanpa bukti</span>
@endif
