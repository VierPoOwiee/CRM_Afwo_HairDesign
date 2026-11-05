@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('nav-active', 'reports-sales')

@section('container_width', 'app-content-wide')

@section('html-class', 'page-laporan-penjualan')
@section('body-class', 'page-laporan-penjualan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $today = \Illuminate\Support\Carbon::today();
    $yesterday = (new \Illuminate\Support\Carbon($today))->subDay();

    $grouped = $transaksis->isEmpty()
        ? collect()
        : $transaksis->groupBy(fn ($t) => $t->waktu_kunjungan ? $t->waktu_kunjungan->toDateString() : 'x');

    $metodeMap = [
        'cash' => ['badge' => 'badge-cash', 'label' => 'Tunai'],
        'qris_bni' => ['badge' => 'badge-qris', 'label' => 'QRIS'],
        'qris_bri' => ['badge' => 'badge-qris', 'label' => 'QRIS'],
        'transfer' => ['badge' => 'badge-transfer', 'label' => 'Transfer'],
        'debit' => ['badge' => 'badge-transfer', 'label' => 'Debit'],
        'kartu_kredit' => ['badge' => 'badge-qris', 'label' => 'Kartu Kredit'],
    ];

    $fmtTgl = fn ($d) => \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('j M Y');
    $periodeRingkas = $fmtTgl($dari).' – '.$fmtTgl($sampai);

    $filterAktif = [];
    if ($jenisPengerjaan) {
        $filterAktif[] = 'Jenis Pengerjaan: '.($jenisPengerjaan === 'sendiri' ? 'Sendiri' : 'Berdua');
    }
    if ($metode) {
        $filterAktif[] = 'Metode Pembayaran: '.($metodeMap[$metode]['label'] ?? $metode);
    }
    if ($stafId) {
        $namaStafFilter = $karyawans->firstWhere('id', (int) $stafId)?->nama;
        $filterAktif[] = 'Karyawan: '.($namaStafFilter ?? '#'.$stafId);
    }

    $dicetakLabel = now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM YYYY, HH:mm').' WIB';

    $kategoriIkon = function (string $kategori) {
        $k = strtolower($kategori);
        $scissors = '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>';
        $clock = '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a9 9 0 0 1 9 9c0 4.97-4.03 9-9 9A9 9 0 0 1 3 11a9 9 0 0 1 9-9z"></path><path d="M12 7v5l3 3"></path></svg>';
        $pencil = '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
        $tag = '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>';
        $star = '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L14.39 8.26L21 9.27L16.27 13.88L17.82 20.4L12 16.77L6.18 20.4L7.73 13.88L3 9.27L9.61 8.26L12 2Z"></path></svg>';
        $chart = '<svg class="cat-tile-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>';

        if (str_contains($k, 'potong') || str_contains($k, 'cut') || str_contains($k, 'groom')) {
            return $scissors;
        }
        if (str_contains($k, 'warna') || str_contains($k, 'color') || str_contains($k, 'bleach') || str_contains($k, 'highlight')) {
            return $pencil;
        }
        if (str_contains($k, 'spa') || str_contains($k, 'treatment') || str_contains($k, 'creambath') || str_contains($k, 'smoothing')) {
            return $clock;
        }
        if (str_contains($k, 'styl') || str_contains($k, 'blow') || str_contains($k, 'hairdo')) {
            return $star;
        }
        if (str_contains($k, 'retail') || str_contains($k, 'produk')) {
            return $tag;
        }
        return $chart;
    };

    $insightValid = $insight && is_array($insight->konten_insight) && !empty($insight->konten_insight['headline']);
    $insightData = $insightValid ? $insight->konten_insight : [];
    $sentiment = in_array($insightData['sentiment'] ?? '', ['positive', 'negative', 'neutral']) ? $insightData['sentiment'] : 'neutral';

    $trendIcon = [
        'up' => ['path' => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941', 'color' => 'var(--status-ok)'],
        'down' => ['path' => 'M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181', 'color' => 'var(--status-danger)'],
        'neutral' => ['path' => 'M7.5 12h9', 'color' => 'var(--text-link)'],
    ];
    $trendKey = fn ($k) => match ($k) {
        'positive', 'up' => 'up',
        'negative', 'down' => 'down',
        default => 'neutral',
    };

    $omsetSekarang = (float) ($ringkasanData['omset_bulan_ini'] ?? 0);
    $omsetSebelumnya = (float) ($ringkasanData['omset_bulan_lalu'] ?? 0);
    $omsetMaks = max($omsetSekarang, $omsetSebelumnya, 0);
    $perubahan = 0;
    if ($omsetSebelumnya > 0) {
        $perubahan = (($omsetSekarang - $omsetSebelumnya) / $omsetSebelumnya) * 100;
    } elseif ($omsetSekarang > 0) {
        $perubahan = 100;
    }
    $periodeBulanIni = \Carbon\Carbon::parse($insightPeriode);
    $isBulanBerjalan = $periodeBulanIni->isCurrentMonth();
    $bulanLaluRange = $periodeBulanIni->copy()->subMonth()->startOfMonth()->format('d M Y')
        .' s.d. '.$periodeBulanIni->copy()->subMonth()->endOfMonth()->format('d M Y');
    $bulanIniRange = ($isBulanBerjalan
        ? $periodeBulanIni->copy()->startOfMonth()->format('d M Y').' s.d. '.now()->format('d M Y').' (MTD)'
        : $periodeBulanIni->copy()->startOfMonth()->format('d M Y').' s.d. '.$periodeBulanIni->copy()->endOfMonth()->format('d M Y'));
    $omsetChart = [
        'lalu' => $omsetSebelumnya,
        'ini' => $omsetSekarang,
        'rangeLalu' => $bulanLaluRange,
        'rangeIni' => $bulanIniRange,
        'mtd' => $isBulanBerjalan,
    ];

    $trenPuncak = $trenHarian->sortByDesc('total')->first();

    $catTotalAll = (float) $breakdownKategori->sum('total_subtotal');
    $catTiles = $breakdownKategori->take(4)->map(function ($row) use ($catTotalAll) {
        $pct = $catTotalAll > 0 ? round(((float) $row->total_subtotal / $catTotalAll) * 100) : 0;
        return [
            'nama' => $row->kategori,
            'pct' => $pct,
            'total' => (float) $row->total_subtotal,
        ];
    });

    $hasBreakdownComparison = is_array($ringkasanData['breakdown_kategori'] ?? null)
        && count($ringkasanData['breakdown_kategori']) > 0;
@endphp

@section('content')

    {{-- ===== KOP laporan (hanya tampil saat cetak) ===== --}}
    <div class="print-only print-kop">
        <div class="print-kop__brand">AFWO Hair Design</div>
        <h1 class="print-kop__title">Laporan Penjualan</h1>
        <p class="print-kop__meta">Periode: {{ $periodeRingkas }}</p>
        @if (!empty($filterAktif))
            <p class="print-kop__filters">Filter aktif: {{ implode(' | ', $filterAktif) }}</p>
        @endif
        <p class="print-kop__meta">Dicetak: {{ $dicetakLabel }}</p>
    </div>

    {{-- ===== Page Header (pola global) ===== --}}
    <div class="page-header">
        <span class="page-header__eyebrow">Laporan Penjualan</span>
        <div class="page-header__row">
            <div>
                <h1 class="page-header__title" id="report-page-title">Ringkasan Penjualan</h1>
                <p class="page-header__subtitle">Pantau omset, jumlah transaksi, dan layanan terlaris pada periode terpilih.</p>
            </div>
        </div>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.penjualan'),
        'resetUrl' => route('laporan.penjualan', ['preset' => 'bulan-ini']),
        'showExtraFilters' => true,
        'showJenisPengerjaan' => true,
        'showMetode' => true,
    ])

    @if (session('success'))
        <div class="report-empty-note no-print" style="background-color: var(--status-ok-bg); color: var(--status-ok-fg); border: 1px solid var(--status-ok-border); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="report-empty-note no-print" style="background-color: var(--status-danger-bg); color: var(--status-danger-fg); border: 1px solid var(--status-danger-border); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('error') }}</div>
    @endif
    @if (session('insight_info'))
        <div class="report-empty-note no-print" style="background-color: var(--gold-50); color: var(--text-link); border: 1px solid var(--gold-200); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('insight_info') }}</div>
    @endif

    {{-- ============ AI INSIGHT CARD ============ --}}
    <section class="card ai-insight-card" id="ai-insight-card" data-field="ai-insight-card">
        <div class="ai-insight-header">
            <div class="ai-insight-heading">
                <span class="ai-sparkle-badge" aria-hidden="true">
                    <svg class="ai-sparkle-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" focusable="false">
                        <path d="M12 2L14.39 8.26L21 9.27L16.27 13.88L17.82 20.4L12 16.77L6.18 20.4L7.73 13.88L3 9.27L9.61 8.26L12 2Z"></path>
                    </svg>
                </span>
                <div class="ai-insight-heading-text">
                    <h2 class="ai-insight-title">AI Insight — Analisa Keuntungan</h2>
                    <p class="ai-insight-meta">
                        @if ($insight)
                            Terakhir digenerate: {{ $insight->dibuat_pada->format('d M Y H:i') }}
                        @else
                            Belum ada analisa tersimpan
                        @endif
                    </p>
                </div>
            </div>

            <div class="ai-insight-actions no-print">
                <form method="POST" action="{{ route('laporan.insight.generate') }}" class="ai-insight-generate">
                    @csrf
                    <input type="hidden" name="periode" value="{{ $insightPeriode }}">
                    <button type="submit" id="btnGenerateInsight" class="btn btn--ghost"
                        @if ($insightCooldown || $kuotaAi['habis'])
                            disabled
                            title="{{ $kuotaAi['habis'] ? 'Kuota AI hari ini habis, reset ' . $kuotaAi['label_reset'] : 'Analisa baru saja digenerate, tunggu beberapa menit' }}"
                        @endif>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ $insight ? 'Generate Ulang' : 'Generate Analisa' }}
                    </button>
                    @if ($insightCooldown)
                        <span id="insightCooldownLabel" data-sisa="{{ $insightCooldownSisaDetik }}" class="insight-cooldown-chip">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Bisa generate lagi dalam
                            <span id="insightCountdown" class="tabular-nums">{{ floor($insightCooldownSisaDetik / 60) }}:{{ str_pad($insightCooldownSisaDetik % 60, 2, '0', STR_PAD_LEFT) }}</span>
                        </span>
                    @endif
                </form>

                <span class="insight-quota-pill">
                    Kuota <strong>{{ $kuotaAi['terpakai'] }}/{{ $kuotaAi['batas'] }}</strong>
                    <span class="insight-quota-sep" aria-hidden="true">&middot;</span>
                    @if ($kuotaAi['habis'])
                        <span class="warn">habis, reset {{ $kuotaAi['label_reset'] }}</span>
                    @elseif ($kuotaAi['hampir_habis'])
                        <span class="warn">sisa {{ $kuotaAi['sisa'] }}, reset {{ $kuotaAi['label_reset'] }}</span>
                    @else
                        <span>reset {{ $kuotaAi['label_reset'] }}</span>
                    @endif
                </span>
            </div>
        </div>

        @if ($insightValid)
            <div class="ai-insight-content">
                <p class="insight-headline">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="{{ $trendIcon[$trendKey($sentiment)]['color'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="{{ $trendIcon[$trendKey($sentiment)]['path'] }}"/>
                    </svg>
                    <span>{{ $insightData['headline'] }}</span>
                </p>

                @if (!empty($insightData['sorotan']))
                    <div class="insight-sorotan-grid">
                        @foreach ($insightData['sorotan'] as $sorotan)
                            <div class="insight-sorotan-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $trendIcon[$trendKey($sorotan['trend'] ?? 'neutral')]['color'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="{{ $trendIcon[$trendKey($sorotan['trend'] ?? 'neutral')]['path'] }}"/>
                                </svg>
                                <span>{{ $sorotan['teks'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (!empty($insightData['rekomendasi']))
                    <div class="insight-subsection no-print">
                        <p class="insight-rekomendasi-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
                                <path d="M12 2L14.39 8.26L21 9.27L16.27 13.88L17.82 20.4L12 16.77L6.18 20.4L7.73 13.88L3 9.27L9.61 8.26L12 2Z"></path>
                            </svg>
                            Rekomendasi
                        </p>
                        <ul class="insight-rekomendasi-list">
                            @foreach ($insightData['rekomendasi'] as $rekomendasi)
                                <li>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--status-ok)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>{{ $rekomendasi }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @elseif ($insight && ! $insightValid)
            <div class="insight-note-empty no-print">Analisa tersimpan masih menggunakan format lama (teks panjang). Klik tombol "Generate Ulang" untuk memperbaruinya ke tampilan ringkas.</div>
        @else
            <div class="insight-note-empty no-print">Belum ada analisa. Klik tombol "Generate Analisa" untuk mendapatkan insight dari AI.</div>
        @endif

    </section>

    {{-- ============ TOTAL REVENUE & CHART ============ --}}
    <section class="card revenue-chart-card" data-field="revenue-chart-card">
        <div class="card__header">
            <div class="card__title-group">
                <h2 class="card__title">Grafik Pendapatan</h2>
                <p class="card__subtitle">Perbandingan omset {{ $periodeRingkas }}.</p>
            </div>
            @if ($omsetMaks > 0)
                <span class="badge badge--accent growth-badge-pill {{ $perubahan >= 0 ? '' : 'down' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        @if ($perubahan >= 0)
                            <line x1="7" y1="17" x2="17" y2="7"></line>
                            <polyline points="7 7 17 7 17 17"></polyline>
                        @else
                            <line x1="7" y1="7" x2="17" y2="17"></line>
                            <polyline points="17 7 17 17 7 17"></polyline>
                        @endif
                    </svg>
                    <span>{{ $perubahan >= 0 ? '+' : '' }}{{ abs(round($perubahan)) }}% dari bulan lalu</span>
                </span>
            @endif
        </div>

        <div class="revenue-chart-body">
            <div class="chart-header-left">
                <span class="chart-label-sm">Total Pendapatan</span>
                <span class="chart-revenue-val">{{ $fmt($totalOmset) }}</span>
                @if ($isBulanBerjalan)
                    <span class="card__subtitle">Bulan Ini = omset month-to-date s.d. {{ now()->format('d M Y') }} (belum final).</span>
                @endif
            </div>

            @if ($omsetMaks <= 0)
                <div class="report-empty-note">Belum ada transaksi pada periode ini. Chart akan muncul otomatis setelah ada transaksi.</div>
            @else
                <div class="svg-bar-chart-container">
                    <canvas id="chartOmsetBulan"></canvas>
                </div>
                <div class="chart-axis-labels">
                    <span>Bulan Lalu</span>
                    <span>Bulan Ini{{ $isBulanBerjalan ? ' (MTD)' : '' }}</span>
                </div>
            @endif
        </div>
    </section>

    {{-- Tren Omset Harian --}}
    @if ($trenHarian->isNotEmpty())
        <section class="card revenue-chart-card no-print">
            <div class="card__header">
                <div class="card__title-group">
                    <h2 class="card__title">Tren Omset Harian</h2>
                    <p class="card__subtitle">{{ $trenHarian->count() }} hari punya transaksi pada periode ini.</p>
                    @if ($trenPuncak)
                        <p class="card__subtitle chart-tren-peak">Hari tertinggi: {{ $trenPuncak['tanggal'] }} ({{ $fmt($trenPuncak['total']) }}).</p>
                    @endif
                </div>
            </div>
            <div class="revenue-chart-body">
                <div class="chart-header-left">
                    <span class="chart-label-sm">Total Harian</span>
                    <span class="chart-revenue-val">{{ $fmt($trenHarian->sum('total')) }}</span>
                </div>
                <div class="svg-bar-chart-container chart-compact">
                    <canvas id="chartTren"></canvas>
                </div>
            </div>
        </section>
    @endif

    {{-- ============ REPORT STATS ============ --}}
    <div class="report-stat-triplet" data-field="report-stats">
        <article class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__label">Total Omset</span>
                <span class="stat-card__icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                        <circle cx="12" cy="12" r="2.5"></circle>
                        <path d="M6 12h.01M18 12h.01"></path>
                    </svg>
                </span>
            </div>
            <span class="stat-card__value" id="report-total-revenue" data-field="total-revenue">{{ $fmt($totalOmset) }}</span>
            <span class="stat-card__subtext">{{ $periodeRingkas }}</span>
        </article>

        <article class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__label">Jumlah Transaksi</span>
                <span class="stat-card__icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2z"></path>
                        <path d="M9 7h6M9 11h6M9 15h4"></path>
                    </svg>
                </span>
            </div>
            <span class="stat-card__value" id="report-transaction-count" data-field="transaction-count">{{ $jumlahTransaksi }}</span>
            <span class="stat-card__subtext">Transaksi selesai</span>
        </article>

        <article class="stat-card">
            <div class="stat-card__top">
                <span class="stat-card__label">Rata-rata / Transaksi</span>
                <span class="stat-card__icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M4 20V10M10 20V4M16 20v-8M22 20H2"></path>
                    </svg>
                </span>
            </div>
            <span class="stat-card__value" id="report-average-revenue" data-field="average-revenue">{{ $fmt($rataRata) }}</span>
            <span class="stat-card__subtext">Per transaksi</span>
        </article>
    </div>

    {{-- ============ CATEGORY BREAKDOWN ============ --}}
    @if ($breakdownKategori->isNotEmpty())
        <section class="card category-breakdown-card" id="category-breakdown" data-field="category-breakdown">
            <div class="card__header">
                <div class="card__title-group">
                    <h2 class="card__title">Rincian Kategori Layanan</h2>
                    <p class="card__subtitle">Kontribusi tiap kategori terhadap total omset periode ini.</p>
                </div>
            </div>

            <div class="category-visual-grid">
                @foreach ($catTiles as $tile)
                    <div class="cat-tile-box">
                        {!! $kategoriIkon($tile['nama']) !!}
                        <span class="cat-tile-percentage">{{ $tile['pct'] }}%</span>
                        <span class="cat-tile-name">{{ $tile['nama'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="cat-detail-list">
                @foreach ($breakdownKategori as $row)
                    <div class="cat-detail-row">
                        <div class="cat-detail-left">
                            <span class="cat-detail-name">{{ $row->kategori }}</span>
                            <span class="cat-detail-count" data-field="category-count">{{ $row->jumlah_item }} item</span>
                        </div>
                        <span class="cat-detail-total" data-field="category-total">{{ $fmt($row->total_subtotal) }}</span>
                    </div>
                @endforeach
            </div>

            @if ($hasBreakdownComparison)
                <div class="insight-subsection no-print">
                    <p class="insight-rekomendasi-title">Perbandingan Kategori — Bulan Ini vs Bulan Lalu</p>
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartBreakdownKategori"></canvas>
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- ============ RINGKASAN + AKSI ============ --}}
    <section class="report-summary-banner" data-field="category-summary-banner">
        <div class="report-summary-text">
            <span class="report-summary-label">Ringkasan Periode ({{ $periodeRingkas }})</span>
            <span class="report-summary-total" id="dark-summary-total-val">Total Omset: {{ $fmt($totalOmset) }}</span>
        </div>
        <div class="report-summary-actions">
            <button type="button" class="btn btn--secondary" id="btn-export-csv" data-field="btn-export-csv">Ekspor CSV</button>
            <button type="button" class="btn btn--primary" id="btn-generate-report" data-field="btn-generate-report">Cetak Laporan</button>
        </div>
    </section>

    {{-- ============ RIWAYAT TRANSAKSI ============ --}}
    <section class="report-history-section" data-field="report-history-section">
        <div class="section-heading">
            <h2 class="card__title">Riwayat Transaksi</h2>
            <p class="card__subtitle">Rincian transaksi harian pada periode terpilih.</p>
        </div>

        @forelse ($grouped as $tanggal => $dayTrxs)
            @php
                $dateObj = \Illuminate\Support\Carbon::parse($tanggal);
                $dayName = $namaHari[$dateObj->dayOfWeek];
                $dateLabel = $dayName.', '.$dateObj->day.' '.$namaBulan[$dateObj->month - 1].' '.$dateObj->year;
                if ($dateObj->isSameDay($today)) {
                    $heading = 'Hari Ini — '.$dateLabel;
                } elseif ($dateObj->isSameDay($yesterday)) {
                    $heading = 'Kemarin — '.$dateLabel;
                } else {
                    $heading = $dateLabel;
                }
                $dayTotal = $dayTrxs->sum(fn ($t) => (float) $t->total_bayar);
            @endphp

            <div class="daily-group-card">
                <div class="daily-group-header">
                    <div class="daily-group-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-link)" stroke-width="2.5" aria-hidden="true" focusable="false">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>{{ $heading }}</span>
                    </div>
                    <div class="daily-summary-pills">
                        <span class="daily-group-badge">{{ $dayTrxs->count() }} Transaksi</span>
                        <span class="daily-total-amount">{{ $fmt($dayTotal) }}</span>
                    </div>
                </div>

                <div class="table-responsive-wrapper">
                    <table class="daily-trans-table">
                        <thead>
                            <tr>
                                <th>ID / Waktu</th>
                                <th>Pelanggan</th>
                                <th>Layanan &amp; Stylist</th>
                                <th>Total Biaya</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayTrxs as $t)
                                @php
                                    $detailRows = $t->details->where('subtotal', '>', 0);
                                    $namaBarang = $detailRows->map(function ($d) {
                                        if ($d->tipe_item === 'layanan' && $d->layanan) {
                                            return $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                                        }
                                        if ($d->tipe_item === 'produk' && $d->produk) {
                                            return $d->produk->nama_produk;
                                        }
                                        return '(Item)';
                                    })->unique()->take(2)->values();
                                    $stafNames = $t->details->pluck('staf.nama')->filter()->unique()->take(2)->values();
                                    $iniNama = collect(preg_split('/\s+/', trim((string) ($t->pelanggan->nama ?? ''))))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
                                    $methodInfo = $metodeMap[$t->metode_pembayaran] ?? ['badge' => '', 'label' => $t->labelMetode()];
                                @endphp

                                <tr data-trx-id="{{ $t->id }}">
                                    <td style="width: 110px;">
                                        <span class="trans-order-id-link">{{ $t->no_struk }}</span>
                                        <div class="trans-time-val">{{ $t->waktu_kunjungan ? $t->waktu_kunjungan->format('H:i') : '--:--' }} WIB</div>
                                    </td>
                                    <td>
                                        <div class="trans-client-meta">
                                            <span class="trans-client-avatar">{{ $iniNama }}</span>
                                            <div>
                                                <div class="trans-client-name">{{ $t->pelanggan->nama ?? '-' }}</div>
                                                <div class="trans-client-phone">{{ $t->pelanggan->no_wa ?? 'No. WA belum diisi' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="trans-service-title">{{ $namaBarang->join(', ') ?: '-' }}</div>
                                        <div class="trans-worker-tag">Stylist: <strong>{{ $stafNames->join(', ') ?: '-' }}</strong></div>
                                    </td>
                                    <td>
                                        <div class="trans-amount-cell">{{ $fmt($t->total_bayar) }}</div>
                                    </td>
                                    <td>
                                        <span class="payment-method-badge {{ $methodInfo['badge'] }}">{{ $methodInfo['label'] }}</span>
                                    </td>
                                    <td>
                                        <span class="status-pill status-paid">Selesai (Paid)</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="report-empty-note">Tidak ada transaksi untuk periode dan filter ini.</div>
        @endforelse
    </section>

    {{-- ===== Riwayat transaksi versi tabel padat (hanya tampil saat cetak) ===== --}}
    @if ($transaksis->isNotEmpty())
        <div class="print-only print-history">
            <h2 class="print-section-title">Riwayat Transaksi</h2>
            <table class="print-trans-table">
                <thead>
                    <tr>
                        <th class="col-tanggal">Tanggal</th>
                        <th class="col-trx">Kode TRX</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Stylist</th>
                        <th class="col-metode">Metode</th>
                        <th class="col-total num">Total</th>
                        <th class="col-status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksis as $t)
                        @php
                            $printDetails = $t->details->where('subtotal', '>', 0);
                            $printNamaBarang = $printDetails->map(function ($d) {
                                if ($d->tipe_item === 'layanan' && $d->layanan) {
                                    return $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                                }
                                if ($d->tipe_item === 'produk' && $d->produk) {
                                    return $d->produk->nama_produk;
                                }
                                return '(Item)';
                            })->unique()->implode(', ');
                            $printStaf = $t->details->pluck('staf.nama')->filter()->unique()->implode(', ');
                            $printMetode = $metodeMap[$t->metode_pembayaran] ?? ['label' => $t->labelMetode()];
                        @endphp
                        <tr>
                            <td>{{ $t->waktu_kunjungan ? $t->waktu_kunjungan->format('d/m/y H:i') : '-' }}</td>
                            <td>{{ $t->no_struk }}</td>
                            <td>{{ $t->pelanggan->nama ?? '-' }}</td>
                            <td>{{ $printNamaBarang ?: '-' }}</td>
                            <td>{{ $printStaf ?: '-' }}</td>
                            <td>{{ $printMetode['label'] }}</td>
                            <td class="num">{{ $fmt($t->total_bayar) }}</td>
                            <td>{{ $t->status === 'selesai' ? 'Selesai' : 'Batal' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Canvas/Chart.js tidak bisa membaca var(--x) langsung, jadi token harus
    // di-resolve ke nilai rgb yang sebenarnya lewat theme engine.
    function tok(name, fallback) {
        var v = (window.AFWOTheme && window.AFWOTheme.token(name)) || '';
        return v || fallback || '#000';
    }

    function rpFull(v) {
        return 'Rp' + Number(v).toLocaleString('id-ID');
    }

    function rpShort(v) {
        if (v >= 1e9) return 'Rp' + (v / 1e9).toFixed(1).replace('.', ',') + ' M';
        if (v >= 1e6) return 'Rp' + (v / 1e6).toFixed(1).replace('.', ',') + ' jt';
        return 'Rp' + Math.round(v);
    }

    // Saat cetak, grafik dipaksa memakai warna tema terang supaya label dan
    // grid tetap terbaca di atas kertas putih (terutama saat layar dark mode).
    var printColorOverride = false;

    // Plugin: tulis angka rupiah di atas tiap bar
    const rupiahLabelPlugin = {
        id: 'rupiahLabel',
        afterDatasetsDraw(chart) {
            const { ctx } = chart;
            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                if (meta.hidden) return;
                meta.data.forEach((bar, j) => {
                    const raw = dataset.data[j];
                    if (raw <= 0) return;
                    ctx.save();
                    ctx.fillStyle = printColorOverride ? '#4B4843' : tok('--text-2', '#374151');
                    ctx.font = '600 11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(rpShort(raw), bar.x, bar.y - 4);
                    ctx.restore();
                });
            });
        }
    };

    // Chart 1: Perbandingan omset bulan ini (MTD) vs bulan lalu penuh
    const canvasOmset = document.getElementById('chartOmsetBulan');
    let chartOmsetInst = null;
    if (canvasOmset) {
        const omset = @json($omsetChart);

        chartOmsetInst = new Chart(canvasOmset, {
            type: 'bar',
            data: {
                labels: ['Bulan Lalu', 'Bulan Ini' + (omset.mtd ? ' (MTD)' : '')],
                datasets: [{
                    label: 'Omset',
                    data: [omset.lalu, omset.ini],
                    backgroundColor: [tok('--gold-800'), tok('--gold-600')],
                    borderColor: [tok('--gold-900'), tok('--gold-700')],
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 90,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 24 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: () => 'Perbandingan Omset',
                            label: (ctx) => {
                                const nama = ctx.dataIndex === 0 ? 'Bulan Lalu' : 'Bulan Ini' + (omset.mtd ? ' (MTD)' : '');
                                return nama + ': ' + rpFull(ctx.parsed.y);
                            },
                            footer: (items) => 'Periode: ' + (items[0].dataIndex === 0 ? omset.rangeLalu : omset.rangeIni),
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: (v) => 'Rp' + (v / 1e6).toFixed(0).replace('.', ',') + ' jt'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            },
            plugins: [rupiahLabelPlugin]
        });
    }

    // Chart 2: Breakdown kategori layanan (bulan ini vs bulan lalu)
    const canvasKategori = document.getElementById('chartBreakdownKategori');
    let chartKategoriInst = null;
    if (canvasKategori) {
        const breakdown = @json($ringkasanData['breakdown_kategori'] ?? []);
        if (Array.isArray(breakdown) && breakdown.length) {
            const kategoriLabels = breakdown.map((k) => k.kategori);
            const warnaBulanIni = () => breakdown.map((k) =>
                k.omset_ini >= k.omset_lalu ? tok('--status-ok', '#047857') : tok('--status-danger', '#DC2626')
            );

            chartKategoriInst = new Chart(canvasKategori, {
                type: 'bar',
                data: {
                    labels: kategoriLabels,
                    datasets: [
                        {
                            label: 'Bulan Lalu',
                            data: breakdown.map((k) => k.omset_lalu),
                            backgroundColor: tok('--text-3', '#9CA3AF'),
                            borderRadius: 4,
                        },
                        {
                            label: 'Bulan Ini',
                            data: breakdown.map((k) => k.omset_ini),
                            backgroundColor: warnaBulanIni(),
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 20 } },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.dataset.label + ': ' + rpFull(ctx.parsed.y)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => 'Rp' + (v / 1e6).toFixed(0).replace('.', ',') + ' jt'
                            }
                        }
                    }
                },
                plugins: [rupiahLabelPlugin]
            });
        }
    }

    // Chart 3: Tren omset harian
    const canvasTren = document.getElementById('chartTren');
    let chartTrenInst = null;
    if (canvasTren) {
        const labelsTren = @json($trenHarian->pluck('tanggal'));
        const dataTren = @json($trenHarian->pluck('total'));
        const trenPadat = labelsTren.length > 12;
        const trenBarThickness = trenPadat ? 28 : 90;
        const trenPlugins = trenPadat ? [] : [rupiahLabelPlugin];

        chartTrenInst = new Chart(canvasTren, {
            type: 'bar',
            data: {
                labels: labelsTren,
                datasets: [{
                    label: 'Omset',
                    data: dataTren,
                    backgroundColor: tok('--gold-600', '#B45309'),
                    borderColor: tok('--gold-700', '#8A6A2E'),
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: trenBarThickness
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 24 }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => items[0].label,
                            label: (ctx) => 'Omset: ' + rpFull(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: tok('--text-3', '#6F6B63'),
                            font: { size: 11 },
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: tok('--border', '#E8E5DD') },
                        ticks: {
                            color: tok('--text-3', '#6F6B63'),
                            font: { size: 10 },
                            callback: (v) => 'Rp' + (v / 1e6).toFixed(0).replace('.', ',') + ' jt'
                        }
                    }
                }
            },
            plugins: trenPlugins
        });
    }

    // ===== Persiapan grafik untuk cetak (A4) =====
    // Chart.js merender canvas sesuai ukuran container di layar. Saat masuk
    // mode print, container dibuat lebar penuh dan tinggi tetap supaya grafik
    // tidak gepeng. Ukuran semula disimpan untuk dipulihkan setelah print.
    var printChartState = null;

    function chartsForPrint() {
        return [chartOmsetInst, chartTrenInst, chartKategoriInst].filter(Boolean);
    }

    function collectChartColors(chart) {
        var scales = chart.options.scales || {};
        var scaleColors = {};
        Object.keys(scales).forEach(function (key) {
            var sc = scales[key];
            scaleColors[key] = {
                tick: sc.ticks ? sc.ticks.color : undefined,
                grid: sc.grid ? sc.grid.color : undefined,
                border: sc.border ? sc.border.color : undefined
            };
        });

        var legendLabels = chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels;

        return {
            datasets: chart.data.datasets.map(function (ds) {
                return { backgroundColor: ds.backgroundColor, borderColor: ds.borderColor };
            }),
            scaleColors: scaleColors,
            legendLabels: legendLabels || null,
            legendColor: legendLabels ? legendLabels.color : undefined
        };
    }

    function applyPrintChartColors(chart) {
        if (chart === chartOmsetInst) {
            var dsOmset = chart.data.datasets[0];
            dsOmset.backgroundColor = ['#6E5008', '#D8A322'];
            dsOmset.borderColor = ['#3A2A03', '#A87C14'];
        } else if (chart === chartTrenInst) {
            var dsTren = chart.data.datasets[0];
            dsTren.backgroundColor = '#D8A322';
            dsTren.borderColor = '#A87C14';
        } else if (chart === chartKategoriInst) {
            var dsLalu = chart.data.datasets[0];
            var dsIni = chart.data.datasets[1];
            dsLalu.backgroundColor = '#6F6B63';
            if (dsIni) {
                dsIni.backgroundColor = dsIni.data.map(function (v, i) {
                    return v >= (dsLalu.data[i] || 0) ? '#047857' : '#DC2626';
                });
            }
        }

        var scales = chart.options.scales || {};
        Object.keys(scales).forEach(function (key) {
            var sc = scales[key];
            if (sc.ticks) sc.ticks.color = '#4B4843';
            if (sc.grid && sc.grid.display !== false) sc.grid.color = '#E8E5DD';
            if (sc.border) sc.border.color = '#E8E5DD';
        });

        var legendLabels = chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels;
        if (legendLabels) legendLabels.color = '#4B4843';
    }

    function restoreChartColors(chart, colors) {
        chart.data.datasets.forEach(function (ds, i) {
            if (!colors.datasets[i]) return;
            ds.backgroundColor = colors.datasets[i].backgroundColor;
            ds.borderColor = colors.datasets[i].borderColor;
        });

        var scales = chart.options.scales || {};
        Object.keys(colors.scaleColors).forEach(function (key) {
            var sc = scales[key];
            if (!sc) return;
            var saved = colors.scaleColors[key];
            if (sc.ticks) sc.ticks.color = saved.tick;
            if (sc.grid) sc.grid.color = saved.grid;
            if (sc.border) sc.border.color = saved.border;
        });

        if (colors.legendLabels) colors.legendLabels.color = colors.legendColor;
    }

    function resizeChartsForPrint() {
        var charts = chartsForPrint();
        if (!charts.length) return;

        if (!printChartState) {
            printChartState = charts.map(function (chart) {
                var el = chart.canvas.parentElement;
                var rect = el.getBoundingClientRect();
                var xTicks = chart.options.scales && chart.options.scales.x && chart.options.scales.x.ticks;
                var yTicks = chart.options.scales && chart.options.scales.y && chart.options.scales.y.ticks;
                return {
                    chart: chart,
                    el: el,
                    width: el.style.width,
                    maxWidth: el.style.maxWidth,
                    height: el.style.height,
                    computedHeight: rect.height,
                    animation: chart.options.animation,
                    xFont: xTicks ? xTicks.font : null,
                    yFont: yTicks ? yTicks.font : null,
                    colors: collectChartColors(chart)
                };
            });
        }

        printColorOverride = true;

        charts.forEach(function (chart) {
            var el = chart.canvas.parentElement;
            el.style.width = '100%';
            el.style.maxWidth = '100%';
            el.style.height = '260px';

            chart.options.animation = false;

            var xTicks = chart.options.scales && chart.options.scales.x && chart.options.scales.x.ticks;
            var yTicks = chart.options.scales && chart.options.scales.y && chart.options.scales.y.ticks;
            if (xTicks) {
                xTicks.font = { size: 12 };
                xTicks.autoSkip = true;
                xTicks.maxRotation = 0;
            }
            if (yTicks) {
                yTicks.font = { size: 11 };
            }

            applyPrintChartColors(chart);
            chart.resize();
            chart.update('none');
        });
    }

    function restoreChartsAfterPrint() {
        if (!printChartState) return;

        printColorOverride = false;

        printChartState.forEach(function (state) {
            var chart = state.chart;
            var el = state.el;
            el.style.width = state.width;
            el.style.maxWidth = state.maxWidth;
            // Kunci tinggi asli dulu supaya Chart.js tidak menghitung tinggi
            // dari canvas yang masih 260px (menyebabkan chart tetap gepeng).
            el.style.height = state.computedHeight > 0 ? state.computedHeight + 'px' : state.height;

            chart.options.animation = state.animation;

            var xTicks = chart.options.scales && chart.options.scales.x && chart.options.scales.x.ticks;
            var yTicks = chart.options.scales && chart.options.scales.y && chart.options.scales.y.ticks;
            if (xTicks && state.xFont !== null) {
                xTicks.font = state.xFont;
            }
            if (yTicks && state.yFont !== null) {
                yTicks.font = state.yFont;
            }

            restoreChartColors(chart, state.colors);
            chart.resize();
            chart.update('none');

            // Setelah canvas kembali ke tinggi asli, lepas lagi tinggi
            // sementara agar container tetap mengikuti CSS responsif.
            window.requestAnimationFrame(function () {
                el.style.height = state.height;
                chart.resize();
                chart.update('none');
            });
        });

        printChartState = null;
    }

    window.addEventListener('beforeprint', resizeChartsForPrint);
    window.addEventListener('afterprint', restoreChartsAfterPrint);

    // DevTools "Emulate CSS media type: print" tidak memicu beforeprint,
    // jadi perubahan media juga dipantau lewat matchMedia.
    if (window.matchMedia) {
        var printMediaQuery = window.matchMedia('print');
        var onPrintMediaChange = function (e) {
            if (e.matches) {
                resizeChartsForPrint();
            } else {
                restoreChartsAfterPrint();
            }
        };
        if (printMediaQuery.addEventListener) {
            printMediaQuery.addEventListener('change', onPrintMediaChange);
        } else if (printMediaQuery.addListener) {
            printMediaQuery.addListener(onPrintMediaChange);
        }
    }

    // Warna kanvas tidak bisa membaca var(--x), jadi saat tema berganti
    // grafik harus diwarnai ulang dengan nilai token yang di-resolve ulang.
    document.addEventListener('afwo:themechange', function () {
        if (chartOmsetInst) {
            const ds = chartOmsetInst.data.datasets[0];
            ds.backgroundColor = [tok('--gold-800', '#5C4033'), tok('--gold-600', '#C6A15B')];
            ds.borderColor = [tok('--gold-900', '#3D2A1C'), tok('--gold-700', '#8A6A2E')];
            chartOmsetInst.update('none');
        }

        if (chartKategoriInst) {
            const sets = chartKategoriInst.data.datasets;
            if (sets[0]) sets[0].backgroundColor = tok('--text-3', '#9CA3AF');
            if (sets[1]) {
                sets[1].backgroundColor = sets[1].data.map(function (v, i) {
                    const lalu = sets[0] ? sets[0].data[i] : 0;
                    return v >= lalu ? tok('--status-ok', '#047857') : tok('--status-danger', '#DC2626');
                });
            }
            chartKategoriInst.update('none');
        }

        if (chartTrenInst) {
            const ds = chartTrenInst.data.datasets[0];
            ds.backgroundColor = tok('--gold-600', '#B45309');
            ds.borderColor = tok('--gold-700', '#8A6A2E');
            chartTrenInst.options.scales.x.ticks.color = tok('--text-3', '#6F6B63');
            chartTrenInst.options.scales.y.ticks.color = tok('--text-3', '#6F6B63');
            chartTrenInst.options.scales.y.grid.color = tok('--border', '#E8E5DD');
            chartTrenInst.update('none');
        }
    });

    // Countdown cooldown Generate Ulang
    const cdLabel = document.getElementById('insightCooldownLabel');
    if (cdLabel) {
        let sisa = parseInt(cdLabel.dataset.sisa, 10);
        const cdText = document.getElementById('insightCountdown');
        const btnGenerate = document.getElementById('btnGenerateInsight');

        const timer = setInterval(function () {
            sisa--;
            if (sisa <= 0) {
                clearInterval(timer);
                cdLabel.remove();
                if (btnGenerate) {
                    btnGenerate.disabled = false;
                    btnGenerate.removeAttribute('title');
                }
            } else {
                cdText.textContent = Math.floor(sisa / 60) + ':' + String(sisa % 60).padStart(2, '0');
            }
        }, 1000);
    }

    // Export CSV dari tabel riwayat
    const btnExport = document.getElementById('btn-export-csv');
    if (btnExport) {
        btnExport.addEventListener('click', function () {
            const rows = [['No Struk', 'Pelanggan', 'Tanggal', 'Layanan & Stylist', 'Total Biaya', 'Metode Bayar', 'Status']];
            document.querySelectorAll('.daily-trans-table tbody tr').forEach(function (tr) {
                const cells = [];
                tr.querySelectorAll('td').forEach(function (td) {
                    cells.push(td.innerText.replace(/\s+/g, ' ').trim());
                });
                rows.push(cells);
            });
            const csv = rows.map(function (r) {
                return r.map(function (c) { return '"' + String(c).replace(/"/g, '""') + '"'; }).join(';');
            }).join('\r\n');
            const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'laporan-penjualan.csv';
            a.click();
            URL.revokeObjectURL(a.href);
        });
    }

    // Cetak laporan
    const btnGenerateReport = document.getElementById('btn-generate-report');
    if (btnGenerateReport) {
        btnGenerateReport.addEventListener('click', function () {
            resizeChartsForPrint();
            window.requestAnimationFrame(function () {
                window.setTimeout(function () {
                    window.print();
                }, 100);
            });
        });
    }
});
</script>
@endpush