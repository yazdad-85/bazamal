<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Admin {{ $siteName ?? 'Bazar Amal' }}</title>
    <meta name="robots" content="noindex, nofollow">
    @include('partials.favicon', ['manifestUrl' => route('admin.manifest', absolute: false)])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important}</style>
    @livewireStyles
</head>
<body class="font-sans antialiased bg-stone-100 text-stone-900">
    <div class="min-h-screen lg:flex">
        <aside class="sticky top-0 z-30 bg-brand-800 text-white lg:static lg:w-64 lg:min-h-screen shrink-0">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-white/10 lg:block lg:px-5 lg:py-5">
                <div class="min-w-0">
                    <a href="{{ route('admin.dashboard') }}" class="font-display font-extrabold text-xl tracking-tight flex items-center gap-2">
                        @if (!empty($siteLogoUrl))
                            <img src="{{ $siteLogoUrl }}" alt="" class="h-9 w-9 rounded-lg object-contain bg-white/10">
                        @endif
                        <span class="truncate">{{ $siteName ?? 'Bazar Amal' }}</span>
                    </a>
                    <p class="text-xs text-white/60 mt-1">Panel Panitia</p>
                </div>
                <button id="admin-menu-button" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="admin-navigation" class="shrink-0 rounded-lg p-2 hover:bg-white/10 lg:hidden">
                    <svg id="admin-menu-open-icon" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg id="admin-menu-close-icon" class="hidden h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav id="admin-navigation" class="hidden max-h-[calc(100vh-5rem)] overflow-y-auto p-3 space-y-1 lg:block lg:max-h-none lg:overflow-visible">
                @php
                    $links = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'match' => 'admin.dashboard'],
                        ['route' => 'admin.orders.index', 'label' => 'Pesanan', 'match' => 'admin.orders.*'],
                        ['route' => 'admin.reports.index', 'label' => 'Laporan', 'match' => 'admin.reports.*'],
                    ];
                    if (auth()->user()?->isInti()) {
                        $links[] = ['route' => 'admin.products.index', 'label' => 'Produk', 'match' => 'admin.products.*'];
                        $links[] = ['route' => 'admin.users.index', 'label' => 'Pengguna', 'match' => 'admin.users.*'];
                        $links[] = ['route' => 'admin.settings.edit', 'label' => 'Pengaturan', 'match' => 'admin.settings.*'];
                    }
                @endphp
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs($link['match']) ? 'bg-white/15 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        <span>{{ $link['label'] }}</span>
                        @if ($link['route'] === 'admin.orders.index')
                            <livewire:admin.incoming-order-badge />
                        @endif
                    </a>
                @endforeach
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2.5 text-sm text-white/60 hover:bg-white/10">Lihat Toko</a>
                <form method="POST" action="{{ route('logout') }}" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full text-left rounded-lg px-3 py-2.5 text-sm text-white/60 hover:bg-white/10">Keluar</button>
                </form>
            </nav>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="bg-white border-b border-stone-200 px-4 sm:px-6 py-4 flex items-center justify-between gap-3">
                <h1 class="font-display font-bold text-lg text-brand-800">@yield('heading', 'Dashboard')</h1>
                <div class="text-right">
                    <div class="text-sm font-semibold text-stone-700">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-stone-400">{{ auth()->user()->role_label }}</div>
                </div>
            </header>

            <main class="p-4 sm:p-6">
                @if (session('success'))
                    <div class="mb-4 rounded-xl bg-brand-50 text-brand-800 text-sm px-4 py-3 border border-brand-100">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-xl bg-red-50 text-red-700 text-sm px-4 py-3 border border-red-100">{{ session('error') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @include('partials.pwa-controls')
    @livewireScripts
</body>
</html>
