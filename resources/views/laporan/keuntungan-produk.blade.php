@extends('layouts.app')

@section('title', 'Laporan Keuntungan Produk')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Laporan Keuntungan Produk</h1>
        <p class="mt-1 text-sm text-gray-500">
            Selisih harga jual dengan modal (harga beli/restock) yang tersimpan saat transaksi.
            Modal rata-rata dihitung otomatis dari riwayat pembelian.</p>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.keuntungan-produk'),
        'resetUrl' => route('laporan.keuntungan-produk', ['preset' => 'bulan-ini']),
    ])

    {{-- Filter kategori produk --}}
    <form action="{{ route('laporan.keuntungan-produk') }}" method="GET" class="mt-3">
        <input type="hidden" name="preset" value="{{ $preset }}">
        <input type="hidden" name="dari" value="{{ $dari }}">
        <input type="hidden" name="sampai" value="{{ $sampai }}">
        <div class="flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 bg-card px-4 py-3 shadow-sm">
            <span class="text-xs font-medium text-gray-500">Kategori Produk:</span>
            <a href="{{ route('laporan.keuntungan-produk', ['preset' => $preset, 'dari' => $dari, 'sampai' => $sampai]) }}"
               class="rounded-full px-3 py-1 text-xs font-medium {{ $kategoriFilter === '' ? 'bg-dark text-white' : 'bg-badge-neutral-bg text-badge-neutral-text hover:bg-gray-200' }}">
                Semua
            </a>
            @foreach ($kategoriList as $key => $label)
                <a href="{{ route('laporan.keuntungan-produk', ['preset' => $preset, 'dari' => $dari, 'sampai' => $sampai, 'kategori_produk' => $key]) }}"
                   class="rounded-full px-3 py-1 text-xs font-medium {{ $kategoriFilter === $key ? 'bg-dark text-white' : 'bg-badge-neutral-bg text-badge-neutral-text hover:bg-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- Tabs --}}
    <div class="mt-6 flex flex-wrap items-center gap-2 border-b border-gray-200 pb-0">
        <button type="button" data-tab="retail" onclick="switchTab('retail')"
                class="tab-btn tab-btn-retail rounded-t-lg px-4 py-2 text-sm font-medium border border-b-0">Mode 1 &middot; Retail (Dijual Per PCS)</button>
        <button type="button" data-tab="treatment" onclick="switchTab('treatment')"
                class="tab-btn tab-btn-treatment rounded-t-lg px-4 py-2 text-sm font-medium border border-b-0">Mode 2 &middot; Treatment (Dijual per Layanan)</button>
    </div>

    @php
        $rp = fn ($n) => 'Rp'.number_format((float) $n, 0, ',', '.');
        $pct = fn ($n) => number_format((float) $n, 1, ',', '.').' %';
    @endphp

    {{-- ===== Mode 1: Retail ===== --}}
    <div id="tab-panel-retail" class="tab-panel mt-6 space-y-6">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Omset Produk</p>
                <p class="mt-1 text-lg font-bold text-gray-900">{{ $rp($retailTotals['omset']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Modal (Harga Beli)</p>
                <p class="mt-1 text-lg font-bold text-gray-900">{{ $rp($retailTotals['modal']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Keuntungan</p>
                <p class="mt-1 text-lg font-bold {{ $retailTotals['keuntungan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $rp($retailTotals['keuntungan']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Margin</p>
                <p class="mt-1 text-lg font-bold {{ $retailTotals['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $pct($retailTotals['margin']) }}</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-card shadow-sm">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Keuntungan per Produk (Retail)</h2>
                <p class="text-xs text-gray-400">{{ intval($retailTotals['qty']) }} unit terjual dalam {{ count($retailRows) }} produk, {{ $retailTotals['jumlah'] }} item transaksi.</p>
            </div>
            @if ($retailRows->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-medium text-text-secondary">Belum ada penjualan produk retail di periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-400">
                                <th class="px-4 py-2.5 font-medium">Produk</th>
                                <th class="py-2.5 pr-4 font-medium">Kategori</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Qty</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Omset</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Modal</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Keuntungan</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($retailRows as $r)
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-900">
                                        <span class="font-medium">{{ $r['nama_produk'] }}</span>
                                        @if ($r['merek']) <span class="text-xs text-gray-400">({{ $r['merek'] }})</span> @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-500">{{ $r['kategori'] }}</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-900">{{ intval($r['qty']) }} {{ $r['satuan'] }}</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-900">{{ $rp($r['omset']) }}</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-500">{{ $rp($r['modal']) }}</td>
                                    <td class="py-2.5 pr-4 text-right font-medium {{ $r['keuntungan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $rp($r['keuntungan']) }}</td>
                                    <td class="py-2.5 pr-4 text-right {{ $r['keuntungan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $r['omset'] > 0 ? $pct(($r['keuntungan'] / $r['omset']) * 100) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== Mode 2: Treatment ===== --}}
    <div id="tab-panel-treatment" class="tab-panel mt-6 space-y-6 hidden">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Omset Produk Terpakai</p>
                <p class="mt-1 text-lg font-bold text-gray-900">{{ $rp($layananTotals['omset']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Modal Bahan</p>
                <p class="mt-1 text-lg font-bold text-gray-900">{{ $rp($layananTotals['modal']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Keuntungan</p>
                <p class="mt-1 text-lg font-bold {{ $layananTotals['keuntungan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $rp($layananTotals['keuntungan']) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-card p-4 shadow-sm">
                <p class="text-xs font-medium text-gray-500">Margin</p>
                <p class="mt-1 text-lg font-bold {{ $layananTotals['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $pct($layananTotals['margin']) }}</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-card shadow-sm">
            <div class="border-b border-gray-100 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Keuntungan per Produk (Treatment)</h2>
                <p class="text-xs text-gray-400">Dihitung dari total ml produk yang terpakai pada layanan: nilai jual produk (omset) dikurangi modal bahan.
                {{ $kategoriFilter !== '' ? 'Diffilter kategori produk: '.$kategoriList[$kategoriFilter].'.' : 'Semua kategori produk.' }}</p>
            </div>
            @if ($layananRows->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm font-medium text-text-secondary">Belum ada pemakaian produk pada layanan di periode ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-400">
                                <th class="px-4 py-2.5 font-medium">Produk</th>
                                <th class="py-2.5 pr-4 font-medium">Kategori</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Total Pemakaian (ml)</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Omset</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Modal Bahan</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Keuntungan</th>
                                <th class="py-2.5 pr-4 font-medium text-right">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($layananRows as $r)
                                <tr>
                                    <td class="px-4 py-2.5 text-gray-900">
                                        <span class="font-medium">{{ $r['nama_produk'] }}</span>
                                        @if ($r['merek']) <span class="text-xs text-gray-400">({{ $r['merek'] }})</span> @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-500">{{ $r['kategori'] }}</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-900">{{ number_format((float) $r['total_ml'], 0, ',', '.') }} ml</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-900">{{ $rp($r['omset']) }}</td>
                                    <td class="py-2.5 pr-4 text-right text-gray-500">{{ $rp($r['modal']) }}</td>
                                    <td class="py-2.5 pr-4 text-right font-medium {{ $r['keuntungan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $rp($r['keuntungan']) }}</td>
                                    <td class="py-2.5 pr-4 text-right {{ $r['keuntungan'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $r['omset'] > 0 ? $pct(($r['keuntungan'] / $r['omset']) * 100) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Grafik tren 6 bulan --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
        <h2 class="text-sm font-semibold text-gray-900">Tren Keuntungan Produk 6 Bulan Terakhir</h2>
        <p class="mt-0.5 text-xs text-gray-400">Retail + treatment (nilai jual produk terpakai dikurangi modal bahan).</p>
        <div class="relative h-64 mt-4">
            <canvas id="chartKeuntunganProduk"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    function switchTab(name) {
        document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.add('hidden'); });
        document.getElementById('tab-panel-' + name).classList.remove('hidden');
        document.querySelectorAll('.tab-btn').forEach(function (b) {
            b.classList.toggle('bg-white', b.dataset.tab !== name);
            b.classList.toggle('bg-gray-200', b.dataset.tab !== name);
            b.classList.toggle('text-gray-700', b.dataset.tab !== name);
            b.classList.toggle('text-gray-900', b.dataset.tab === name);
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
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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