<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksiProduk extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi_produk';

    protected $fillable = [
        'id_detail_transaksi',
        'id_produk',
        'pemakaian_ml',
        'harga_per_unit',
        'subtotal',
    ];

    protected $casts = [
        'pemakaian_ml' => 'decimal:2',
        'harga_per_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function detailTransaksi(): BelongsTo
    {
        return $this->belongsTo(DetailTransaksi::class, 'id_detail_transaksi');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function computeSubtotal(): float
    {
        $pemakaianMl = (float) $this->pemakaian_ml;
        $hargaPerUnit = (float) $this->harga_per_unit;

        if ($pemakaianMl <= 0 || $hargaPerUnit <= 0) {
            return 0;
        }

        $unit = $pemakaianMl / 10;

        return $unit * $hargaPerUnit;
    }
}
