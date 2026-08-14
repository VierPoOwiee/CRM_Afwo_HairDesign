<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    protected $fillable = [
        'nama',
        'kontak',
        'skema_komisi',
        'persen_komisi_harian',
    ];

    protected $casts = [
        'persen_komisi_harian' => 'decimal:2',
    ];
}
