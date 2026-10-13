@extends('layouts.site')
@section('title', $event['name'].' — Event IDLAPS')
@section('description', $event['description'])
@section('content')
<section class="event-detail-hero {{ !$event['image'] ? 'event-detail-graphic' : '' }}">
    @if($event['image'])<img class="event-detail-cover" src="{{ $event['image'] }}" alt="Dokumentasi {{ $event['name'] }}">@endif
    <div class="hero-shade"></div><div class="shell event-detail-heading"><a class="back-link" href="{{ route('events.index') }}">← Semua event</a><p class="eyebrow">{{ $event['sport'] }} / {{ $event['year'] }}</p><h1>{{ $event['name'] }}</h1><div class="detail-meta"><span><x-icon name="calendar" /> {{ $event['date'] }}</span><span><x-icon name="pin" /> {{ $event['location'] }}</span></div></div>
</section>
<section class="shell section event-detail-body"><div><p class="eyebrow"><span class="line-marker"></span> EVENT OVERVIEW</p><h2>TENTANG EVENT.</h2><p class="large-copy">{{ $event['description'] }}</p><div class="tag-list">@foreach($event['categories'] as $category)<span>{{ $category }}</span>@endforeach</div>@if($event['article_url'])<a class="text-link" href="{{ $event['article_url'] }}" target="_blank" rel="noopener">Baca liputan lengkap <x-icon /></a>@endif</div><aside class="event-action-panel"><x-icon name="clock" /><h3>Setiap usaha,<br>tercatat.</h3>@if($event['result_url'])<p>Hasil event tersedia di halaman result IDLAPS yang digunakan saat penyelenggaraan.</p><a class="button" href="{{ $event['result_url'] }}" target="_blank" rel="noopener">Lihat hasil lomba <x-icon /></a>@else<p>Tautan hasil event ini belum tersedia dalam katalog.</p><a class="button" href="{{ route('results.index') }}">Cari event lainnya <x-icon /></a>@endif</aside></section>
@include('partials.cta')
@endsection
