@extends('layouts.admin')
@section('title', 'Akun admin')
@section('content')
<div class="page-heading"><div><p class="eyebrow">PENGATURAN AKUN</p><h1>Akses studio.</h1><p>{{ auth()->user()->email }}</p></div></div>
<section class="panel account-panel"><h2>Ganti kata sandi</h2><p>Gunakan minimal 12 karakter yang berisi huruf dan angka.</p><form method="POST" action="{{ route('admin.password') }}">@csrf @method('PUT')
<label>Kata sandi saat ini<input name="current_password" type="password" required autocomplete="current-password"></label>
<label>Kata sandi baru<input name="password" type="password" required minlength="12" autocomplete="new-password"></label>
<label>Ulangi kata sandi baru<input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"></label>
<button class="button">Simpan kata sandi →</button></form></section>
@endsection
