@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('nav-active', Auth::user()->isOwner() ? 'history' : 'transaksi-baru')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/add-transaction.css') }}">
@endpush

@section('content')
    @include('transaksis._form', [
        'pageTitle' => 'Tambah Transaksi',
        'pageSubtitle' => 'Catat kunjungan layanan / penjualan produk pelanggan.',
        'formAction' => route('transaksi.store'),
        'isEdit' => false,
        'editData' => null,
    ])
@endsection