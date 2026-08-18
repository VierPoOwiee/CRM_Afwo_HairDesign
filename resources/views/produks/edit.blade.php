@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
    <div class="flex items-center gap-4">
        <a href="{{ route('produk.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Produk</h1>
            <p class="mt-1 text-sm text-gray-500">Perbarui data {{ $produk->nama_produk }}.</p>
        </div>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('produk.update', $produk) }}" method="POST">
            @csrf
            @method('PUT')
            @include('produks._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
