<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'id_staf',
        'tanggal',
        'hadir',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'hadir' => 'boolean',
    ];

    public function staf(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_staf');
    }
}