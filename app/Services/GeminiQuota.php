<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

final class GeminiQuota
{
    private const KUNCI_AWAL = 'gemini_quota:';

    public function kunciHariIni(): string
    {
        return self::KUNCI_AWAL.now()->toDateString();
    }

    public function batas(): int
    {
        $batas = (int) config('services.gemini.daily_limit', 30);

        return $batas > 0 ? $batas : 30;
    }

    public function terpakaiHariIni(): int
    {
        return (int) Cache::get($this->kunciHariIni(), 0);
    }

    public function sisaHariIni(): int
    {
        return max(0, $this->batas() - $this->terpakaiHariIni());
    }

    public function resetPada(): Carbon
    {
        return Carbon::tomorrow()->startOfDay();
    }

    public function habis(): bool
    {
        return $this->terpakaiHariIni() >= $this->batas();
    }

    public function hampirHabis(): bool
    {
        return ! $this->habis() && $this->sisaHariIni() <= 5;
    }

    public function bolehGunakan(): bool
    {
        return ! $this->habis();
    }

    public function catatPenggunaan(): void
    {
        $kunci = $this->kunciHariIni();

        if (! Cache::has($kunci)) {
            $ttl = max(60, $this->resetPada()->diffInSeconds(now()));
            Cache::put($kunci, 0, $ttl);
        }

        Cache::increment($kunci);
    }

    public function detail(): array
    {
        $resetPada = $this->resetPada();

        return [
            'terpakai' => $this->terpakaiHariIni(),
            'batas' => $this->batas(),
            'sisa' => $this->sisaHariIni(),
            'habis' => $this->habis(),
            'hampir_habis' => $this->hampirHabis(),
            'reset_pada' => $resetPada,
            'label_reset' => 'pukul '.$resetPada->format('H:i'),
        ];
    }
}