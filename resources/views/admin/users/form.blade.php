@extends('layouts.admin')

@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('heading', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
    <form method="POST"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
          class="max-w-xl bg-white rounded-2xl border border-stone-200 p-5 space-y-4"
          x-data="{ role: '{{ old('role', $user->role) }}' }">
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
            <label class="block text-sm font-semibold mb-1">Peran</label>
            <select name="role" x-model="role" class="w-full rounded-xl border-stone-200">
                <option value="inti">Panitia Inti</option>
                <option value="koordinator">Koordinator Lembaga</option>
            </select>
            @error('role') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div x-show="role === 'koordinator'" x-cloak>
            <label class="block text-sm font-semibold mb-1">Lembaga</label>
            <select name="institution" class="w-full rounded-xl border-stone-200">
                @foreach ($institutions as $institution)
                    <option value="{{ $institution }}" @selected(old('institution', $user->institution) === $institution)>{{ $institution }}</option>
                @endforeach
            </select>
            @error('institution') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-stone-400 mt-1">Koordinator hanya melihat pesanan siswa dari lembaga ini.</p>
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
