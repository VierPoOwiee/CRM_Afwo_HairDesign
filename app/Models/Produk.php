<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    public const STOK_MENIPIS = 10;

    protected $table = 'produk';

    protected $fillable = [
        'nama_produk',
        'merek',
        'kategori_produk',
        'satuan',
        'harga_per_satuan',
        'stok',
        'aktif',
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2',
        'stok' => 'integer',
        'aktif' => 'boolean',
    ];

    public function labelKategori(): string
    {
        return $this->kategori_produk === 'dijual'
            ? 'Dijual Per PCS'
            : 'Dipakai Layanan';
    }

    public function labelHarga(): string
    {
        $harga = 'Rp'.number_format((float) $this->harga_per_satuan, 0, ',', '.');

        if (str_starts_with($this->satuan, '/')) {
            return $harga.$this->satuan;
        }

        return $harga.'/'.$this->satuan;
    }

    public function stokMenipis(): bool
    {
        return $this->stok <= self::STOK_MENIPIS;
    }
}
