@extends('layouts.app')

@section('title', 'Arsip Pelanggan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil-pelanggan.css') }}">
@endpush

@php
    $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $fmtTgl = function ($dt) use ($bulanIndo) {
        $c = \Carbon\Carbon::parse($dt);
        return $c->format('d') . ' ' . $bulanIndo[$c->format('n') - 1] . ' ' . $c->format('Y');
    };
@endphp

@section('content')

    <div class="arsip-header-row">
        <div class="arsip-header-group">
            <h1 class="arsip-page-title">Arsip Pelanggan</h1>
            <p class="arsip-page-subtitle">
                {{ $pelanggans->total() }} pelanggan diarsipkan. Data riwayat transaksi tetap utuh.
            </p>
        </div>

        <div class="arsip-toolbar">
            <form action="{{ route('pelanggan.arsip') }}" method="GET" class="arsip-search-form">
                <input type="text" name="q" value="{{ $q }}" class="arsip-search-input"
                       placeholder="Cari nama, no. WA, IG, alamat...">
                <button type="submit" class="btn-search-dark">Cari</button>
            </form>
            <a href="{{ route('pelanggan.index') }}" class="btn-back-gold">Kembali</a>
        </div>
    </div>

    @if ($pelanggans->isEmpty())
        <div class="arsip-empty-box">
            <p class="arsip-empty-text">Tidak ada pelanggan yang diarsipkan.</p>
            @if ($q !== '')
                <p style="font-size:.84rem; color: var(--text-muted); margin-top: 4px;">Hasil untuk “{{ $q }}” tidak ditemukan.</p>
                <a href="{{ route('pelanggan.arsip') }}" class="arsip-empty-link">&larr; Tampilkan semua</a>
            @else
                <a href="{{ route('pelanggan.index') }}" class="arsip-empty-link">&larr; Kembali ke data pelanggan</a>
            @endif
        </div>
    @else
        <div class="arsip-grid">
            @foreach ($pelanggans as $p)
                <div class="arsip-card">
                    <div class="arsip-card-top">
                        <span class="arsip-card-name">{{ $p->nama }}</span>
                        <span class="arsip-trashed-badge">Diarsipkan {{ $fmtTgl($p->deleted_at) }}</span>
                    </div>

                    <div class="arsip-meta-list">
                        <div class="arsip-meta-row">
                            <span class="arsip-meta-label">WhatsApp</span>
                            <span class="arsip-meta-value">{{ $p->no_wa }}</span>
                        </div>
                        <div class="arsip-meta-row">
                            <span class="arsip-meta-label">Instagram</span>
                            <span class="arsip-meta-value">{{ $p->username_instagram ? ltrim($p->username_instagram, '@') : '-' }}</span>
                        </div>
                        <div class="arsip-meta-row">
                            <span class="arsip-meta-label">Alamat</span>
                            <span class="arsip-meta-value">{{ $p->alamat ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="arsip-card-footer">
                        <form action="{{ route('pelanggan.restore', $p->id) }}" method="POST"
                              onsubmit="return confirm('Pulihkan pelanggan &quot;{{ addslashes($p->nama) }}&quot;?')">
                            @csrf
                            <button type="submit" class="btn-restore">Pulihkan</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($pelanggans->hasPages())
            <div style="margin-top: 6px;">
                {{ $pelanggans->links() }}
            </div>
        @endif
    @endif

@endsection