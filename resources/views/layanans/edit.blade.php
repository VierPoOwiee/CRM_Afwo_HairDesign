@extends('layouts.app')

@section('title', 'Edit Layanan')

@section('nav-active', 'services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-layanan.css') }}">
@endpush

@section('content')
    <div class="edit-layanan-header">
        <h1 class="edit-layanan-title" id="edit-layanan-title">Edit Layanan</h1>
        <p class="edit-layanan-subtitle" id="edit-layanan-subtitle">Perbarui "{{ $layanan->nama_layanan }}" beserta varian harga &amp; komisinya.</p>
    </div>

    @include('layanans._form', [
        'submitLabel' => 'Simpan Perubahan',
        'hargaRows' => $layanan->hargaLayanan,
    ])
@endsection