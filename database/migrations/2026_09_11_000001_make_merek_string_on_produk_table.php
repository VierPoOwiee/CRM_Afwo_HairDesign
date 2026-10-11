<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ubah kolom merek dari enum (Alfaparf, Milbon, Keaune, Omni, Matrix)
// menjadi string bebas agar merek baru bisa ditambahkan dengan fleksibel.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->string('merek', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->enum('merek', ['Alfaparf', 'Milbon', 'Keaune', 'Omni', 'Matrix'])->nullable()->change();
        });
    }
};