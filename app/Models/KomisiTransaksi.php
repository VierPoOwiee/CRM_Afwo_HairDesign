<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomisiTransaksi extends Model
{
    use HasFactory;

    protected $table = 'komisi_transaksi';

    protected $fillable = [
        'id_transaksi',
        'id_staf',
        'jumlah_komisi',
        'keterangan',
    ];

    protected $casts = [
        'jumlah_komisi' => 'decimal:2',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiKunjungan::class, 'id_transaksi');
    }

    public function staf(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_staf');
    }

    /**
     * Recalculate komisi for a given staff in a transaction.
     * Sums komisi_nominal from detail_transaksi where tipe_item=layanan
     * (skip staff with persen_omset_harian scheme).
     * Returns the calculated sum (but doesn't save — caller decides to save or let admin edit).
     */
    public static function calculateDefault(TransaksiKunjungan $transaksi, Karyawan $staf): float
    {
        return (float) $transaksi->details()
            ->where('id_staf', $staf->id)
            ->where('tipe_item', 'layanan')
            ->sum('komisi_nominal');
    }

    /**
     * Sync komisi_transaksi records for a transaction.
     * For each unique staff in detail_transaksi, create or update komisi_transaksi.
     * Skip staff with persen_omset_harian scheme.
     * Only auto-set if jumlah_komisi is 0 (new record).
     */
    public static function syncForTransaksi(TransaksiKunjungan $transaksi): void
    {
        $staffIds = $transaksi->details()
            ->whereNotNull('id_staf')
            ->pluck('id_staf')
            ->unique();

        foreach ($staffIds as $stafId) {
            $staf = Karyawan::find($stafId);
            if (!$staf || $staf->skema_komisi === 'persen_omset_harian') {
                continue;
            }

            $default = self::calculateDefault($transaksi, $staf);

            self::updateOrCreate(
                [
                    'id_transaksi' => $transaksi->id,
                    'id_staf' => $stafId,
                ],
                [
                    'jumlah_komisi' => $default,
                ]
            );
        }
    }
}
