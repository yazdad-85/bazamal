@props(['product'])

<article class="rounded-2xl border border-stone-100 bg-white overflow-hidden shadow-sm hover:shadow-md transition">
    <a href="{{ route('products.show', $product) }}" class="block aspect-square bg-stone-100 overflow-hidden">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
    </a>
    <div class="p-3 space-y-2">
        <span @class([
            'inline-flex text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full',
            'bg-brand-100 text-brand-700' => $product->bazar_type === 'besar',
            'bg-orange-100 text-accent-700' => $product->bazar_type === 'kecil',
        ])>
            {{ $product->bazar_label }}
        </span>
        <a href="{{ route('products.show', $product) }}" class="block font-semibold text-sm leading-snug line-clamp-2 hover:text-brand-700">
            {{ $product->name }}
        </a>
        <div class="flex items-center justify-between gap-2">
            <p class="font-extrabold text-brand-700 text-sm">{{ $product->formatted_price }}</p>
            <form action="{{ route('cart.store') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" @disabled($product->stock < 1)
                    class="rounded-full bg-brand-600 hover:bg-brand-700 disabled:opacity-40 text-white text-xs font-bold px-3 py-1.5">
                    Tambah +
                </button>
            </form>
        </div>
    </div>
</article>
