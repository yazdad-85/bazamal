<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#145214">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $siteName ?? 'Bazar Amal' }}">
@if (!empty($siteLogoUrl))
    <link rel="icon" href="{{ $siteLogoUrl }}">
    <link rel="shortcut icon" href="{{ $siteLogoUrl }}">
    <link rel="apple-touch-icon" href="{{ $siteLogoUrl }}">
@else
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
@endif
