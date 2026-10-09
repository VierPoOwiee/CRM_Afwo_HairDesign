<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->enum('merek', ['Alfaparf', 'Milbon', 'Keaune', 'Omni', 'Matrix'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Isi merek yang null dengan salah satu nilai enum agar NOT NULL
        // tidak gagal. Nilai asli yang hilang tidak bisa dipulihkan oleh
        // rollback (perlu backup data manual).
        DB::table('produk')
            ->whereNull('merek')
            ->update(['merek' => 'Omni']);

        Schema::table('produk', function (Blueprint $table) {
            $table->enum('merek', ['Alfaparf', 'Milbon', 'Keaune', 'Omni', 'Matrix'])->nullable(false)->change();
        });
    }
};
