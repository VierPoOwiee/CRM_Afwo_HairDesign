<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    public const STOK_MENIPIS = 10;

    /** Tipe produk yang dipakai staf saat layanan (bukan dijual per pcs). */
    public const KATEGORI_LAYANAN = [
        'Color',
        'Bleaching',
        'Oxidant',
        'Keratin',
        'Smoothing',
        'Hairtreatment',
        'Creambath',
        'Serum',
        'Repair',
        'Keriting',
        'Masker',
    ];

    /** Daftar tipe produk yang bisa dipakai saat layanan. */
    public static function kategoriLayanan(): array
    {
        return self::KATEGORI_LAYANAN;
    }

    protected $table = 'produk';

    protected $fillable = [
        'nama_produk',
        'merek',
        'kategori_produk',
        'satuan_penggunaan',
        'netto',
        'sisa_penggunaan',
        'harga_per_pcs',
        'harga_per_penggunaan',
        'harga_modal_rata_rata',
        'stok',
        'aktif',
    ];

    protected $casts = [
        'netto' => 'decimal:2',
        'sisa_penggunaan' => 'decimal:2',
        'harga_per_pcs' => 'decimal:2',
        'harga_per_penggunaan' => 'decimal:2',
        'harga_modal_rata_rata' => 'decimal:2',
        'stok' => 'integer',
        'aktif' => 'boolean',
    ];

    public function layanan(): BelongsToMany
    {
        return $this->belongsToMany(Layanan::class, 'layanan_produk', 'id_produk', 'id_layanan');
    }

    public function pembelianProduk(): HasMany
    {
        return $this->hasMany(PembelianProduk::class, 'produk_id');
    }

    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatStokProduk::class, 'produk_id');
    }

    public function labelKategori(): string
    {
        if (in_array($this->kategori_produk, self::KATEGORI_LAYANAN, true)) {
            return $this->kategori_produk;
        }

        return (string) $this->kategori_produk;
    }

    /**
     * Label harga jual per pcs, atau null jika tidak dijual per pcs.
     */
    public function labelHargaPcs(): ?string
    {
        if ((float) $this->harga_per_pcs <= 0) {
            return null;
        }

        return 'Rp'.number_format((float) $this->harga_per_pcs, 0, ',', '.').'/pcs';
    }

    /**
     * Label harga pemakaian layanan, mis. "Rp100.000/10ml", atau null jika kosong.
     */
    public function labelHargaPenggunaan(): ?string
    {
        if ((float) $this->harga_per_penggunaan <= 0) {
            return null;
        }

        return 'Rp'.number_format((float) $this->harga_per_penggunaan, 0, ',', '.').$this->satuanPenggunaanLabel();
    }

    /**
     * Gabungan kedua harga untuk ditampilkan singkat.
     */
    public function labelHarga(): string
    {
        $parts = array_filter([$this->labelHargaPcs(), $this->labelHargaPenggunaan()]);

        return empty($parts) ? '-' : implode(' · ', $parts);
    }

    /**
     * Satuan penggunaan siap tampil, mis. "/10ml" atau "per gram".
     */
    public function satuanPenggunaanLabel(): string
    {
        $satuan = trim((string) $this->satuan_penggunaan);

        return $satuan === '' ? '/10ml' : $satuan;
    }

    /**
     * Pembagi untuk menghitung biaya pemakaian: jumlah unit dari angka pada
     * satuan penggunaan. Contoh "/10ml" => 10, "/20ml" => 20, "per gram" => 1.
     */
    public function divisorPenggunaan(): float
    {
        if (preg_match('/(\d+(?:[.,]\d+)?)/', str_replace(',', '.', $this->satuanPenggunaanLabel()), $m)) {
            $divisor = (float) $m[1];

            return $divisor > 0 ? $divisor : 1.0;
        }

        return 1.0;
    }

    public function stokMenipis(): bool
    {
        return $this->stok <= self::STOK_MENIPIS;
    }

    /**
     * Berapa kali satu pcs bisa dipakai untuk layanan = netto / pembagi satuan.
     * Contoh netto 100, satuan "/10ml" => 10 kali. Null jika netto belum diisi.
     */
    public function jumlahPemakaianPerPcs(): ?float
    {
        $netto = (float) $this->netto;
        if ($netto <= 0) {
            return null;
        }

        return round($netto / $this->divisorPenggunaan(), 2);
    }

    public function labelNetto(): ?string
    {
        if ((float) $this->netto <= 0) {
            return null;
        }

        $angka = rtrim(rtrim(number_format((float) $this->netto, 2, ',', '.'), '0'), ',');
        $satuan = ltrim($this->satuanPenggunaanLabel(), '/');

        return $angka.' '.$satuan;
    }

    public function labelModal(): string
    {
        return 'Rp'.number_format((float) $this->harga_modal_rata_rata, 0, ',', '.');
    }

    /**
     * Label modal per pcs, mis. "Rp50.000/pcs".
     */
    public function labelModalPerSatuan(): string
    {
        return 'Rp'.number_format((float) $this->harga_modal_rata_rata, 0, ',', '.').'/pcs';
    }
}
