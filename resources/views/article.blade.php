@extends('layouts.site')
@section('title', e(($record->article_title ?: $record->name).' — IDLAPS Journal'))
@section('description', e(Str::limit($record->description, 160)))
@section('og_image', e($event['image'] ? url($event['image']) : asset('media/bukit-batu.jpg')))
@section('og_type', 'article')
@section('content')
<article class="shell section cms-article"><a class="back-link" href="{{ route('news.index') }}">← Semua berita</a><p class="eyebrow">IDLAPS JOURNAL / {{ $event['sport'] }}</p><h1>{{ $record->article_title ?: $record->name }}</h1><p class="location">{{ $event['date'] }} · {{ $event['location'] }}</p>
@if($event['image'])<img class="article-cover" src="{{ $event['image'] }}" alt="Dokumentasi {{ $event['name'] }}">@endif
<div class="article-copy">@forelse(preg_split('/\R\s*\R/', trim($record->article_body ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $paragraph)<p>{{ $paragraph }}</p>@empty<p>Draf artikel belum dibuat.</p>@endforelse</div>
<a class="button" href="{{ ($preview ?? false) ? route('admin.events.preview', $record) : $event['url'] }}">Lihat event & dokumentasi <x-icon /></a></article>
@include('partials.cta')
@endsection
