<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-stone-300 text-brand-600 shadow-sm focus:ring-brand-500" name="remember">
                <span class="ms-2 text-sm text-stone-600">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-brand-700">← Toko</a>
            <button type="submit" class="rounded-xl bg-brand-700 hover:bg-brand-600 text-white font-bold px-5 py-2.5 text-sm">
                Masuk
            </button>
        </div>
    </form>
</x-guest-layout>
