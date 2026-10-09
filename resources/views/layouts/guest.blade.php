<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $siteName ?? config('app.name', 'Bazar Amal') }} — Login</title>
        @include('partials.favicon')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-brand-800 via-brand-700 to-brand-900">
            <a href="{{ route('home') }}" class="font-display font-extrabold text-2xl text-white tracking-tight flex items-center gap-3">
                @if (!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="" class="h-12 w-12 rounded-xl object-contain bg-white/10 p-1">
                @endif
                {{ $siteName ?? 'Bazar Amal' }}
            </a>
            <p class="text-white/70 text-sm mt-1 mb-4">Login Panitia</p>

            <div class="w-full sm:max-w-md px-6 py-5 bg-white shadow-xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
