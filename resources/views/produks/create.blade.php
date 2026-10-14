@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('nav-active', 'products')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-layanan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/edit-produk.css') }}">
@endpush

@section('content')
    <div class="edit-header-wrapper">
        <a href="{{ route('produk.index') }}" class="back-arrow-link" aria-label="Kembali ke Data Produk">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        <div class="edit-title-group">
            <h1 class="edit-produk-title" id="edit-produk-title">Tambah Produk</h1>
            <p class="edit-produk-subtitle" id="edit-produk-subtitle">Lengkapi data produk baru di bawah ini.</p>
        </div>
    </div>

    @include('produks._form', ['submitLabel' => 'Simpan Produk'])
@endsection