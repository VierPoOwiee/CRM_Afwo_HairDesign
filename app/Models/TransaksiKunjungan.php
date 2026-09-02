<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\KomisiTransaksi;

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
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
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
