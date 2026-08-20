@extends('layouts.app')

@section('title', 'Rekap Komisi Staf')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rekap Komisi Staf</h1>
            <p class="mt-1 text-sm text-gray-500">Total komisi yang harus dibayarkan ke semua staf.</p>
        </div>
        <button onclick="window.print()" class="inline-flex shrink-0 items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 no-print">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.rekap-komisi'),
        'resetUrl' => route('laporan.rekap-komisi', ['preset' => 'bulan-ini']),
        'showExtraFilters' => false,
    ])

    {{-- Total Grand --}}
    <div class="mt-6 rounded-lg border-2 border-violet-200 bg-violet-50 p-5 shadow-sm text-center">
        <p class="text-sm font-medium text-violet-700">Total Komisi yang Harus Dibayar</p>
        <p class="mt-1 text-3xl font-bold text-violet-900">Rp{{ number_format($totalBayarSemuaStaf, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-violet-500">Periode: {{ $dari }} s/d {{ $sampai }}</p>
    </div>

    @if ($grandTotal->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data komisi untuk periode ini.</p>
        </div>
    @else
        {{-- Rekap Tabel --}}
        <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Rekap per Staf</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Staf</th>
                            <th class="px-4 py-3">Skema</th>
                            <th class="px-4 py-3 text-right">Komisi per Layanan</th>
                            <th class="px-4 py-3 text-right">Komisi Harian (%)</th>
                            <th class="px-4 py-3 text-right">Total Komisi</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($grandTotal as $row)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="document.getElementById('detail-{{ $row['id_staf'] }}').classList.toggle('hidden')">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row['nama_staf'] }}</td>
                                <td class="px-4 py-3">
                                    @if ($row['skema'] === 'persen_omset_harian')
                                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">% Omset Harian</span>
                                    @else
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Per Layanan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    @if ($row['total_per_layanan'] > 0)
                                        Rp{{ number_format($row['total_per_layanan'], 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">
                                    @if ($row['total_persen_harian'] > 0)
                                        Rp{{ number_format($row['total_persen_harian'], 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($row['total_keseluruhan'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-gray-400">
                                    <svg class="h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Detail per Staf (expandable) --}}
        <div class="mt-6 space-y-6">
            @foreach ($grandTotal as $row)
                <div id="detail-{{ $row['id_staf'] }}" class="hidden rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $row['nama_staf'] }}</h3>
                            <p class="text-xs text-gray-500">
                                @if ($row['skema'] === 'persen_omset_harian')
                                    Skema: Persen Omset Harian
                                @else
                                    Skema: Per Layanan
                                @endif
                            </p>
                        </div>
                        <p class="text-sm font-bold text-gray-900">Total: Rp{{ number_format($row['total_keseluruhan'], 0, ',', '.') }}</p>
                    </div>

                    @if ($row['skema'] === 'persen_omset_harian' && $row['id_staf'])
                        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 no-print">
                            <form action="{{ route('laporan.rekap-komisi.hitung-ulang') }}" method="POST" class="flex items-end gap-3">
                                @csrf
                                <input type="hidden" name="id_staf" value="{{ $row['id_staf'] }}">
                                <input type="hidden" name="preset" value="{{ $preset }}">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Hitung Ulang Komisi Harian</label>
                                    <input type="date" name="tanggal" value="{{ $sampai }}"
                                           class="mt-1 block rounded-md border-gray-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                                </div>
                                <button type="submit"
                                        class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-blue-700">
                                    Hitung Ulang
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($row['skema'] === 'persen_omset_harian')
                        @php $harianItems = $komisiHarian->get($row['id_staf'], collect()); @endphp
                        @if ($harianItems->isNotEmpty())
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">Tanggal</th>
                                        <th class="px-4 py-2 text-right">Omset Dasar</th>
                                        <th class="px-4 py-2 text-right">Persen</th>
                                        <th class="px-4 py-2 text-right">Komisi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($harianItems as $h)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-gray-700">{{ $h->tanggal->format('d M Y') }}</td>
                                            <td class="px-4 py-2 text-right text-gray-900">Rp{{ number_format($h->total_omset_dasar, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 text-right text-gray-700">{{ $h->persen }}%</td>
                                            <td class="px-4 py-2 text-right font-medium text-gray-900">Rp{{ number_format($h->jumlah_komisi, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="px-6 py-4 text-sm text-gray-400">Tidak ada data komisi harian untuk periode ini.</p>
                        @endif
                    @else
                        @php $layananItems = $komisiPerLayanan->get($row['id_staf'], collect()); @endphp
                        @if ($layananItems->isNotEmpty())
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">No Struk</th>
                                        <th class="px-4 py-2">Tanggal</th>
                                        <th class="px-4 py-2 text-right">Komisi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($layananItems as $kt)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2">
                                                <a href="{{ route('transaksi.show', $kt->transaksi) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                                    {{ $kt->transaksi->no_struk ?? '-' }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 text-gray-700">{{ $kt->transaksi->waktu_kunjungan->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-4 py-2 text-right font-medium text-gray-900">Rp{{ number_format($kt->jumlah_komisi, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="px-6 py-4 text-sm text-gray-400">Tidak ada data komisi per layanan untuk periode ini.</p>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endsection
