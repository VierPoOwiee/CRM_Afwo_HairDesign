@extends('layouts.app')

@section('title', 'Edit Transaksi')

@section('nav-active', 'history')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/add-transaction.css') }}">
@endpush

@section('content')
    @include('transaksis._form', [
        'pageTitle' => 'Edit Transaksi',
        'pageSubtitle' => 'Perbarui '.$transaksi->no_struk.' — kunjungan layanan / penjualan produk pelanggan.',
        'formAction' => route('transaksi.update', $transaksi),
        'isEdit' => true,
        'transaksi' => $transaksi,
        'editData' => $editData,
        'karyawans' => $karyawans,
        'produks' => $produks,
    ])
@endsection
