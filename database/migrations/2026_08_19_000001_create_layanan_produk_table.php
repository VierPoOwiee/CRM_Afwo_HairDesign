<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_layanan')->constrained('layanan')->cascadeOnDelete();
            $table->foreignId('id_produk')->constrained('produk')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['id_layanan', 'id_produk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan_produk');
    }
};
