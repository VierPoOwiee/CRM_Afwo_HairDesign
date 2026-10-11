<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStokProduk extends Model
{
    use HasFactory;

    /** Jenis perubahan stok yang dicatat. */
    public const JENIS_RESTOCK = 'restock';
    public const JENIS_PENJUALAN = 'penjualan';
    public const JENIS_PEMAKAIAN_LAYANAN = 'pemakaian_layanan';
    public const JENIS_BATAL = 'batal';
    public const JENIS_PENYESUAIAN = 'penyesuaian';

    protected $table = 'riwayat_stok_produk';

    protected $fillable = [
        'produk_id',
        'jenis',
        'perubahan',
        'stok_akhir',
        'harga_modal_rata_rata',
        'keterangan',
        'dicatat_oleh',
        'tanggal',
    ];

    protected $casts = [
        'jenis' => 'string',
        'perubahan' => 'decimal:2',
        'stok_akhir' => 'decimal:2',
        'harga_modal_rata_rata' => 'decimal:2',
        'tanggal' => 'datetime',
    ];

    public const LABEL_JENIS = [
        'restock' => 'Restock',
        'penjualan' => 'Penjualan Retail',
        'pemakaian_layanan' => 'Pemakaian Layanan',
        'batal' => 'Transaksi Dibatalkan',
        'penyesuaian' => 'Penyesuaian',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function labelJenis(): string
    {
        return self::LABEL_JENIS[$this->jenis] ?? (string) $this->jenis;
    }
}