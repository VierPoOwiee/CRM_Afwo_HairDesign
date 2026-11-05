<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 900;

    private const PESAN_UMUM = 'Jika akun terdaftar dan memiliki email pemulihan, tautan reset sudah dikirim. Cek juga folder spam. Tautan berlaku 30 menit.';

    private const PESAN_TAUTAN_SALAH = 'Tautan reset tidak valid atau sudah kedaluwarsa.';

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string', 'max:255'],
        ], [
            'login.required' => 'Masukkan username atau email.',
        ]);

        $identifier = trim((string) $request->input('login'));
        $identifierLower = Str::lower($identifier);
        $kunci = $this->kunciPembatas('email', $identifier, $request);

        if (RateLimiter::tooManyAttempts($kunci, self::MAX_ATTEMPTS)) {
            $detik = RateLimiter::availableIn($kunci);

            return back()->withErrors([
                'login' => "Terlalu banyak permintaan, coba lagi dalam {$detik} detik.",
            ])->onlyInput('login');
        }

        $user = User::query()
            ->where('username', $identifierLower)
            ->orWhereRaw('LOWER(email) = ?', [$identifierLower])
            ->first();

        if (! $user) {
            Log::info('Permintaan reset password tidak diproses: akun tidak ditemukan.', [
                'ip' => $request->ip(),
            ]);
        } elseif (! $user->aktif) {
            Log::warning('Permintaan reset password tidak diproses: akun nonaktif.', [
                'user_id' => $user->id,
            ]);
        } elseif (! filled($user->email)) {
            Log::warning('Permintaan reset password tidak diproses: akun tidak memiliki email.', [
                'user_id' => $user->id,
            ]);
        } else {
            $this->catatMailerModePengembangan();

            try {
                $status = Password::broker()->sendResetLink(['email' => $user->email]);

                if ($status !== Password::RESET_LINK_SENT) {
                    Log::warning('Permintaan reset password tidak terkirim oleh broker.', [
                        'user_id' => $user->id,
                        'status' => $status,
                    ]);
                }
            } catch (\Throwable $e) {
                // Jangan bocorkan detail kegagalan pengiriman ke pengguna.
                Log::error('Gagal mengirim email reset password', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        RateLimiter::hit($kunci, self::DECAY_SECONDS);

        return back()->with('status', self::PESAN_UMUM)->onlyInput('login');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $email = Str::lower(trim((string) $request->input('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user || ! $user->aktif) {
            throw ValidationException::withMessages([
                'email' => self::PESAN_TAUTAN_SALAH,
            ]);
        }

        $status = Password::broker()->reset([
            'email' => $user->email,
            'password' => (string) $request->input('password'),
            'token' => (string) $request->input('token'),
        ], function (User $user, string $password) use ($request) {
            $this->simpanPasswordBaru($request, $user, $password);
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => self::PESAN_TAUTAN_SALAH,
            ]);
        }

        return redirect()->route('login')->with(
            'success',
            'Password berhasil diubah. Silakan masuk dengan password baru Anda.'
        );
    }

    private function simpanPasswordBaru(Request $request, User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->wajib_ganti_password = false;
        $user->remember_token = Str::random(60);
        $user->save();

        $this->hapusSesi($user);

        if (filled($user->email)) {
            try {
                $user->notify(new PasswordChangedNotification());
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirim notifikasi perubahan password.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Password berhasil direset.', [
            'username' => $user->username,
            'ip' => $request->ip(),
            'waktu' => now()->toDateTimeString(),
            'jalur' => 'email',
        ]);
    }

    private function catatMailerModePengembangan(): void
    {
        $mailer = config('mail.default');

        if (app()->environment('local') && in_array($mailer, ['log', 'array'], true)) {
            Log::warning("MAIL_MAILER={$mailer}: email tidak benar-benar dikirim; tautan reset ada di log ini.");
        }
    }

    private function hapusSesi(User $user): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }

    private function kunciPembatas(string $jalur, string $identifier, Request $request): string
    {
        return Str::transliterate(
            'password-'.$jalur.'|'.Str::lower($identifier).'|'.$request->ip()
        );
    }
}
