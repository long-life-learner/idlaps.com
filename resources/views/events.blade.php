@extends('layouts.site')
@section('title', 'Event & Portofolio Race Timing — IDLAPS')
@section('description', 'Jelajahi dokumentasi event lari dan sepeda yang didukung IDLAPS. Temukan informasi event, liputan, dan hasil lomba.')
@section('content')
<section class="page-intro shell"><p class="eyebrow"><span class="line-marker"></span> THE RACES. THE PEOPLE. THE MOMENTS.</p><div class="section-heading"><h1>SETIAP EVENT.<br><em>CERITA BERBEDA.</em></h1><p class="heading-aside">Dari jalan raya hingga lintasan tanah.<br>Jelajahi event yang menjadi bagian<br>dari perjalanan IDLAPS.</p></div></section>
<section class="shell directory-section"><div data-component="event-directory" data-mode="events"><div class="event-grid">@foreach($events as $event)<x-event-card :event="$event" />@endforeach</div></div></section>
@include('partials.cta')
@endsection
@push('scripts')<script>window.idlapsEvents = {{ Illuminate\Support\Js::from($events->values()) }};</script>@endpush
