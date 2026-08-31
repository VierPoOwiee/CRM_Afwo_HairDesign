@extends('layouts.app')

@section('title', $produk->nama_produk)

@section('content')
    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('produk.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $produk->nama_produk }}</h1>
            <p class="mt-1 text-sm text-gray-500">Detail informasi produk.</p>
        </div>
        <div class="ml-auto flex items-center gap-4 text-sm">
            <a href="{{ route('produk.edit', $produk) }}" class="font-medium text-accent-text hover:text-accent">
                Edit
            </a>
            <form action="{{ route('produk.destroy', $produk) }}" method="POST"
                  onsubmit="return confirm('Hapus produk &quot;{{ addslashes($produk->nama_produk) }}&quot;?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="font-medium text-red-600 hover:text-red-800">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-card p-6 shadow-sm">
        <dl class="space-y-3 text-sm">
            @if ($produk->kategori_produk !== 'dijual')
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Merek</dt>
                <dd class="text-right text-gray-900">{{ $produk->merek }}</dd>
            </div>
            @endif
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Kategori</dt>
                <dd class="text-right text-gray-900">
                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ $produk->labelKategori() }}</span>
                </dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Satuan</dt>
                <dd class="text-right text-gray-900">{{ $produk->satuan }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Harga</dt>
                <dd class="text-right font-medium text-gray-900">{{ $produk->labelHarga() }}</dd>
            </div>
            @if ($produk->kategori_produk === 'dijual')
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Stok</dt>
                <dd class="text-right text-gray-900">
                    {{ $produk->stok }}
                    @if ($produk->aktif && $produk->stokMenipis())
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Menipis</span>
                    @endif
                </dd>
            </div>
            @endif
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Status</dt>
                <dd class="text-right text-gray-900">
                    @if ($produk->aktif)
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Nonaktif</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
@endsection
