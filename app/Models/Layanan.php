<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';

    protected $fillable = [
        'nama_layanan',
        'kategori',
        'aktif',
        'termasuk_potong',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'termasuk_potong' => 'boolean',
    ];

    public function hargaLayanan(): HasMany
    {
        return $this->hasMany(HargaLayanan::class, 'id_layanan');
    }

    public function produk(): BelongsToMany
    {
        return $this->belongsToMany(Produk::class, 'layanan_produk', 'id_layanan', 'id_produk');
    }
}
