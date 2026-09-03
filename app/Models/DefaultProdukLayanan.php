<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultProdukLayanan extends Model
{
    use HasFactory;

    protected $table = 'default_produk_layanan';

    protected $fillable = [
        'id_harga_layanan',
        'kategori_produk',
        'default_ml',
    ];

    protected $casts = [
        'default_ml' => 'decimal:2',
    ];

    public function hargaLayanan(): BelongsTo
    {
        return $this->belongsTo(HargaLayanan::class, 'id_harga_layanan');
    }
}