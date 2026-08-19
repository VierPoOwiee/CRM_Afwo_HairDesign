<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\KomisiHarianSpesial;
use App\Models\KomisiTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKomisiController extends Controller
{
    public function index(Request $request)
    {
        $dari = $request->input('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', now()->endOfMonth()->toDateString());
        $stafId = $request->input('id_staf');

        $dariCarbon = Carbon::parse($dari)->startOfDay();
        $sampaiCarbon = Carbon::parse($sampai)->endOfDay();

        $karyawans = Karyawan::orderBy('nama')->get();

        // Komisi per layanan (from komisi_transaksi)
        $komisiPerLayanan = KomisiTransaksi::whereHas('transaksi', function ($q) use ($dariCarbon, $sampaiCarbon) {
                $q->where('waktu_kunjungan', '>=', $dariCarbon)
                  ->where('waktu_kunjungan', '<=', $sampaiCarbon)
                  ->where('status', 'selesai');
            })
            ->when($stafId, function ($q) use ($stafId) {
                $q->where('id_staf', $stafId);
            })
            ->with('staf', 'transaksi')
            ->orderBy('id_staf')
            ->get()
            ->groupBy('id_staf');

        // Komisi harian spesial (from komisi_harian_spesial)
        $komisiHarian = KomisiHarianSpesial::whereBetween('tanggal', [$dariCarbon->toDateString(), $sampaiCarbon->toDateString()])
            ->when($stafId, function ($q) use ($stafId) {
                $q->where('id_staf', $stafId);
            })
            ->with('staf')
            ->orderBy('id_staf')
            ->get()
            ->groupBy('id_staf');

        // Build combined recap
        $rekap = collect();

        // Process komisi per layanan
        foreach ($komisiPerLayanan as $stafId => $items) {
            $staf = $items->first()->staf;
            $totalKomisi = $items->sum('jumlah_komisi');
            $totalItems = $items->count();

            $rekap->push([
                'id_staf' => $stafId,
                'nama_staf' => $staf->nama ?? 'Staf #' . $stafId,
                'skema' => $staf->skema_komisi ?? 'per_layanan',
                'sumber' => 'per_layanan',
                'total_komisi' => $totalKomisi,
                'detail_count' => $totalItems,
                'tanggal_range' => $dari . ' s/d ' . $sampai,
            ]);
        }

        // Process komisi harian spesial
        foreach ($komisiHarian as $stafId => $items) {
            $staf = $items->first()->staf;
            $totalKomisi = $items->sum('jumlah_komisi');

            $rekap->push([
                'id_staf' => $stafId,
                'nama_staf' => $staf->nama ?? 'Staf #' . $stafId,
                'skema' => $staf->skema_komisi ?? 'persen_omset_harian',
                'sumber' => 'persen_harian',
                'total_komisi' => $totalKomisi,
                'detail_count' => $items->count(),
                'tanggal_range' => $dari . ' s/d ' . $sampai,
            ]);
        }

        $rekap = $rekap->sortBy('nama_staf')->values();

        // Grand total per staf
        $grandTotal = $rekap->groupBy('id_staf')->map(function ($group) {
            return [
                'nama_staf' => $group->first()['nama_staf'],
                'skema' => $group->first()['skema'],
                'total_per_layanan' => $group->where('sumber', 'per_layanan')->sum('total_komisi'),
                'total_persen_harian' => $group->where('sumber', 'persen_harian')->sum('total_komisi'),
                'total_keseluruhan' => $group->sum('total_komisi'),
            ];
        })->values();

        return view('laporan-komisi.index', compact(
            'karyawans', 'komisiPerLayanan', 'komisiHarian',
            'dari', 'sampai', 'stafId', 'rekap', 'grandTotal'
        ));
    }

    /**
     * Trigger hitung ulang komisi harian untuk staf tertentu.
     */
    public function hitungUlang(Request $request)
    {
        $request->validate([
            'id_staf' => ['required', 'exists:karyawans,id'],
            'tanggal' => ['required', 'date'],
        ]);

        $staf = Karyawan::findOrFail($request->id_staf);
        KomisiHarianSpesial::calculateForDate($staf, $request->tanggal);

        return back()->with('success', 'Komisi harian untuk "' . $staf->nama . '" tanggal ' . $request->tanggal . ' berhasil dihitung ulang.');
    }
}
