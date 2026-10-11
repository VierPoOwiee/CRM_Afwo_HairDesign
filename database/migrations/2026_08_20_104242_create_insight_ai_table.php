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
        Schema::create('insight_ai', function (Blueprint $table) {
            $table->id();
            $table->date('periode');
            $table->json('data_ringkasan');
            $table->text('konten_insight');
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamps();

            $table->unique('periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insight_ai');
    }
};
