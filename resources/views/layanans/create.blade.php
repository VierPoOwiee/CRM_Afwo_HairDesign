@extends('layouts.app')

@section('title', 'Tambah Layanan')

@section('nav-active', 'services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-layanan.css') }}">
@endpush

@section('content')
    <div class="edit-layanan-header">
        <h1 class="edit-layanan-title" id="edit-layanan-title">Tambah Layanan</h1>
        <p class="edit-layanan-subtitle" id="edit-layanan-subtitle">Lengkapi layanan beserta varian harga &amp; komisinya.</p>
    </div>

    @include('layanans._form', ['submitLabel' => 'Simpan Layanan'])
@endsection