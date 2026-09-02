<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harga_layanan', function (Blueprint $table) {
            $table->renameColumn('tarif_kelebihan_per_10gr', 'notes');
        });

        Schema::table('harga_layanan', function (Blueprint $table) {
            $table->string('notes', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('harga_layanan', function (Blueprint $table) {
            $table->renameColumn('notes', 'tarif_kelebihan_per_10gr');
        });

        Schema::table('harga_layanan', function (Blueprint $table) {
            $table->decimal('tarif_kelebihan_per_10gr', 15, 2)->nullable()->change();
        });
    }
};