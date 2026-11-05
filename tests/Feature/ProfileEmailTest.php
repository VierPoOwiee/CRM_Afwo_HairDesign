<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEmailTest extends TestCase
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
}
