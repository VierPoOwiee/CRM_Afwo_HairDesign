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

    $sektor = fn (int $j) => $j >= 5 ? 'vip' : ($j >= 3 ? 'loyal' : 'reguler');
    $sektorLabel = ['vip' => 'VIP', 'loyal' => 'Loyal', 'reguler' => 'Reguler'];
    $jumlahSektor = ['vip' => 0, 'loyal' => 0, 'reguler' => 0];
    foreach ($pelanggans as $row) {
        $jumlahSektor[$sektor((int) $row->jumlah_kunjungan)]++;
    }

    $sektorIkon = [
        'vip' => '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.9 6.26L21 9.27l-4.5 4.37 1.06 6.19L12 16.77l-5.56 3.06L7.5 13.64 3 9.27l6.1-.99L12 2z"></path></svg>',
        'loyal' => '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"></path></svg>',
        'reguler' => '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
    ];

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

    {{-- ===== Segmentasi Pelanggan ===== --}}
    <div class="category-breakdown-card">
        <div class="category-breakdown-title">Segmentasi Pelanggan</div>

        <div class="category-visual-grid">
            @foreach ($jumlahSektor as $key => $jumlah)
                <div class="cat-tile-box">
                    {!! $sektorIkon[$key] !!}
                    <span class="cat-tile-percentage">{{ $jumlah }}</span>
                    <span class="cat-tile-name">{{ $sektorLabel[$key] }}</span>
                </div>
            @endforeach
        </div>

        <div class="cat-detail-list">
            <div class="cat-detail-row">
                <span class="cat-detail-name">VIP &middot; 5 kunjungan atau lebih</span>
                <span class="cat-detail-total">{{ $jumlahSektor['vip'] }} orang</span>
            </div>
            <div class="cat-detail-row">
                <span class="cat-detail-name">Loyal &middot; 3&ndash;4 kunjungan</span>
                <span class="cat-detail-total">{{ $jumlahSektor['loyal'] }} orang</span>
            </div>
            <div class="cat-detail-row">
                <span class="cat-detail-name">Reguler &middot; 1&ndash;2 kunjungan</span>
                <span class="cat-detail-total">{{ $jumlahSektor['reguler'] }} orang</span>
            </div>
        </div>
    </div>

    {{-- ===== Toolbar: filter segmen + pencarian ===== --}}
    <div class="trans-toolbar-card">
        <div class="trans-tabs-row" id="segmen-tabs">
            <button type="button" class="trans-filter-tab active" data-segmen="">Semua</button>
            <button type="button" class="trans-filter-tab" data-segmen="vip">VIP</button>
            <button type="button" class="trans-filter-tab" data-segmen="loyal">Loyal</button>
            <button type="button" class="trans-filter-tab" data-segmen="reguler">Reguler</button>
        </div>
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
                            @php $segmen = $sektor((int) $p->jumlah_kunjungan); @endphp
                            <tr data-segmen="{{ $segmen }}"
                                data-cari="{{ \Illuminate\Support\Str::lower($p->nama . ' ' . ($p->no_wa ?? '')) }}">
                                <td>
                                    <div class="trans-client-meta">
                                        <div class="trans-client-avatar">{{ mb_substr($p->nama, 0, 1) }}</div>
                                        <div>
                                            <a href="{{ route('laporan.pelanggan-riwayat', $p) }}" class="client-name-link">
                                                {{ $p->nama }}
                                            </a>
                                            <div class="trans-worker-tag">
                                                <span class="segment-pill segment-pill-{{ $segmen }}">{{ $sektorLabel[$segmen] }}</span>
                                            </div>
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
                            <th style="text-align: right; color: #B45309;">{{ $fmt($totalBelanja) }}</th>
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
    var tombolSegmen = Array.prototype.slice.call(document.querySelectorAll('#segmen-tabs .trans-filter-tab'));
    var segmenAktif = '';

    function terapkanFilter() {
        var kata = (inputCari ? inputCari.value : '').trim().toLowerCase();
        var tampil = 0;

        baris.forEach(function (tr) {
            var cocokSegmen = !segmenAktif || tr.dataset.segmen === segmenAktif;
            var cocokCari = !kata || (tr.dataset.cari || '').indexOf(kata) !== -1;
            var tampilkan = cocokSegmen && cocokCari;
            tr.classList.toggle('hidden', !tampilkan);
            if (tampilkan) tampil++;
        });

        if (badgeJumlah) badgeJumlah.textContent = tampil + ' Orang';
        if (catatanKosong) catatanKosong.classList.toggle('hidden', tampil > 0);
        if (tfoot) tfoot.classList.toggle('hidden', segmenAktif !== '' || kata !== '');
    }

    tombolSegmen.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            segmenAktif = tombol.dataset.segmen || '';
            tombolSegmen.forEach(function (t) { t.classList.toggle('active', t === tombol); });
            terapkanFilter();
        });
    });

    if (inputCari) inputCari.addEventListener('input', terapkanFilter);
})();
</script>
@endpush
