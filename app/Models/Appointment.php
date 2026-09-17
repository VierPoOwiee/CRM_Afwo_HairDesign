<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    /**
     * Jam operasional salon (default 09:00 - 20:00).
     */
    public const JAM_BUKA = 9;

    public const JAM_TUTUP = 20;

    protected $fillable = [
        'id_pelanggan',
        'id_karyawan',
        'tanggal',
        'jam_mulai',
        'durasi_menit',
        'preferensi',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'datetime',
        'durasi_menit' => 'integer',
        'status' => 'string',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    /**
     * Layanan yang dipesan pada appointment ini (bisa lebih dari satu).
     */
    public function layanans(): BelongsToMany
    {
        return $this->belongsToMany(Layanan::class, 'appointment_layanan')
            ->withTimestamps();
    }

    /**
     * Jam selesai berdasarkan jam_mulai + durasi_menit.
     */
    public function jamSelesai(): ?\Illuminate\Support\Carbon
    {
        if (! $this->jam_mulai) {
            return null;
        }

        return $this->jam_mulai->copy()->addMinutes((int) $this->durasi_menit);
    }

    /**
     * Nama semua layanan pada appointment ini, dipisah koma (untuk pesan
     * flash / pencarian), memanfaatkan relasi $this->layanans.
     */
    public function layananNama(): string
    {
        return $this->layanans->pluck('nama_layanan')->implode(', ');
    }
}
