@extends('layouts.app')

@section('title', $produk->nama_produk)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/produk.css') }}">
@endpush

@section('content')
    <div class="page-header-row">
        <a href="{{ route('produk.index') }}" class="btn-back-ghost" aria-label="Kembali ke daftar produk">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        <div class="page-header-title-group">
            <h1 class="page-title">{{ $produk->nama_produk }}</h1>
            <p class="page-subtitle">Detail informasi produk.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('produk.restock-form', $produk) }}" class="btn btn-primary">Restock</a>
            <a href="{{ route('produk.edit', $produk) }}" class="btn btn-secondary">Edit</a>
            <form action="{{ route('produk.destroy', $produk) }}" method="POST"
                  onsubmit="return confirm('Hapus produk &quot;{{ addslashes($produk->nama_produk) }}&quot;?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
    </div>

    <div class="detail-card">
        <dl class="def-list">
            @if ($produk->kategori_produk !== 'dijual')
            <div class="def-row">
                <dt class="def-label">Merek</dt>
                <dd class="def-value">{{ $produk->merek }}</dd>
            </div>
            @endif
            <div class="def-row">
                <dt class="def-label">Kategori</dt>
                <dd class="def-value">
                    <span class="pill pill--muted">{{ $produk->labelKategori() }}</span>
                </dd>
            </div>
            <div class="def-row">
                <dt class="def-label">Satuan</dt>
                <dd class="def-value">{{ $produk->satuan }}</dd>
            </div>
            <div class="def-row">
                <dt class="def-label">Harga</dt>
                <dd class="def-value product-price-val">{{ $produk->labelHarga() }}</dd>
            </div>
            <div class="def-row">
                <dt class="def-label">Modal Rata-Rata</dt>
                <dd class="def-value product-price-val">{{ $produk->labelModalPerSatuan() }}</dd>
            </div>
            @if ($produk->kategori_produk === 'dijual')
            <div class="def-row">
                <dt class="def-label">Stok</dt>
                <dd class="def-value">
                    {{ $produk->stok }}
                    @if ($produk->aktif && $produk->stokMenipis())
                        <span class="pill pill--warn">Menipis</span>
                    @endif
                </dd>
            </div>
            @endif
            <div class="def-row">
                <dt class="def-label">Status</dt>
                <dd class="def-value">
                    @if ($produk->aktif)
                        <span class="pill pill--ok">Aktif</span>
                    @else
                        <span class="pill pill--muted">Nonaktif</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
@endsection
