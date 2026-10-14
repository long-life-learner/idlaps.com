@extends('layouts.site')
@section('title', 'Berita & Cerita Event — IDLAPS')
@section('description', 'Liputan event dan cerita dari lapangan bersama IDLAPS Timing System. Baca dokumentasi kegiatan dan kolaborasi penyelenggara.')
@section('content')
<section class="page-intro shell"><p class="eyebrow"><span class="line-marker"></span> IDLAPS JOURNAL</p><h1>CATATAN<br><em>LAPANGAN.</em></h1><p class="page-description">Orang-orang, kolaborasi, dan momen di balik setiap event.</p></section>
<section class="shell directory-section"><div class="news-grid">@foreach($articles as $article)<article class="news-card"><a href="{{ $article['article_url'] }}" target="_blank" rel="noopener">@if($article['image'])<img src="{{ $article['image'] }}" alt="Dokumentasi {{ $article['name'] }}" width="850" height="560">@else<div class="journal-placeholder">IDLAPS / EVENT STORY</div>@endif<div><p class="eyebrow">LIPUTAN EVENT / {{ $article['year'] }}</p><h2>{{ $article['article_title'] }}</h2><p>{{ $article['description'] }}</p><span class="text-link">Baca liputan lengkap <x-icon /></span></div></a></article>@endforeach</div><a href="https://idlaps.com/berita" target="_blank" rel="noopener" class="text-link archive-link">Jelajahi arsip berita IDLAPS <x-icon /></a></section>
@include('partials.cta')
@endsection
