@extends('layouts.app')

@section('title', 'Tambah Layanan')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900">Tambah Layanan</h1>
    <p class="mt-1 text-sm text-gray-500">Lengkapi layanan beserta varian harga &amp; komisinya.</p>

    <div class="mt-6 max-w-5xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('layanan.store') }}" method="POST">
            @csrf
            @include('layanans._form', ['submitLabel' => 'Simpan Layanan'])
        </form>
    </div>
@endsection
