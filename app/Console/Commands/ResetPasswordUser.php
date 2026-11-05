<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\RecoveryCodes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ResetPasswordUser extends Command
{
    protected $signature = 'user:reset-password {username} {--generate-codes : Buat 8 kode pemulihan baru untuk user}';

    protected $description = 'Reset password user berdasarkan username, atau buat kode pemulihan baru (dipakai owner saat lupa password).';

    public function handle(): int
    {
        $username = strtolower(trim((string) $this->argument('username')));

        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error('User tidak ditemukan');

            return self::FAILURE;
        }

        if ($this->option('generate-codes')) {
            $kode = RecoveryCodes::replaceFor($user);

            $this->info("Kode pemulihan baru untuk {$username}:");
            $this->newLine();

            foreach ($kode as $item) {
                $this->line('  '.$item);
            }

            $this->newLine();
            $this->warn('Simpan kode ini di tempat aman. Kode tidak akan ditampilkan lagi.');

            return self::SUCCESS;
        }

        $password = $this->secret('Password baru (minimal 8 karakter):');
        $konfirmasi = $this->secret('Ulangi password baru:');

        if ($password !== $konfirmasi) {
            $this->error('Konfirmasi password tidak cocok.');

            return self::FAILURE;
        }

        if (strlen((string) $password) < 8) {
            $this->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        // Diisi lewat properti langsung (bukan mass-assignment) karena
        // remember_token tidak ada di $fillable.
        $user->password = Hash::make($password);
        $user->remember_token = Str::random(60);
        $user->save();

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $this->info("Password untuk {$username} berhasil direset.");

        return self::SUCCESS;
    }
}
