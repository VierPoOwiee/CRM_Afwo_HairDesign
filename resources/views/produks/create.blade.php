@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900">Tambah Produk</h1>
    <p class="mt-1 text-sm text-gray-500">Lengkapi data produk baru di bawah ini.</p>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('produk.store') }}" method="POST">
            @csrf
            @include('produks._form', ['submitLabel' => 'Simpan Produk'])
        </form>
    </div>
@endsection
