@php
    $site = $siteName ?? config('app.name', 'Bazar Amal');
    $page = trim($__env->yieldContent('title'));
    $seoTitle = trim($__env->yieldContent('seo_title'));
    if ($seoTitle === '') {
        $seoTitle = $page !== '' ? $page.' — '.$site : $site;
    }
    $description = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('meta_description'))) ?? '');
    if ($description === '') {
        try {
            $description = \App\Models\Setting::metaDescription();
        } catch (\Throwable) {
            $description = 'Toko bazar amal untuk siswa dan masyarakat. Setiap pembelian mendukung kegiatan amal pendidikan.';
        }
    }
    $description = \Illuminate\Support\Str::limit($description, 180, '');
    $robots = trim($__env->yieldContent('robots')) ?: 'index, follow';
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $image = trim($__env->yieldContent('og_image'));
    if ($image === '') {
        $image = $siteLogoUrl ?? '';
    }
    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
@endphp
<title>{{ $seoTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:locale" content="id_ID">
<meta property="og:site_name" content="{{ $site }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($image !== '')
    <meta property="og:image" content="{{ $image }}">
    <meta name="twitter:image" content="{{ $image }}">
    <meta name="twitter:card" content="summary_large_image">
@else
    <meta name="twitter:card" content="summary">
@endif
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $description }}">
