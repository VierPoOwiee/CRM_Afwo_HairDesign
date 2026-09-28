@extends('layouts.app')

@section('title', 'Laporan Keuntungan Produk')

@section('nav-active', 'reports-sales')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $pct = fn ($n) => number_format((float) $n, 1, ',', '.') . ' %';
    $upDown = fn (float $v) => $v >= 0 ? '#047857' : '#B91C1C';
@endphp

@section('content')

    <div class="report-intro-section">
        <h1 class="report-title">Laporan Keuntungan Produk</h1>
        <p class="report-subtitle">
            Selisih harga jual dengan modal (harga beli/restock) yang tersimpan saat transaksi.
            Modal rata-rata dihitung otomatis dari riwayat pembelian.
        </p>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.keuntungan-produk'),
        'resetUrl' => route('laporan.keuntungan-produk', ['preset' => 'bulan-ini']),
    ])

    <div class="report-pill-row">
        <a href="{{ route('laporan.keuntungan-produk', ['preset' => $preset, 'dari' => $dari, 'sampai' => $sampai]) }}"
           class="category-pill {{ $kategoriFilter === '' ? 'active' : '' }}">
            Semua Kategori
        </a>
        @foreach ($kategoriList as $key => $label)
            <a href="{{ route('laporan.keuntungan-produk', ['preset' => $preset, 'dari' => $dari, 'sampai' => $sampai, 'kategori_produk' => $key]) }}"
               class="category-pill {{ $kategoriFilter === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Tabs Mode --}}
    <div class="trans-tabs-row">
        <button type="button" class="trans-filter-tab active" data-tab="retail" onclick="switchTab('retail')">Mode 1 &middot; Retail (Dijual Per PCS)</button>
        <button type="button" class="trans-filter-tab" data-tab="treatment" onclick="switchTab('treatment')">Mode 2 &middot; Treatment (Dijual per Layanan)</button>
    </div>

    {{-- ===== Mode 1: Retail ===== --}}
    <div id="tab-panel-retail" class="tab-panel">
        <div class="report-stat-grid">
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Omset Produk</span>
                <span class="mini-stat-val">{{ $fmt($retailTotals['omset']) }}</span>
            </div>
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Modal (Harga Beli)</span>
                <span class="mini-stat-val">{{ $fmt($retailTotals['modal']) }}</span>
            </div>
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Keuntungan</span>
                <span class="mini-stat-val" style="color: {{ $upDown($retailTotals['keuntungan']) }};">{{ $fmt($retailTotals['keuntungan']) }}</span>
            </div>
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Margin</span>
                <span class="mini-stat-val" style="color: {{ $upDown($retailTotals['margin']) }};">{{ $pct($retailTotals['margin']) }}</span>
            </div>
        </div>

        <div class="daily-group-card">
            <div class="daily-group-header">
                <div class="daily-group-title">
                    <span>Keuntungan per Produk (Retail)</span>
                </div>
                <span class="daily-group-badge">{{ $retailRows->count() }} Produk</span>
            </div>
            @if ($retailRows->isEmpty())
                <div class="report-empty-note">Belum ada penjualan produk retail di periode ini.</div>
            @else
                <div class="table-responsive-wrapper">
                    <table class="daily-trans-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: right;">Omset</th>
                                <th style="text-align: right;">Modal</th>
                                <th style="text-align: right;">Keuntungan</th>
                                <th style="text-align: right;">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($retailRows as $r)
                                <tr>
                                    <td>
                                        <span class="trans-service-title">{{ $r['nama_produk'] }}</span>
                                        @if ($r['merek'])
                                            <div class="trans-worker-tag">{{ $r['merek'] }}</div>
                                        @endif
                                    </td>
                                    <td class="trans-worker-tag">{{ $r['kategori'] }}</td>
                                    <td style="text-align: right; font-weight: 700;">{{ intval($r['qty']) }} {{ $r['satuan'] }}</td>
                                    <td style="text-align: right;" class="trans-amount-cell">{{ $fmt($r['omset']) }}</td>
                                    <td style="text-align: right;" class="trans-worker-tag">{{ $fmt($r['modal']) }}</td>
                                    <td style="text-align: right; font-weight: 800; color: {{ $upDown($r['keuntungan']) }};">{{ $fmt($r['keuntungan']) }}</td>
                                    <td style="text-align: right; font-weight: 800; color: {{ $upDown($r['keuntungan']) }};">
                                        {{ $r['omset'] > 0 ? $pct(($r['keuntungan'] / $r['omset']) * 100) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <p class="report-subtitle" style="margin-top: 10px;">
            {{ intval($retailTotals['qty']) }} unit terjual dalam {{ $retailRows->count() }} produk, {{ $retailTotals['jumlah'] }} item transaksi.
        </p>
    </div>

    {{-- ===== Mode 2: Treatment ===== --}}
    <div id="tab-panel-treatment" class="tab-panel hidden">
        <div class="report-stat-grid">
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Omset Produk Terpakai</span>
                <span class="mini-stat-val">{{ $fmt($layananTotals['omset']) }}</span>
            </div>
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Modal Bahan</span>
                <span class="mini-stat-val">{{ $fmt($layananTotals['modal']) }}</span>
            </div>
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Keuntungan</span>
                <span class="mini-stat-val" style="color: {{ $upDown($layananTotals['keuntungan']) }};">{{ $fmt($layananTotals['keuntungan']) }}</span>
            </div>
            <div class="report-stat-mini-card">
                <span class="mini-stat-label">Margin</span>
                <span class="mini-stat-val" style="color: {{ $upDown($layananTotals['margin']) }};">{{ $pct($layananTotals['margin']) }}</span>
            </div>
        </div>

        <div class="daily-group-card">
            <div class="daily-group-header">
                <div class="daily-group-title">
                    <span>Keuntungan per Produk (Treatment)</span>
                </div>
                <span class="daily-group-badge">{{ $layananRows->count() }} Produk</span>
            </div>
            @if ($layananRows->isEmpty())
                <div class="report-empty-note">Belum ada pemakaian produk pada layanan di periode ini.</div>
            @else
                <div class="table-responsive-wrapper">
                    <table class="daily-trans-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th style="text-align: right;">Total Pemakaian</th>
                                <th style="text-align: right;">Omset</th>
                                <th style="text-align: right;">Modal Bahan</th>
                                <th style="text-align: right;">Keuntungan</th>
                                <th style="text-align: right;">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($layananRows as $r)
                                <tr>
                                    <td>
                                        <span class="trans-service-title">{{ $r['nama_produk'] }}</span>
                                        @if ($r['merek'])
                                            <div class="trans-worker-tag">{{ $r['merek'] }}</div>
                                        @endif
                                    </td>
                                    <td class="trans-worker-tag">{{ $r['kategori'] }}</td>
                                    <td style="text-align: right; font-weight: 700;">{{ number_format((float) $r['total_ml'], 0, ',', '.') }} ml</td>
                                    <td style="text-align: right;" class="trans-amount-cell">{{ $fmt($r['omset']) }}</td>
                                    <td style="text-align: right;" class="trans-worker-tag">{{ $fmt($r['modal']) }}</td>
                                    <td style="text-align: right; font-weight: 800; color: {{ $upDown($r['keuntungan']) }};">{{ $fmt($r['keuntungan']) }}</td>
                                    <td style="text-align: right; font-weight: 800; color: {{ $upDown($r['keuntungan']) }};">
                                        {{ $r['omset'] > 0 ? $pct(($r['keuntungan'] / $r['omset']) * 100) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <p class="report-subtitle" style="margin-top: 10px;">
            Dihitung dari total ml produk yang terpakai pada layanan: nilai jual produk (omset) dikurangi modal bahan.
            {{ $kategoriFilter !== '' ? 'Difilter kategori produk: '.($kategoriList[$kategoriFilter] ?? $kategoriFilter).'.' : 'Semua kategori produk.' }}
        </p>
    </div>

    {{-- Grafik tren 6 bulan --}}
    <div class="revenue-chart-card">
        <div class="revenue-chart-header">
            <div class="chart-header-left">
                <span class="chart-label-sm">Tren Keuntungan Produk</span>
                <span class="chart-revenue-val">6 Bulan Terakhir</span>
            </div>
        </div>
        <div class="svg-bar-chart-container" style="height: 240px;">
            <canvas id="chartKeuntunganProduk"></canvas>
        </div>
        <p class="report-subtitle" style="margin-top: 8px;">Retail + treatment (nilai jual produk terpakai dikurangi modal bahan).</p>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    function switchTab(name) {
        document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.add('hidden'); });
        document.getElementById('tab-panel-' + name).classList.remove('hidden');
        document.querySelectorAll('.trans-filter-tab').forEach(function (b) {
            b.classList.toggle('active', b.dataset.tab === name);
        });
    }
    window.switchTab = switchTab;

    var labels = @json($trenBulanan->pluck('label')->values());
    var values = @json($trenBulanan->pluck('keuntungan')->map(fn ($v) => round((float) $v))->values());

    var canvas = document.getElementById('chartKeuntunganProduk');
    if (canvas && window.Chart && labels.length > 0) {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Keuntungan Produk',
                    data: values,
                    backgroundColor: values.map(function (v) { return v >= 0 ? 'rgba(16,185,129,0.75)' : 'rgba(239,68,68,0.75)'; }),
                    borderColor: values.map(function (v) { return v >= 0 ? 'rgb(5,150,105)' : 'rgb(220,38,38)'; }),
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20 } },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return 'Keuntungan: Rp' + new Intl.NumberFormat('id-ID').format(ctx.raw); } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function (v) { return 'Rp' + (v >= 1000 ? (v / 1000) + 'K' : v); } } }
                }
            }
        });
    }

    switchTab('retail');
})();
</script>
@endpush