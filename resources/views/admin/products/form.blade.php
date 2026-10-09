@extends('layouts.admin')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    <form method="POST"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          enctype="multipart/form-data"
          class="max-w-2xl bg-white rounded-2xl border border-stone-200 p-5 space-y-4">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-semibold mb-1">Nama Produk</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full rounded-xl border-stone-200">
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-stone-200">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-semibold mb-1">Harga</label>
                <input type="number" name="price" min="0" value="{{ old('price', $product->price) }}" required class="w-full rounded-xl border-stone-200">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Stok</label>
                <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock) }}" required class="w-full rounded-xl border-stone-200">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Kategori Bazar</label>
                <select name="bazar_type" class="w-full rounded-xl border-stone-200">
                    <option value="besar" @selected(old('bazar_type', $product->bazar_type) === 'besar')>Bazar Besar</option>
                    <option value="kecil" @selected(old('bazar_type', $product->bazar_type) === 'kecil')>Bazar Kecil</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Gambar</label>
            @if ($product->image)
                <img src="{{ $product->image_url }}" alt="" class="w-24 h-24 rounded-xl object-cover mb-2">
            @endif
            <input type="file" name="image" accept="image/*" class="block w-full text-sm">
            @error('image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="inline-flex items-center gap-2 text-sm font-semibold">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="rounded border-stone-300 text-brand-600">
            Produk aktif
        </label>

        <div class="flex gap-2 pt-2">
            <button class="rounded-xl bg-brand-700 text-white font-bold px-5 py-2.5">Simpan</button>
            <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-stone-200 font-bold px-5 py-2.5">Batal</a>
        </div>
    </form>
@endsection
