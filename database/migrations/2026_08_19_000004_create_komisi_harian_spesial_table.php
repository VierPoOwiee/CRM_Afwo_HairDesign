<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komisi_harian_spesial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_staf')->constrained('karyawans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('total_omset_dasar', 15, 2)->default(0);
            $table->decimal('persen', 5, 2)->default(0);
            $table->decimal('jumlah_komisi', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['id_staf', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komisi_harian_spesial');
    }
};
