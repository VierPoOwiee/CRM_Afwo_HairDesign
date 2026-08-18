<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $fillable = [
        'nama_layanan',
        'kategori',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function hargaLayanan(): HasMany
    {
        return $this->hasMany(HargaLayanan::class, 'id_layanan');
    }
}
