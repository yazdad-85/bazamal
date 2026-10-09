<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo')
    @include('partials.favicon')
    @stack('head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-stone-50 text-stone-900 min-h-screen">
    <div class="min-h-screen flex flex-col max-w-lg mx-auto bg-white shadow-xl shadow-stone-200/60 md:max-w-3xl lg:max-w-5xl">
        <header class="sticky top-0 z-30 bg-brand-700 text-white">
            <div class="flex items-center gap-3 px-4 py-3">
                <a href="{{ route('home') }}" class="font-display font-extrabold tracking-tight text-lg shrink-0 flex items-center gap-2 min-w-0">
                    @if (!empty($siteLogoUrl))
                        <img src="{{ $siteLogoUrl }}" alt="{{ $siteName }}" class="h-8 w-8 rounded-lg object-contain bg-white/10">
                    @endif
                    <span class="truncate">{{ $siteName ?? 'Bazar Amal' }}</span>
                </a>
                <form action="{{ route('products.index') }}" method="get" class="flex-1">
                    <input type="hidden" name="bazar" value="{{ request('bazar', 'besar') }}">
                    <input
                        type="search"
                        name="q"
                        placeholder="Pencarian"
                        value="{{ request('q') }}"
                        class="w-full rounded-full border-0 bg-white/15 text-white placeholder:text-white/70 text-sm px-4 py-2 focus:ring-2 focus:ring-white/40"
                    >
                </form>
                <a href="{{ route('orders.index') }}" class="p-2 rounded-full hover:bg-white/10" aria-label="Pesanan Saya" title="Pesanan Saya">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </a>
                <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-white/10" aria-label="Keranjang">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 22a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                    @if(($cartCount ?? 0) > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[1.15rem] h-[1.15rem] px-1 rounded-full bg-accent-500 text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </header>

        @if (session('success'))
            <div class="mx-4 mt-3 rounded-xl bg-brand-50 text-brand-800 text-sm px-4 py-3 border border-brand-100">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mx-4 mt-3 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3 border border-red-100">{{ session('error') }}</div>
        @endif

        <main class="flex-1 pb-8">
            @yield('content')
        </main>

        <footer class="border-t border-stone-100 px-4 py-4 text-center text-xs text-stone-500">
            {{ $siteName ?? 'Bazar Amal' }} · Toko amal untuk siswa dan masyarakat
        </footer>
    </div>
    @livewireScripts
</body>
</html>
