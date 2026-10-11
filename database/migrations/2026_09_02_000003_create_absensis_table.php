<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_staf')->constrained('karyawans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->boolean('hadir')->default(true);
            $table->timestamps();

            $table->unique(['id_staf', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};