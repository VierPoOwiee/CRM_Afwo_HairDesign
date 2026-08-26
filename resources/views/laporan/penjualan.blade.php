@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
    @include('laporan._nav')
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
                    <h2 class="text-base font-semibold text-gray-900">AI Insight â€” Analisa Keuntungan</h2>
                    @if ($insight)
                        <p class="text-xs text-gray-400">Terakhir digenerate: {{ $insight->dibuat_pada->format('d M Y H:i') }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('laporan.insight.generate') }}" class="shrink-0 no-print">
                @csrf
                <input type="hidden" name="periode" value="{{ $insightPeriode }}">
                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
                    <button type="submit" id="btnGenerateInsight"
                        @if ($insightCooldown) disabled title="Analisa baru saja digenerate, tunggu beberapa menit" @endif
                        class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-purple-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ $insight ? 'Generate Ulang' : 'Generate Analisa' }}
                    </button>
                    @if ($insightCooldown)
                        <span id="insightCooldownLabel" data-sisa="{{ $insightCooldownSisaDetik }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-md bg-purple-100 px-3 py-2 text-xs font-medium text-purple-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Bisa generate lagi dalam
                            <span id="insightCountdown" class="tabular-nums">{{ floor($insightCooldownSisaDetik / 60) }}:{{ str_pad($insightCooldownSisaDetik % 60, 2, '0', STR_PAD_LEFT) }}</span>
                        </span>
                    @endif
                </div>
            </form>
        </div>

        @php
            $insightValid = $insight && is_array($insight->konten_insight) && !empty($insight->konten_insight['headline']);
            $insightData = $insightValid ? $insight->konten_insight : [];
            $sentiment = in_array($insightData['sentiment'] ?? '', ['positive', 'negative', 'neutral']) ? $insightData['sentiment'] : 'neutral';

            $bannerClass = match ($sentiment) {
                'positive' => 'bg-green-50 border-green-200 text-green-800',
                'negative' => 'bg-red-50 border-red-200 text-red-800',
                default => 'bg-gray-100 border-gray-200 text-gray-700',
            };

            $trendIcon = [
                'up' => ['path' => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941', 'class' => 'text-green-600'],
                'down' => ['path' => 'M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181', 'class' => 'text-red-600'],
                'neutral' => ['path' => 'M7.5 12h9', 'class' => 'text-gray-500'],
            ];
        @endphp

        {{-- Headline banner --}}
        @if ($insightValid)
            <div class="mt-5 flex items-start gap-3 rounded-lg border p-4 {{ $bannerClass }}">
                @if ($sentiment === 'positive')
                    <svg class="mt-0.5 h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendIcon['up']['path'] }}"/>
                    </svg>
                @elseif ($sentiment === 'negative')
                    <svg class="mt-0.5 h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendIcon['down']['path'] }}"/>
                    </svg>
                @else
                    <svg class="mt-0.5 h-6 w-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendIcon['neutral']['path'] }}"/>
                    </svg>
                @endif
                <p class="text-lg font-semibold leading-snug">{{ $insightData['headline'] }}</p>
            </div>
        @endif

        {{-- Grafik perbandingan bulan ini vs bulan lalu (dari data database) --}}
        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2 no-print">
            <div class="rounded-lg border border-purple-100 bg-white p-4">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Omset: Bulan Ini vs Bulan Lalu</h3>
                <div class="relative h-56"><canvas id="chartPerbandinganOmset"></canvas></div>
            </div>
            <div class="rounded-lg border border-purple-100 bg-white p-4">
                <h3 class="mb-3 text-sm font-semibold text-gray-900">Breakdown Kategori Layanan</h3>
                <div class="relative h-56"><canvas id="chartBreakdownKategori"></canvas></div>
            </div>
        </div>

        {{-- Sorotan: chip grid --}}
        @if ($insightValid && !empty($insightData['sorotan']))
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($insightData['sorotan'] as $sorotan)
                    @php
                        $ikon = $trendIcon[$sorotan['trend'] ?? 'neutral'];
                    @endphp
                    <div class="flex items-start gap-2.5 rounded-lg border border-purple-100 bg-white/70 px-4 py-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $ikon['class'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon['path'] }}"/>
                        </svg>
                        <p class="text-sm font-medium leading-relaxed text-gray-800">{{ $sorotan['teks'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Rekomendasi: checklist ringkas --}}
        @if ($insightValid && !empty($insightData['rekomendasi']))
            <div class="mt-4 rounded-lg bg-white/70 px-4 py-3">
                <p class="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-purple-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Rekomendasi
                </p>
                <ul class="space-y-2">
                    @foreach ($insightData['rekomendasi'] as $rekomendasi)
                        <li class="flex items-start gap-2.5 text-sm leading-relaxed text-gray-800">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $rekomendasi }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @elseif ($insight && ! $insightValid)
            {{-- Data lama masih format teks panjang --}}
            <div class="mt-4 rounded-md border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 no-print">
                Analisa tersimpan masih menggunakan format lama (teks panjang). Klik tombol "Generate Ulang" untuk memperbaruinya ke tampilan ringkas.
            </div>
        @else
            <div class="mt-4 text-center text-sm text-gray-400">
                Belum ada analisa. Klik tombol "Generate Analisa" untuk mendapatkan insight dari AI.
            </div>
        @endif

        {{-- Divider + Tanya AI --}}
        <div id="tanya-ai" class="mt-6 border-t-2 border-purple-200 pt-5 no-print">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Tanya AI Soal Data Bisnis Anda
            </h3>
            <p class="mt-1 text-xs text-gray-500">Ajukan pertanyaan bebas tentang data bisnis bulan ini. AI menjawab langsung dari ringkasan data periode aktif.</p>

            <form method="POST" action="{{ route('laporan.insight.tanya') }}" id="formTanyaAi" class="mt-3">
                @csrf
                <input type="hidden" name="periode" value="{{ $insightPeriode }}">
                <textarea name="pertanyaan" rows="2" maxlength="500" required
                    placeholder="Contoh: kenapa omset kategori Warna Rambut turun drastis?"
                    class="block w-full resize-y rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('pertanyaan') }}</textarea>
                @error('pertanyaan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <div class="mt-2 flex items-center justify-end gap-3">
                    <span class="text-[10px] text-gray-400">Maksimal 500 karakter</span>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Kirim
                    </button>
                </div>
            </form>

            @if ($tanyaRiwayat->isNotEmpty())
                <div class="mt-5 space-y-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Riwayat Pertanyaan</p>
                    @foreach ($tanyaRiwayat as $item)
                        <div>
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-lg rounded-br-none bg-violet-600 px-3 py-2 text-sm font-medium text-white">
                                    {{ $item->pertanyaan }}
                                </div>
                            </div>
                            <div class="mt-2 flex justify-start">
                                <div class="max-w-[90%] whitespace-pre-line rounded-lg rounded-bl-none bg-white px-3 py-2 text-left text-sm leading-relaxed text-gray-700 ring-1 ring-gray-200">
                                    {{ $item->jawaban }}
                                </div>
                            </div>
                            <p class="mt-1 text-right text-[10px] text-gray-400">{{ $item->dibuat_pada->format('d M Y H:i') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
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
                                        <span class="text-gray-400">â€”</span>
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
    function rpFull(v) {
        return 'Rp' + Number(v).toLocaleString('id-ID');
    }

    function rpShort(v) {
        if (v >= 1e9) return 'Rp' + (v / 1e9).toFixed(1).replace('.', ',') + ' M';
        if (v >= 1e6) return 'Rp' + (v / 1e6).toFixed(1).replace('.', ',') + ' jt';
        if (v >= 1e3) return 'Rp' + Math.round(v / 1e3) + ' rb';
        return 'Rp' + v;
    }

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
                    ctx.save();
                    ctx.fillStyle = '#374151';
                    ctx.font = '600 11px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(rpShort(raw), bar.x, bar.y - 4);
                    ctx.restore();
                });
            });
        }
    };

    // Chart 1: Perbandingan omset bulan ini vs bulan lalu
    const canvasOmset = document.getElementById('chartPerbandinganOmset');
    if (canvasOmset) {
        const ringkasan = @json($ringkasanData);
        const naik = ringkasan.omset_bulan_ini >= ringkasan.omset_bulan_lalu;

        new Chart(canvasOmset, {
            type: 'bar',
            data: {
                labels: ['Bulan Lalu', 'Bulan Ini'],
                datasets: [{
                    data: [ringkasan.omset_bulan_lalu, ringkasan.omset_bulan_ini],
                    backgroundColor: ['#9CA3AF', naik ? '#16a34a' : '#dc2626'],
                    borderRadius: 6,
                    maxBarThickness: 80,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 20 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => rpFull(ctx.parsed.y)
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
            },
            plugins: [rupiahLabelPlugin]
        });
    }

    // Chart 2: Breakdown kategori layanan (maks 5 teratas)
    const canvasKategori = document.getElementById('chartBreakdownKategori');
    if (canvasKategori) {
        const breakdown = @json($ringkasanData['breakdown_kategori']);
        const kategoriLabels = breakdown.map((k) => k.kategori);
        const warnaBulanIni = breakdown.map((k) =>
            k.omset_ini >= k.omset_lalu ? '#16a34a' : '#dc2626'
        );

        new Chart(canvasKategori, {
            type: 'bar',
            data: {
                labels: kategoriLabels,
                datasets: [
                    {
                        label: 'Bulan Lalu',
                        data: breakdown.map((k) => k.omset_lalu),
                        backgroundColor: '#9CA3AF',
                        borderRadius: 4,
                    },
                    {
                        label: 'Bulan Ini',
                        data: breakdown.map((k) => k.omset_ini),
                        backgroundColor: warnaBulanIni,
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
                            callback: (v) => 'Rp' + (v / 1000).toFixed(0) + 'rb'
                        }
                    }
                }
            },
            plugins: [rupiahLabelPlugin]
        });
    }

    // Cooldown tombol Tanya AI: disable 3 detik setelah kirim
    const formTanya = document.getElementById('formTanyaAi');
    if (formTanya) {
        formTanya.addEventListener('submit', function () {
            const btn = formTanya.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            setTimeout(() => { btn.disabled = false; }, 3000);
        });
    }

    // Countdown cooldown Generate Ulang: aktifkan tombol otomatis saat habis
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
