@extends('layouts.app')

@section('title', 'Dashboard')

@section('nav-active', 'dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@php
    $chartWidth = 600;
    $paddingX = 20;
    $chartHeight = 95;
    $groundY = 142;
    $topY = 25;

    $trend = $pemasukan7Hari->map(function ($item) {
        return ['day' => $item['label'], 'income' => (float) $item['total']];
    })->values()->toArray();

    $incomeSeries = array_column($trend, 'income');
    $maxIncome = $incomeSeries ? max($incomeSeries) : 1;
    $minIncome = $incomeSeries ? min($incomeSeries) : 0;
    $range = max(($maxIncome - $minIncome * 0.5), 1);
    $numPoints = count($trend);
    $stepX = $numPoints > 1 ? ($chartWidth - $paddingX * 2) / ($numPoints - 1) : 0;

    $chartPoints = [];
    foreach ($trend as $i => $d) {
        $x = $paddingX + $i * $stepX;
        $normalized = ($d['income'] - $minIncome * 0.5) / $range;
        $y = $groundY - $normalized * $chartHeight;
        $chartPoints[] = ['x' => $x, 'y' => $y, 'day' => $d['day'], 'income' => $d['income']];
    }

    function buildSmoothPath(array $pts): string
    {
        if (count($pts) === 0) return '';
        $d = 'M ' . number_format($pts[0]['x'], 1) . ',' . number_format($pts[0]['y'], 1);
        for ($i = 0; $i < count($pts) - 1; $i++) {
            $p0 = $pts[$i];
            $p1 = $pts[$i + 1];
            $cpX1 = $p0['x'] + ($p1['x'] - $p0['x']) * 0.5;
            $cpY1 = $p0['y'];
            $cpX2 = $cpX1;
            $cpY2 = $p1['y'];
            $d .= ' C ' . number_format($cpX1, 1) . ',' . number_format($cpY1, 1) . ' '
                . number_format($cpX2, 1) . ',' . number_format($cpY2, 1) . ' '
                . number_format($p1['x'], 1) . ',' . number_format($p1['y'], 1);
        }
        return $d;
    }

    $linePathD = buildSmoothPath($chartPoints);
    $firstX = $chartPoints[0]['x'] ?? $paddingX;
    $lastX = end($chartPoints)['x'] ?? ($chartWidth - $paddingX);
    $areaPathD = $linePathD . ' L ' . number_format($lastX, 1) . ',' . $groundY
        . ' L ' . number_format($firstX, 1) . ',' . $groundY . ' Z';
@endphp

@section('content')
    {{-- Welcome Header Section --}}
    <div class="dashboard-welcome-header">
        <h1 class="welcome-title" id="welcome-title" data-field="welcome-title">Selamat datang kembali{{ Auth::check() ? ', ' . strtok(Auth::user()->name, ' ') : '' }}</h1>
        <p class="welcome-subtitle" id="welcome-subtitle" data-field="welcome-subtitle">Ringkasan aktivitas hari ini, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}.</p>
    </div>

    {{-- 1. Top Row 4 KPI Stat Cards --}}
    <div class="stats-cards-grid">

        {{-- Card 1: Pemasukan Hari Ini (Featured Dark Card) --}}
        <div class="stat-kpi-card featured-dark-card" data-field="card-today-income">
            <div class="stat-card-top">
                <span class="stat-card-label">Pemasukan Hari Ini</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-card-value" id="today-income" data-field="today-income">Rp{{ number_format((float) $totalPemasukanHariIni, 0, ',', '.') }}</div>
            <span class="badge-growth-pill" id="today-growth" data-field="today-growth">
                @if ($persenPerubahan !== null)
                    {{ $persenPerubahan >= 0 ? '+' : '' }}{{ number_format($persenPerubahan, 0, ',', '.') }}% dari kemarin
                @else
                    Belum ada data kemarin
                @endif
            </span>
        </div>

        {{-- Card 2: Pelanggan Baru --}}
        <a href="{{ route('pelanggan.index') }}" class="stat-kpi-card" data-field="card-new-clients" title="Lihat Data Pelanggan">
            <div class="stat-card-top">
                <span class="stat-card-label">Pelanggan Baru</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
            </div>
            <div class="stat-card-value" id="new-clients-count" data-field="new-clients-count">{{ $pelangganBaruHariIni }}</div>
            <span class="stat-card-subtext" id="new-clients-subtext">pelanggan baru hari ini</span>
        </a>

        {{-- Card 3: Transaksi Hari Ini --}}
        <a href="{{ route('transaksi.index') }}" class="stat-kpi-card" data-field="card-today-transactions" title="Lihat Transaksi">
            <div class="stat-card-top">
                <span class="stat-card-label">Transaksi Hari Ini</span>
                <div class="stat-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <line x1="6" y1="8" x2="18" y2="8"></line>
                        <line x1="6" y1="12" x2="14" y2="12"></line>
                        <line x1="6" y1="16" x2="10" y2="16"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-card-value" id="today-transactions-count" data-field="today-transactions-count">{{ $transaksiHariIni->count() }}</div>
            <span class="stat-card-subtext" id="today-transactions-subtext">transaksi selesai</span>
        </a>

        {{-- Card 4: Stok Menipis --}}
        <a href="{{ route('produk.index') }}" class="stat-kpi-card" data-field="card-low-stock" title="Lihat Stok Produk">
            <div class="stat-card-top">
                <span class="stat-card-label">Stok Menipis</span>
                <div class="stat-card-icon warning-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-card-value danger-value" id="low-stock-count" data-field="low-stock-count">{{ $produkStokMenipis->count() }}</div>
            <span class="stat-card-subtext" id="low-stock-subtext">produk perlu direstock</span>
        </a>

    </div>

    {{-- 2. Main Dashboard Split Grid (Left ~65% / Right ~35%) --}}
    <div class="dashboard-main-grid">

        {{-- Left Column --}}
        <div class="dashboard-col-left">

            {{-- Tren Pemasukan (Area Line Chart) --}}
            <div class="dash-card" data-field="card-income-trend">
                <div class="dash-card-header">
                    <h2 class="dash-card-title">Tren Pemasukan</h2>
                    <span class="dash-card-meta">7 hari terakhir</span>
                </div>

                <div class="chart-container-box" id="income-chart-container">
                    <svg viewBox="0 0 600 160" class="chart-svg-responsive" id="income-trend-svg" aria-label="Grafik Tren Pemasukan">
                        <defs>
                            <linearGradient id="areaGradientGold" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#E5A93C" stop-opacity="0.25"/>
                                <stop offset="85%" stop-color="#FBF8F2" stop-opacity="0.08"/>
                                <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0.0"/>
                            </linearGradient>
                        </defs>

                        <g class="chart-gridlines" stroke="#F1F5F9" stroke-width="1" stroke-dasharray="3 3">
                            <line x1="0" y1="30" x2="600" y2="30"></line>
                            <line x1="0" y1="80" x2="600" y2="80"></line>
                            <line x1="0" y1="130" x2="600" y2="130"></line>
                        </g>

                        <path id="chart-area-path" fill="url(#areaGradientGold)" d="{{ $areaPathD }}"></path>
                        <path id="chart-line-path" fill="none" stroke="#C5932D" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" d="{{ $linePathD }}"></path>

                        <g id="chart-points-group">
                            @foreach ($chartPoints as $pt)
                                <circle cx="{{ number_format($pt['x'], 1) }}" cy="{{ number_format($pt['y'], 1) }}" r="4" fill="#FFFFFF" stroke="#C5932D" stroke-width="2.5" class="chart-interactive-point">
                                    <title>{{ $pt['day'] }}: Rp{{ number_format($pt['income'], 0, ',', '.') }}</title>
                                </circle>
                            @endforeach
                        </g>
                    </svg>

                    <div class="chart-x-axis-labels" id="chart-axis-days">
                        @foreach ($trend as $d)
                            <span>{{ $d['day'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Transaksi Hari Ini (Table) --}}
            <div class="dash-card" data-field="card-today-transactions-table">
                <div class="dash-card-header">
                    <h2 class="dash-card-title">Transaksi Hari Ini</h2>
                    <a href="{{ route('transaksi.index') }}" class="dash-card-link" id="link-see-all-transactions">Lihat semua</a>
                </div>

                <div class="table-responsive-wrapper">
                    <table class="today-trans-table">
                        <thead>
                            <tr>
                                <th>NO STRUK</th>
                                <th>PELANGGAN</th>
                                <th>LAYANAN</th>
                                <th>WAKTU</th>
                                <th>TOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="today-transactions-body" data-field="today-transactions-body">
                            @forelse ($transaksiHariIni as $trx)
                                <tr>
                                    <td><span class="trans-no-badge">{{ $trx->no_struk }}</span></td>
                                    <td><span class="trans-client-name">{{ $trx->pelanggan->nama ?? 'Pelanggan' }}</span></td>
                                    <td><span class="trans-service-text">{{ $trx->details->where('tipe_item', 'layanan')->map(fn ($d) => $d->layanan->nama_layanan ?? 'Layanan')->implode(', ') ?: '-' }}</span></td>
                                    <td><span class="trans-time-text">{{ $trx->waktu_kunjungan ? $trx->waktu_kunjungan->format('H:i') : '-' }}</span></td>
                                    <td><span class="trans-total-bold">Rp{{ number_format((float) $trx->total_bayar, 0, ',', '.') }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 24px; color: var(--text-muted);">Belum ada transaksi hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="dashboard-col-right">

            {{-- Stok Produk Menipis --}}
            <div class="dash-card" data-field="card-low-stock-list">
                <div class="dash-card-header" style="margin-bottom: 12px;">
                    <div class="dash-card-title-group">
                        <h2 class="dash-card-title">Stok Produk Menipis</h2>
                        <span class="dash-card-subtitle">Di bawah batas minimum</span>
                    </div>
                </div>

                <div class="low-stock-list" id="low-stock-list-container" data-field="low-stock-list-container">
                    @forelse ($produkStokMenipis as $produk)
                        <div class="low-stock-item">
                            <div class="low-stock-left">
                                <div class="stock-initial-badge">{{ collect(str_word_count($produk->nama_produk, 1))->take(2)->map(fn ($w) => strtoupper(substr($w, 0, 1)))->implode('') ?: strtoupper(substr($produk->nama_produk, 0, 2)) }}</div>
                                <div class="stock-item-info">
                                    <span class="stock-item-name">{{ $produk->nama_produk }}</span>
                                    <span class="stock-item-category">{{ $produk->merek ? $produk->merek . ' • ' : '' }}{{ $produk->labelKategori() }}</span>
                                </div>
                            </div>
                            <span class="badge-stock-danger">{{ $produk->stok }} pcs</span>
                        </div>
                    @empty
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Semua stok aman.</span>
                    @endforelse
                </div>
            </div>

            {{-- Layanan Terpopuler --}}
            <div class="dash-card" data-field="card-popular-services">
                <div class="dash-card-header" style="margin-bottom: 12px;">
                    <div class="dash-card-title-group">
                        <h2 class="dash-card-title">Layanan Terpopuler</h2>
                        <span class="dash-card-subtitle">Minggu ini</span>
                    </div>
                </div>

                <div class="popular-services-list" id="popular-services-container" data-field="popular-services-container">
                    @forelse ($layananTerpopuler as $layanan)
                        <div class="popular-service-row">
                            <span class="service-row-name">{{ $layanan['nama'] }}</span>
                            <span class="service-row-count">{{ $layanan['jumlah'] }}x</span>
                        </div>
                    @empty
                        <span style="color: var(--text-muted); font-size: 0.9rem;">Belum ada data layanan.</span>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush