<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        return view('auth.profile');
    }

    public function updateInfo(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9._]+$/',
                'unique:users,username,' . Auth::id(),
            ],
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password' => 'Password saat ini salah.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, atau underscore.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.unique' => 'Username sudah dipakai akun lain.',
        ]);

        Auth::user()->update([
            'name' => $request->name,
            'username' => strtolower(trim($request->username)),
        ]);

        return back()->with('success', 'Informasi akun berhasil diperbarui.');
    }

    public function updateEmail(Request $request)
    {
        $data = $request->validateWithBag('email', [
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(Auth::id()),
            ],
            'current_password' => ['required', 'current_password'],
        ], [
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah dipakai akun lain.',
            'current_password' => 'Password saat ini salah.',
        ]);

        $email = filled($data['email'] ?? null)
            ? strtolower(trim($data['email']))
            : null;

        Auth::user()->update(['email' => $email]);

        return back()->with(
            'success',
            $email
                ? 'Email pemulihan berhasil disimpan.'
                : 'Email pemulihan berhasil dihapus.'
        );
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ], [
            'current_password' => 'Password saat ini salah.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.different' => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        $user = Auth::user();

        $user->update([
            'password' => $request->password,
            'wajib_ganti_password' => false,
        ]);

        $request->session()->regenerate();

        return redirect()->route($user->homeRoute())
            ->with('success', 'Password berhasil diperbarui.');
    }
}
