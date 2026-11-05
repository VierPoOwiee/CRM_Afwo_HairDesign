<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileRecoveryTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'kasir',
            'password' => 'password123',
        ], $atribut));
    }

    public function test_email_pemulihan_butuh_password_saat_ini(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->put(route('profile.email.update'), [
                'email' => 'baru@example.com',
                'current_password' => 'salah-password',
            ])
            ->assertSessionHasErrorsIn('email', ['current_password']);

        $this->assertNull($user->fresh()->email);
    }

    public function test_email_pemulihan_duplikat_ditolak(): void
    {
        $this->user(['email' => 'dipakai@example.com']);
        $user = $this->user();

        $this->actingAs($user)
            ->put(route('profile.email.update'), [
                'email' => 'dipakai@example.com',
                'current_password' => 'password123',
            ])
            ->assertSessionHasErrorsIn('email', ['email']);

        $this->assertNull($user->fresh()->email);
    }

    public function test_email_pemulihan_berhasil_disimpan_dan_dihapus(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->put(route('profile.email.update'), [
                'email' => 'Pemulihan@Example.com',
                'current_password' => 'password123',
            ])
            ->assertSessionHas('success');

        $this->assertSame('pemulihan@example.com', $user->fresh()->email);

        $this->actingAs($user)
            ->put(route('profile.email.update'), [
                'email' => '',
                'current_password' => 'password123',
            ])
            ->assertSessionHas('success');

        $this->assertNull($user->fresh()->email);
    }

    public function test_generate_kode_pemulihan_di_profil(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)
            ->post(route('profile.recovery-codes.generate'), [
                'current_password' => 'password123',
            ]);

        $response->assertRedirect(route('profile.show').'#kode-pemulihan');
        $response->assertSessionHas('recovery_codes');

        $kode = session('recovery_codes');

        $this->assertCount(8, $kode);

        foreach ($kode as $item) {
            $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/', $item);
        }

        $this->assertSame(8, $user->recoveryCodes()->count());
        $this->assertSame(8, $user->recoveryCodes()->whereNull('used_at')->count());

        foreach ($user->recoveryCodes as $tersimpan) {
            $this->assertNotContains($tersimpan->code_hash, $kode);
        }
    }

    public function test_generate_kode_pemulihan_butuh_password_saat_ini(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('profile.recovery-codes.generate'), [
                'current_password' => 'salah-password',
            ])
            ->assertSessionHasErrorsIn('recovery', ['current_password']);

        $this->assertSame(0, $user->recoveryCodes()->count());
    }

    public function test_generate_ulang_kode_menghapus_kode_lama(): void
    {
        $user = $this->user();

        $this->actingAs($user)->post(route('profile.recovery-codes.generate'), [
            'current_password' => 'password123',
        ]);

        $pertama = $user->recoveryCodes()->pluck('id')->all();

        $this->actingAs($user)->post(route('profile.recovery-codes.generate'), [
            'current_password' => 'password123',
        ]);

        $kedua = $user->recoveryCodes()->pluck('id')->all();

        $this->assertCount(8, $kedua);
        $this->assertEmpty(array_intersect($pertama, $kedua));
    }
}
