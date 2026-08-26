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

    public function agregasiData(Carbon $bulan): array
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

    private function panggilGeminiApi(array $dataRingkasan): array
    {
        $systemPrompt = 'Kamu adalah analis bisnis untuk salon kecantikan. Kamu akan diberi data ringkasan '
            .'keuangan bulan ini dibanding bulan lalu. JANGAN menjawab dengan paragraf panjang. '
            .'Balas HANYA dalam format JSON valid (tanpa markdown code fence, tanpa teks pembuka/penutup) '
            .'dengan struktur persis seperti ini:'
            .'{'
            .'"headline": "satu kalimat pendek (maks 15 kata) rangkuman inti bulan ini, contoh: Omset naik 64% didorong Treatment Rambut",'
            .'"sentiment": "positive ATAU negative ATAU neutral (berdasarkan tren omset dominan)",'
            .'"sorotan": ['
            .'  {"teks": "poin singkat maks 12 kata, contoh: Treatment Rambut naik jadi Rp4.000.000", "trend": "up ATAU down ATAU neutral"},'
            .'  ... (3-4 poin sorotan paling penting saja, urutkan dari paling signifikan)'
            .'],'
            .'"rekomendasi": ["rekomendasi singkat maks 12 kata", ... (maksimal 3 item, actionable, bukan teori)]'
            .'} '
            .'Jangan mengarang angka di luar data yang diberikan. Setiap poin sorotan dan rekomendasi HARUS singkat dan langsung ke inti, hindari kalimat penjelas panjang.';

        $userMessage = "Data ringkasan bulan ini vs bulan lalu:\n\n".json_encode($dataRingkasan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $konten = $this->postKeGemini($systemPrompt, $userMessage);

        return $this->parseInsightJson($konten);
    }

    public function tanyaJawab(Carbon $bulan, string $pertanyaan): string
    {
        $dataRingkasan = $this->agregasiData($bulan);

        $systemPromptTanyaJawab = 'Kamu adalah analis bisnis untuk salon kecantikan. Owner akan bertanya '
            .'sesuatu terkait data bisnisnya. Berikut data ringkasan bulan ini vs bulan lalu yang tersedia: '
            .json_encode($dataRingkasan, JSON_UNESCAPED_UNICODE).'. '
            .'Jawab pertanyaan owner secara LANGSUNG dan RINGKAS (maksimal 3-4 kalimat atau beberapa poin '
            .'singkat kalau perlu), dalam Bahasa Indonesia. Kalau pertanyaan owner butuh data yang TIDAK '
            .'tersedia dalam ringkasan ini, katakan terus terang bahwa datanya tidak tersedia di ringkasan '
            .'ini, jangan mengarang jawaban. Hindari paragraf panjang, langsung ke inti jawaban.';

        $userMessage = 'Pertanyaan owner: '.$pertanyaan;

        return $this->postKeGemini($systemPromptTanyaJawab, $userMessage);
    }

    private function postKeGemini(string $systemPrompt, string $userMessage): string
    {
        $response = Http::timeout(30)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/'
                .config('services.gemini.model', 'gemini-3.6-flash')
                .':generateContent?key='.config('services.gemini.key'),
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

            throw new \RuntimeException('Gemini API gagal merespons (status: '.$response->status().')');
        }

        $konten = $response->json('candidates.0.content.parts.0.text');

        if (empty($konten)) {
            Log::warning('Gemini API mengembalikan response kosong', ['response' => $response->json()]);

            throw new \RuntimeException('Gemini API mengembalikan konten kosong');
        }

        return $konten;
    }

    private function parseInsightJson(string $konten): array
    {
        try {
            $hasil = json_decode($this->ekstrakJsonObject($konten), true);

            if (! is_array($hasil) || empty($hasil['headline'])) {
                throw new \RuntimeException('Struktur JSON insight tidak valid');
            }

            $sorotan = collect($hasil['sorotan'] ?? [])
                ->map(function ($item) {
                    $trend = $item['trend'] ?? 'neutral';

                    return [
                        'teks' => trim((string) ($item['teks'] ?? '')),
                        'trend' => in_array($trend, ['up', 'down', 'neutral'], true) ? $trend : 'neutral',
                    ];
                })
                ->filter(fn ($item) => $item['teks'] !== '')
                ->take(4)
                ->values()
                ->toArray();

            $rekomendasi = collect($hasil['rekomendasi'] ?? [])
                ->map(fn ($item) => trim((string) $item))
                ->filter(fn ($item) => $item !== '')
                ->take(3)
                ->values()
                ->toArray();

            $sentiment = $hasil['sentiment'] ?? 'neutral';

            return [
                'headline' => trim((string) $hasil['headline']),
                'sentiment' => in_array($sentiment, ['positive', 'negative', 'neutral'], true) ? $sentiment : 'neutral',
                'sorotan' => $sorotan,
                'rekomendasi' => $rekomendasi,
            ];
        } catch (\Throwable $e) {
            Log::warning('Gagal parse JSON insight dari Gemini', [
                'error' => $e->getMessage(),
                'raw' => mb_substr($konten, 0, 500),
            ]);

            return $this->fallbackInsight();
        }
    }

    private function ekstrakJsonObject(string $konten): string
    {
        $awal = strpos($konten, '{');
        $akhir = strrpos($konten, '}');

        if ($awal === false || $akhir === false || $akhir < $awal) {
            return $konten;
        }

        return substr($konten, $awal, $akhir - $awal + 1);
    }

    private function fallbackInsight(): array
    {
        return [
            'headline' => 'Gagal memproses analisa, coba generate ulang.',
            'sentiment' => 'neutral',
            'sorotan' => [],
            'rekomendasi' => [],
        ];
    }
}
