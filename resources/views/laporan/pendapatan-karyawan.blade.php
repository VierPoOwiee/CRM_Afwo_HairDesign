@extends('layouts.app')

@section('title', 'Pendapatan Bulanan Karyawan')

@section('content')
    @include('laporan._nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pendapatan Bulanan Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Gaji pokok + komisi + uang makan yang diterima tiap karyawan dalam satu bulan.</p>
        </div>
        <button onclick="window.print()" class="inline-flex shrink-0 items-center gap-2 self-start rounded-lg bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 no-print sm:self-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    </div>

    {{-- Filter bulan --}}
    <div class="rounded-md bg-card p-4 shadow-sm no-print">
        <form action="{{ route('laporan.pendapatan-karyawan') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500">Bulan</label>
                <select name="bulan"
                        class="mt-1 block rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                    @foreach ($pilihanBulan as $p)
                        <option value="{{ $p['value'] }}" {{ $p['value'] === $bulanInput ? 'selected' : '' }}>{{ $p['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                Tampilkan
            </button>
        </form>
    </div>

    {{-- Total keseluruhan --}}
    <div class="mt-6 rounded-lg border-2 border-accent/30 bg-accent-light p-5 shadow-sm text-center">
        <p class="text-sm font-medium text-accent-text">Total Pendapatan Semua Karyawan — {{ $dariCarbon->format('F Y') }}</p>
        <p class="mt-1 text-3xl font-bold text-text-primary">Rp{{ number_format($grandTotal['total_pendapatan'], 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-text-muted">Komisi Rp{{ number_format($grandTotal['total_komisi'], 0, ',', '.') }} + Gaji Pokok Rp{{ number_format($grandTotal['gaji_pokok'], 0, ',', '.') }} + Uang Makan Rp{{ number_format($grandTotal['uang_makan'], 0, ',', '.') }}</p>
    </div>

    {{-- Tabel per karyawan --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-card shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Skema</th>
                        <th class="px-4 py-3 text-right">Komisi per Layanan</th>
                        <th class="px-4 py-3 text-right">Komisi Harian (%)</th>
                        <th class="px-4 py-3 text-right">Total Komisi</th>
                        <th class="px-4 py-3 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Hadir (hari)</th>
                        <th class="px-4 py-3 text-right">Uang Makan</th>
                        <th class="px-4 py-3 text-right">Total Pendapatan</th>
                        <th class="px-4 py-3 no-print"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($baris as $row)
                        @php $k = $row['karyawan']; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                            <td class="px-4 py-3">
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">% Omset Harian ({{ $k->persen_komisi_harian }}%)</span>
                                @else
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Per Layanan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                @if ($row['komisi_per_layanan'] > 0)
                                    Rp{{ number_format($row['komisi_per_layanan'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                @if ($row['komisi_persen_harian'] > 0)
                                    Rp{{ number_format($row['komisi_persen_harian'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($row['total_komisi'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                @if ($row['jumlah_hadir'] > 0)
                                    {{ $row['jumlah_hadir'] }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                @if ($row['uang_makan'] > 0)
                                    Rp{{ number_format($row['uang_makan'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-accent-text">Rp{{ number_format($row['total_pendapatan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right no-print">
                                <a href="{{ route('laporan.rekap-komisi.slip', [
                                        'karyawan' => $k->id,
                                        'preset' => 'custom',
                                        'dari' => $dariCarbon->toDateString(),
                                        'sampai' => $sampaiCarbon->toDateString(),
                                    ]) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-card px-2.5 py-1.5 text-xs font-medium text-accent-text ring-1 ring-inset ring-accent/30 hover:bg-accent-light">
                                    Slip Detail
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($baris->isNotEmpty())
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-sm">
                        <tr>
                            <td class="px-4 py-3 font-bold text-gray-900" colspan="2">TOTAL</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['komisi_per_layanan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['komisi_persen_harian'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($grandTotal['total_komisi'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['gaji_pokok'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $grandTotal['jumlah_hadir'] }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['uang_makan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-text-primary">Rp{{ number_format($grandTotal['total_pendapatan'], 0, ',', '.') }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <p class="mt-3 text-xs text-gray-400">
        Gaji pokok dibayar utuh per bulan (tidak di-pro-rata). Komisi dihitung dari transaksi berstatus selesai pada bulan terpilih.
        Uang makan Rp{{ number_format($uangMakanPerHari, 0, ',', '.') }} per hari hadir dari absensi, hanya untuk karyawan skema per layanan.
    </p>
@endsection
