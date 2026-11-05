@extends('layouts.app')

@section('title', 'Data Pelanggan Aktif')

@section('nav-active', 'reports-active-clients')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pelanggan-aktif.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $totalPelanggan = $pelanggans->count();
    $totalKunjungan = (int) $pelanggans->sum('jumlah_kunjungan');
    $totalBelanja = (float) $pelanggans->sum('total_belanja');
    $rataRata = $totalKunjungan > 0 ? $totalBelanja / $totalKunjungan : 0;

    $topBelanja = $pelanggans->sortByDesc(fn ($p) => (float) $p->total_belanja)->take(5)->values();

    $fmtTanggal = function ($dt) {
        if (!$dt) return '-';
        $m = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $c = \Carbon\Carbon::parse($dt);
        return $c->format('d') . ' ' . $m[$c->format('n') - 1] . ' ' . $c->format('Y');
    };
@endphp

@section('content')

    <div class="report-intro-section">
        <h1 class="report-title">Data Pelanggan Aktif</h1>
        <p class="report-subtitle">
            Pelanggan dengan minimal 1 transaksi selesai pada periode yang dipilih,
            diurutkan dari kunjungan terakhir.
        </p>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.pelanggan-aktif'),
        'resetUrl' => route('laporan.pelanggan-aktif', ['preset' => 'bulan-ini']),
    ])

    {{-- ===== Ringkasan KPI ===== --}}
    <div class="report-stat-grid">
        <div class="report-stat-mini-card">
            <span class="mini-stat-label">Total Pelanggan Aktif</span>
            <span class="mini-stat-val">{{ $totalPelanggan }}</span>
            <span class="mini-stat-sub">orang bertransaksi</span>
        </div>
        <div class="report-stat-mini-card">
            <span class="mini-stat-label">Total Kunjungan</span>
            <span class="mini-stat-val">{{ $totalKunjungan }}</span>
            <span class="mini-stat-sub">transaksi selesai</span>
        </div>
        <div class="report-stat-mini-card is-featured">
            <span class="mini-stat-label">Total Nilai Transaksi</span>
            <span class="mini-stat-val">{{ $fmt($totalBelanja) }}</span>
            <span class="mini-stat-sub">omset periode terpilih</span>
        </div>
        <div class="report-stat-mini-card">
            <span class="mini-stat-label">Rata-rata Belanja (AOV)</span>
            <span class="mini-stat-val">{{ $fmt($rataRata) }}</span>
            <span class="mini-stat-sub">per kunjungan</span>
        </div>
    </div>

    {{-- ===== Toolbar: pencarian nama / no. WhatsApp ===== --}}
    <div class="trans-toolbar-card active-clients-toolbar">
        <div class="trans-search-filter-controls">
            <div class="trans-search-box">
                <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="20" y1="20" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="search" id="cari-pelanggan" class="search-input" placeholder="Cari nama atau no. WhatsApp&hellip;">
            </div>
        </div>
    </div>

    {{-- ===== Tabel Pelanggan ===== --}}
    <div class="daily-group-card">
        <div class="daily-group-header">
            <div class="daily-group-title">
                <span>Daftar Pelanggan</span>
            </div>
            <span class="daily-group-badge" id="pelanggan-count">{{ $totalPelanggan }} Orang</span>
        </div>

        @if ($pelanggans->isEmpty())
            <div class="report-empty-note" style="padding: 40px 16px;">
                Tidak ada pelanggan aktif untuk periode yang dipilih.
            </div>
        @else
            <div class="table-responsive-wrapper">
                <table class="daily-trans-table" id="tabel-pelanggan-aktif">
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
                            <tr data-cari="{{ \Illuminate\Support\Str::lower($p->nama . ' ' . ($p->no_wa ?? '')) }}">
                                <td>
                                    <div class="trans-client-meta">
                                        <div class="trans-client-avatar">{{ mb_substr($p->nama, 0, 1) }}</div>
                                        <div>
                                            <a href="{{ route('laporan.pelanggan-riwayat', $p) }}" class="client-name-link">
                                                {{ $p->nama }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="trans-worker-tag">{{ $p->no_wa ?: '-' }}</td>
                                <td style="text-align: right;">
                                    <span class="visit-chip">{{ (int) $p->jumlah_kunjungan }}&times;</span>
                                </td>
                                <td style="text-align: right;" class="trans-amount-cell client-spend-cell">
                                    {{ $fmt($p->total_belanja ?? 0) }}
                                </td>
                                <td class="trans-worker-tag">{{ $fmtTanggal($p->kunjungan_terakhir) }}</td>
                                <td style="text-align: right;">
                                    <a href="{{ route('laporan.pelanggan-riwayat', $p) }}" class="btn-table-action">
                                        Riwayat
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="client-tfoot-row">
                            <th colspan="2">TOTAL</th>
                            <th style="text-align: right;">{{ $totalKunjungan }}</th>
                            <th style="text-align: right; color: var(--text-link);">{{ $fmt($totalBelanja) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="report-empty-note hidden" id="pelanggan-tidak-cocok" style="padding: 40px 16px;">
                Pelanggan tidak ditemukan pada filter &amp; pencarian ini.
            </div>
        @endif
    </div>

    {{-- ===== Top Pelanggan by Belanja ===== --}}
    @if ($topBelanja->isNotEmpty())
        <div class="daily-group-card" style="margin-top: 20px;">
            <div class="daily-group-header">
                <div class="daily-group-title">
                    <span>Top 5 Pelanggan Berdasarkan Belanja</span>
                </div>
                <span class="daily-group-badge">Periode Terpilih</span>
            </div>
            <div class="table-responsive-wrapper">
                <table class="daily-trans-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Pelanggan</th>
                            <th style="text-align: right;">Kunjungan</th>
                            <th style="text-align: right;">Total Belanja</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topBelanja as $i => $p)
                            <tr>
                                <td class="trans-worker-tag">{{ $i + 1 }}</td>
                                <td>
                                    <div class="trans-client-meta">
                                        <div class="trans-client-avatar">{{ mb_substr($p->nama, 0, 1) }}</div>
                                        <div>
                                            <a href="{{ route('laporan.pelanggan-riwayat', $p) }}" class="client-name-link">
                                                {{ $p->nama }}
                                            </a>
                                            <div class="trans-client-phone">
                                                Kunjungan terakhir {{ $fmtTanggal($p->kunjungan_terakhir) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: right; font-weight: 700;">{{ (int) $p->jumlah_kunjungan }}&times;</td>
                                <td style="text-align: right;" class="trans-amount-cell client-spend-cell">
                                    {{ $fmt($p->total_belanja ?? 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
<script>
(function () {
    var tabel = document.getElementById('tabel-pelanggan-aktif');
    if (!tabel) return;

    var baris = Array.prototype.slice.call(tabel.querySelectorAll('tbody tr'));
    var tfoot = tabel.querySelector('tfoot');
    var inputCari = document.getElementById('cari-pelanggan');
    var badgeJumlah = document.getElementById('pelanggan-count');
    var catatanKosong = document.getElementById('pelanggan-tidak-cocok');

    function terapkanFilter() {
        var kata = (inputCari ? inputCari.value : '').trim().toLowerCase();
        var tampil = 0;

        baris.forEach(function (tr) {
            var cocokCari = !kata || (tr.dataset.cari || '').indexOf(kata) !== -1;
            tr.classList.toggle('hidden', !cocokCari);
            if (cocokCari) tampil++;
        });

        if (badgeJumlah) badgeJumlah.textContent = tampil + ' Orang';
        if (catatanKosong) catatanKosong.classList.toggle('hidden', tampil > 0);
        if (tfoot) tfoot.classList.toggle('hidden', kata !== '');
    }

    if (inputCari) inputCari.addEventListener('input', terapkanFilter);
})();
</script>
@endpush
