<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembelianProduk extends Model
{
    use HasFactory;

    protected $table = 'pembelian_produk';

    protected $fillable = [
        'produk_id',
        'qty',
        'harga_beli',
        'harga_modal_rata_rata',
        'stok_setelah',
        'keterangan',
        'dicatat_oleh',
        'tanggal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_beli' => 'decimal:2',
        'harga_modal_rata_rata' => 'decimal:2',
        'stok_setelah' => 'integer',
        'tanggal' => 'date',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}