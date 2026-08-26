<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\InsightAi;
use App\Models\Karyawan;
use App\Models\KomisiHarianSpesial;
use App\Models\KomisiTransaksi;
use App\Models\Pelanggan;
use App\Models\PertanyaanAi;
use App\Models\TransaksiKunjungan;
use App\Services\LaporanAiInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        $preset = $request->input('preset', 'bulan-ini');
        [$dari, $sampai] = $this->resolvePeriode($preset, $request->input('dari'), $request->input('sampai'));

        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();

        $jenisPengerjaan = $request->input('jenis_pengerjaan');
        $stafId = $request->input('id_staf');
        $metode = $request->input('metode_pembayaran');

        $baseQuery = TransaksiKunjungan::where('waktu_kunjungan', '>=', $dariCarbon)
            ->where('waktu_kunjungan', '<=', $sampaiCarbon)
            ->where('status', 'selesai');

        if ($stafId) {
            $baseQuery->whereHas('details', function ($q) use ($stafId) {
                $q->where('id_staf', $stafId);
            });
        }
        if ($jenisPengerjaan) {
            $baseQuery->where('jenis_pengerjaan', $jenisPengerjaan);
        }
        if ($metode) {
            $baseQuery->where('metode_pembayaran', $metode);
        }

        $transaksis = $baseQuery->clone()->with(['pelanggan', 'komisiTransaksi.staf'])->orderBy('waktu_kunjungan', 'desc')->get();

        $totalOmset = $transaksis->sum('total_bayar');
        $jumlahTransaksi = $transaksis->count();
        $rataRata = $jumlahTransaksi > 0 ? $totalOmset / $jumlahTransaksi : 0;

        $trenHarian = $transaksis->groupBy(function ($t) {
            return $t->waktu_kunjungan->format('Y-m-d');
        })->map(fn ($items) => [
            'tanggal' => $items->first()->waktu_kunjungan->format('d M'),
            'total' => $items->sum('total_bayar'),
            'jumlah' => $items->count(),
        ])->values();

        $breakdownKategori = DetailTransaksi::whereHas('transaksi', function ($q) use ($dariCarbon, $sampaiCarbon) {
            $q->where('waktu_kunjungan', '>=', $dariCarbon)
                ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                ->where('status', 'selesai');
        })
            ->where('tipe_item', 'layanan')
            ->when($stafId, fn ($q) => $q->where('id_staf', $stafId))
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.kategori, sum(detail_transaksi.subtotal) as total_subtotal, count(*) as jumlah_item')
            ->groupBy('layanan.kategori')
            ->orderByDesc('total_subtotal')
            ->get();

        $karyawans = Karyawan::orderBy('nama')->get();

        $insightPeriode = Carbon::parse($dari)->startOfMonth()->toDateString();
        $insight = InsightAi::where('periode', $insightPeriode)->first();
        $insightCooldown = false;
        $insightCooldownSisaDetik = 0;
        if ($insight) {
            $sisaDetik = (int) ceil(3600 - $insight->dibuat_pada->diffInSeconds(now()));
            if ($sisaDetik > 0) {
                $insightCooldown = true;
                $insightCooldownSisaDetik = $sisaDetik;
            }
        }

        $service = new LaporanAiInsightService;
        $ringkasanData = $service->agregasiData(Carbon::parse($insightPeriode));
        $tanyaRiwayat = PertanyaanAi::where('periode', $insightPeriode)
            ->orderByDesc('dibuat_pada')
            ->orderByDesc('id')
            ->get();

        return view('laporan.penjualan', compact(
            'dari', 'sampai', 'preset', 'transaksis', 'totalOmset',
            'jumlahTransaksi', 'rataRata', 'trenHarian', 'breakdownKategori',
            'karyawans', 'jenisPengerjaan', 'stafId', 'metode',
            'insight', 'insightPeriode', 'insightCooldown', 'insightCooldownSisaDetik',
            'ringkasanData', 'tanyaRiwayat',
        ));
    }

    public function pelangganAktif(Request $request)
    {
        $preset = $request->input('preset', 'bulan-ini');
        [$dari, $sampai] = $this->resolvePeriode($preset, $request->input('dari'), $request->input('sampai'));

        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();

        $pelanggans = Pelanggan::whereHas('transaksiKunjungan', function ($q) use ($dariCarbon, $sampaiCarbon) {
            $q->where('waktu_kunjungan', '>=', $dariCarbon)
                ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                ->where('status', 'selesai');
        })
            ->withCount(['transaksiKunjungan as jumlah_kunjungan' => function ($q) use ($dariCarbon, $sampaiCarbon) {
                $q->where('waktu_kunjungan', '>=', $dariCarbon)
                    ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                    ->where('status', 'selesai');
            }])
            ->withSum(['transaksiKunjungan as total_belanja' => function ($q) use ($dariCarbon, $sampaiCarbon) {
                $q->where('waktu_kunjungan', '>=', $dariCarbon)
                    ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                    ->where('status', 'selesai');
            }], 'total_bayar')
            ->withMax('transaksiKunjungan as kunjungan_terakhir', 'waktu_kunjungan')
            ->orderByDesc('kunjungan_terakhir')
            ->get();

        return view('laporan.pelanggan-aktif', compact('pelanggans', 'dari', 'sampai', 'preset'));
    }

    public function pelangganRiwayat(Pelanggan $pelanggan)
    {
        $transaksis = TransaksiKunjungan::where('id_pelanggan', $pelanggan->id)
            ->where('status', 'selesai')
            ->with('details.staf', 'details.layanan', 'details.produk')
            ->orderBy('waktu_kunjungan', 'desc')
            ->get();

        $totalBelanja = $transaksis->sum('total_bayar');
        $jumlahKunjungan = $transaksis->count();

        return view('laporan.pelanggan-riwayat', compact('pelanggan', 'transaksis', 'totalBelanja', 'jumlahKunjungan'));
    }

    public function rekapKomisi(Request $request)
    {
        return view('laporan.rekap-komisi', $this->buildRekapData($request));
    }

    /**
     * Slip pendapatan satu karyawan (1 halaman per karyawan).
     * Periode mengikuti filter yang sama dengan laporan utama.
     */
    public function slipPendapatan(Request $request, Karyawan $karyawan)
    {
        $preset = $request->input('preset', 'bulan-ini');
        [$dari, $sampai] = $this->resolvePeriode($preset, $request->input('dari'), $request->input('sampai'));

        $slip = $this->buildSlipData($karyawan, $dari, $sampai);

        return view('laporan.slip-pendapatan', compact('karyawan', 'slip', 'dari', 'sampai', 'preset'));
    }

    /**
     * Versi cetak gabungan: halaman laporan utama dulu,
     * lalu slip pendapatan tiap karyawan (1 halaman per karyawan).
     */
    public function cetakRekapKomisi(Request $request)
    {
        $data = $this->buildRekapData($request);

        $data['slips'] = $data['karyawans']->map(function ($karyawan) use ($data) {
            return [
                'karyawan' => $karyawan,
                'slip' => $this->buildSlipData($karyawan, $data['dari'], $data['sampai']),
            ];
        });

        return view('laporan.cetak-rekap-komisi', $data);
    }

    /**
     * Halaman ringkasan pendapatan bulanan per karyawan (gaji pokok + komisi bulan terpilih).
     */
    public function pendapatanKaryawan(Request $request)
    {
        $bulanInput = $request->input('bulan', now()->format('Y-m'));
        try {
            $bulan = Carbon::createFromFormat('Y-m', $bulanInput)->startOfDay();
        } catch (\Throwable) {
            $bulan = now()->startOfDay();
        }
        $bulanInput = $bulan->format('Y-m');

        $dariCarbon = $bulan->copy()->startOfMonth();
        $sampaiCarbon = $bulan->copy()->endOfMonth();

        $karyawans = Karyawan::orderBy('nama')->get();

        $komisiPerLayanan = KomisiTransaksi::whereHas('transaksi', function ($q) use ($dariCarbon, $sampaiCarbon) {
            $q->where('waktu_kunjungan', '>=', $dariCarbon)
                ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                ->where('status', 'selesai');
        })
            ->selectRaw('id_staf, sum(jumlah_komisi) as total')
            ->groupBy('id_staf')
            ->pluck('total', 'id_staf');

        $komisiHarian = KomisiHarianSpesial::whereBetween('tanggal', [$dariCarbon->toDateString(), $sampaiCarbon->toDateString()])
            ->selectRaw('id_staf, sum(jumlah_komisi) as total')
            ->groupBy('id_staf')
            ->pluck('total', 'id_staf');

        $baris = $karyawans->map(function ($karyawan) use ($komisiPerLayanan, $komisiHarian) {
            $perLayanan = (float) ($komisiPerLayanan[$karyawan->id] ?? 0);
            $persenHarian = (float) ($komisiHarian[$karyawan->id] ?? 0);
            $gajiPokok = (float) $karyawan->gaji_pokok;

            return [
                'karyawan' => $karyawan,
                'komisi_per_layanan' => $perLayanan,
                'komisi_persen_harian' => $persenHarian,
                'total_komisi' => $perLayanan + $persenHarian,
                'gaji_pokok' => $gajiPokok,
                'total_pendapatan' => $perLayanan + $persenHarian + $gajiPokok,
            ];
        });

        $grandTotal = [
            'komisi_per_layanan' => $baris->sum('komisi_per_layanan'),
            'komisi_persen_harian' => $baris->sum('komisi_persen_harian'),
            'total_komisi' => $baris->sum('total_komisi'),
            'gaji_pokok' => $baris->sum('gaji_pokok'),
            'total_pendapatan' => $baris->sum('total_pendapatan'),
        ];

        // Pilihan bulan: 12 bulan terakhir
        $pilihanBulan = collect();
        for ($i = 0; $i < 12; $i++) {
            $b = now()->subMonths($i);
            $pilihanBulan->push([
                'value' => $b->format('Y-m'),
                'label' => $this->namaBulan((int) $b->format('n')).' '.$b->format('Y'),
            ]);
        }

        return view('laporan.pendapatan-karyawan', compact(
            'baris', 'grandTotal', 'dariCarbon', 'sampaiCarbon', 'bulanInput', 'pilihanBulan',
        ));
    }

    private function namaBulan(int $nomor): string
    {
        return [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$nomor];
    }

    private function buildRekapData(Request $request): array
    {
        $preset = $request->input('preset', 'bulan-ini');
        [$dari, $sampai] = $this->resolvePeriode($preset, $request->input('dari'), $request->input('sampai'));

        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();
        $stafId = $request->input('id_staf');

        $karyawans = Karyawan::orderBy('nama')->get();

        $komisiPerLayanan = KomisiTransaksi::whereHas('transaksi', function ($q) use ($dariCarbon, $sampaiCarbon) {
            $q->where('waktu_kunjungan', '>=', $dariCarbon)
                ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                ->where('status', 'selesai');
        })
            ->when($stafId, fn ($q) => $q->where('id_staf', $stafId))
            ->with('staf', 'transaksi')
            ->orderBy('id_staf')
            ->get()
            ->groupBy('id_staf');

        $komisiHarian = KomisiHarianSpesial::whereBetween('tanggal', [$dariCarbon->toDateString(), $sampaiCarbon->toDateString()])
            ->when($stafId, fn ($q) => $q->where('id_staf', $stafId))
            ->with('staf')
            ->orderBy('id_staf')
            ->get()
            ->groupBy('id_staf');

        $rekap = collect();

        foreach ($komisiPerLayanan as $sid => $items) {
            $staf = $items->first()->staf;
            $rekap->push([
                'id_staf' => $sid,
                'nama_staf' => $staf->nama ?? 'Staf #'.$sid,
                'skema' => $staf->skema_komisi ?? 'per_layanan',
                'sumber' => 'per_layanan',
                'total_komisi' => $items->sum('jumlah_komisi'),
                'detail_count' => $items->count(),
            ]);
        }

        foreach ($komisiHarian as $sid => $items) {
            $staf = $items->first()->staf;
            $rekap->push([
                'id_staf' => $sid,
                'nama_staf' => $staf->nama ?? 'Staf #'.$sid,
                'skema' => $staf->skema_komisi ?? 'persen_omset_harian',
                'sumber' => 'persen_harian',
                'total_komisi' => $items->sum('jumlah_komisi'),
                'detail_count' => $items->count(),
            ]);
        }

        $rekap = $rekap->sortBy('nama_staf')->values();

        $grandTotal = $rekap->groupBy('id_staf')->map(function ($group) {
            return [
                'id_staf' => $group->first()['id_staf'],
                'nama_staf' => $group->first()['nama_staf'],
                'skema' => $group->first()['skema'],
                'total_per_layanan' => $group->where('sumber', 'per_layanan')->sum('total_komisi'),
                'total_persen_harian' => $group->where('sumber', 'persen_harian')->sum('total_komisi'),
                'total_keseluruhan' => $group->sum('total_komisi'),
            ];
        })->values();

        $totalBayarSemuaStaf = $grandTotal->sum('total_keseluruhan');

        // Ringkasan pendapatan per karyawan (komisi + gaji pokok) untuk section "Pendapatan Karyawan"
        $pendapatanStaf = $karyawans->map(function ($karyawan) use ($grandTotal) {
            $row = $grandTotal->firstWhere('id_staf', $karyawan->id);
            $totalKomisi = (float) ($row['total_keseluruhan'] ?? 0);
            $gajiPokok = (float) $karyawan->gaji_pokok;

            return [
                'karyawan' => $karyawan,
                'total_komisi' => $totalKomisi,
                'gaji_pokok' => $gajiPokok,
                'total_pendapatan' => $totalKomisi + $gajiPokok,
                'ada_komisi' => $row !== null,
            ];
        });

        return compact(
            'karyawans', 'komisiPerLayanan', 'komisiHarian',
            'dari', 'sampai', 'preset', 'stafId', 'rekap', 'grandTotal',
            'totalBayarSemuaStaf', 'pendapatanStaf',
        );
    }

    /**
     * Susun data slip pendapatan satu karyawan untuk periode terpilih:
     * breakdown komisi harian, mingguan, gaji pokok, dan total pendapatan.
     * Breakdown mingguan SELALU dihitung ulang dari data harian.
     */
    private function buildSlipData(Karyawan $staf, string $dari, string $sampai): array
    {
        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();

        if ($staf->skema_komisi === 'persen_omset_harian') {
            $sumber = 'persen_harian';

            $harian = KomisiHarianSpesial::where('id_staf', $staf->id)
                ->whereBetween('tanggal', [$dariCarbon->toDateString(), $sampaiCarbon->toDateString()])
                ->orderBy('tanggal')
                ->get()
                ->map(fn ($row) => [
                    'tanggal' => $row->tanggal->copy(),
                    'komisi' => (float) $row->jumlah_komisi,
                    'omset_dasar' => (float) $row->total_omset_dasar,
                    'persen' => $row->persen,
                    'model' => $row,
                ])
                ->values();
        } else {
            $sumber = 'per_layanan';

            $harian = KomisiTransaksi::where('id_staf', $staf->id)
                ->whereHas('transaksi', function ($q) use ($dariCarbon, $sampaiCarbon) {
                    $q->where('waktu_kunjungan', '>=', $dariCarbon)
                        ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                        ->where('status', 'selesai');
                })
                ->with('transaksi')
                ->get()
                ->groupBy(fn ($kt) => $kt->transaksi->waktu_kunjungan->format('Y-m-d'))
                ->sortKeys()
                ->map(function ($items, $tanggal) {
                    return [
                        'tanggal' => Carbon::parse($tanggal),
                        'komisi' => (float) $items->sum('jumlah_komisi'),
                        'transaksis' => $items->map(fn ($i) => $i->transaksi)->unique('id')->values(),
                    ];
                })
                ->values();
        }

        $mingguan = $harian
            ->groupBy(fn ($r) => $r['tanggal']->copy()->startOfWeek(Carbon::MONDAY)->toDateString())
            ->sortKeys()
            ->map(function ($items, $mingguMulai) {
                $mulai = Carbon::parse($mingguMulai);

                return [
                    'mulai' => $mulai->copy(),
                    'sampai' => $mulai->copy()->endOfWeek(Carbon::SUNDAY),
                    'komisi' => (float) $items->sum('komisi'),
                ];
            })
            ->values();

        $totalKomisi = round($harian->sum('komisi'), 2);
        $gajiPokok = (float) $staf->gaji_pokok;

        return [
            'sumber' => $sumber,
            'harian' => $harian,
            'mingguan' => $mingguan,
            'total_komisi' => $totalKomisi,
            'gaji_pokok' => $gajiPokok,
            'total_pendapatan' => $totalKomisi + $gajiPokok,
        ];
    }

    public function hitungUlang(Request $request)
    {
        $request->validate([
            'id_staf' => ['required', 'exists:karyawans,id'],
            'tanggal' => ['required', 'date'],
        ]);

        $staf = Karyawan::findOrFail($request->id_staf);
        KomisiHarianSpesial::calculateForDate($staf, $request->tanggal);

        return back()->with('success', 'Komisi harian untuk "'.$staf->nama.'" tanggal '.$request->tanggal.' berhasil dihitung ulang.');
    }

    private function resolvePeriode(?string $preset, ?string $dari, ?string $sampai): array
    {
        if ($dari && $sampai) {
            return [$dari, $sampai];
        }

        $now = Carbon::now();

        return match ($preset) {
            'harian' => [$now->copy()->startOfDay()->toDateString(), $now->copy()->endOfDay()->toDateString()],
            'mingguan' => [
                $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                $now->copy()->endOfWeek(Carbon::SUNDAY)->toDateString(),
            ],
            'bulan-ini' => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ],
            'bulan-lalu' => [
                $now->copy()->subMonth()->startOfMonth()->toDateString(),
                $now->copy()->subMonth()->endOfMonth()->toDateString(),
            ],
            default => [
                $now->copy()->startOfMonth()->toDateString(),
                $now->copy()->endOfMonth()->toDateString(),
            ],
        };
    }
}
