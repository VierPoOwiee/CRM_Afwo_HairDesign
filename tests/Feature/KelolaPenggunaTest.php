<?php

namespace Tests\Feature;

use App\Models\PembelianProduk;
use App\Models\RiwayatStokProduk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelolaPenggunaTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::factory()->create(['role' => 'owner']);
    }

    private function kasir(array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'kasir',
            'aktif' => true,
            'wajib_ganti_password' => false,
        ], $atribut));
    }

    private function produk(): int
    {
        return DB::table('produk')->insertGetId([
            'nama_produk' => 'Produk Uji',
            'merek' => 'Matrix',
            'kategori_produk' => 'Hairtreatment',
            'satuan_penggunaan' => 'pcs',
            'harga_per_pcs' => 10000,
            'harga_per_penggunaan' => 0,
            'harga_modal_rata_rata' => 5000,
            'stok' => 10,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_form_tambah_kasir_tidak_lagi_meminta_email(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->get(route('pengguna.index'));

        $response->assertOk();
        $response->assertSee('Role otomatis Kasir. Isi password awal untuk kasir, dan sampaikan langsung kepadanya.');
        $response->assertSee('name="password"', false);
        $response->assertSee('Buat acak');
        $response->assertSee('Wajib ganti password saat login pertama');
        $response->assertDontSee('Email Pemulihan');
    }

    public function test_tambah_kasir_menyimpan_password_hash_dan_bisa_login(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->post(route('pengguna.store'), [
            'name' => 'Kasir Baru',
            'username' => 'KasirBaru',
            'password' => 'rahasia123',
            'wajib_ganti_password' => '1',
        ]);

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success', 'Akun kasir kasirbaru berhasil dibuat.');
        $response->assertSessionMissing('password_sementara');

        $kasir = User::where('username', 'kasirbaru')->firstOrFail();

        $this->assertSame('kasir', $kasir->role);
        $this->assertTrue($kasir->aktif);
        $this->assertTrue($kasir->wajib_ganti_password);
        $this->assertNull($kasir->email);
        $this->assertNotSame('rahasia123', $kasir->password);
        $this->assertTrue(Hash::check('rahasia123', $kasir->password));

        $this->post(route('logout'));

        $this->post(route('login.post'), [
            'username' => 'kasirbaru',
            'password' => 'rahasia123',
        ])->assertRedirect(route('profile.show').'#ganti-password');
    }

    public function test_password_kurang_dari_delapan_karakter_ditolak(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Pendek',
                'username' => 'kasirpendek',
                'password' => 'pendek',
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['username' => 'kasirpendek']);
        $this->assertSame('Password minimal 8 karakter.', session('errors')->first('password'));
    }

    public function test_username_duplikat_ditolak(): void
    {
        $owner = $this->owner();
        $this->kasir(['username' => 'kasirsama']);

        $this->actingAs($owner)
            ->post(route('pengguna.store'), [
                'name' => 'Kasir Lain',
                'username' => 'kasirsama',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('username');

        $this->assertSame(1, User::where('username', 'kasirsama')->count());
    }

    public function test_tanpa_centang_wajib_ganti_kasir_langsung_ke_halaman_utamanya(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post(route('pengguna.store'), [
            'name' => 'Kasir Langsung',
            'username' => 'kasirlangsung',
            'password' => 'rahasia123',
        ])->assertSessionHas('success');

        $kasir = User::where('username', 'kasirlangsung')->firstOrFail();
        $this->assertFalse($kasir->wajib_ganti_password);

        $this->post(route('logout'));

        $this->post(route('login.post'), [
            'username' => 'kasirlangsung',
            'password' => 'rahasia123',
        ])->assertRedirect(route('transaksi.create'));
    }

    public function test_kasir_tidak_bisa_mengakses_endpoint_kelola_pengguna(): void
    {
        $kasir = $this->kasir();
        $target = $this->kasir();

        $this->actingAs($kasir)->get(route('pengguna.index'))->assertForbidden();
        $this->actingAs($kasir)->post(route('pengguna.store'), [
            'name' => 'Penyusup',
            'username' => 'penyusup',
            'password' => 'rahasia123',
        ])->assertForbidden();
        $this->actingAs($kasir)->delete(route('pengguna.destroy', $target))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_reset_password_tetap_menampilkan_kotak_password_sementara(): void
    {
        $owner = $this->owner();
        $kasir = $this->kasir(['wajib_ganti_password' => false]);

        $this->actingAs($owner)
            ->put(route('pengguna.reset-password', $kasir))
            ->assertRedirect(route('pengguna.index'))
            ->assertSessionHas('password_sementara');

        $sementara = session('password_sementara');

        $this->assertTrue(Hash::check($sementara['password'], $kasir->fresh()->password));

        $this->actingAs($owner)
            ->get(route('pengguna.index'))
            ->assertOk()
            ->assertSee('Direset')
            ->assertSee('Minta user login memakai password ini. Ia akan langsung diarahkan ke halaman Profil untuk membuat password baru.');

        $this->assertTrue($kasir->fresh()->wajib_ganti_password);
    }

    public function test_owner_bisa_menghapus_kasir_beserta_data_terkait(): void
    {
        $owner = $this->owner();
        $kasir = $this->kasir(['email' => 'hapus@example.com']);

        DB::table('sessions')->insert([
            'id' => 'sesi-kasir-uji',
            'user_id' => $kasir->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'hapus@example.com',
            'token' => 'token-uji',
            'created_at' => now(),
        ]);

        $produkId = $this->produk();

        $pembelian = PembelianProduk::create([
            'produk_id' => $produkId,
            'qty' => 1,
            'harga_beli' => 5000,
            'harga_modal_rata_rata' => 5000,
            'stok_setelah' => 10,
            'dicatat_oleh' => $kasir->id,
            'tanggal' => now()->toDateString(),
        ]);

        $riwayat = RiwayatStokProduk::create([
            'produk_id' => $produkId,
            'jenis' => RiwayatStokProduk::JENIS_PENYESUAIAN,
            'perubahan' => 1,
            'stok_akhir' => 10,
            'harga_modal_rata_rata' => 5000,
            'dicatat_oleh' => $kasir->id,
            'tanggal' => now(),
        ]);

        $response = $this->actingAs($owner)->delete(route('pengguna.destroy', $kasir));

        $response->assertRedirect(route('pengguna.index'));
        $response->assertSessionHas('success', "Akun {$kasir->username} berhasil dihapus permanen.");

        $this->assertDatabaseMissing('users', ['id' => $kasir->id]);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $kasir->id)->count());
        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', 'hapus@example.com')->count());

        $this->assertDatabaseHas('pembelian_produk', [
            'id' => $pembelian->id,
            'dicatat_oleh' => null,
        ]);
        $this->assertDatabaseHas('riwayat_stok_produk', [
            'id' => $riwayat->id,
            'dicatat_oleh' => null,
        ]);
    }

    public function test_owner_tidak_bisa_menghapus_diri_sendiri_atau_owner_lain(): void
    {
        $owner = $this->owner();
        $ownerLain = $this->owner();

        $this->actingAs($owner)
            ->delete(route('pengguna.destroy', $owner))
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete(route('pengguna.destroy', $ownerLain))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $owner->id]);
        $this->assertDatabaseHas('users', ['id' => $ownerLain->id]);
    }

    public function test_banner_dan_redirect_setelah_ganti_password_wajib(): void
    {
        $kasir = $this->kasir(['wajib_ganti_password' => true]);

        $this->actingAs($kasir)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Password Anda masih sementara. Buat password baru sekarang untuk melanjutkan.')
            ->assertSee('id="ganti-password"', false);
    }

    public function test_password_baru_wajib_minimal_delapan_dan_berbeda_dari_password_lama(): void
    {
        $kasir = $this->kasir([
            'password' => 'password123',
            'wajib_ganti_password' => true,
        ]);

        $this->actingAs($kasir)
            ->put(route('profile.password.update'), [
                'current_password' => 'password123',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($kasir)
            ->put(route('profile.password.update'), [
                'current_password' => 'password123',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue($kasir->fresh()->wajib_ganti_password);
    }

    public function test_ganti_password_wajib_berhasil_dan_flag_menjadi_false(): void
    {
        $kasir = $this->kasir([
            'password' => 'password123',
            'wajib_ganti_password' => true,
        ]);

        $response = $this->actingAs($kasir)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect(route('transaksi.create'));
        $response->assertSessionHas('success', 'Password berhasil diperbarui.');

        $kasir->refresh();

        $this->assertFalse($kasir->wajib_ganti_password);
        $this->assertTrue(Hash::check('PasswordBaru123', $kasir->password));
        $this->assertFalse(Hash::check('password123', $kasir->password));
    }
}
