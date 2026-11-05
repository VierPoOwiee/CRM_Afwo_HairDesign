<?php

namespace Tests\Feature;

use App\Models\PembelianProduk;
use App\Models\Produk;
use App\Models\RecoveryCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $atribut = []): User
    {
        return User::factory()->create(array_merge(['role' => $role], $atribut));
    }

    public function test_owner_bisa_menambah_kasir_dan_password_ter_hash_bisa_dipakai_login(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Baru',
                'username' => 'Kasir.Baru',
                'password' => 'rahasia123',
                'wajib_ganti_password' => '1',
            ])
            ->assertRedirect(route('pengguna.index'))
            ->assertSessionHas('success', 'Akun kasir kasir.baru berhasil dibuat.');

        $kasir = User::where('username', 'kasir.baru')->first();

        $this->assertNotNull($kasir);
        $this->assertSame('kasir', $kasir->role);
        $this->assertTrue($kasir->aktif);
        $this->assertTrue($kasir->wajib_ganti_password);
        $this->assertNotSame('rahasia123', $kasir->password);
        $this->assertTrue(Hash::check('rahasia123', $kasir->password));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.post'), [
            'username' => 'kasir.baru',
            'password' => 'rahasia123',
        ])->assertRedirect(route('profile.show').'#ganti-password');
    }

    public function test_password_kasir_kurang_dari_8_karakter_ditolak(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Pendek',
                'username' => 'kasir_pendek',
                'password' => '1234567',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['username' => 'kasir_pendek']);
    }

    public function test_username_kasir_duplikat_ditolak(): void
    {
        $owner = $this->user('owner');
        $this->user('kasir', ['username' => 'duplikat']);

        $this->actingAs($owner)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Duplikat',
                'username' => 'duplikat',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('username');

        $this->assertSame(1, User::where('username', 'duplikat')->count());
    }

    public function test_kasir_tanpa_wajib_ganti_langsung_ke_halaman_operasional(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Langsung',
                'username' => 'kasir_langsung',
                'password' => 'rahasia123',
                // checkbox wajib ganti password sengaja tidak dikirim
            ])
            ->assertRedirect(route('pengguna.index'));

        $kasir = User::where('username', 'kasir_langsung')->first();
        $this->assertNotNull($kasir);
        $this->assertFalse($kasir->wajib_ganti_password);

        $this->post(route('logout'))->assertRedirect(route('login'));

        // Dashboard khusus owner, jadi halaman operasional pertama kasir
        // adalah daftar pelanggan (bukan dipaksa ke Profil).
        $this->post(route('login.post'), [
            'username' => 'kasir_langsung',
            'password' => 'rahasia123',
        ])->assertRedirect(route('pelanggan.index'));
    }

    public function test_kasir_tidak_bisa_mengakses_endpoint_pengguna(): void
    {
        $kasir = $this->user('kasir');
        $target = $this->user('kasir');

        $this->actingAs($kasir)
            ->get(route('pengguna.index'))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Ilegal',
                'username' => 'kasir_ilegal',
                'password' => 'rahasia123',
            ])
            ->assertForbidden();

        $this->actingAs($kasir)
            ->put(route('pengguna.reset-password', $target))
            ->assertForbidden();

        $this->actingAs($kasir)
            ->put(route('pengguna.toggle-aktif', $target))
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'kasir_ilegal']);
    }

    public function test_owner_bisa_menghapus_kasir(): void
    {
        $owner = $this->user('owner');
        $kasir = $this->user('kasir', ['username' => 'kasir-hapus']);

        $this->actingAs($owner)
            ->delete(route('pengguna.destroy', $kasir))
            ->assertRedirect(route('pengguna.index'))
            ->assertSessionHas('success', 'Akun kasir-hapus berhasil dihapus permanen.');

        $this->assertDatabaseMissing('users', ['id' => $kasir->id]);
        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_kasir_tidak_bisa_mengakses_endpoint_hapus(): void
    {
        $kasir = $this->user('kasir');
        $target = $this->user('kasir');

        $this->actingAs($kasir)
            ->delete(route('pengguna.destroy', $target))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_owner_tidak_bisa_menghapus_dirinya_sendiri(): void
    {
        $owner = $this->user('owner');

        $this->actingAs($owner)
            ->delete(route('pengguna.destroy', $owner))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
    }

    public function test_owner_tidak_bisa_menghapus_owner_lain(): void
    {
        $owner = $this->user('owner');
        $ownerLain = $this->user('owner');

        $this->actingAs($owner)
            ->delete(route('pengguna.destroy', $ownerLain))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $ownerLain->id]);
    }

    public function test_tombol_hapus_hanya_muncul_untuk_baris_kasir(): void
    {
        $owner = $this->user('owner');
        $ownerLain = $this->user('owner');
        $kasir = $this->user('kasir');

        $response = $this->actingAs($owner)->get(route('pengguna.index'));

        $response->assertOk();
        $response->assertSee('Hapus');
        $response->assertSee('user-delete-modal', false);

        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, 'data-user-url='));
        $this->assertStringContainsString('data-user-url="'.route('pengguna.destroy', $kasir).'"', $html);
        $this->assertStringNotContainsString('data-user-url="'.route('pengguna.destroy', $ownerLain).'"', $html);
    }

    public function test_sesi_dan_data_terkait_terhapus_lalu_riwayat_produk_tetap_ada(): void
    {
        $owner = $this->user('owner');
        $kasir = $this->user('kasir', [
            'username' => 'kasir-lama',
            'email' => 'kasir-lama@example.com',
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Produk Test',
            'merek' => 'Test',
            'kategori_produk' => 'Color',
            'satuan_penggunaan' => '/10ml',
            'harga_per_pcs' => 20000,
            'harga_per_penggunaan' => 5000,
            'harga_modal_rata_rata' => 10000,
            'stok' => 5,
            'aktif' => true,
        ]);

        $pembelian = PembelianProduk::create([
            'produk_id' => $produk->id,
            'qty' => 5,
            'harga_beli' => 10000,
            'harga_modal_rata_rata' => 10000,
            'stok_setelah' => 5,
            'keterangan' => 'Restock oleh kasir',
            'dicatat_oleh' => $kasir->id,
            'tanggal' => now()->toDateString(),
        ]);

        DB::table('sessions')->insert([
            'id' => 'sesi-kasir-lama',
            'user_id' => $kasir->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        RecoveryCode::create([
            'user_id' => $kasir->id,
            'code_hash' => 'hash-kode-pemulihan',
            'used_at' => null,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => $kasir->email,
            'token' => 'token-reset',
            'created_at' => now(),
        ]);

        $this->actingAs($owner)
            ->delete(route('pengguna.destroy', $kasir))
            ->assertRedirect(route('pengguna.index'));

        $this->assertDatabaseMissing('users', ['id' => $kasir->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $kasir->id]);
        $this->assertDatabaseMissing('recovery_codes', ['user_id' => $kasir->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $kasir->email]);

        $this->assertDatabaseHas('pembelian_produk', [
            'id' => $pembelian->id,
            'dicatat_oleh' => null,
        ]);
    }
}
