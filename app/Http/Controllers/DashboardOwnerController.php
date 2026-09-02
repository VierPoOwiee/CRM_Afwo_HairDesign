<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Models\Pelanggan;
use Carbon\Carbon;

class DashboardOwnerController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $totalPemasukanHariIni = TransaksiKunjungan::whereDate('waktu_kunjungan', $today)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $totalPemasukanKemarin = TransaksiKunjungan::whereDate('waktu_kunjungan', $yesterday)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $persenPerubahan = $totalPemasukanKemarin > 0
            ? round((($totalPemasukanHariIni - $totalPemasukanKemarin) / $totalPemasukanKemarin) * 100, 1)
            : null;

        $pelangganBaruHariIni = Pelanggan::whereDate('created_at', $today)->count();

        $transaksiHariIni = TransaksiKunjungan::whereDate('waktu_kunjungan', $today)
            ->where('status', 'selesai')
            ->with(['pelanggan', 'details.layanan', 'details.produk'])
            ->orderBy('waktu_kunjungan', 'desc')
            ->get();

        $produkStokMenipis = Produk::where('stok', '<', Produk::STOK_MENIPIS)
            ->where('aktif', true)
            ->orderBy('stok')
            ->get();

        $pemasukan7Hari = collect(range(6, 0))->map(function ($i) {
            $tanggal = Carbon::today()->subDays($i);
            $total = (float) TransaksiKunjungan::whereDate('waktu_kunjungan', $tanggal)
                ->where('status', 'selesai')
                ->sum('total_bayar');

            return [
                'tanggal' => $tanggal,
                'label' => $tanggal->format('D'),
                'total' => $total,
            ];
        })->values();

        $maxTotal = max(1, $pemasukan7Hari->max('total'));

        $layananTerpopuler = DetailTransaksi::where('tipe_item', 'layanan')
            ->whereNotNull('id_layanan')
            ->whereHas('transaksi', function ($q) use ($today) {
                $q->where('status', 'selesai')
                    ->whereDate('waktu_kunjungan', '>=', $today->copy()->startOfWeek())
                    ->whereDate('waktu_kunjungan', '<=', $today->copy()->endOfWeek());
            })
            ->with('layanan')
            ->get()
            ->groupBy('id_layanan')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'nama' => $first->layanan->nama_layanan ?? 'Layanan',
                    'jumlah' => $items->sum('qty'),
                ];
            })
            ->sortByDesc('jumlah')
            ->take(4)
            ->values();

        return view('dashboard.index', compact(
            'totalPemasukanHariIni',
            'persenPerubahan',
            'pelangganBaruHariIni',
            'transaksiHariIni',
            'produkStokMenipis',
            'pemasukan7Hari',
            'maxTotal',
            'layananTerpopuler',
        ));
    }
}