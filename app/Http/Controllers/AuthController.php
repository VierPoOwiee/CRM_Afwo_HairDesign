<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $username = strtolower(trim($request->input('username')));

        $throttleKey = Str::transliterate(
            Str::lower($username).'|'.$request->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'username' => "Terlalu banyak percobaan, coba lagi dalam {$seconds} detik.",
            ])->onlyInput('username');
        }

        if (Auth::attempt([
            'username' => $username,
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            if (! Auth::user()->aktif) {
                Auth::logout();
                RateLimiter::hit($throttleKey, 60);

                return back()->withErrors([
                    'username' => 'Akun tidak aktif, hubungi owner.',
                ])->onlyInput('username');
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            if (Auth::user()->wajib_ganti_password) {
                return redirect()->to(route('profile.show').'#ganti-password')
                    ->with('success', 'Silakan ganti password sementara Anda terlebih dahulu.');
            }

            return $this->redirectByRole();
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole()
    {
        if (Auth::user()->role === 'owner') {
            return redirect()->route('dashboard');
        }

        return redirect()->route('pelanggan.index');
    }
}
