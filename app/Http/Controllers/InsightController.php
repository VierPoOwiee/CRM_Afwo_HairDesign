<?php

namespace App\Http\Controllers;

use App\Models\InsightAi;
use App\Models\PertanyaanAi;
use App\Services\GeminiQuota;
use App\Services\LaporanAiInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function generate(Request $request)
    {
        $periode = $request->input('periode', Carbon::now()->format('Y-m'));
        $bulan = Carbon::parse($periode.'-01');

        $cooldownMinutes = 60;
        $existing = InsightAi::where('periode', $bulan->startOfMonth()->toDateString())->first();

        if ($existing && $existing->dibuat_pada->diffInMinutes(now()) < $cooldownMinutes) {
            return back()->with('insight_info', 'Analisa untuk bulan ini baru saja digenerate. Coba lagi dalam beberapa menit.');
        }

        $kuota = new GeminiQuota;
        if ($kuota->habis()) {
            return back()->with('error', 'Kuota AI hari ini sudah habis ('.$kuota->terpakaiHariIni().'/'.$kuota->batas().'). Analisa bisa digenerate lagi besok, reset '.$kuota->detail()['label_reset'].'.');
        }

        try {
            $service = new LaporanAiInsightService;
            $service->generateUntukBulan($bulan);
            $kuota->catatPenggunaan();

            return back()->with('success', 'Analisa AI berhasil digenerate.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate analisa: '.$e->getMessage().'. Coba lagi nanti.');
        }
    }

    public function tanya(Request $request)
    {
        $validated = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:500'],
            'periode' => ['required', 'date_format:Y-m-d'],
        ]);

        $bulan = Carbon::parse($validated['periode'])->startOfMonth();
        $pertanyaan = trim($validated['pertanyaan']);

        $kuota = new GeminiQuota;
        if ($kuota->habis()) {
            return back()
                ->withFragment('tanya-ai')
                ->withInput()
                ->with('error', 'Kuota AI hari ini sudah habis ('.$kuota->terpakaiHariIni().'/'.$kuota->batas().'). Bisa tanya lagi besok, reset '.$kuota->detail()['label_reset'].'.');
        }

        try {
            $service = new LaporanAiInsightService;
            $jawaban = $service->tanyaJawab($bulan, $pertanyaan);
            $kuota->catatPenggunaan();
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menjawab pertanyaan: '.$e->getMessage());
        }

        PertanyaanAi::create([
            'periode' => $bulan->toDateString(),
            'pertanyaan' => $pertanyaan,
            'jawaban' => $jawaban,
            'dibuat_pada' => now(),
        ]);

        return back()->withFragment('tanya-ai')->with('success', 'Pertanyaan berhasil dijawab.');
    }
}
