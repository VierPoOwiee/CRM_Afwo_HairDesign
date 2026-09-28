<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PenggunaController extends Controller
{
    public function index()
    {
        $users = User::orderByRaw("CASE WHEN role = 'owner' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('pengguna.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9._]+$/',
                'unique:users,username',
            ],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, atau underscore.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.unique' => 'Username sudah dipakai akun lain.',
        ]);

        $sementara = $this->passwordSementara();

        $user = User::create([
            'name' => $data['name'],
            'username' => strtolower(trim($data['username'])),
            'email' => null,
            'role' => 'kasir',
            'aktif' => true,
            'wajib_ganti_password' => true,
            'password' => Hash::make($sementara),
        ]);

        return redirect()->route('pengguna.index')->with(
            'password_sementara',
            ['username' => $user->username, 'password' => $sementara, 'aksi' => 'Dibuat']
        );
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->tolakAkunSendiri($request, $user);

        $sementara = $this->passwordSementara();

        $user->password = Hash::make($sementara);
        $user->wajib_ganti_password = true;
        $user->remember_token = Str::random(60);
        $user->save();

        $this->hapusSesi($user);

        return redirect()->route('pengguna.index')->with(
            'password_sementara',
            ['username' => $user->username, 'password' => $sementara, 'aksi' => 'Direset']
        );
    }

    public function toggleAktif(Request $request, User $user)
    {
        $this->tolakAkunSendiri($request, $user);

        $user->aktif = ! $user->aktif;
        $user->save();

        if (! $user->aktif) {
            $this->hapusSesi($user);
        }

        return back()->with(
            'success',
            $user->aktif
                ? "Akun {$user->username} berhasil diaktifkan."
                : "Akun {$user->username} berhasil dinonaktifkan."
        );
    }

    private function passwordSementara(): string
    {
        return Str::password(10, true, true, false);
    }

    private function tolakAkunSendiri(Request $request, User $user): void
    {
        if ($request->user() && $request->user()->id === $user->id) {
            abort(403, 'Anda tidak dapat mengubah akun sendiri dari halaman ini. Gunakan halaman Profil Saya.');
        }
    }

    private function hapusSesi(User $user): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }
}
