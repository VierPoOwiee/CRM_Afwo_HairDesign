<?php

namespace Tests\Feature;

use App\Models\InsightAi;
use App\Models\PertanyaanAi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InsightTest extends TestCase
{
    use RefreshDatabase;

    private function loginOwner(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner']));
    }

    private function fakeKey(): void
    {
        config(['services.gemini.key' => 'TEST-KEY-RAHASIA-123']);
    }

    private function fakeApiSuccess(string $isiTeks = ''): void
    {
        $isiTeks = $isiTeks ?: json_encode([
            'headline' => 'Omset naik 64% didorong Treatment Rambut',
            'sentiment' => 'positive',
            'sorotan' => [
                ['teks' => 'Treatment naik jadi Rp4.000.000', 'trend' => 'up'],
            ],
            'rekomendasi' => ['Fokus promosi treatment rambut'],
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => $isiTeks]]]],
                ],
            ], 200),
        ]);
    }

    public function test_generate_insight_berhasil_tersimpan(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        $this->fakeApiSuccess();

        $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')])
            ->assertRedirect();

        $this->assertDatabaseHas('insight_ai', [
            'periode' => now()->startOfMonth()->toDateString(),
        ]);
        $insight = InsightAi::first();
        $this->assertSame('Omset naik 64% didorong Treatment Rambut', $insight->konten_insight['headline'] ?? $insight->konten_insight);
    }

    public function test_generate_kedua_terkena_cooldown_1_jam(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        $this->fakeApiSuccess();

        $this->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')]);
        $response = $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')]);

        $response->assertRedirect();
        $response->assertSessionHas('insight_info');
        $this->assertSame(1, InsightAi::count());
    }

    public function test_generate_tanpa_api_key_mengembalikan_pesan_error_bukan_500(): void
    {
        $this->loginOwner();
        config(['services.gemini.key' => null]);

        $response = $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(0, InsightAi::count());
    }

    public function test_generate_saat_kuota_habis_ditolak(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        $this->fakeApiSuccess();
        Cache::put('gemini_quota:'.now()->toDateString(), 30, 600);

        $response = $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')]);

        $response->assertRedirect()->assertSessionHas('error');
        $this->assertSame(0, InsightAi::count());
    }

    public function test_generate_api_gagal_status_403_menampilkan_pesan_ramah(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response('forbidden', 403),
        ]);

        $response = $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_generate_json_tidak_valid_jatuh_ke_fallback(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        $this->fakeApiSuccess('bukan json sama sekali');

        $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')])
            ->assertRedirect();

        $insight = InsightAi::first();
        $this->assertSame('Gagal memproses analisa, coba generate ulang.', $insight->konten_insight['headline']);
    }

    public function test_tanya_dengan_pertanyaan_panjang_ditolak(): void
    {
        $this->loginOwner();
        $this->fakeKey();

        $response = $this->post(route('laporan.insight.tanya'), [
            'pertanyaan' => str_repeat('a', 501),
            'periode' => now()->startOfMonth()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('pertanyaan');
    }

    public function test_tanya_berhasil_menyimpan_pertanyaan_dan_jawaban(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Komisi Rina Rp125.000.']]]],
                ],
            ], 200),
        ]);

        $this->from(route('laporan.penjualan'))
            ->post(route('laporan.insight.tanya'), [
                'pertanyaan' => 'Berapa komisi Rina?',
                'periode' => now()->startOfMonth()->format('Y-m-d'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pertanyaan_ai', [
            'pertanyaan' => 'Berapa komisi Rina?',
        ]);
        $this->assertStringContainsString('Komisi Rina', PertanyaanAi::first()->jawaban);
    }

    public function test_api_key_siang_mengalir_di_html(): void
    {
        $this->loginOwner();
        $this->fakeKey();
        $this->fakeApiSuccess();

        $this->post(route('laporan.insight.generate'), ['periode' => now()->format('Y-m')]);

        $response = $this->get(route('laporan.penjualan', ['dari' => now()->startOfDay()->toDateString(), 'sampai' => now()->endOfDay()->toDateString()]));
        $response->assertOk();
        $response->assertDontSee('TEST-KEY-RAHASIA-123');
    }
}