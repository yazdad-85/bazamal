<span wire:poll.10s class="inline-flex">
    @if ($count > 0)
        <span class="inline-flex items-center justify-center rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold px-2 py-0.5"
              style="min-width:1.25rem"
              aria-label="{{ $count }} pesanan masuk">{{ $count }}</span>
    @endif
</span>
