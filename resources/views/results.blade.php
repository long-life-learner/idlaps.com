@extends('layouts.site')
@section('title', 'Cari Hasil Lomba — IDLAPS Timing System')
@section('description', 'Temukan hasil lomba event IDLAPS. Pilih event Anda dan akses hasil resmi, kategori, serta catatan waktu peserta.')
@section('content')
<section class="page-intro shell"><p class="eyebrow"><span class="line-marker"></span> THE FINISH LINE IS JUST THE BEGINNING.</p><div class="section-heading"><h1>USAHA ANDA.<br><em>CATATAN WAKTU ANDA.</em></h1><p class="heading-aside">Pilih event untuk melihat hasil lomba.<br>Setiap garis finis punya cerita.<br>Temukan milik Anda.</p></div></section>
<section class="shell directory-section"><div data-component="event-directory" data-mode="results"><div class="fallback-results">@foreach($events as $event)<a href="{{ $event['result_url'] }}" target="_blank" rel="noopener">{{ $event['name'] }} <x-icon /></a>@endforeach</div></div><div class="demo-invitation"><div><span class="eyebrow">PREVIEW PENGALAMAN BARU</span><h3>Coba pencarian hasil IDLAPS.</h3><p>Lihat rancangan result baru menggunakan data simulasi.</p></div><a class="button button-outline" href="{{ route('results.demo') }}">Buka demo result <x-icon /></a></div></section>
@endsection
@push('scripts')<script>window.idlapsEvents = {{ Illuminate\Support\Js::from($events->values()) }};</script>@endpush
