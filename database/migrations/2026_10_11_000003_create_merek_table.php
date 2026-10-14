<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merek', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        $names = DB::table('produk')
            ->whereNotNull('merek')
            ->where('merek', '!=', '')
            ->distinct()
            ->pluck('merek')
            ->all();

        $names = array_values(array_unique(array_merge(
            $names,
            ['Alfaparf', 'Milbon', 'Keaune', 'Omni', 'Matrix']
        )));

        $now = now();
        $rows = array_map(fn ($nama) => [
            'nama' => $nama,
            'created_at' => $now,
            'updated_at' => $now,
        ], $names);

        if (! empty($rows)) {
            DB::table('merek')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('merek');
    }
};
