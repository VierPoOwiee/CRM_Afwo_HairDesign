<?php

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
