<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'kasir',
            'password' => 'password123',
            'wajib_ganti_password' => true,
        ], $atribut));
    }

    public function test_banner_wajib_ganti_password_muncul_dan_mengarahkan_ke_form(): void
    {
        $this->actingAs($this->kasir());

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Password Anda masih sementara. Buat password baru sekarang untuk melanjutkan.')
            ->assertSee('ganti-password', false)
            ->assertSee('scrollIntoView', false);
    }

    public function test_banner_tidak_muncul_jika_tidak_wajib_ganti_password(): void
    {
        $this->actingAs($this->kasir(['wajib_ganti_password' => false]));

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertDontSee('Password Anda masih sementara. Buat password baru sekarang untuk melanjutkan.');
    }

    public function test_password_baru_kurang_dari_8_karakter_ditolak(): void
    {
        $user = $this->kasir();
        $this->actingAs($user);

        $this->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->wajib_ganti_password);
    }

    public function test_password_baru_tidak_boleh_sama_dengan_password_lama(): void
    {
        $user = $this->kasir();
        $this->actingAs($user);

        $this->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('password');

        $this->assertStringContainsString(
            'tidak boleh sama',
            session('errors')->first('password')
        );

        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->wajib_ganti_password);
    }

    public function test_password_saat_ini_salah_ditolak(): void
    {
        $user = $this->kasir();
        $this->actingAs($user);

        $this->put(route('profile.password.update'), [
            'current_password' => 'salah-password',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertSessionHasErrors('current_password');

        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertTrue($user->wajib_ganti_password);
    }

    public function test_ganti_password_berhasil_menghapus_flag_dan_mengarahkan_kasir(): void
    {
        $user = $this->kasir();
        $this->actingAs($user);

        $this->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])
            ->assertRedirect(route('pelanggan.index'))
            ->assertSessionHas('success', 'Password berhasil diperbarui.');

        $user->refresh();
        $this->assertTrue(Hash::check('PasswordBaru123', $user->password));
        $this->assertFalse($user->wajib_ganti_password);
    }

    public function test_ganti_password_owner_diarahkan_ke_dashboard(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'password' => 'password123',
            'wajib_ganti_password' => true,
        ]);

        $this->actingAs($owner)
            ->put(route('profile.password.update'), [
                'current_password' => 'password123',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Password berhasil diperbarui.');

        $owner->refresh();
        $this->assertTrue(Hash::check('PasswordBaru123', $owner->password));
        $this->assertFalse($owner->wajib_ganti_password);
    }
}
