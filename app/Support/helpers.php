<?php

use App\Models\TransaksiKunjungan;
use App\Support\StrukPresenter;

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi rupiah dengan pemisah ribuan titik.
     * Contoh: rupiah(1170000) => "Rp 1.170.000".
     */
    function rupiah($nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }
}

if (! function_exists('struk_data')) {
    /**
     * Bangun data struk dari transaksi (memakai presenter yang sama dengan view).
     */
    function struk_data(TransaksiKunjungan $transaksi): array
    {
        return StrukPresenter::forTransaksi($transaksi);
    }
}
