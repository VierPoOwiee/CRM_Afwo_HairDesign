<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Default pemakaian produk per varian layanan (pengganti notes teks bebas).
// Satu varian boleh punya beberapa baris, mis. Fashion Color S:
//   Color 80ml + Bleaching 100ml.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('default_produk_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_harga_layanan')->constrained('harga_layanan')->cascadeOnDelete();
            $table->string('kategori_produk');
            $table->decimal('default_ml', 10, 2);
            $table->timestamps();

            $table->index('id_harga_layanan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('default_produk_layanan');
    }
};