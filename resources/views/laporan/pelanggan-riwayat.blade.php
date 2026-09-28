@extends('layouts.app')

@section('title', 'Riwayat ' . $pelanggan->nama)

@section('nav-active', 'reports-active-clients')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pelanggan-aktif.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $rataRata = $jumlahKunjungan > 0 ? $totalBelanja / $jumlahKunjungan : 0;
    $fmtTanggal = function ($dt, $denganJam = false) {
        if (!$dt) return '-';
        $m = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $c = \Carbon\Carbon::parse($dt);
        $tgl = $c->format('d') . ' ' . $m[$c->format('n') - 1] . ' ' . $c->format('Y');
        return $denganJam ? $tgl . ' &middot; ' . $c->format('H:i') : $tgl;
    };
@endphp

@section('content')

    <div class="trans-page-header-row">
        <div class="trans-header-title-group">
            <a href="{{ route('laporan.pelanggan-aktif', ['preset' => 'bulan-ini']) }}" class="btn-table-action btn-table-action-back">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
                Kembali ke Pelanggan Aktif
            </a>
            <h1 class="trans-page-title">{{ $pelanggan->nama }}</h1>
            <p class="trans-page-subtitle">Riwayat transaksi lengkap &middot; {{ $pelanggan->no_wa ?: 'tanpa nomor WhatsApp' }}</p>
        </div>
    </div>

    <div class="report-stat-triplet">
        <div class="report-stat-mini-card">
            <span class="mini-stat-label">Total Kunjungan</span>
            <span class="mini-stat-val">{{ $jumlahKunjungan }}</span>
            <span class="mini-stat-sub">transaksi selesai</span>
        </div>
        <div class="report-stat-mini-card is-featured">
            <span class="mini-stat-label">Total Belanja</span>
            <span class="mini-stat-val">{{ $fmt($totalBelanja) }}</span>
            <span class="mini-stat-sub">seluruh riwayat</span>
        </div>
        <div class="report-stat-mini-card">
            <span class="mini-stat-label">Rata-rata per Kunjungan</span>
            <span class="mini-stat-val">{{ $fmt($rataRata) }}</span>
            <span class="mini-stat-sub">kunjungan terakhir {{ $fmtTanggal($transaksis->first()?->waktu_kunjungan) }}</span>
        </div>
    </div>

    <div class="daily-group-card">
        <div class="daily-group-header">
            <div class="daily-group-title">
                <span>Riwayat Transaksi</span>
            </div>
            <span class="daily-group-badge">{{ $jumlahKunjungan }} Transaksi</span>
        </div>

        @if ($transaksis->isEmpty())
            <div class="report-empty-note" style="padding: 40px 16px;">
                Belum ada riwayat transaksi untuk pelanggan ini.
            </div>
        @else
            <div class="table-responsive-wrapper">
                <table class="daily-trans-table">
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
                                    <a href="{{ route('transaksi.show', $t) }}" class="trans-order-id-link">{{ $t->no_struk }}</a>
                                </td>
                                <td class="trans-worker-tag">{!! $fmtTanggal($t->waktu_kunjungan, true) !!}</td>
                                <td>
                                    @forelse ($t->details as $d)
                                        <div class="trans-service-title">
                                            @if ($d->tipe_item === 'layanan')
                                                {{ $d->layanan->nama_layanan ?? '-' }}
                                                <span class="trans-worker-tag">({{ $d->varian_dipilih ?? '-' }})</span>
                                            @else
                                                {{ $d->produk->nama_produk ?? '-' }}
                                                <span class="trans-worker-tag">&times; {{ $d->qty }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="trans-worker-tag">&mdash;</span>
                                    @endforelse
                                </td>
                                <td>
                                    @forelse ($t->details->where('tipe_item', 'layanan')->whereNotNull('id_staf') as $d)
                                        <div class="trans-worker-tag">{{ $d->staf->nama }}</div>
                                    @empty
                                        <span class="trans-worker-tag">&mdash;</span>
                                    @endforelse
                                </td>
                                <td style="text-align: right;" class="trans-amount-cell client-spend-cell">
                                    {{ $fmt($t->total_bayar) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="client-tfoot-row">
                            <th colspan="4">TOTAL {{ $jumlahKunjungan }} TRANSAKSI</th>
                            <th style="text-align: right; color: #B45309;">{{ $fmt($totalBelanja) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

@endsection
