<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanAi extends Model
{
    protected $table = 'pertanyaan_ai';

    protected $fillable = [
        'sesi_id',
        'periode',
        'pertanyaan',
        'jawaban',
        'dibuat_pada',
    ];

    protected $casts = [
        'periode' => 'date',
        'dibuat_pada' => 'datetime',
    ];
}
