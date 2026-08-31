<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $fillable = [
        'id_transaksi',
        'id_staf',
        'tipe_item',
        'id_layanan',
        'id_produk',
        'varian_dipilih',
        'ketebalan_rambut',
        'gram_pemakaian_tambahan',
        'qty',
        'harga_saat_transaksi',
        'diskon',
        'subtotal',
        'komisi_nominal',
        'catatan',
    ];

    protected $casts = [
        'gram_pemakaian_tambahan' => 'decimal:2',
        'qty' => 'decimal:2',
        'harga_saat_transaksi' => 'decimal:2',
        'diskon' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'komisi_nominal' => 'decimal:2',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiKunjungan::class, 'id_transaksi');
    }

    public function staf(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_staf');
    }

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function produkPenggunaan(): HasMany
    {
        return $this->hasMany(DetailTransaksiProduk::class, 'id_detail_transaksi');
    }

    public function computeSubtotal(): float
    {
        return max(0, ((float) $this->harga_saat_transaksi * (float) $this->qty) - (float) ($this->diskon ?? 0));
    }

    public function computeHargaSuggested(float $hargaDasarMin, ?float $tarifKelebihan): float
    {
        $gramTambahan = (float) $this->gram_pemakaian_tambahan;

        if ($gramTambahan <= 0 || !$tarifKelebihan) {
            return $hargaDasarMin;
        }

        $kelipatan = (int) ceil($gramTambahan / 10);
        return $hargaDasarMin + ($kelipatan * (float) $tarifKelebihan);
    }
}
