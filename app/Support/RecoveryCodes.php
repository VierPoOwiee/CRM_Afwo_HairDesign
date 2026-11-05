<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RecoveryCodes
{
    public const JUMLAH = 8;

    /**
     * Tanpa karakter ambigu: 0/O dan 1/I tidak dipakai.
     */
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * @return array<int, string>
     */
    public static function generate(int $jumlah = self::JUMLAH): array
    {
        $kode = [];

        while (count($kode) < $jumlah) {
            $baru = self::acakGrup().'-'.self::acakGrup();

            if (! in_array($baru, $kode, true)) {
                $kode[] = $baru;
            }
        }

        return $kode;
    }

    /**
     * Hapus kode lama lalu simpan kode baru dalam bentuk hash.
     * Kode asli hanya dikembalikan sekali untuk ditampilkan ke pengguna.
     *
     * @return array<int, string>
     */
    public static function replaceFor(User $user, int $jumlah = self::JUMLAH): array
    {
        $kode = self::generate($jumlah);

        DB::transaction(function () use ($user, $kode) {
            $user->recoveryCodes()->delete();

            foreach ($kode as $item) {
                $user->recoveryCodes()->create([
                    'code_hash' => Hash::make($item),
                ]);
            }
        });

        return $kode;
    }

    public static function normalize(string $kode): string
    {
        return preg_replace('/\s+/', '', strtoupper(trim($kode))) ?? '';
    }

    private static function acakGrup(): string
    {
        $hasil = '';
        $maks = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < 4; $i++) {
            $hasil .= self::ALPHABET[random_int(0, $maks)];
        }

        return $hasil;
    }
}
