<?php

namespace App\Console\Commands;

use App\Services\LaporanAiInsightService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInsightBulanan extends Command
{
    protected $signature = 'insight:generate-bulanan {bulan?}';

    protected $description = 'Generate insight AI untuk laporan bulanan. Default: bulan sebelumnya.';

    public function handle(): int
    {
        $bulan = $this->argument('bulan')
            ? Carbon::parse($this->argument('bulan'))
            : Carbon::now()->subMonth();

        $this->info('Generating insight untuk: '.$bulan->format('F Y'));

        try {
            $service = new LaporanAiInsightService;
            $insight = $service->generateUntukBulan($bulan);

            $this->info('Insight berhasil digenerate.');
            $this->line('Periode: '.$insight->periode->format('d M Y'));
            $this->line('Headline: '.($insight->konten_insight['headline'] ?? '-'));
            $this->line('Jumlah sorotan: '.count($insight->konten_insight['sorotan'] ?? []));

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal generate insight: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
