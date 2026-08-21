<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\InsightAi;
use App\Models\Pelanggan;
use App\Models\TransaksiKunjungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LaporanAiInsightService
{
    public function generateUntukBulan(Carbon $bulan): InsightAi
    {
        $dataRingkasan = $this->agregasiData($bulan);
        $kontenInsight = $this->panggilGeminiApi($dataRingkasan);

        return InsightAi::updateOrCreate(
            ['periode' => $bulan->copy()->startOfMonth()->toDateString()],
            [
                'data_ringkasan' => $dataRingkasan,
                'konten_insight' => $kontenInsight,
                'dibuat_pada' => now(),
            ]
        );
    }

    private function agregasiData(Carbon $bulan): array
    {
        $bulanIni = $bulan->copy()->startOfMonth();
        $bulanLalu = $bulan->copy()->subMonth()->startOfMonth();

        $omsetBulanIni = $this->hitungOmset($bulanIni);
        $omsetBulanLalu = $this->hitungOmset($bulanLalu);

        $jumlahTransaksiIni = $this->hitungJumlahTransaksi($bulanIni);
        $jumlahTransaksiLalu = $this->hitungJumlahTransaksi($bulanLalu);

        $pelangganBaruIni = $this->hitungPelangganBaru($bulanIni);
        $pelangganBaruLalu = $this->hitungPelangganBaru($bulanLalu);

        $breakdownKategori = $this->hitungBreakdownKategori($bulanIni, $bulanLalu);

        return [
            'omset_bulan_ini' => (int) $omsetBulanIni,
            'omset_bulan_lalu' => (int) $omsetBulanLalu,
            'jumlah_transaksi_ini' => (int) $jumlahTransaksiIni,
            'jumlah_transaksi_lalu' => (int) $jumlahTransaksiLalu,
            'pelanggan_baru_ini' => (int) $pelangganBaruIni,
            'pelanggan_baru_lalu' => (int) $pelangganBaruLalu,
            'breakdown_kategori' => $breakdownKategori,
        ];
    }

    private function hitungOmset(Carbon $bulan): float
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        return TransaksiKunjungan::where('waktu_kunjungan', '>=', $awal)
            ->where('waktu_kunjungan', '<=', $akhir)
            ->where('status', 'selesai')
            ->sum('total_bayar');
    }

    private function hitungJumlahTransaksi(Carbon $bulan): int
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        return TransaksiKunjungan::where('waktu_kunjungan', '>=', $awal)
            ->where('waktu_kunjungan', '<=', $akhir)
            ->where('status', 'selesai')
            ->count();
    }

    private function hitungPelangganBaru(Carbon $bulan): int
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        return Pelanggan::where('created_at', '>=', $awal)
            ->where('created_at', '<=', $akhir)
            ->count();
    }

    private function hitungBreakdownKategori(Carbon $bulanIni, Carbon $bulanLalu): array
    {
        $awalIni = $bulanIni->copy()->startOfMonth();
        $akhirIni = $bulanIni->copy()->endOfMonth();
        $awalLalu = $bulanLalu->copy()->startOfMonth();
        $akhirLalu = $bulanLalu->copy()->endOfMonth();

        $kategoriIni = DetailTransaksi::whereHas('transaksi', function ($q) use ($awalIni, $akhirIni) {
                $q->where('waktu_kunjungan', '>=', $awalIni)
                  ->where('waktu_kunjungan', '<=', $akhirIni)
                  ->where('status', 'selesai');
            })
            ->where('tipe_item', 'layanan')
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.kategori, sum(detail_transaksi.subtotal) as total_subtotal')
            ->groupBy('layanan.kategori')
            ->pluck('total_subtotal', 'kategori');

        $kategoriLalu = DetailTransaksi::whereHas('transaksi', function ($q) use ($awalLalu, $akhirLalu) {
                $q->where('waktu_kunjungan', '>=', $awalLalu)
                  ->where('waktu_kunjungan', '<=', $akhirLalu)
                  ->where('status', 'selesai');
            })
            ->where('tipe_item', 'layanan')
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.kategori, sum(detail_transaksi.subtotal) as total_subtotal')
            ->groupBy('layanan.kategori')
            ->pluck('total_subtotal', 'kategori');

        $semuaKategori = $kategoriIni->keys()->merge($kategoriLalu->keys())->unique();

        $breakdown = $semuaKategori->map(function ($kategori) use ($kategoriIni, $kategoriLalu) {
            return [
                'kategori' => $kategori,
                'omset_ini' => (int) ($kategoriIni->get($kategori, 0)),
                'omset_lalu' => (int) ($kategoriLalu->get($kategori, 0)),
            ];
        })->sortByDesc('omset_ini')->take(5)->values()->toArray();

        return $breakdown;
    }

    private function panggilGeminiApi(array $dataRingkasan): string
    {
        $systemPrompt = 'Kamu adalah analis bisnis untuk salon kecantikan. Kamu akan diberi data ringkasan '
            . 'keuangan bulan ini dibanding bulan lalu. Berikan analisa singkat (maksimal 4-5 paragraf) '
            . 'dalam Bahasa Indonesia yang mudah dipahami pemilik salon (bukan orang teknis): sorot tren '
            . 'penting (naik/turun dan kemungkinan sebabnya berdasarkan data yang ada), dan tutup dengan '
            . '2-3 rekomendasi actionable. Jangan mengarang angka di luar data yang diberikan. Jangan pakai markdown, tulis paragraf biasa.';

        $userMessage = "Data ringkasan bulan ini vs bulan lalu:\n\n" . json_encode($dataRingkasan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $response = Http::timeout(30)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent'
                . '?key=' . config('services.gemini.key'),
            [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userMessage],
                        ],
                    ],
                ],
            ]
        );

        if ($response->failed()) {
            Log::error('Gemini API gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Gemini API gagal merespons (status: ' . $response->status() . ')');
        }

        $konten = $response->json('candidates.0.content.parts.0.text');

        if (empty($konten)) {
            Log::warning('Gemini API mengembalikan response kosong', ['response' => $response->json()]);

            throw new \RuntimeException('Gemini API mengembalikan konten kosong');
        }

        return $konten;
    }
}
