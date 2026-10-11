<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public const UANG_MAKAN_PER_HARI = 25000;

    public function index(Request $request)
    {
        $bulanInput = trim((string) $request->input('bulan', now()->format('Y-m')));
        try {
            $bulan = Carbon::createFromFormat('Y-m', $bulanInput);
        } catch (\Throwable) {
            $bulan = Carbon::now();
        }
        $bulanInput = $bulan->format('Y-m');
        $awalBulan = $bulan->copy()->startOfMonth()->toDateString();
        $akhirBulan = $bulan->copy()->endOfMonth()->toDateString();

        $tanggalInput = trim((string) $request->input('tanggal', now()->format('Y-m-d')));
        try {
            $tanggal = Carbon::parse($tanggalInput);
        } catch (\Throwable) {
            $tanggal = Carbon::now();
        }
        $tanggalInput = $tanggal->format('Y-m-d');

        $karyawans = Karyawan::orderBy('nama')->get();

        // Kehadiran pada tanggal yang dipilih (untuk centang checklist)
        $kehadiranHariIni = Absensi::where('tanggal', $tanggalInput)
            ->pluck('hadir', 'id_staf')
            ->map(fn ($hadir) => (bool) $hadir);

        // Rekap kehadiran & uang makan per karyawan untuk bulan terpilih
        $hadirPerStaf = Absensi::whereBetween('tanggal', [$awalBulan, $akhirBulan])
            ->where('hadir', true)
            ->selectRaw('id_staf, count(*) as jumlah')
            ->groupBy('id_staf')
            ->pluck('jumlah', 'id_staf');

        $ringkasan = $karyawans->map(function (Karyawan $k) use ($hadirPerStaf) {
            $jumlahHadir = (int) ($hadirPerStaf[$k->id] ?? 0);
            $uangMakan = $k->skema_komisi === 'persen_omset_harian'
                ? 0
                : $jumlahHadir * self::UANG_MAKAN_PER_HARI;

            return [
                'karyawan' => $k,
                'jumlah_hadir' => $jumlahHadir,
                'uang_makan' => $uangMakan,
            ];
        });

        $totalHadir = $ringkasan->sum('jumlah_hadir');
        $totalUangMakan = $ringkasan->sum('uang_makan');

        $pilihanBulan = collect();
        for ($i = 0; $i < 12; $i++) {
            $b = now()->subMonths($i);
            $pilihanBulan->push([
                'value' => $b->format('Y-m'),
                'label' => $this->namaBulan((int) $b->format('n')).' '.$b->format('Y'),
            ]);
        }

        return view('absensi.index', compact(
            'karyawans', 'kehadiranHariIni', 'tanggal', 'tanggalInput',
            'bulanInput', 'ringkasan', 'totalHadir', 'totalUangMakan', 'pilihanBulan',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
            'hadir' => ['nullable', 'array'],
            'hadir.*' => ['in:1'],
        ]);

        $tanggal = Carbon::parse($request->tanggal)->toDateString();
        $daftarHadir = $request->input('hadir', []);

        foreach (Karyawan::pluck('id') as $idKaryawan) {
            Absensi::updateOrCreate(
                ['id_staf' => $idKaryawan, 'tanggal' => $tanggal],
                ['hadir' => array_key_exists((string) $idKaryawan, $daftarHadir)]
            );
        }

        return back()->with('success', 'Absensi tanggal '.$tanggal.' berhasil disimpan.');
    }

    private function namaBulan(int $nomor): string
    {
        return [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$nomor];
    }
}