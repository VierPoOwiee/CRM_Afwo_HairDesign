<?php

namespace App\Http\Controllers;

use App\Models\InsightAi;
use App\Services\LaporanAiInsightService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function generate(Request $request)
    {
        $periode = $request->input('periode', Carbon::now()->format('Y-m'));
        $bulan = Carbon::parse($periode . '-01');

        $cooldownMinutes = 60;
        $existing = InsightAi::where('periode', $bulan->startOfMonth()->toDateString())->first();

        if ($existing && $existing->dibuat_pada->diffInMinutes(now()) < $cooldownMinutes) {
            return back()->with('insight_info', 'Analisa untuk bulan ini baru saja digenerate. Coba lagi dalam beberapa menit.');
        }

        try {
            $service = new LaporanAiInsightService();
            $service->generateUntukBulan($bulan);

            return back()->with('success', 'Analisa AI berhasil digenerate.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate analisa: ' . $e->getMessage() . '. Coba lagi nanti.');
        }
    }
}
