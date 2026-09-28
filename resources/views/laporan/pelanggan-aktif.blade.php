@extends('layouts.app')

@section('title', 'Data Pelanggan Aktif')

@section('nav-active', 'reports-active-clients')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pelanggan-aktif.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $totalPelanggan = $pelanggans->count();
    $totalKunjungan = (int) $pelanggans->sum('jumlah_kunjungan');
    $totalBelanja = (float) $pelanggans->sum('total_belanja');
    $rataRata = $totalKunjungan > 0 ? $totalBelanja / $totalKunjungan : 0;
    $badge = function (int $j) {
        return $j >= 5 ? 'badge-vip' : ($j >= 3 ? 'badge-loyal' : 'badge-reguler');
    };
    $seg = fn ($lbl) => strtoupper($lbl);
    $fmtTanggal = function ($dt) {
        if (!$dt) return '-';
        $m = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $c = \Carbon\Carbon::parse($dt);
        return $c->format('d') . ' ' . $m[$c->format('n') - 1] . ' ' . $c->format('Y');
    };
@endphp

@section('content')

    <div class="active-clients-header">
        <h1 class="active-clients-title">Data Pelanggan Aktif</h1>
        <p class="active-clients-subtitle">Pelanggan dengan minimal 1 transaksi selesai dalam periode yang dipilih.</p>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.pelanggan-aktif'),
        'resetUrl' => route('laporan.pelanggan-aktif', ['preset' => 'bulan-ini']),
    ])

    <div class="active-clients-kpi-grid">
        <div class="kpi-mini-card">
            <span class="kpi-mini-label">Total Pelanggan Aktif</span>
            <div class="kpi-mini-value">{{ $totalPelanggan }}</div>
            <span class="kpi-mini-sub">orang berkunjung periode ini</span>
        </div>
        <div class="kpi-mini-card">
            <span class="kpi-mini-label">Total Kunjungan</span>
            <div class="kpi-mini-value">{{ $totalKunjungan }}</div>
            <span class="kpi-mini-sub">transaksi selesai</span>
        </div>
        <div class="kpi-mini-card featured-gold-kpi">
            <span class="kpi-mini-label">Total Nilai Transaksi</span>
            <div class="kpi-mini-value">{{ $fmt($totalBelanja) }}</div>
            <span class="kpi-mini-sub">omset periode terpilih</span>
        </div>
        <div class="kpi-mini-card">
            <span class="kpi-mini-label">Rata-rata Belanja (AOV)</span>
            <div class="kpi-mini-value">{{ $fmt($rataRata) }}</div>
            <span class="kpi-mini-sub">per kunjungan</span>
        </div>
    </div>

    <div class="active-clients-results-card">
        @if ($pelanggans->isEmpty())
            <div class="empty-state-card">
                <div class="empty-state-content">
                    <p class="empty-state-text">Tidak ada pelanggan aktif untuk periode yang dipilih.</p>
                </div>
            </div>
        @else
            <div class="results-toolbar-header">
                <div class="sort-label">Daftar Pelanggan <span style="opacity:.6;">&middot; {{ $totalPelanggan }} orang</span></div>
            </div>
            <div class="table-responsive-wrapper">
                <table class="active-clients-table">
                    <thead>
                        <tr>
                            <th>Pelanggan</th>
                            <th>Kontak</th>
                            <th style="text-align: right;">Jumlah Kunjungan</th>
                            <th style="text-align: right;">Total Belanja</th>
                            <th>Kunjungan Terakhir</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pelanggans as $p)
                            <tr>
                                <td>
                                    <div class="client-meta-cell">
                                        <div class="client-avatar-circle">{{ mb_substr($p->nama, 0, 1) }}</div>
                                        <div>
                                            <a href="{{ route('laporan.pelanggan-riwayat', $p) }}" class="client-name-link">{{ $p->nama }}</a>
                                            <div><span class="client-segment-badge {{ $badge((int) $p->jumlah_kunjungan) }}">{{ $seg($badge((int) $p->jumlah_kunjungan) === 'badge-vip' ? 'VIP' : ($badge((int) $p->jumlah_kunjungan) === 'badge-loyal' ? 'Loyal' : 'Reguler')) }}</span></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color: var(--text-secondary);">{{ $p->no_wa }}</td>
                                <td style="text-align: right;">
                                    <span class="visit-badge">{{ $p->jumlah_kunjungan }}&times;</span>
                                </td>
                                <td style="text-align: right;" class="spend-amount-highlight">{{ $fmt($p->total_belanja ?? 0) }}</td>
                                <td style="color: var(--text-secondary);">{{ $fmtTanggal($p->kunjungan_terakhir) }}</td>
                                <td>
                                    <div class="action-buttons-cell">
                                        <a href="{{ route('laporan.pelanggan-riwayat', $p) }}" class="btn-table-action btn-view-profile">Riwayat &rarr;</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection