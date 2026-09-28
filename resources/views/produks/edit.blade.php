@extends('layouts.app')

@section('title', 'Edit Produk')

@section('nav-active', 'products')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-layanan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/edit-produk.css') }}">
@endpush

@section('content')
    <div class="edit-produk-header">
        <h1 class="edit-produk-title" id="edit-produk-title">Edit Produk</h1>
        <p class="edit-produk-subtitle" id="edit-produk-subtitle">Perbarui data "{{ $produk->nama_produk }}" beserta tarifnya.</p>
    </div>

    @include('produks._form', ['submitLabel' => 'Simpan Perubahan'])
@endsection