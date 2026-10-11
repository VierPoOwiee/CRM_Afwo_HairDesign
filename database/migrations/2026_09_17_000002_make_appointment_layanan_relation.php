<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu appointment bisa berisi lebih dari satu layanan.
        Schema::create('appointment_layanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('layanan_id');
            $table->timestamps();

            $table->unique(['appointment_id', 'layanan_id']);
            $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
            $table->foreign('layanan_id')->references('id')->on('layanan')->cascadeOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['id_layanan']);
            $table->dropColumn(['id_layanan', 'judul_layanan', 'estimasi_biaya']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('id_layanan')->nullable()->after('id_karyawan');
            $table->string('judul_layanan')->nullable()->after('id_layanan');
            $table->decimal('estimasi_biaya', 12, 2)->nullable()->after('durasi_menit');
        });

        Schema::dropIfExists('appointment_layanan');
    }
};