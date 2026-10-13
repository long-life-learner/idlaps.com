@extends('layouts.site')
@section('title', 'Demo Pencarian Hasil Lomba — IDLAPS')
@section('description', 'Pratinjau interaktif halaman hasil lomba IDLAPS menggunakan data simulasi. Bukan hasil dari event resmi.')
@section('content')
<div class="demo-notice"><div class="shell"><span class="demo-tag">DEMO</span> Data simulasi untuk pratinjau desain. Bukan hasil lomba resmi.</div></div>
<section class="shell result-intro"><a class="back-link" href="{{ route('results.index') }}">← Semua hasil lomba</a><div class="result-title-row"><div><p class="eyebrow"><span class="line-marker"></span> IDLAPS RESULT EXPERIENCE</p><h1>YOUR RACE.<br><em>YOUR MOMENT.</em></h1></div><div class="event-mini-brand"><x-icon name="flag" /><span>IDLAPS<br><strong>DEMO RUN</strong></span></div></div><p class="page-description">Cari nama atau nomor BIB untuk menemukan catatan waktu Anda.</p></section>
<section class="shell result-workspace" data-component="race-results"><noscript><p>Pencarian demo membutuhkan JavaScript. Anda tetap bisa membuka <a href="{{ route('results.index') }}">direktori hasil resmi</a>.</p></noscript></section>
@endsection
