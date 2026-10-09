<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Appointment;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentAbsensiTest extends TestCase
{
    use RefreshDatabase;

    private function loginKasir(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']));
    }

    private function buatStaf(string $skema = 'per_layanan'): Karyawan
    {
        return Karyawan::create([
            'nama' => 'Stylist Test',
            'kontak' => '0812',
            'skema_komisi' => $skema,
            'persen_komisi_harian' => 0,
            'gaji_pokok' => 0,
        ]);
    }

    private function buatLayanan(): Layanan
    {
        return Layanan::create([
            'nama_layanan' => 'Creambath',
            'kategori' => 'Treatment',
            'aktif' => true,
            'termasuk_potong' => false,
        ]);
    }

    public function test_appointment_crud_flow(): void
    {
        $this->loginKasir();
        $pelanggan = Pelanggan::create(['nama' => 'Klien', 'no_wa' => '+6281234500000']);
        $staf = $this->buatStaf();
        $layanan = $this->buatLayanan();

        // Store
        $response = $this->post(route('appointment.store'), [
            'id_pelanggan' => $pelanggan->id,
            'id_karyawan' => $staf->id,
            'id_layanan' => [$layanan->id],
            'tanggal' => '2026-10-20',
            'jam_mulai' => '10:00',
            'durasi_menit' => 60,
            'preferensi' => 'Hair mask spesial',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id_pelanggan' => $pelanggan->id,
            'id_karyawan' => $staf->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '10:00:00',
            'preferensi' => 'Hair mask spesial',
        ]);
        $appointment = Appointment::first();
        $this->assertDatabaseHas('appointment_layanan', ['appointment_id' => $appointment->id, 'layanan_id' => $layanan->id]);

        // Show (JSON)
        $this->getJson(route('appointment.show', $appointment))
            ->assertOk()
            ->assertJson(['id' => $appointment->id, 'jam_mulai' => '10:00', 'layanan' => 'Creambath']);

        // Update
        $this->put(route('appointment.update', $appointment), [
            'id_pelanggan' => $pelanggan->id,
            'id_karyawan' => $staf->id,
            'id_layanan' => [$this->buatLayanan()->id, $layanan->id],
            'tanggal' => '2026-10-21',
            'jam_mulai' => '11:30',
            'durasi_menit' => 90,
            'preferensi' => null,
        ])->assertRedirect();

        $appointment->refresh();
        $this->assertSame('2026-10-21', $appointment->tanggal->format('Y-m-d'));
        $this->assertSame('11:30', $appointment->jam_mulai->format('H:i'));
        $this->assertSame(90, $appointment->durasi_menit);
        $this->assertSame(2, $appointment->layanans()->count());

        // Mark selesai
        $this->put(route('appointment.selesai', $appointment))->assertRedirect();
        $this->assertSame('selesai', $appointment->fresh()->status);

        // Destroy
        $this->delete(route('appointment.destroy', $appointment))->assertRedirect();
        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_appointment_validasi_layanan_wajib(): void
    {
        $this->loginKasir();
        $this->post(route('appointment.store'), [
            'id_pelanggan' => 1,
            'id_karyawan' => 1,
            'id_layanan' => [],
            'tanggal' => '2026-10-20',
            'jam_mulai' => '10:00',
            'durasi_menit' => 60,
        ])->assertSessionHasErrors(['id_pelanggan', 'id_karyawan', 'id_layanan']);
    }

    public function test_kalender_februari_2028_kabisat_mulai_senin(): void
    {
        $this->loginKasir();
        $response = $this->get(route('appointment.index', ['bulan' => '2028-02']));
        $response->assertOk();

        $hariGrid = $response->viewData('hariGrid');
        $this->assertTrue($hariGrid->isNotEmpty());

        $pertengahanFebruari = $hariGrid->first(fn ($h) => $h['tanggal']->format('Y-m-d') === '2028-02-15');
        // 15 Feb 2028 adalah Selasa → grid hari dimulai Senin
        $this->assertSame(2, $pertengahanFebruari['tanggal']->dayOfWeek, 'Grid harus mulai hari Senin');

        $jumlahHari = count($hariGrid);
        $this->assertSame(0, $jumlahHari % 7, 'Jumlah sel harus kelipatan 7');
        // Feb 2028 punya 29 hari + tailing ke bulan lain → dalam rentang
        $this->assertGreaterThanOrEqual(28, $jumlahHari);

        $tanggalDalam = $hariGrid->filter(fn ($h) => $h['dalamBulanIni'])->map(fn ($h) => $h['tanggal']->format('Y-m-d'));
        $this->assertSame('2028-02-29', $tanggalDalam->last());
    }

    public function test_kalender_parameter_bulan_invalid_fallback_ke_bulan_sekarang(): void
    {
        $this->loginKasir();
        $response = $this->get(route('appointment.index', ['bulan' => 'not-a-date']));
        $response->assertOk();

        $bulanAktif = $response->viewData('bulanAktif');
        $this->assertSame(now()->format('Y-m'), $bulanAktif->format('Y-m'));
    }

    public function test_absensi_masuk_pulang_tidak_dobel_per_staf_per_tanggal(): void
    {
        $this->loginKasir();
        $staf1 = $this->buatStaf();
        $staf2 = $this->buatStaf();
        $tanggal = now()->toDateString();

        $this->post(route('absensi.store'), [
            'tanggal' => $tanggal,
            'hadir' => [(string) $staf1->id => '1'],
        ])->assertRedirect();

        // Simpan ulang di tanggal yang sama → updateOrCreate, tidak dobel
        $this->post(route('absensi.store'), [
            'tanggal' => $tanggal,
            'hadir' => [(string) $staf1->id => '1', (string) $staf2->id => '1'],
        ])->assertRedirect();

        $this->assertSame(1, Absensi::where('id_staf', $staf1->id)->where('tanggal', $tanggal)->count());
        $this->assertSame(1, Absensi::where('id_staf', $staf2->id)->where('tanggal', $tanggal)->count());
        $this->assertTrue((bool) Absensi::where('id_staf', $staf1->id)->where('tanggal', $tanggal)->first()->hadir);
        $this->assertTrue((bool) Absensi::where('id_staf', $staf2->id)->where('tanggal', $tanggal)->first()->hadir);

        $response = $this->get(route('absensi.index', ['tanggal' => $tanggal]));
        $response->assertOk();
    }

    public function test_absensi_uang_makan_hanya_untuk_skema_per_layanan(): void
    {
        $this->loginKasir();
        $perLayanan = $this->buatStaf();
        $persen = $this->buatStaf('persen_omset_harian');
        $persen->update(['persen_komisi_harian' => 10]);
        $tanggal = now()->toDateString();

        $hadir = [(string) $perLayanan->id => '1', (string) $persen->id => '1'];
        $this->post(route('absensi.store'), ['tanggal' => $tanggal, 'hadir' => $hadir])->assertRedirect();

        $response = $this->get(route('absensi.index', ['bulan' => now()->format('Y-m'), 'tanggal' => $tanggal]));
        $response->assertOk();

        $ringkasan = $response->viewData('ringkasan');
        $uangPerLayanan = $ringkasan->firstWhere('karyawan.id', $perLayanan->id)['uang_makan'];
        $uangPersen = $ringkasan->firstWhere('karyawan.id', $persen->id)['uang_makan'];

        $this->assertSame(25000, $uangPerLayanan);
        $this->assertSame(0, $uangPersen);
    }
}