<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaLayanan extends Model
{
    use HasFactory;

    protected $table = 'harga_layanan';

    protected $fillable = [
        'id_layanan',
        'varian',
        'harga_dasar_min',
        'harga_dasar_max',
        'tarif_kelebihan_per_10gr',
        'komisi_min',
        'komisi_max',
    ];

    protected $casts = [
        'harga_dasar_min' => 'decimal:2',
        'harga_dasar_max' => 'decimal:2',
        'tarif_kelebihan_per_10gr' => 'decimal:2',
        'komisi_min' => 'decimal:2',
        'komisi_max' => 'decimal:2',
    ];

    public function layanan(): BelongsTo
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }

    public function labelHargaDasar(): string
    {
        $min = (float) $this->harga_dasar_min;
        $max = (float) $this->harga_dasar_max;

        $label = 'Rp'.number_format($min, 0, ',', '.');

        if ($max !== $min) {
            $label .= ' - Rp'.number_format($max, 0, ',', '.');
        }

        return $label;
    }

    public function labelHarga(): string
    {
        $label = $this->labelHargaDasar();

        if ($this->tarif_kelebihan_per_10gr) {
            $label .= ' (+Rp'.number_format((float) $this->tarif_kelebihan_per_10gr, 0, ',', '.').'/10gr kelebihan)';
        }

        return $label;
    }

    public function labelKomisi(): ?string
    {
        if ($this->komisi_min === null && $this->komisi_max === null) {
            return null;
        }

        $min = $this->komisi_min !== null ? (float) $this->komisi_min : null;
        $max = $this->komisi_max !== null ? (float) $this->komisi_max : null;

        if ($min !== null && $max !== null && $min === $max) {
            return 'Rp'.number_format($min, 0, ',', '.');
        }

        return 'Rp'.number_format($min ?? 0, 0, ',', '.')
            .' - Rp'.number_format($max ?? 0, 0, ',', '.');
    }
}
