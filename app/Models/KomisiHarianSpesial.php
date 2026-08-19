<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomisiHarianSpesial extends Model
{
    use HasFactory;

    protected $table = 'komisi_harian_spesial';

    protected $fillable = [
        'id_staf',
        'tanggal',
        'total_omset_dasar',
        'persen',
        'jumlah_komisi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_omset_dasar' => 'decimal:2',
        'persen' => 'decimal:2',
        'jumlah_komisi' => 'decimal:2',
    ];

    public function staf(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_staf');
    }

    /**
     * Calculate daily commission for a specific staff on a specific date.
     * Basis: sum subtotal from detail_transaksi on that date (status=selesai),
     * EXCLUDING items where layanan.termasuk_potong=true.
     */
    public static function calculateForDate(Karyawan $staf, $tanggal): void
    {
        $persen = (float) $staf->persen_komisi_harian;
        if ($persen <= 0) {
            return;
        }

        $tanggalCarbon = \Carbon\Carbon::parse($tanggal)->startOfDay();
        $tanggalEnd = $tanggalCarbon->copy()->endOfDay();

        $totalSubtotal = DetailTransaksi::whereHas('transaksi', function ($q) use ($tanggalCarbon, $tanggalEnd) {
                $q->where('waktu_kunjungan', '>=', $tanggalCarbon)
                  ->where('waktu_kunjungan', '<=', $tanggalEnd)
                  ->where('status', 'selesai');
            })
            ->where('tipe_item', 'layanan')
            ->whereHas('layanan', function ($q) {
                $q->where('termasuk_potong', false);
            })
            ->sum('subtotal');

        // Also include produk items (not tipe_item=layanan) in omset
        $totalSubtotalProduk = DetailTransaksi::whereHas('transaksi', function ($q) use ($tanggalCarbon, $tanggalEnd) {
                $q->where('waktu_kunjungan', '>=', $tanggalCarbon)
                  ->where('waktu_kunjungan', '<=', $tanggalEnd)
                  ->where('status', 'selesai');
            })
            ->where('tipe_item', 'produk')
            ->sum('subtotal');

        $totalOmset = (float) $totalSubtotal + (float) $totalSubtotalProduk;
        $jumlahKomisi = $totalOmset * ($persen / 100);

        self::updateOrCreate(
            [
                'id_staf' => $staf->id,
                'tanggal' => $tanggalCarbon->toDateString(),
            ],
            [
                'total_omset_dasar' => $totalOmset,
                'persen' => $persen,
                'jumlah_komisi' => round($jumlahKomisi),
            ]
        );
    }
}
