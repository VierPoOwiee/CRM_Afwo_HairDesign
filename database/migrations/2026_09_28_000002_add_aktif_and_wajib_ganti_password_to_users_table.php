<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('aktif')->default(true)->after('role');
            });
        }

        if (! Schema::hasColumn('users', 'wajib_ganti_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('wajib_ganti_password')->default(false)->after('aktif');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wajib_ganti_password')) {
                $table->dropColumn('wajib_ganti_password');
            }

            if (Schema::hasColumn('users', 'aktif')) {
                $table->dropColumn('aktif');
            }
        });
    }
};
