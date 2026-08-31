@extends('layouts.app')

@section('title', 'Laporan Komisi')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Laporan Komisi</h1>
            <p class="mt-1 text-sm text-gray-500">Rekap komisi per staf per periode.</p>
        </div>
    </div>

    <div class="rounded-lg bg-card p-6 shadow-sm">
        <form action="{{ route('laporan-komisi.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Staf</label>
                <select name="id_staf"
                        class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                    <option value="">Semua Staf</option>
                    @foreach ($karyawans as $k)
                        <option value="{{ $k->id }}" {{ $stafId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                    Filter
                </button>
            </div>
        </form>
    </div>

    @if ($grandTotal->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data komisi untuk periode ini.</p>
        </div>
    @else
        <div class="mt-6 rounded-lg border border-gray-200 bg-card shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Rekap Komisi per Staf</h2>
                <p class="mt-0.5 text-xs text-gray-500">Periode: {{ $dari }} s/d {{ $sampai }}</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Staf</th>
                        <th class="px-4 py-3">Skema</th>
                        <th class="px-4 py-3 text-right">Komisi per Layanan</th>
                        <th class="px-4 py-3 text-right">Komisi Harian (%)</th>
                        <th class="px-4 py-3 text-right">Total Komisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($grandTotal as $row)
                        <tr class="hover:bg-gray-50">
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
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total Semua Staf</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">Rp{{ number_format($grandTotal->sum('total_keseluruhan'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Detail per staf --}}
        <div class="mt-6 space-y-6">
            @foreach ($grandTotal as $row)
                @php
                    $stafKomisi = $rekap->where('id_staf', $row['id_staf']);
                @endphp
                <div class="rounded-lg border border-gray-200 bg-card shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $row['nama_staf'] }}</h3>
                            <p class="text-xs text-gray-500">
                                @if ($row['skema'] === 'persen_omset_harian')
                                    Skema: Persen Omset Harian ({{ $row['skema'] }})
                                @else
                                    Skema: Per Layanan
                                @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">Total: Rp{{ number_format($row['total_keseluruhan'], 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @if ($row['skema'] === 'persen_omset_harian' && $row['id_staf'])
                        <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
                            <form action="{{ route('laporan-komisi.hitung-ulang') }}" method="POST" class="flex items-end gap-3">
                                @csrf
                                <input type="hidden" name="id_staf" value="{{ $row['id_staf'] }}">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Hitung Ulang Komisi Harian</label>
                                    <input type="date" name="tanggal" value="{{ $sampai }}"
                                           class="mt-1 block rounded-lg border-gray-300 bg-card text-text-primary px-3 py-1.5 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                                </div>
                                <button type="submit"
                                        class="rounded-md bg-blue-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-blue-700">
                                    Hitung Ulang
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($row['skema'] === 'persen_omset_harian')
                        {{-- Show daily breakdown --}}
                        @php
                            $harianItems = $komisiHarian->get($row['id_staf'], collect());
                        @endphp
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
                        {{-- Show per-layanan breakdown --}}
                        @php
                            $layananItems = $komisiPerLayanan->get($row['id_staf'], collect());
                        @endphp
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
