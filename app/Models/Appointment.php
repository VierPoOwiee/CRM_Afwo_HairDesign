<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';

    public const KUOTA_MAKSIMAL = 10;

    public const JAM_BUKA = 9;

    public const JAM_TUTUP = 18;

    public const BOBOT_KATEGORI = [
        'Potong' => 1,
        'Styling' => 1,
        'Treatment Rambut' => 2,
        'Treatment' => 2,
        'Warna Rambut' => 3,
    ];

    protected $fillable = [
        'tanggal',
        'waktu',
        'nama',
        'service',
        'kategori',
        'no_wa',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Nama hari dalam Bahasa Indonesia (Minggu..Sabtu).
     */
    public function hari(): string
    {
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $hari[$this->tanggal->dayOfWeek];
    }

    /**
     * Total bobot kuota yang terpakai pada slot tanggal + waktu tertentu.
     *
     * @param  string  $tanggal
     * @param  string  $waktu
     * @param  int|null  $excludeId  id appointment yang dikecualikan (saat edit)
     */
    public static function kuotaTerpakai(string $tanggal, string $waktu, ?int $excludeId = null): int
    {
        $query = static::query()
            ->whereDate('tanggal', $tanggal)
            ->where('waktu', $waktu);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $bobot = self::BOBOT_KATEGORI;

        return (int) $query->get()->sum(function ($appointment) use ($bobot) {
            $kategori = $appointment->kategori;

            return $bobot[$kategori] ?? 1;
        });
    }

    /**
     * Daftar slot waktu WITA (jam buka s/d tutup, tiap 30 menit), format "HH:MM".
     */
    public static function slotWaktu(): array
    {
        $slot = [];

        for ($menit = self::JAM_BUKA * 60; $menit <= self::JAM_TUTUP * 60; $menit += 30) {
            $slot[] = sprintf('%02d:%02d', intdiv($menit, 60), $menit % 60);
        }

        return $slot;
    }

    /**
     * Sisa kuota untuk setiap slot waktu pada tanggal tertentu.
     *
     * Mengembalikan array berbentuk ['HH:MM' => sisa, ...].
     *
     * @param  string  $tanggal
     * @param  int|null  $excludeId
     */
    public static function slotKuota(string $tanggal, ?int $excludeId = null): array
    {
        $result = [];

        foreach (self::slotWaktu() as $waktu) {
            $result[$waktu] = max(0, self::KUOTA_MAKSIMAL - self::kuotaTerpakai($tanggal, $waktu, $excludeId));
        }

        return $result;
    }

    /**
     * Bobot kuota untuk sebuah kategori.
     *
     * @param  string|null  $kategori
     */
    public static function bobot(?string $kategori): int
    {
        return self::BOBOT_KATEGORI[$kategori ?? ''] ?? 1;
    }
}