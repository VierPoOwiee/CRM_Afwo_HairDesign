<?php

namespace App\Services;

use App\Models\PembelianProduk;
use App\Models\Produk;
use App\Models\RiwayatStokProduk;
use Illuminate\Support\Facades\DB;

/**
 * Manajemen modal produk: restock (pembelian) dan pencatatan riwayat stok.
 *
 * harga_modal_rata_rata dihitung sebagai weighted average cost:
 *   (stok_lama * modal_lama + qty_beli * harga_beli) / (stok_lama + qty_beli)
 * dan aman saat stok_lama = 0 (hasil = harga_beli).
 */
class ProdukModalService
{
    /**
     * Proses restock produk dalam satu transaksi DB:
     * simpan pembelian, update stok + harga_modal_rata_rata, catat riwayat stok.
     */
    public static function restock(
        Produk $produk,
        int|float $qty,
        int|float $hargaBeli,
        ?string $keterangan = null,
        mixed $tanggal = null,
        ?int $dicatatOleh = null
    ): Produk {
        return DB::transaction(function () use ($produk, $qty, $hargaBeli, $keterangan, $tanggal, $dicatatOleh) {
            $produk = Produk::query()->lockForUpdate()->findOrFail($produk->id);

            $stokLama = (int) $produk->stok;
            $modalLama = (float) $produk->harga_modal_rata_rata;
            $qty = (float) $qty;
            $hargaBeli = (float) $hargaBeli;

            $modalBaru = $stokLama + $qty > 0
                ? round((($stokLama * $modalLama) + ($qty * $hargaBeli)) / ($stokLama + $qty), 2)
                : round($hargaBeli, 2);

            $stokBaru = $stokLama + $qty;
            $tgl = $tanggal !== null ? $tanggal : now()->toDateString();

            $produk->update([
                'stok' => $stokBaru,
                'harga_modal_rata_rata' => $modalBaru,
            ]);

            PembelianProduk::create([
                'produk_id' => $produk->id,
                'qty' => $qty,
                'harga_beli' => $hargaBeli,
                'harga_modal_rata_rata' => $modalBaru,
                'stok_setelah' => $stokBaru,
                'keterangan' => $keterangan !== null ? $keterangan : null,
                'dicatat_oleh' => $dicatatOleh,
                'tanggal' => $tgl,
            ]);

            RiwayatStokProduk::create([
                'produk_id' => $produk->id,
                'jenis' => RiwayatStokProduk::JENIS_RESTOCK,
                'perubahan' => $qty,
                'stok_akhir' => $stokBaru,
                'harga_modal_rata_rata' => $modalBaru,
                'keterangan' => $keterangan !== null ? $keterangan : 'Restock produk',
                'dicatat_oleh' => $dicatatOleh,
                'tanggal' => now(),
            ]);

            return $produk;
        });
    }

    /**
     * Catat perubahan stok selain restock (penjualan retail, pemakaian layanan,
     * restore saat transaksi dibatalkan, penyesuaian manual).
     */
    public static function catat(
        Produk $produk,
        string $jenis,
        int|float $perubahan,
        int|float $stokAkhir,
        string $keterangan,
        ?int $dicatatOleh = null,
        mixed $tanggal = null
    ): void {
        RiwayatStokProduk::create([
            'produk_id' => $produk->id,
            'jenis' => $jenis,
            'perubahan' => $perubahan,
            'stok_akhir' => $stokAkhir,
            'harga_modal_rata_rata' => (float) $produk->harga_modal_rata_rata,
            'keterangan' => $keterangan,
            'dicatat_oleh' => $dicatatOleh,
            'tanggal' => $tanggal ?? now(),
        ]);
    }

    /**
     * Hitung modal rata-rata yang baru tanpa menyimpan (dipakai untuk preview).
     */
    public static function hitungModalBaru(int $stokLama, int|float $modalLama, int|float $qty, int|float $hargaBeli): float
    {
        $stokLama = (float) $stokLama;
        $modalLama = (float) $modalLama;
        $qty = (float) $qty;
        $hargaBeli = (float) $hargaBeli;

        if ($stokLama + $qty <= 0) {
            return round($hargaBeli, 2);
        }

        return round((($stokLama * $modalLama) + ($qty * $hargaBeli)) / ($stokLama + $qty), 2);
    }
}