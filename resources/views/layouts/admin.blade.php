<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Admin {{ $siteName ?? 'Bazar Amal' }}</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none !important}</style>
    @livewireStyles
</head>
<body class="font-sans antialiased bg-stone-100 text-stone-900">
    <div class="min-h-screen lg:flex">
        <aside class="bg-brand-800 text-white lg:w-64 lg:min-h-screen shrink-0">
            <div class="px-5 py-5 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="font-display font-extrabold text-xl tracking-tight flex items-center gap-2">
                    @if (!empty($siteLogoUrl))
                        <img src="{{ $siteLogoUrl }}" alt="" class="h-9 w-9 rounded-lg object-contain bg-white/10">
                    @endif
                    <span class="truncate">{{ $siteName ?? 'Bazar Amal' }}</span>
                </a>
                <p class="text-xs text-white/60 mt-1">Panel Panitia</p>
            </div>
            <nav class="p-3 space-y-1">
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
                       class="block rounded-lg px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs($link['match']) ? 'bg-white/15 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }}">
                        {{ $link['label'] }}
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
    @livewireScripts
</body>
</html>
