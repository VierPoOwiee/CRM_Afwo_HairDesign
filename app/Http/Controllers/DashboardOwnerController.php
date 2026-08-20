<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Models\Pelanggan;
use Carbon\Carbon;

class DashboardOwnerController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalPemasukanHariIni = TransaksiKunjungan::whereDate('waktu_kunjungan', $today)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $pelangganBaruHariIni = Pelanggan::whereDate('created_at', $today)->count();

        $transaksiHariIni = TransaksiKunjungan::whereDate('waktu_kunjungan', $today)
            ->where('status', 'selesai')
            ->with('pelanggan')
            ->orderBy('waktu_kunjungan', 'desc')
            ->get();

        $produkStokMenipis = Produk::where('stok', '<', Produk::STOK_MENIPIS)
            ->where('aktif', true)
            ->orderBy('stok')
            ->get();

        return view('dashboard.index', compact(
            'totalPemasukanHariIni',
            'pelangganBaruHariIni',
            'transaksiHariIni',
            'produkStokMenipis',
        ));
    }
}
