<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['tanggal', 'waktu']);
            $table->renameColumn('waktu', 'jam_mulai');

            $table->foreignId('id_pelanggan')->nullable()->after('id');
            $table->foreignId('id_karyawan')->nullable()->after('id_pelanggan');
            $table->foreignId('id_layanan')->nullable()->after('id_karyawan');
            $table->string('judul_layanan')->nullable()->after('id_layanan');
            $table->unsignedInteger('durasi_menit')->default(60)->after('jam_mulai');
            $table->decimal('estimasi_biaya', 12, 2)->nullable()->after('durasi_menit');
            $table->text('preferensi')->nullable()->after('estimasi_biaya');
            $table->enum('status', ['terkonfirmasi', 'selesai'])->default('terkonfirmasi')->after('preferensi');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['nama', 'service', 'kategori', 'no_wa']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('id_pelanggan')->references('id')->on('pelanggans')->cascadeOnDelete();
            $table->foreign('id_karyawan')->references('id')->on('karyawans')->nullOnDelete();
            $table->foreign('id_layanan')->references('id')->on('layanan')->nullOnDelete();
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['id_pelanggan', 'id_karyawan', 'id_layanan']);
            $table->dropIndex(['tanggal']);

            $table->string('nama')->nullable();
            $table->string('service')->nullable();
            $table->string('kategori')->nullable();
            $table->string('no_wa')->nullable();
            $table->renameColumn('jam_mulai', 'waktu');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['id_pelanggan', 'id_karyawan', 'id_layanan', 'judul_layanan', 'durasi_menit', 'estimasi_biaya', 'preferensi', 'status']);
            $table->index(['tanggal', 'waktu']);
        });
    }
};