<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identitas Salon (dipakai pada struk/invoice)
    |--------------------------------------------------------------------------
    */
    'nama' => env('SALON_NAME', 'AFWO Hair Design'),
    'alamat' => env('SALON_ALAMAT', 'Isi alamat salon di .env'),
    'telepon' => env('SALON_TELEPON', ''),
    'pesan_penutup' => env('SALON_PESAN_STRUK', 'Terima kasih atas kunjungan Anda'),
    'catatan_kaki' => env('SALON_CATATAN_STRUK', ''),

    // Path relatif di dalam public/ (mis. "img/logo-struk.png").
    // Gunakan gambar hitam putih agar tercetak baik pada struk thermal.
    'logo' => env('SALON_LOGO', null),
];
