<?php

namespace Tests\Feature;

use App\Models\RecoveryCode;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
use App\Support\RecoveryCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const PESAN_UMUM = 'Jika akun terdaftar dan memiliki email pemulihan, tautan reset sudah dikirim. Cek juga folder spam.';

    private const PESAN_KODE_SALAH = 'Username atau kode pemulihan tidak valid.';

    private function user(array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'kasir',
            'aktif' => true,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
        ], $atribut));
    }

    private function buatSesi(User $user): void
    {
        DB::table('sessions')->insert([
            'id' => Str::random(40),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->getTimestamp(),
        ]);
    }

    public function test_halaman_pemulihan_dapat_diakses_tamu(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Password');

        $this->get(route('password.recovery'))
            ->assertOk()
            ->assertSee('Kode Pemulihan');

        $this->get(route('password.reset', ['token' => 'token-uji', 'email' => 'uji@example.com']))
            ->assertOk()
            ->assertSee('Atur Ulang Password');
    }

    public function test_halaman_lupa_password_hanya_untuk_tamu(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get(route('password.request'))
            ->assertRedirect();
    }

    public function test_profil_menampilkan_section_pemulihan(): void
    {
        $user = $this->user(['email' => null]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Email Pemulihan')
            ->assertSee('Belum ada email pemulihan')
            ->assertSee('Kode Pemulihan');
    }

    public function test_login_tidak_mengisi_password_default(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('value="12345678"', false);
    }

    public function test_lupa_password_memberi_pesan_generik_sama_untuk_akun_ada_dan_tidak(): void
    {
        Notification::fake();

        $this->user(['username' => 'ada', 'email' => 'ada@example.com']);

        $this->post(route('password.email'), ['login' => 'tidak-ada'])
            ->assertRedirect()
            ->assertSessionHas('status', self::PESAN_UMUM);

        $this->assertSame(self::PESAN_UMUM, session('status'));

        $this->post(route('password.email'), ['login' => 'ada'])
            ->assertRedirect()
            ->assertSessionHas('status', self::PESAN_UMUM);

        $this->assertSame(self::PESAN_UMUM, session('status'));
    }

    public function test_email_reset_hanya_dikirim_untuk_user_aktif_yang_punya_email(): void
    {
        Notification::fake();

        $aktif = $this->user(['username' => 'aktif', 'email' => 'aktif@example.com']);
        $nonaktif = $this->user(['username' => 'nonaktif', 'email' => 'nonaktif@example.com', 'aktif' => false]);
        $tanpaEmail = $this->user(['username' => 'tanpaemail', 'email' => null]);

        $this->post(route('password.email'), ['login' => 'aktif'])
            ->assertSessionHas('status', self::PESAN_UMUM);
        $this->post(route('password.email'), ['login' => 'nonaktif']);
        $this->post(route('password.email'), ['login' => 'tanpaemail']);

        Notification::assertSentTo($aktif, ResetPasswordNotification::class);
        Notification::assertNotSentTo($nonaktif, ResetPasswordNotification::class);
        Notification::assertNotSentTo($tanpaEmail, ResetPasswordNotification::class);
        Notification::assertCount(1);
    }

    public function test_lupa_password_dibatasi_rate_limiter(): void
    {
        Notification::fake();

        $this->user(['username' => 'batas', 'email' => 'batas@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['login' => 'batas']);
        }

        $response = $this->post(route('password.email'), ['login' => 'batas']);

        $response->assertSessionHasErrors('login');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('login'));
    }

    public function test_reset_via_token_sukses(): void
    {
        Notification::fake();

        $user = $this->user([
            'email' => 'reset@example.com',
            'wajib_ganti_password' => true,
            'remember_token' => 'token-lama',
        ]);

        $this->buatSesi($user);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();

        $this->assertTrue(Hash::check('PasswordBaru123', $user->password));
        $this->assertFalse($user->wajib_ganti_password);
        $this->assertNotSame('token-lama', $user->remember_token);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', $user->email)->count());

        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_token_reset_kedaluwarsa_ditolak(): void
    {
        Notification::fake();

        $user = $this->user(['email' => 'expired@example.com']);
        $token = Password::broker()->createToken($user);

        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(31)]);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        Notification::assertNotSentTo($user, PasswordChangedNotification::class);
    }

    public function test_token_reset_tidak_bisa_dipakai_dua_kali(): void
    {
        Notification::fake();

        $user = $this->user(['email' => 'sekali@example.com']);
        $token = Password::broker()->createToken($user);

        $data = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ];

        $this->post(route('password.update'), $data)->assertRedirect(route('login'));
        $this->post(route('password.update'), $data)->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
    }

    public function test_reset_menolak_user_nonaktif(): void
    {
        Notification::fake();

        $user = $this->user([
            'email' => 'nonaktif-reset@example.com',
            'aktif' => false,
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        Notification::assertNotSentTo($user, PasswordChangedNotification::class);
    }

    public function test_kode_pemulihan_sukses(): void
    {
        Notification::fake();

        $user = $this->user([
            'username' => 'kodeuser',
            'wajib_ganti_password' => true,
            'remember_token' => 'token-lama',
        ]);

        $this->buatSesi($user);

        $kode = RecoveryCodes::replaceFor($user)[0];
        $kodeDenganSpasi = strtolower(substr($kode, 0, 4).' - '.substr($kode, 5));

        $response = $this->post(route('password.recovery.submit'), [
            'username' => strtoupper($user->username),
            'code' => $kodeDenganSpasi,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();

        $this->assertTrue(Hash::check('PasswordBaru123', $user->password));
        $this->assertFalse($user->wajib_ganti_password);
        $this->assertNotSame('token-lama', $user->remember_token);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
        $this->assertSame(1, RecoveryCode::where('user_id', $user->id)->whereNotNull('used_at')->count());

        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_kode_pemulihan_tidak_bisa_dipakai_dua_kali(): void
    {
        Notification::fake();

        $user = $this->user(['username' => 'sekali-kode']);
        $kode = RecoveryCodes::replaceFor($user)[0];

        $data = [
            'username' => $user->username,
            'code' => $kode,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ];

        $this->post(route('password.recovery.submit'), $data)->assertRedirect(route('login'));
        $this->post(route('password.recovery.submit'), $data)->assertSessionHasErrors('code');

        $this->assertSame(1, RecoveryCode::where('user_id', $user->id)->whereNotNull('used_at')->count());
    }

    public function test_kode_pemulihan_salah_ditolak_dengan_pesan_generik(): void
    {
        Notification::fake();

        $user = $this->user(['username' => 'salah-kode']);
        RecoveryCodes::replaceFor($user);

        $response = $this->post(route('password.recovery.submit'), [
            'username' => $user->username,
            'code' => '0000-0000',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertSame(self::PESAN_KODE_SALAH, session('errors')->first('code'));
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        Notification::assertNotSentTo($user, PasswordChangedNotification::class);
    }

    public function test_kode_pemulihan_dibatasi_rate_limiter(): void
    {
        Notification::fake();

        $user = $this->user(['username' => 'batas-kode']);
        RecoveryCodes::replaceFor($user);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.recovery.submit'), [
                'username' => $user->username,
                'code' => '0000-0000',
                'password' => 'PasswordBaru123',
                'password_confirmation' => 'PasswordBaru123',
            ]);
        }

        $response = $this->post(route('password.recovery.submit'), [
            'username' => $user->username,
            'code' => '0000-0000',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('code'));
    }

    public function test_kode_lama_invalid_setelah_generate_ulang(): void
    {
        Notification::fake();

        $user = $this->user(['username' => 'regenerate']);
        $kodeLama = RecoveryCodes::replaceFor($user)[0];

        RecoveryCodes::replaceFor($user);

        $response = $this->post(route('password.recovery.submit'), [
            'username' => $user->username,
            'code' => $kodeLama,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertSame(self::PESAN_KODE_SALAH, session('errors')->first('code'));
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }

    public function test_kode_pemulihan_menolak_user_nonaktif(): void
    {
        Notification::fake();

        $user = $this->user([
            'username' => 'nonaktif-kode',
            'aktif' => false,
        ]);

        $kode = RecoveryCodes::replaceFor($user)[0];

        $response = $this->post(route('password.recovery.submit'), [
            'username' => $user->username,
            'code' => $kode,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
    }
}
