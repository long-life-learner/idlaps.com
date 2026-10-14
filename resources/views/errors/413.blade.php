@extends('layouts.admin')
@section('title', 'Ukuran unggahan terlalu besar')
@section('content')
<section class="panel account-panel"><p class="eyebrow">UNGGAHAN BELUM TERSIMPAN</p><h1>Foto terlalu besar.</h1><p>Total unggahan melebihi batas server. Kembali ke editor, pilih lebih sedikit foto, lalu simpan kembali. Informasi yang belum tersimpan mungkin perlu diisi ulang.</p><a class="button" href="{{ route('admin.events.index') }}">Kembali ke CMS →</a></section>
@endsection
