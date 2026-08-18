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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk');
            $table->enum('merek', ['Alfaparf', 'Milbon', 'Keaune']);
            $table->enum('kategori_produk', ['dijual', 'dipakai_layanan'])->default('dipakai_layanan');
            $table->enum('satuan', ['pcs', '/10ml']);
            $table->decimal('harga_per_satuan', 15, 2);
            $table->integer('stok')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
