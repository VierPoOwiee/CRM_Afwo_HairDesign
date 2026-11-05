<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Notifications\ResetPasswordNotification;
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

    private const PESAN_UMUM = 'Jika akun terdaftar dan memiliki email pemulihan, tautan reset sudah dikirim. Cek juga folder spam. Tautan berlaku 30 menit.';

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
            ->assertSee('Lupa Password')
            ->assertSee('Tautan berlaku 30 menit');

        $this->get(route('password.reset', ['token' => 'token-uji', 'email' => 'uji@example.com']))
            ->assertOk()
            ->assertSee('Atur Ulang Password');
    }

    public function test_halaman_kode_pemulihan_sudah_dihapus(): void
    {
        $this->get('/lupa-password/kode')->assertNotFound();
    }

    public function test_halaman_lupa_password_hanya_untuk_tamu(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get(route('password.request'))
            ->assertRedirect();
    }

    public function test_profil_menampilkan_email_pemulihan_tanpa_kode_pemulihan(): void
    {
        $user = $this->user(['email' => null]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Email Pemulihan')
            ->assertSee('Belum ada email pemulihan');
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
        $this->post(route('password.email'), ['login' => 'nonaktif'])
            ->assertSessionHas('status', self::PESAN_UMUM);
        $this->post(route('password.email'), ['login' => 'tanpaemail'])
            ->assertSessionHas('status', self::PESAN_UMUM);

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

    public function test_notifikasi_reset_memuat_host_request_dan_token_yang_valid(): void
    {
        Notification::fake();

        $user = $this->user(['username' => 'hostuji', 'email' => 'hostuji@example.com']);

        $this->post('http://crm.test/lupa-password', ['login' => 'hostuji'])
            ->assertSessionHas('status', self::PESAN_UMUM);

        $token = null;
        $url = null;

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($user, &$token, &$url) {
                $token = $notification->token;
                $url = $notification->toMail($user)->actionUrl;

                return true;
            }
        );

        $this->assertNotNull($token);
        $this->assertStringStartsWith('http://crm.test/reset-password/', $url);
        $this->assertStringContainsString('email=', $url);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect(route('login'));
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

    public function test_reset_menerima_email_dengan_spasi_dan_huruf_besar(): void
    {
        Notification::fake();

        $user = $this->user(['email' => 'campur@example.com']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => '  CAMPUR@EXAMPLE.COM  ',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
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
}
