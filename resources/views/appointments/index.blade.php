@extends('layouts.app')

@section('title', 'Data Appointment')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Data Appointment</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $appointments->total() }} janji temu terdaftar.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('appointment.index') }}" method="GET" class="flex w-full max-w-lg flex-wrap items-center gap-2">
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cari nama, service, no WA..."
                       class="block w-full min-w-0 flex-1 rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                       class="block rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <button type="submit"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    Cari
                </button>
                @if ($q !== '' || $tanggal !== null && $tanggal !== '')
                    <a href="{{ route('appointment.index') }}"
                       class="text-sm font-medium text-red-600 hover:text-red-800">
                        Reset
                    </a>
                @endif
            </form>
            <a href="{{ route('appointment.create') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                <span class="text-lg leading-none">+</span>
                <span class="hidden sm:inline">Tambah</span>
            </a>
        </div>
    </div>

    @if ($appointments->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-text-secondary">Belum ada data appointment.</p>
            @if ($q !== '' || $tanggal !== null && $tanggal !== '')
                <p class="mt-1 text-sm text-text-muted">Tidak ditemukan hasil untuk filter "<span class="font-medium">@if ($q !== ''){{ $q }}@else{{ $tanggal }}@endif</span>".</p>
                <a href="{{ route('appointment.index') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('appointment.create') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    Tambah appointment pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="rounded-lg border border-gray-200 bg-card overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Hari</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">No. WA</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($appointments as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700">{{ $a->hari() }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $a->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $a->nama }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $a->service }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $a->waktu }}</td>
                                <td class="px-4 py-3">
                                    @if ($a->no_wa)
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $a->no_wa) }}" target="_blank"
                                           class="text-success hover:underline">
                                            {{ $a->no_wa }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3 text-sm">
                                        <a href="{{ route('appointment.edit', $a) }}" class="font-medium text-accent-text hover:text-accent">
                                            Edit
                                        </a>
                                        <form action="{{ route('appointment.destroy', $a) }}" method="POST"
                                              onsubmit="return confirm('Hapus appointment &quot;{{ addslashes($a->nama) }}&quot;?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($appointments->hasPages())
            <div class="mt-4">
                {{ $appointments->links() }}
            </div>
        @endif
    @endif

    @php
        $ringkasanHari = \Carbon\Carbon::parse($tglRingkasan);
        $hariNama = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $ringkasanJudul = $hariNama[$ringkasanHari->dayOfWeek] . ', ' . $ringkasanHari->format('d M Y');
    @endphp

    <div class="mt-6 rounded-lg border border-gray-200 bg-card shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-text-primary">Jadwal {{ $ringkasanJudul }}</h2>
                <p class="mt-0.5 text-sm text-text-muted">Ringkasan layanan yang akan dilayani hari ini.</p>
            </div>
            <span class="shrink-0 rounded-full bg-accent-light px-2.5 py-1 text-xs font-medium text-accent-text">
                {{ $ringkasanHarian->count() }} janji temu
            </span>
        </div>

        @if ($ringkasanHarian->isEmpty())
            <div class="px-5 py-10 text-center">
                <p class="text-sm font-medium text-text-secondary">Tidak ada jadwal pada tanggal ini.</p>
                <p class="mt-1 text-sm text-text-muted">Semua slot waktu kosong.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($ringkasanHarian->groupBy('waktu') as $waktu => $jadwal)
                    <div class="flex flex-col gap-1 px-5 py-3 sm:flex-row sm:items-center sm:gap-4">
                        <div class="flex w-32 shrink-0 items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-accent-light text-sm font-semibold text-accent-text">
                                {{ substr($waktu, 0, 2) }}
                            </span>
                            <span class="text-sm font-semibold text-text-primary">
                                {{ substr($waktu, 0, 5) }} <span class="font-normal text-text-muted">WITA</span>
                            </span>
                        </div>
                        <div class="flex flex-1 flex-col gap-1">
                            @foreach ($jadwal as $j)
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium text-text-primary">{{ $j->nama }}</span>
                                    <span class="text-text-muted">—</span>
                                    <span class="text-gray-700">{{ $j->service }}</span>
                                    @if ($j->kategori)
                                        <span class="text-xs text-text-muted">({{ $j->kategori }})</span>
                                    @endif
                                </p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection