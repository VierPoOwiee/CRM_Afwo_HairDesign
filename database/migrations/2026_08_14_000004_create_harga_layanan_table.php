<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('harga_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_layanan')->constrained('layanan')->cascadeOnDelete();
            $table->string('varian');
            $table->decimal('harga_dasar_min', 15, 2);
            $table->decimal('harga_dasar_max', 15, 2);
            $table->decimal('tarif_kelebihan_per_10gr', 15, 2)->nullable();
            $table->decimal('komisi_min', 15, 2)->nullable();
            $table->decimal('komisi_max', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['id_layanan', 'varian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_layanan');
    }
};
