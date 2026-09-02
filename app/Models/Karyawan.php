<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    protected $fillable = [
        'nama',
        'kontak',
        'skema_komisi',
        'persen_komisi_harian',
        'gaji_pokok',
    ];

    protected $casts = [
        'persen_komisi_harian' => 'decimal:2',
        'gaji_pokok' => 'decimal:2',
    ];

    public function labelSkema(): string
    {
        return $this->skema_komisi === 'persen_omset_harian'
            ? 'Persen Omset Harian ('.$this->persen_komisi_harian.'%)'
            : 'Per Layanan';
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'id_staf');
    }

    public function komisiTransaksi(): HasMany
    {
        return $this->hasMany(KomisiTransaksi::class, 'id_staf');
    }

    public function komisiHarianSpesial(): HasMany
    {
        return $this->hasMany(KomisiHarianSpesial::class, 'id_staf');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'id_staf');
    }

    /**
     * Get the latest komisi_harian_spesial record for this staff.
     */
    public function latestKomisiHarian(): HasOne
    {
        return $this->hasOne(KomisiHarianSpesial::class, 'id_staf')->latestOfMany('tanggal');
    }
}
