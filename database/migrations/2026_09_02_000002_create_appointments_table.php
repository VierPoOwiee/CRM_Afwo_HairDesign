<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->time('waktu');
            $table->string('nama');
            $table->string('service');
            $table->string('no_wa')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'waktu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};