@extends('layouts.app')

@section('title', 'Absensi Karyawan')

@section('content')
    <div>
        <h1 class="text-2xl font-bold text-text-primary">Absensi Karyawan</h1>
        <p class="mt-1 text-sm text-text-muted">
            Catat kehadiran harian. Uang makan Rp25.000 dibayar per hari hadir untuk karyawan skema per layanan (karyawan % omset harian tidak mendapat uang makan).
        </p>
    </div>

    {{-- Catat kehadiran untuk tanggal tertentu --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-card shadow-sm">
        <form action="{{ route('absensi.store') }}" method="POST">
            @csrf
            <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Catat Kehadiran</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Pilih tanggal, centang karyawan yang hadir, lalu simpan.</p>
                </div>
                <label class="block text-xs font-medium text-gray-500">
                    Tanggal
                    <input type="date" name="tanggal" value="{{ $tanggalInput }}" required
                           class="mt-1 block rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:outline-none focus:ring-accent/30">
                </label>
            </div>

            @if ($karyawans->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-400">Belum ada karyawan terdaftar. Tambahkan karyawan dahulu.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Karyawan</th>
                                <th class="px-6 py-3">Skema</th>
                                <th class="px-6 py-3 text-center">Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($karyawans as $k)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                                    <td class="px-6 py-3">
                                        @if ($k->skema_komisi === 'persen_omset_harian')
                                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">% Omset Harian</span>
                                        @else
                                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Per Layanan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input type="checkbox" name="hadir[{{ $k->id }}]" value="1"
                                               @checked($kehadiranHariIni[$k->id] ?? false)
                                               class="h-4 w-4 rounded border-gray-300 text-accent-text focus:ring-accent/30">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400">
                        Karyawan <span class="font-medium text-gray-600">% Omset Harian</span> tidak menerima uang makan.
                    </p>
                    <button type="submit"
                            class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                        Simpan Kehadiran
                    </button>
                </div>
            @endif
        </form>
    </div>

    {{-- Rekap bulanan --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-card shadow-sm">
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Rekap Kehadiran &amp; Uang Makan</h2>
                <p class="mt-0.5 text-xs text-gray-500">Uang makan Rp25.000 per kehadiran, dijumlahkan per bulan dan ikut masuk ke laporan pendapatan karyawan.</p>
            </div>
            <form action="{{ route('absensi.index') }}" method="GET" class="flex items-end gap-2">
                <div>
                    <label class="block text-xs font-medium text-gray-500">Bulan</label>
                    <select name="bulan" onchange="this.form.submit()"
                            class="mt-1 block rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:outline-none focus:ring-accent/30">
                        @foreach ($pilihanBulan as $p)
                            <option value="{{ $p['value'] }}" {{ $p['value'] === $bulanInput ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    Tampilkan
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Karyawan</th>
                        <th class="px-6 py-3">Skema</th>
                        <th class="px-6 py-3 text-center">Total Hadir</th>
                        <th class="px-6 py-3 text-right">Uang Makan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($ringkasan as $row)
                        @php $k = $row['karyawan']; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                            <td class="px-6 py-3">
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">% Omset Harian</span>
                                @else
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Per Layanan</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center font-medium text-gray-900">{{ $row['jumlah_hadir'] }}</td>
                            <td class="px-6 py-3 text-right">
                                @if ($row['uang_makan'] > 0)
                                    <span class="font-semibold text-accent-text">Rp{{ number_format($row['uang_makan'], 0, ',', '.') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($ringkasan->isNotEmpty())
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-sm">
                        <tr>
                            <td class="px-6 py-3 font-bold text-gray-900" colspan="2">TOTAL</td>
                            <td class="px-6 py-3 text-center font-semibold text-gray-900">{{ $totalHadir }}</td>
                            <td class="px-6 py-3 text-right font-bold text-text-primary">Rp{{ number_format($totalUangMakan, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection