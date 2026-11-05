<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Support\RecoveryCodes;
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

    private const PESAN_UMUM = 'Jika akun terdaftar dan memiliki email pemulihan, tautan reset sudah dikirim. Cek juga folder spam.';

    private const PESAN_KODE_SALAH = 'Username atau kode pemulihan tidak valid.';

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
        $kunci = $this->kunciPembatas('email', $identifier, $request);

        if (RateLimiter::tooManyAttempts($kunci, self::MAX_ATTEMPTS)) {
            $detik = RateLimiter::availableIn($kunci);

            return back()->withErrors([
                'login' => "Terlalu banyak permintaan, coba lagi dalam {$detik} detik.",
            ])->onlyInput('login');
        }

        $user = User::query()
            ->where('username', Str::lower($identifier))
            ->orWhere('email', $identifier)
            ->first();

        if ($user && $user->aktif && filled($user->email)) {
            try {
                Password::broker()->sendResetLink(['email' => $user->email]);
            } catch (\Throwable $e) {
                // Jangan bocorkan detail kegagalan pengiriman ke pengguna.
                Log::error('Gagal mengirim email reset password.', [
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

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! $user->aktif) {
            throw ValidationException::withMessages([
                'email' => self::PESAN_TAUTAN_SALAH,
            ]);
        }

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'token'),
            function (User $user, string $password) use ($request) {
                $this->simpanPasswordBaru($request, $user, $password, 'email');
            }
        );

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

    public function showRecoveryForm()
    {
        return view('auth.recovery-code');
    }

    public function recoverWithCode(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'username.required' => 'Masukkan username.',
            'code.required' => 'Masukkan kode pemulihan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $username = Str::lower(trim((string) $request->input('username')));
        $kunci = $this->kunciPembatas('recovery', $username, $request);

        if (RateLimiter::tooManyAttempts($kunci, self::MAX_ATTEMPTS)) {
            $detik = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'code' => "Terlalu banyak percobaan, coba lagi dalam {$detik} detik.",
            ]);
        }

        $kode = RecoveryCodes::normalize((string) $request->input('code'));
        $user = User::where('username', $username)->first();

        if (! $user || ! $user->aktif) {
            RateLimiter::hit($kunci, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'code' => self::PESAN_KODE_SALAH,
            ]);
        }

        $kodeCocok = $user->recoveryCodes()
            ->whereNull('used_at')
            ->get()
            ->first(fn ($item) => Hash::check($kode, $item->code_hash));

        if (! $kodeCocok) {
            RateLimiter::hit($kunci, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'code' => self::PESAN_KODE_SALAH,
            ]);
        }

        $kodeCocok->used_at = now();
        $kodeCocok->save();

        $this->simpanPasswordBaru($request, $user, (string) $request->input('password'), 'kode');

        RateLimiter::clear($kunci);

        return redirect()->route('login')->with(
            'success',
            'Password berhasil diubah. Silakan masuk dengan password baru Anda.'
        );
    }

    private function simpanPasswordBaru(Request $request, User $user, string $password, string $jalur): void
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
            'jalur' => $jalur,
        ]);
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
