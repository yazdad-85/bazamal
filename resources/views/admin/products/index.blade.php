@extends('layouts.admin')

@section('title', 'Produk')
@section('heading', 'Manajemen Produk')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <form method="get" class="flex flex-wrap gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk..." class="rounded-xl border-stone-200 text-sm">
            <select name="bazar" class="rounded-xl border-stone-200 text-sm">
                <option value="">Semua Kategori</option>
                <option value="menu" @selected(request('bazar') === 'menu')>Menu Bazar</option>
                <option value="infak" @selected(request('bazar') === 'infak')>Infak & Sedekah</option>
            </select>
            <button class="rounded-xl bg-stone-800 text-white text-sm font-bold px-4 py-2">Filter</button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="inline-flex justify-center rounded-xl bg-brand-700 text-white font-bold px-4 py-2.5 text-sm">+ Tambah Produk</a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-left text-stone-500">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Stok</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover bg-stone-100">
                                <div>
                                    <div class="font-semibold">{{ $product->name }}</div>
                                    <div class="text-xs text-stone-400">{{ $product->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $product->bazar_label }}</td>
                        <td class="px-4 py-3 font-bold">{{ $product->formatted_price }}</td>
                        <td class="px-4 py-3">{{ $product->stock }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-bold {{ $product->is_active ? 'text-emerald-600' : 'text-stone-400' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-brand-700 font-semibold">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
