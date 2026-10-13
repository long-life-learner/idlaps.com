<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101210">
    <title>@yield('title', 'IDLAPS — Race Timing untuk Setiap Format Event')</title>
    <meta name="description" content="@yield('description', 'Layanan race timing, BIB dan chip, registrasi, serta hasil lomba untuk event olahraga di seluruh Indonesia. Sesuaikan kebutuhan event Anda bersama IDLAPS.')">
    <meta name="robots" content="{{ config('idlaps.indexable') && !request()->routeIs('results.demo') ? 'index, follow' : 'noindex, nofollow' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'IDLAPS — Setiap Detik Berarti')">
    <meta property="og:description" content="Race timing yang menyesuaikan event Anda. Dari garis start sampai hasil akhir.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('media/bukit-batu.jpg') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a href="#main" class="skip-link">Lewati ke konten</a>
    <div class="utility-bar"><div class="shell utility-inner"><span><i class="status-dot"></i> CUSTOM YOUR RACE</span><span>RACE TIMING PARTNER <b> / </b> INDONESIA</span><a href="{{ route('contact') }}">Let’s talk race <x-icon /></a></div></div>
    <header class="site-header">
        <div class="shell header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="IDLAPS beranda"><img src="{{ asset('media/logo.png') }}" width="130" height="57" alt="IDLAPS — Custom Your Race"></a>
            <nav class="desktop-nav" aria-label="Navigasi utama">
                <a href="{{ route('home') }}#layanan">Layanan</a>
                <a href="{{ route('events.index') }}" @class(['active' => request()->routeIs('events.*')])>Event</a>
                <a href="{{ route('results.index') }}" @class(['active' => request()->routeIs('results.*')])>Hasil lomba</a>
                <a href="{{ route('news.index') }}" @class(['active' => request()->routeIs('news.*')])>Berita</a>
                <a href="{{ route('home') }}#tentang">Tentang kami</a>
            </nav>
            <a class="button button-small header-contact" href="{{ route('contact') }}">Konsultasi event <x-icon /></a>
            <button class="menu-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="mobile-nav"><span></span><span></span></button>
        </div>
        <nav id="mobile-nav" class="mobile-nav" aria-label="Navigasi seluler" hidden>
            <a href="{{ route('home') }}#layanan">Layanan <x-icon /></a><a href="{{ route('events.index') }}">Event <x-icon /></a><a href="{{ route('results.index') }}">Hasil lomba <x-icon /></a><a href="{{ route('news.index') }}">Berita <x-icon /></a><a href="{{ route('home') }}#tentang">Tentang kami <x-icon /></a><a href="{{ route('contact') }}">Konsultasi event <x-icon /></a>
        </nav>
    </header>
    <main id="main">@yield('content')</main>
    <footer class="site-footer">
        <div class="shell footer-top"><a href="{{ route('home') }}" class="brand"><img src="{{ asset('media/logo.png') }}" alt="IDLAPS" width="130" height="57" loading="lazy"></a><p>Your race. Your format.<br><span>Our commitment.</span></p><a href="mailto:{{ config('idlaps.contact_email') }}">{{ config('idlaps.contact_email') }} <x-icon /></a></div>
        <div class="shell footer-bottom"><span>© {{ date('Y') }} IDLAPS Timing System.</span><span>Bukit Batu JPG MTB Trail Park · Indonesia</span><a href="{{ route('results.index') }}">Temukan hasil lomba <x-icon name="arrow-right" /></a></div>
    </footer>
    @stack('scripts')
</body>
</html>
