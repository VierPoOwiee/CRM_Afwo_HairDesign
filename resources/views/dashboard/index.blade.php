@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div>
        <h1 class="text-2xl font-semibold text-text-primary">Selamat datang kembali, {{ Auth::user()->name }}</h1>
        <p class="mt-1 text-sm text-text-muted">Ringkasan aktivitas hari ini, {{ now()->translatedFormat('l, d F Y') }}.</p>
    </div>

    {{-- STAT CARDS --}}
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-card-warm p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-accent-text">Pemasukan Hari Ini</p>
                <svg class="w-4 h-4 text-accent-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
            </div>
            <p class="num mt-3 text-2xl font-semibold text-[#5C4419]">Rp{{ number_format((float) $totalPemasukanHariIni, 0, ',', '.') }}</p>
            @if ($persenPerubahan !== null && $persenPerubahan != 0)
                <span class="inline-block mt-3 rounded-full bg-white/60 text-[#6B4F17] text-xs font-medium px-2.5 py-1">
                    {{ $persenPerubahan > 0 ? '+' : '' }}{{ number_format($persenPerubahan, 0, ',', '.') }}% dari kemarin
                </span>
            @else
                <span class="inline-block mt-3 rounded-full bg-white/60 text-[#6B4F17] text-xs font-medium px-2.5 py-1">Data terbaru</span>
            @endif
        </div>

        <div class="rounded-2xl bg-card border border-card-warm-border p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Pelanggan Baru</p>
                <svg class="w-4 h-4 text-text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="9" cy="7" r="3.2"/></svg>
            </div>
            <p class="num mt-3 text-2xl font-semibold text-text-primary">{{ $pelangganBaruHariIni }}</p>
            <p class="mt-3 text-xs text-text-muted">pelanggan baru hari ini</p>
        </div>

        <div class="rounded-2xl bg-card border border-card-warm-border p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Transaksi Hari Ini</p>
                <svg class="w-4 h-4 text-text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>
            </div>
            <p class="num mt-3 text-2xl font-semibold text-text-primary">{{ $transaksiHariIni->count() }}</p>
            <p class="mt-3 text-xs text-text-muted">transaksi selesai</p>
        </div>

        <div class="rounded-2xl bg-card border border-card-warm-border p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-text-muted">Stok Menipis</p>
                <svg class="w-4 h-4 text-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v5"/></svg>
            </div>
            <p class="num mt-3 text-2xl font-semibold text-danger">{{ $produkStokMenipis->count() }}</p>
            <p class="mt-3 text-xs text-text-muted">produk perlu direstock</p>
        </div>
    </div>

    {{-- MAIN GRID --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- LEFT (2/3) --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- TREND CHART --}}
            <div class="rounded-2xl bg-card border border-card-warm-border p-5">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-medium text-text-primary">Tren Pemasukan</p>
                    <span class="text-xs text-text-muted">7 hari terakhir</span>
                </div>
                @php
                    $chartW = 560;
                    $chartH = 120;
                    $padX = 4;
                    $padY = 6;
                    $n = $pemasukan7Hari->count();
                    $points = [];
                    foreach ($pemasukan7Hari as $i => $d) {
                        $x = $n > 1 ? $padX + ($chartW - 2 * $padX) * ($i / ($n - 1)) : $chartW / 2;
                        $y = $chartH - $padY - (($chartH - 2 * $padY) * ((float) $d['total'] / $maxTotal));
                        $points[] = round($x, 1) . ',' . round($y, 1);
                    }
                    $polyline = implode(' ', $points);
                @endphp
                <svg viewBox="0 0 560 120" class="w-full h-24 mt-2" preserveAspectRatio="none">
                    <polyline points="{{ $polyline }}" fill="none" stroke="#C6A15B" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <polygon points="{{ $polyline }} 560,120 0,120" fill="#C6A15B" opacity="0.12"/>
                </svg>
                <div class="flex justify-between text-[11px] text-text-muted mt-1">
                    @foreach ($pemasukan7Hari as $d)
                        <span>{{ $d['label'] }}</span>
                    @endforeach
                </div>
            </div>

            {{-- TRANSAKSI HARI INI --}}
            <div class="rounded-2xl bg-card border border-card-warm-border overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-card-warm-border">
                    <p class="text-sm font-medium text-text-primary">Transaksi Hari Ini</p>
                    <a href="{{ route('transaksi.index') }}" class="text-xs font-medium text-accent-text">Lihat semua</a>
                </div>
                @if ($transaksiHariIni->isEmpty())
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-text-muted">Belum ada transaksi hari ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] uppercase tracking-wide text-text-muted bg-surface">
                                    <th class="px-5 py-2.5 font-medium">No Struk</th>
                                    <th class="px-5 py-2.5 font-medium">Pelanggan</th>
                                    <th class="px-5 py-2.5 font-medium">Layanan</th>
                                    <th class="px-5 py-2.5 font-medium">Waktu</th>
                                    <th class="px-5 py-2.5 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1EAE0]">
                                @foreach ($transaksiHariIni->take(6) as $t)
                                    @php
                                        $itemLabels = collect()
                                            ->concat($t->details->where('tipe_item', 'layanan')->map(fn ($d) => $d->layanan->nama_layanan ?? null))
                                            ->concat($t->details->where('tipe_item', 'produk')->map(fn ($d) => $d->produk->nama_produk ?? null))
                                            ->filter()
                                            ->take(2)
                                            ->implode(', ');
                                    @endphp
                                    <tr class="transition-colors duration-150 hover:bg-card-hover">
                                        <td class="px-5 py-3 font-medium text-[#4A1F27]">
                                            <a href="{{ route('transaksi.show', $t) }}" class="hover:text-accent-text">{{ $t->no_struk }}</a>
                                        </td>
                                        <td class="px-5 py-3 text-text-secondary">{{ $t->pelanggan->nama ?? '-' }}</td>
                                        <td class="px-5 py-3 text-text-muted">{{ $itemLabels }}</td>
                                        <td class="px-5 py-3 text-text-muted">{{ $t->waktu_kunjungan->format('H:i') }}</td>
                                        <td class="num px-5 py-3 text-right font-medium text-text-primary">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT (1/3) --}}
        <div class="space-y-6">

            {{-- STOK MENIPIS --}}
            <div class="rounded-2xl bg-card border border-card-warm-border overflow-hidden">
                <div class="px-5 py-4 border-b border-card-warm-border">
                    <p class="text-sm font-medium text-text-primary">Stok Produk Menipis</p>
                    <p class="mt-0.5 text-xs text-text-muted">Di bawah batas minimum ({{ App\Models\Produk::STOK_MENIPIS }})</p>
                </div>
                @if ($produkStokMenipis->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-text-muted">Semua stok produk aman.</p>
                    </div>
                @else
                    <ul class="divide-y divide-[#F1EAE0]">
                        @foreach ($produkStokMenipis as $produk)
                            <li class="flex items-center justify-between px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-lg bg-surface flex items-center justify-center text-xs font-semibold text-[#4A1F27]">
                                        {{ strtoupper(substr(collect(explode(' ', $produk->nama_produk))->first(), 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-text-primary">{{ $produk->nama_produk }}</p>
                                        <p class="text-xs text-text-muted">@if ($produk->merek){{ $produk->merek }} · @endif{{ $produk->labelKategori() }}</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-danger-bg text-danger text-xs font-semibold px-2.5 py-1">{{ $produk->stok }} {{ $produk->satuan }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- LAYANAN TERPOPULER --}}
            <div class="rounded-2xl bg-card border border-card-warm-border overflow-hidden">
                <div class="px-5 py-4 border-b border-card-warm-border">
                    <p class="text-sm font-medium text-text-primary">Layanan Terpopuler</p>
                    <p class="mt-0.5 text-xs text-text-muted">Minggu ini</p>
                </div>
                @if ($layananTerpopuler->isEmpty())
                    <div class="px-5 py-8 text-center">
                        <p class="text-sm text-text-muted">Belum ada data layanan minggu ini.</p>
                    </div>
                @else
                    <ul class="px-5 py-4 space-y-3">
                        @foreach ($layananTerpopuler as $layanan)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-text-primary">{{ $layanan['nama'] }}</span>
                                <span class="text-text-muted num">{{ $layanan['jumlah'] }}x</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </div>
@endsection