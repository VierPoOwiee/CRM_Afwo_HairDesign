<?php

namespace App\Http\Controllers;

use App\Models\InsightAi;
use App\Models\PertanyaanAi;
use App\Services\GeminiQuota;
use App\Services\LaporanAiInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        ]);

        $pertanyaan = trim($validated['pertanyaan']);
        $periode = Carbon::now()->startOfMonth();
        $sesiId = 'sesi-' . Str::uuid()->toString();

        $kuota = new GeminiQuota;
        if ($kuota->habis()) {
            return back()
                ->withInput()
                ->with('error', 'Kuota AI hari ini sudah habis ('.$kuota->terpakaiHariIni().'/'.$kuota->batas().'). Bisa tanya lagi besok, reset '.$kuota->detail()['label_reset'].'.');
        }

        try {
            $service = new LaporanAiInsightService;
            $jawaban = $service->tanyaJawab($periode, $pertanyaan);
            $kuota->catatPenggunaan();
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menjawab pertanyaan: '.$e->getMessage());
        }

        PertanyaanAi::create([
            'sesi_id' => $sesiId,
            'periode' => $periode->toDateString(),
            'pertanyaan' => $pertanyaan,
            'jawaban' => $jawaban,
            'dibuat_pada' => now(),
        ]);

        return redirect()->route('laporan.ai-chat.sesi.show', $sesiId)
            ->with('success', 'Pertanyaan berhasil dijawab.');
    }
}
