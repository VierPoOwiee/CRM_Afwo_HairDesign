<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "UPDATE insight_ai SET konten_insight = JSON_OBJECT('legacy_text', konten_insight) "
            .'WHERE konten_insight IS NOT NULL AND JSON_VALID(konten_insight) = 0'
        );

        Schema::table('insight_ai', function (Blueprint $table) {
            $table->json('konten_insight')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insight_ai', function (Blueprint $table) {
            $table->text('konten_insight')->change();
        });
    }
};
