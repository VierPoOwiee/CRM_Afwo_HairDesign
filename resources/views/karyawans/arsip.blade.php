@extends('layouts.app')

@section('title', 'Arsip Karyawan')

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
            <h1 class="arsip-page-title">Arsip Karyawan</h1>
            <p class="arsip-page-subtitle">
                {{ $karyawans->total() }} karyawan diarsipkan. Data transaksi &amp; komisi tetap utuh.
            </p>
        </div>

        <div class="arsip-toolbar">
            <form action="{{ route('karyawan.arsip') }}" method="GET" class="arsip-search-form">
                <input type="text" name="q" value="{{ $q }}" class="arsip-search-input"
                       placeholder="Cari nama, kontak...">
                <button type="submit" class="btn-search-dark">Cari</button>
            </form>
            <a href="{{ route('karyawan.index') }}" class="btn-back-gold">Kembali</a>
        </div>
    </div>

    @if ($karyawans->isEmpty())
        <div class="arsip-empty-box">
            <p class="arsip-empty-text">Tidak ada karyawan yang diarsipkan.</p>
            @if ($q !== '')
                <p style="font-size:.84rem; color: var(--text-muted); margin-top: 4px;">Hasil untuk “{{ $q }}” tidak ditemukan.</p>
                <a href="{{ route('karyawan.arsip') }}" class="arsip-empty-link">&larr; Tampilkan semua</a>
            @else
                <a href="{{ route('karyawan.index') }}" class="arsip-empty-link">&larr; Kembali ke data karyawan</a>
            @endif
        </div>
    @else
        <div class="arsip-grid">
            @foreach ($karyawans as $k)
                <div class="arsip-card">
                    <div class="arsip-card-top">
                        <span class="arsip-card-name">{{ $k->nama }}</span>
                        <span class="arsip-trashed-badge">Diarsipkan {{ $fmtTgl($k->deleted_at) }}</span>
                    </div>

                    <div class="arsip-meta-list">
                        <div class="arsip-meta-row">
                            <span class="arsip-meta-label">Kontak</span>
                            <span class="arsip-meta-value">
                                @if ($k->kontak)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $k->kontak) }}" target="_blank" style="color: var(--status-ok);">
                                        {{ $k->kontak }}
                                    </a>
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="arsip-meta-row">
                            <span class="arsip-meta-label">Skema Komisi</span>
                            <span class="arsip-meta-value">
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    Persen omset ({{ $k->persen_komisi_harian }}%)
                                @else
                                    Per layanan
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="arsip-card-footer">
                        <form action="{{ route('karyawan.restore', $k->id) }}" method="POST"
                              onsubmit="return confirm('Pulihkan karyawan &quot;{{ addslashes($k->nama) }}&quot;?')">
                            @csrf
                            <button type="submit" class="btn-restore">Pulihkan</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($karyawans->hasPages())
            @include('partials.pagination', ['paginator' => $karyawans])
        @endif
    @endif

@endsection