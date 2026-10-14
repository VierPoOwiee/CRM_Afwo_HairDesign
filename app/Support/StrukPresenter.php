<?php

namespace App\Support;

use App\Models\DetailTransaksi;
use App\Models\TransaksiKunjungan;

/**
 * Mengubah sebuah TransaksiKunjungan menjadi struktur data siap-tampil untuk
 * struk thermal. Tidak mengubah data apa pun di database dan tidak
 * menduplikasi logika perhitungan total milik model/controller.
 */
class StrukPresenter
{
    private const METODE = [
        'cash' => 'Cash',
        'qris_bni' => 'QRIS BNI',
        'qris_bri' => 'QRIS BRI',
        'debit' => 'Debit',
        'kartu_kredit' => 'Kartu Kredit',
        'transfer' => 'Transfer',
    ];

    public static function forTransaksi(TransaksiKunjungan $transaksi): array
    {
        $transaksi->loadMissing([
            'pelanggan',
            'details.staf',
            'details.layanan',
            'details.produk',
        ]);

        $details = $transaksi->details->sortBy('id')->values();

        // Baris utama = subtotal > 0. Baris staf ke-2 dibuat dengan subtotal 0
        // dan catatan berawalan "(Staf ke-2)".
        $mainRows = $details->filter(fn (DetailTransaksi $d) => (float) $d->subtotal > 0)->values();
        $secondRows = $details->filter(fn (DetailTransaksi $d) => (float) $d->subtotal <= 0
            && str_starts_with((string) $d->catatan, '(Staf ke-2)'))->values();

        $items = [];
        $totalQty = 0.0;
        $subTotal = 0.0;
        $totalDiskon = 0.0;
        $matchedSecondIds = [];

        foreach ($mainRows as $index => $d) {
            $second = $secondRows->first(function (DetailTransaksi $s) use ($d, $matchedSecondIds) {
                if (in_array($s->id, $matchedSecondIds, true)) {
                    return false;
                }

                return $s->tipe_item === $d->tipe_item
                    && (int) $s->id_layanan === (int) $d->id_layanan
                    && (int) $s->id_produk === (int) $d->id_produk
                    && (string) ($s->varian_dipilih ?? '') === (string) ($d->varian_dipilih ?? '')
                    && (int) $s->id_staf !== (int) $d->id_staf;
            });

            if ($second) {
                $matchedSecondIds[] = $second->id;
            }

            $stylistNames = collect([$d->staf->nama ?? null, $second?->staf?->nama])
                ->filter()
                ->unique()
                ->values();

            $qty = (float) $d->qty;
            $harga = (float) $d->harga_saat_transaksi;
            $diskon = (float) ($d->diskon ?? 0);
            $subtotal = (float) $d->subtotal;

            $totalQty += $qty;
            $subTotal += $harga * $qty;
            $totalDiskon += $diskon;

            $items[] = [
                'no' => $index + 1,
                'nama' => self::namaItem($d),
                'qty' => $qty,
                'satuan' => $d->tipe_item === 'produk' ? self::satuanProduk($d) : null,
                'harga' => $harga,
                'diskon' => $diskon,
                'subtotal' => $subtotal,
                'stylist' => $stylistNames->isEmpty() ? null : $stylistNames->join(' & '),
                'ketebalan' => $d->ketebalan_rambut ?: null,
            ];
        }

        $total = (float) $transaksi->total_bayar;

        return [
            'no_struk' => $transaksi->no_struk,
            'tanggal' => $transaksi->waktu_kunjungan?->format('Y-m-d'),
            'jam' => $transaksi->waktu_kunjungan?->format('H:i:s'),
            'pelanggan' => $transaksi->pelanggan->nama ?? null,
            'no_wa' => $transaksi->pelanggan->no_wa ?? null,
            'jenis_pengerjaan' => $transaksi->jenis_pengerjaan,
            'items' => $items,
            'total_qty' => $totalQty,
            'sub_total' => $subTotal,
            'total_diskon' => $totalDiskon,
            'total' => $total,
            'metode_label' => self::METODE[$transaksi->metode_pembayaran]
                ?? (string) $transaksi->metode_pembayaran,
            // Belum ada kolom "uang diterima" di database, jadi bayar = total.
            'bayar' => $total,
            'kembali' => max(0, $total - $total),
            'dibatalkan' => $transaksi->status === 'batal',
        ];
    }

    private static function namaItem(DetailTransaksi $d): string
    {
        if ($d->tipe_item === 'layanan') {
            $nama = $d->layanan->nama_layanan ?? 'Layanan';
            $varian = (string) ($d->varian_dipilih ?? '');

            if ($varian !== '' && strtolower($varian) !== 'default') {
                $nama .= ' ('.ucfirst($varian).')';
            }

            return $nama;
        }

        if ($d->tipe_item === 'produk') {
            return $d->produk->nama_produk ?? 'Produk';
        }

        return '(Item)';
    }

    private static function satuanProduk(DetailTransaksi $d): string
    {
        // Produk retail dijual per pcs.
        return 'pcs';
    }
}
