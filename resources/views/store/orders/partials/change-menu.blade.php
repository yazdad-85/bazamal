@if ($order->buyerCanChangeMenu())
    <form method="POST" action="{{ route('orders.cancel', ['orderCode' => $order->order_code]) }}"
          class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-left space-y-3"
          onsubmit="return confirm('Batalkan pesanan ini? Stok dikembalikan, lalu Anda bisa memilih menu yang benar.')">
        @csrf
        <p class="text-sm font-bold text-amber-900">Salah pilih menu?</p>
        <p class="text-xs text-amber-800">Selama status masih Menunggu Verifikasi, pesanan bisa dibatalkan. Stok kembali, lalu pesan menu yang benar.</p>
        <button type="submit" class="w-full rounded-xl border border-amber-300 bg-white text-amber-900 font-bold py-3 text-sm">
            Batalkan dan ganti menu
        </button>
    </form>
@endif
