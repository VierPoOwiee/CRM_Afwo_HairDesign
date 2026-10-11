<?php

namespace App\Support;

class AppointmentColor
{
    private const PALETTE = ['amber', 'emerald', 'blue', 'violet', 'pink', 'orange', 'teal', 'rose'];

    /**
     * Warna identitas yang konsisten untuk sebuah entitas (pelanggan/karyawan).
     */
    public static function forId(int $id): string
    {
        return self::PALETTE[$id % count(self::PALETTE)];
    }
}