<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom username (nullable dulu) setelah name
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->after('name');
            });
        }

        // 2. Isi username untuk user yang sudah ada dari bagian sebelum "@" pada email
        if (Schema::hasColumn('users', 'email')) {
            $terpakai = DB::table('users')->whereNotNull('username')->pluck('username')->all();

            $users = DB::table('users')->select('id', 'email')->get();

            foreach ($users as $user) {
                if (DB::table('users')->where('id', $user->id)->whereNotNull('username')->exists()) {
                    continue;
                }

                $email = (string) $user->email;
                $dasar = strtolower(preg_replace('/[^a-zA-Z0-9._]/', '', explode('@', $email)[0]) ?? '');

                if ($dasar === '') {
                    $dasar = 'user';
                }

                $dasar = substr($dasar, 0, 24);
                $kandidat = $dasar;
                $urut = 1;

                while (in_array($kandidat, $terpakai, true)) {
                    $kandidat = $dasar . $urut;
                    $urut++;
                }

                $terpakai[] = $kandidat;

                DB::table('users')->where('id', $user->id)->update(['username' => $kandidat]);
            }
        }

        // 3. Jadikan username NOT NULL + unique
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });

        $indexUsername = collect(Schema::getIndexes('users'))
            ->first(fn ($index) => in_array('username', $index['columns'] ?? [], true));

        if (! $indexUsername) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        }

        // 4. Email jadi nullable (tidak dihapus, tidak dipakai lagi)
        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
