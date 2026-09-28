@extends('layouts.app')

@section('title', 'Riwayat ' . $pelanggan->nama)

@section('nav-active', 'reports-active-clients')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelanggan-aktif.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $fmtTanggal = function ($dt, $denganJam = false) {
        if (!$dt) return '-';
        $m = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $c = \Carbon\Carbon::parse($dt);
        $tgl = $c->format('d') . ' ' . $m[$c->format('n') - 1] . ' ' . $c->format('Y');
        return $denganJam ? $tgl . ' &middot; ' . $c->format('H:i') : $tgl;
    };
@endphp

@section('content')

    <div class="active-clients-header">
        <a href="{{ route('laporan.pelanggan-aktif', ['preset' => 'bulan-ini']) }}"
           class="btn-table-action btn-view-profile" style="align-self:flex-start;">&larr; Kembali ke Pelanggan Aktif</a>
        <h1 class="active-clients-title">{{ $pelanggan->nama }}</h1>
        <p class="active-clients-subtitle">Riwayat transaksi lengkap &middot; {{ $pelanggan->no_wa }}</p>
    </div>

    <div class="active-clients-kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="kpi-mini-card">
            <span class="kpi-mini-label">Total Kunjungan</span>
            <div class="kpi-mini-value">{{ $jumlahKunjungan }}</div>
        </div>
        <div class="kpi-mini-card featured-gold-kpi">
            <span class="kpi-mini-label">Total Belanja</span>
            <div class="kpi-mini-value">{{ $fmt($totalBelanja) }}</div>
        </div>
        <div class="kpi-mini-card">
            <span class="kpi-mini-label">Kunjungan Terakhir</span>
            <div class="kpi-mini-value" style="font-size:1.1rem;">{{ $fmtTanggal($transaksis->first()?->waktu_kunjungan) }}</div>
        </div>
    </div>

    <div class="active-clients-results-card">
        @if ($transaksis->isEmpty())
            <div class="empty-state-card">
                <div class="empty-state-content">
                    <p class="empty-state-text">Belum ada riwayat transaksi untuk pelanggan ini.</p>
                </div>
            </div>
        @else
            <div class="results-toolbar-header">
                <div class="sort-label">Riwayat Transaksi <span style="opacity:.6;">&middot; {{ $jumlahKunjungan }} transaksi</span></div>
            </div>
            <div class="table-responsive-wrapper">
                <table class="active-clients-table">
                    <thead>
                        <tr>
                            <th>No Struk</th>
                            <th>Tanggal</th>
                            <th>Item</th>
                            <th>Staf</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksis as $t)
                            <tr>
                                <td>
                                    <a href="{{ route('transaksi.show', $t) }}" class="client-name-link">{{ $t->no_struk }}</a>
                                </td>
                                <td style="color: var(--text-secondary);">{!! $fmtTanggal($t->waktu_kunjungan, true) !!}</td>
                                <td>
                                    @foreach ($t->details as $d)
                                        <div style="font-size:.8rem; color: var(--text-primary); font-weight:500;">
                                            @if ($d->tipe_item === 'layanan')
                                                {{ $d->layanan->nama_layanan ?? '-' }} <span style="color: var(--text-muted);">({{ $d->varian_dipilih ?? '-' }})</span>
                                            @else
                                                {{ $d->produk->nama_produk ?? '-' }} <span style="color: var(--text-muted);">&times; {{ $d->qty }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach ($t->details->where('tipe_item', 'layanan') as $d)
                                        @if ($d->staf)
                                            <div style="font-size:.8rem; color: var(--text-secondary);">{{ $d->staf->nama }}</div>
                                        @endif
                                    @endforeach
                                </td>
                                <td style="text-align: right;" class="spend-amount-highlight">{{ $fmt($t->total_bayar) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection