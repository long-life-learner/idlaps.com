@extends('layouts.site')
@section('content')
<section class="hero">
    <div class="hero-media" data-video-hero><video muted loop playsinline preload="none" poster="{{ asset('media/race-poster.jpg') }}" aria-label="Dokumentasi event sepeda Bukit Batu 2023"><source data-src="{{ asset('media/race.mp4') }}" type="video/mp4"></video></div>
    <div class="hero-shade"></div>
    <div class="shell hero-content">
        <p class="eyebrow"><span class="line-marker"></span> YOUR RACE. OUR PRECISION.</p>
        <h1>SETIAP DETIK<br><em>BERARTI.</em><span class="headline-period">↗</span></h1>
        <p class="hero-description">Dari garis start sampai hasil akhir. Race timing yang<br class="desktop-break"> menyesuaikan event Anda, di seluruh Indonesia.</p>
        <div class="button-row"><a href="{{ route('contact') }}" class="button">Konsultasikan event <x-icon /></a><a href="{{ route('results.index') }}" class="button button-glass">Cari hasil lomba <x-icon name="arrow-right" /></a></div>
    </div>
    <div class="hero-side-note">BUILT AROUND YOUR RACE / EST. 2016</div>
    <div class="shell hero-bottom"><a href="#event-terpilih" class="scroll-link"><x-icon name="arrow-down" /> Jelajahi IDLAPS</a><span class="footage-label">ON THE GROUND <span>BUKIT BATU, 2023</span></span><button class="video-toggle" data-video-toggle aria-label="Putar video latar"><span data-video-symbol>▶</span><span data-video-label>Putar video</span></button></div>
</section>
<div class="discipline-strip"><div class="shell"><span>ONE PARTNER. EVERY FORMAT.</span><div><span>ROAD RUNNING</span> <i aria-hidden="true">✳</i> <span>TRAIL RUNNING</span> <i aria-hidden="true">✳</i> <span>CYCLING</span> <i aria-hidden="true">✳</i> <span>TRIATHLON</span> <i aria-hidden="true">✳</i> <span>MULTISPORT</span></div><span class="strip-tail">CUSTOM YOUR RACE <x-icon /></span></div></div>
<section id="event-terpilih" class="section shell events-section">
    <div class="section-heading"><div><p class="eyebrow"><span class="line-marker"></span> OUT ON THE COURSE</p><h2>DI BALIK SETIAP<br><span class="muted">GARIS FINIS.</span></h2></div><div class="heading-aside"><p>Event berbeda. Semangat yang sama.<br>Kenali perjalanan IDLAPS di lapangan.</p><a class="text-link" href="{{ route('events.index') }}">Jelajahi semua event <x-icon /></a></div></div>
    <div class="event-grid">@foreach($events as $event)<x-event-card :event="$event" />@endforeach</div>
</section>
<section id="layanan" class="services-section section">
    <div class="shell"><div class="section-heading"><div><p class="eyebrow"><span class="line-marker"></span> MADE FOR YOUR EVENT</p><h2>FORMAT ANDA.<br><span class="muted">SOLUSI KAMI.</span></h2></div><p class="heading-aside">Setiap event punya tantangannya sendiri.<br>Pilih dukungan yang Anda butuhkan,<br>kami menyesuaikan.</p></div>
    <div class="services-layout"><div class="service-visual"><img src="{{ asset('media/tkg.jpg') }}" alt="Peserta melintasi garis finis TKG Half Marathon 2024" loading="lazy" width="640" height="900"><div class="service-visual-caption"><span>FOCUS ON THE RACE.</span><strong>WE’LL KEEP<br>THE TIME.</strong><small>TKG HALF MARATHON / LAMPUNG</small></div></div><div class="service-list">
    @foreach([
        ['01', 'Race timing', 'clock', 'Pencatatan waktu untuk lomba lari, sepeda, triathlon, dan format event Anda. Kategori dan penyajian hasil disesuaikan dengan kebutuhan lomba.'],
        ['02', 'BIB & chip', 'flag', 'Dukungan identifikasi peserta yang terhubung dengan kebutuhan pencatatan waktu event.'],
        ['03', 'Registrasi peserta', 'users', 'Alur pendaftaran melalui website registrasi khusus, dengan informasi yang sesuai kebutuhan penyelenggara.'],
        ['04', 'Result & e-certificate', 'award', 'Hasil lomba yang mudah dicari, dilengkapi akses sertifikat melalui halaman khusus event.'],
    ] as [$number, $name, $icon, $description])
    <details class="service-item" @if($loop->first) open @endif><summary><span class="service-number">{{ $number }}</span><x-icon :name="$icon" /><h3>{{ $name }}</h3><span class="details-symbol"></span></summary><div class="service-body"><p>{{ $description }}</p><a href="{{ route('contact') }}?layanan={{ urlencode($name) }}" class="text-link">Diskusikan kebutuhan <x-icon /></a></div></details>
    @endforeach
    <div class="custom-note"><span class="asterisk">✳</span><div><strong>Punya format yang berbeda?</strong><p>Komunitas, korporasi, atau kompetisi. Mari cari format yang pas untuk event Anda.</p></div></div></div></div></div>
</section>
<section class="result-banner"><div class="shell result-banner-inner"><div class="result-banner-icon"><x-icon name="clock" /></div><div><p class="eyebrow">YOU DID THE WORK. FIND YOUR TIME.</p><h2>SUDAH FINIS?<br>INI MOMEN ANDA.</h2></div><div><p>Temukan event, cari nama atau nomor BIB,<br>dan lihat hasil perjuangan Anda.</p><a href="{{ route('results.index') }}" class="button button-dark">Temukan hasil lomba <x-icon name="arrow-right" /></a></div><div class="timing-decoration" aria-hidden="true">00:00<span>.00</span></div></div></section>
<section id="tentang" class="section shell about-section"><div class="about-heading"><p class="eyebrow"><span class="line-marker"></span> YOUR TEAM BEHIND THE TIMING</p><h2>DARI KOMUNITAS.<br>UNTUK SETIAP<br><span class="muted">GARIS START.</span></h2><a href="{{ route('contact') }}" class="text-link">Kenali kami lebih dekat <x-icon /></a></div><div class="about-copy"><p class="large-copy">Kami percaya setiap event punya ceritanya sendiri. Tugas kami membantu Anda mencatat setiap momennya.</p><p>Sejak 2016, IDLAPS mendukung penyelenggaraan event olahraga dari jalan raya hingga lintasan pegunungan. Kami bekerja bersama penyelenggara untuk menyesuaikan sistem timing dengan karakter lomba.</p><div class="about-facts"><div><strong>2016</strong><span>Awal perjalanan kami</span></div><div><strong>INDONESIA</strong><span>Jangkauan layanan</span></div></div></div></section>
<section class="section journal-section"><div class="shell"><div class="section-heading"><div><p class="eyebrow"><span class="line-marker"></span> THE STORIES BEHIND THE RACE</p><h2>CATATAN LAPANGAN.</h2></div><a class="text-link" href="{{ route('news.index') }}">Semua berita <x-icon /></a></div><div class="journal-grid">@foreach($articles as $article)<a class="journal-card" href="{{ $article['article_url'] }}" target="_blank" rel="noopener"><img src="{{ $article['image'] }}" alt="Dokumentasi {{ $article['name'] }}" width="540" height="360" loading="lazy"><div><span class="eyebrow">EVENT STORY / {{ $article['year'] }}</span><h3>{{ $article['name'] }}:<br>cerita dari garis start.</h3><span class="text-link">Baca liputan <x-icon /></span></div></a>@endforeach</div></div></section>
@include('partials.cta')
@endsection
