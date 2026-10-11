<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('harga_layanan', 'notes')) {
            Schema::table('harga_layanan', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('harga_layanan', 'notes')) {
            Schema::table('harga_layanan', function (Blueprint $table) {
                $table->string('notes', 255)->nullable();
            });
        }
    }
};