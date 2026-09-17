<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\KomisiTransaksi;
use App\Services\ProdukModalService;

class TransaksiKunjungan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_kunjungan';

    protected $fillable = [
        'id_pelanggan',
        'jenis_pengerjaan',
        'no_struk',
        'waktu_kunjungan',
        'total_bayar',
        'metode_pembayaran',
        'status',
    ];

    protected $casts = [
        'waktu_kunjungan' => 'datetime',
        'total_bayar' => 'decimal:2',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan')->withTrashed();
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    public function komisiTransaksi(): HasMany
    {
        return $this->hasMany(KomisiTransaksi::class, 'id_transaksi');
    }

    public static function generateNoStruk(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';
        $last = self::where('no_struk', 'like', $prefix . '%')
            ->orderByDesc('no_struk')
            ->value('no_struk');

        if ($last) {
            $lastNum = (int) substr($last, -6);
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    }

    public function recalculateTotal(): void
    {
        $this->total_bayar = $this->details()->sum('subtotal');
        $this->save();
    }

    /**
     * Kembalikan stok produk yang terpakai/dijual di transaksi ini
     * agar stok tetap konsisten saat transaksi dihapus.
     */
    public function restoreStock(string $keterangan = 'Stok dikembalikan'): void
    {
        $dicatatOleh = auth()->check() ? auth()->id() : null;

        foreach ($this->details()->with('produkPenggunaan')->get() as $detail) {
            if ($detail->tipe_item === 'produk' && $detail->id_produk) {
                $produk = Produk::lockForUpdate()->find($detail->id_produk);
                if ($produk) {
                    $produk->increment('stok', $detail->qty);
                    ProdukModalService::catat(
                        $produk,
                        RiwayatStokProduk::JENIS_BATAL,
                        $detail->qty,
                        $produk->stok,
                        $keterangan,
                        $dicatatOleh
                    );
                }
            }

            foreach ($detail->produkPenggunaan as $pu) {
                $produk = Produk::lockForUpdate()->find($pu->id_produk);
                if ($produk) {
                    $produk->increment('stok', $pu->pemakaian_ml);
                    ProdukModalService::catat(
                        $produk,
                        RiwayatStokProduk::JENIS_BATAL,
                        $pu->pemakaian_ml,
                        $produk->stok,
                        $keterangan,
                        $dicatatOleh
                    );
                }
            }
        }
    }

    public function labelMetode(): string
    {
        return match ($this->metode_pembayaran) {
            'cash' => 'Cash',
            'qris_bni' => 'QRIS BNI',
            'qris_bri' => 'QRIS BRI',
            'debit' => 'Debit',
            'kartu_kredit' => 'Kartu Kredit',
            'transfer' => 'Transfer',
            default => $this->metode_pembayaran,
        };
    }
}
