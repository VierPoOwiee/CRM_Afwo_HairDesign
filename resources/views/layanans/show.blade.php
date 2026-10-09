@extends('layouts.app')

@section('title', $layanan->nama_layanan)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}">
@endpush

@section('content')
    <div class="page-header-row">
        <a href="{{ route('layanan.index') }}" class="btn-back-ghost" aria-label="Kembali ke daftar layanan">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        <div class="page-header-title-group">
            <h1 class="page-title">{{ $layanan->nama_layanan }}</h1>
            <p class="page-subtitle">Detail harga &amp; komisi tiap varian.</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('layanan.edit', $layanan) }}" class="btn btn-secondary">Edit</a>
            <form action="{{ route('layanan.destroy', $layanan) }}" method="POST"
                  onsubmit="return confirm('Hapus layanan &quot;{{ addslashes($layanan->nama_layanan) }}&quot; beserta semua varian harganya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
        </div>
    </div>

    <div class="detail-card">
        <dl class="def-list">
            <div class="def-row">
                <dt class="def-label">Kategori</dt>
                <dd class="def-value">{{ $layanan->kategori }}</dd>
            </div>
            <div class="def-row">
                <dt class="def-label">Status</dt>
                <dd class="def-value">
                    @if ($layanan->aktif)
                        <span class="pill pill--ok">Aktif</span>
                    @else
                        <span class="pill pill--muted">Nonaktif</span>
                    @endif
                    @if ($layanan->termasuk_potong)
                        <span class="pill pill--warn">Termasuk Potong</span>
                    @endif
                </dd>
            </div>
            @if ($layanan->produk->isNotEmpty())
                <div class="def-row">
                    <dt class="def-label">Produk Digunakan</dt>
                    <dd class="def-value">
                        @foreach ($layanan->produk as $p)
                            <span class="pill pill--muted">{{ $p->merek }} &mdash; {{ $p->nama_produk }}</span>
                        @endforeach
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="varian-table-wrap">
        <table class="varian-table">
            <thead>
                <tr>
                    <th>Varian</th>
                    <th>Harga</th>
                    <th>Komisi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($layanan->hargaLayanan as $h)
                    <tr>
                        <td>
                            <span class="varian-col-label">Varian</span>
                            <span class="varian-name">{{ $h->varian }}</span>
                        </td>
                        <td>
                            <span class="varian-col-label">Harga</span>
                            <span>{{ $h->labelHargaDasar() }}</span>
                        </td>
                        <td>
                            <span class="varian-col-label">Komisi</span>
                            <span>{{ $h->labelKomisi() ?? '-' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="varian-empty">Belum ada varian harga.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
