@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('nav-active', 'products')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-layanan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/edit-produk.css') }}">
@endpush

@section('content')
    <div class="edit-produk-header">
        <h1 class="edit-produk-title" id="edit-produk-title">Tambah Produk</h1>
        <p class="edit-produk-subtitle" id="edit-produk-subtitle">Lengkapi data produk baru di bawah ini.</p>
    </div>

    @include('produks._form', ['submitLabel' => 'Simpan Produk'])
@endsection