@extends('layouts.admin')
@section('title', 'Masuk')
@section('content')
<div class="login-layout"><div class="login-story"><p class="eyebrow">BEHIND EVERY RACE</p><h1>CERITA EVENT.<br><em>DIMULAI DI SINI.</em></h1><p>Satu tempat untuk foto, informasi event, dan catatan dari lapangan.</p><span class="outline-number" aria-hidden="true">01 / STUDIO</span></div>
<section class="panel login-panel"><p class="eyebrow">KHUSUS ADMINISTRATOR</p><h2>Selamat datang kembali.</h2><p>Masuk untuk mengelola website IDLAPS.</p><form method="POST" action="{{ route('login.store') }}">@csrf
<label>Email<input name="email" type="email" required autocomplete="username" value="{{ old('email') }}" autofocus></label>
<label>Kata sandi<input name="password" type="password" required autocomplete="current-password"></label>
<button class="button wide">Masuk ke studio <span>→</span></button>
<p class="hint">Akun dibuat oleh pengelola website. Hubungi pengelola jika Anda membutuhkan akses.</p></form></section></div>
@endsection
