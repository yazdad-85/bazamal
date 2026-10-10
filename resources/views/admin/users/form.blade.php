@extends('layouts.admin')

@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('heading', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
    <form method="POST"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="max-w-xl bg-white rounded-2xl border border-stone-200 p-5 space-y-4">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-semibold mb-1">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border-stone-200">
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border-stone-200">
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input type="password" name="password" {{ $user->exists ? '' : 'required' }} class="w-full rounded-xl border-stone-200" autocomplete="new-password">
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">Ulangi Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-xl border-stone-200" autocomplete="new-password">
        </div>

        <div class="flex items-center gap-3">
            <button class="rounded-xl bg-brand-700 text-white font-bold px-5 py-2.5">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-stone-500">Batal</a>
        </div>
    </form>
@endsection
