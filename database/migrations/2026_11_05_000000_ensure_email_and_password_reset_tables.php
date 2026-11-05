<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pastikan fondasi reset password via email tersedia:
     * - tabel password_reset_tokens,
     * - kolom users.email nullable namun tetap unik.
     *
     * Migrasi ini idempoten supaya aman dijalankan pada database lama
     * maupun database baru.
     */
    public function up(): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'email')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        $punyaUniqueEmail = collect(Schema::getIndexes('users'))->contains(
            fn (array $index) => ($index['unique'] ?? false)
                && in_array('email', $index['columns'] ?? [], true)
        );

        if (! $punyaUniqueEmail) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        // Sengaja tidak membalik perubahan struktur email agar tidak
        // menghapus data email yang sudah diisi pengguna.
    }
};
