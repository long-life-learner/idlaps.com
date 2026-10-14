<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow"><meta name="theme-color" content="#101210">
    <title>@yield('title', 'Studio') — IDLAPS CMS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg?v=2') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body>
<a class="skip-link" href="#main">Lewati ke konten</a>
<header class="cms-header"><a class="cms-wordmark" href="{{ route('admin.events.index') }}"><span class="cms-logo"><img src="{{ asset('media/idlaps-logo-updated.jpeg') }}" alt="IDLAPS"></span><small>CONTENT STUDIO</small></a>
@auth
<nav aria-label="Navigasi admin"><a href="{{ route('admin.events.index') }}" @class(['active' => request()->routeIs('admin.events.*')])>Event & berita</a><a href="{{ route('admin.account') }}" @class(['active' => request()->routeIs('admin.account')])>Akun</a><a href="{{ route('home') }}" target="_blank" rel="noopener">Lihat website ↗</a></nav>
<form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="text-button">Keluar</button></form>
@else<a href="{{ route('home') }}">← Kembali ke website</a>@endauth
</header>
<main id="main" class="cms-main">
@if(session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="notice error" role="alert"><strong>Periksa kembali isian Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@if($errors->has('photos') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'photos.')))<p>Pilih ulang foto setelah memperbaiki isian.</p>@endif</div>@endif
@yield('content')
</main>
<dialog id="cms-confirm" aria-labelledby="confirm-title"><h2 id="confirm-title">Konfirmasi perubahan</h2><p id="confirm-message"></p><div class="dialog-actions"><button type="button" class="button secondary" data-dialog-cancel>Batal</button><button type="button" class="button" data-dialog-confirm>Lanjutkan</button></div></dialog>
<footer class="cms-footer">IDLAPS / CONTENT STUDIO <span>Custom your race. Tell your story.</span></footer>
</body>
</html>
