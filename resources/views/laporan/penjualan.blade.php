@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Penjualan</h1>
            <p class="mt-1 text-sm text-gray-500">Analisa penjualan dan tren omset.</p>
        </div>
        <button onclick="window.print()" class="inline-flex shrink-0 items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 no-print">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.penjualan'),
        'resetUrl' => route('laporan.penjualan', ['preset' => 'bulan-ini']),
        'showExtraFilters' => true,
        'showJenisPengerjaan' => true,
        'showMetode' => true,
    ])

    @if (session('success'))
        <div class="mt-6 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-md bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif
    @if (session('insight_info'))
        <div class="mt-6 rounded-md bg-yellow-50 p-4 text-sm text-yellow-700">{{ session('insight_info') }}</div>
    @endif

    {{-- AI Insight --}}
    <div class="mt-6 rounded-lg border border-purple-200 bg-gradient-to-br from-purple-50 to-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-100">
                    <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">AI Insight — Analisa Keuntungan</h2>
                    @if ($insight)
                        <p class="text-xs text-gray-400">Terakhir digenerate: {{ $insight->dibuat_pada->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('laporan.insight.generate') }}" class="shrink-0 no-print">
                @csrf
                <input type="hidden" name="periode" value="{{ $insightPeriode }}">
                <button type="submit"
                    @if ($insightCooldown) disabled title="Analisa baru saja digenerate, tunggu beberapa menit" @endif
                    class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-purple-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ $insight ? 'Generate Ulang' : 'Generate Analisa' }}
                </button>
            </form>
        </div>

        @if ($insight)
            <div class="mt-4 text-sm leading-relaxed text-gray-700 whitespace-pre-line">{{ $insight->konten_insight }}</div>
        @else
            <div class="mt-4 text-center text-sm text-gray-400">
                Belum ada analisa. Klik tombol "Generate Analisa" untuk mendapatkan insight dari AI.
            </div>
        @endif
    </div>

    {{-- Ringkasan --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Total Omset</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">Rp{{ number_format($totalOmset, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $dari }} s/d {{ $sampai }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Jumlah Transaksi</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $jumlahTransaksi }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Rata-rata per Transaksi</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">Rp{{ number_format($rataRata, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Chart Tren Harian --}}
    @if ($trenHarian->isNotEmpty())
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm no-print">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Tren Omset Harian</h2>
            <canvas id="chartTren" height="80"></canvas>
        </div>
    @endif

    {{-- Breakdown Kategori Layanan --}}
    @if ($breakdownKategori->isNotEmpty())
        <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Breakdown per Kategori Layanan</h2>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Jumlah Item</th>
                        <th class="px-4 py-3 text-right">Total Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($breakdownKategori as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row->kategori }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ $row->jumlah_item }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($row->total_subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif

    {{-- Tabel Transaksi --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Daftar Transaksi ({{ $transaksis->count() }})</h2>
        </div>
        @if ($transaksis->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-400">Tidak ada transaksi untuk periode dan filter ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">No Struk</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Jenis</th>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">Komisi Staf</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($transaksis as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('transaksi.show', $t) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $t->no_struk }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $t->pelanggan->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->waktu_kunjungan->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full {{ $t->jenis_pengerjaan === 'berdua' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-700' }} px-2 py-0.5 text-xs font-medium">
                                        {{ ucfirst($t->jenis_pengerjaan) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $t->labelMetode() }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($t->komisiTransaksi->isEmpty())
                                        <span class="text-gray-400">—</span>
                                    @else
                                        <div class="flex flex-col items-end gap-0.5">
                                            @foreach ($t->komisiTransaksi as $k)
                                                <span class="text-xs text-gray-600">{{ $k->staf->nama ?? 'Staf' }}: <span class="font-medium text-gray-900">Rp{{ number_format((float) $k->jumlah_komisi, 0, ',', '.') }}</span></span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('chartTren');
    if (!canvas) return;

    const labels = @json($trenHarian->pluck('tanggal'));
    const data = @json($trenHarian->pluck('total'));

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Omset',
                data: data,
                backgroundColor: 'rgba(124, 58, 237, 0.7)',
                borderColor: 'rgb(124, 58, 237)',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp' + (v / 1000).toFixed(0) + 'rb'
                    }
                }
            }
        }
    });
});
</script>
@endpush
