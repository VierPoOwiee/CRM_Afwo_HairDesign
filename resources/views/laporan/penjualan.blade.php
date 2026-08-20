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
