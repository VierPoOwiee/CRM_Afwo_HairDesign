<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename existing "Gunting ..." layanan to "Potong ...".
     * Data migration so it applies to rows already created by the seeder.
     */
    public function up(): void
    {
        DB::table('layanan')
            ->where('nama_layanan', 'Gunting Wanita')
            ->update(['nama_layanan' => 'Potong Wanita']);

        DB::table('layanan')
            ->where('nama_layanan', 'Gunting Pria')
            ->update(['nama_layanan' => 'Potong Pria']);

        DB::table('layanan')
            ->where('nama_layanan', 'Gunting Anak')
            ->update(['nama_layanan' => 'Potong Anak']);
    }

    /**
     * Reverse the migrations — back to original "Gunting ..." names.
     */
    public function down(): void
    {
        DB::table('layanan')
            ->where('nama_layanan', 'Potong Wanita')
            ->update(['nama_layanan' => 'Gunting Wanita']);

        DB::table('layanan')
            ->where('nama_layanan', 'Potong Pria')
            ->update(['nama_layanan' => 'Gunting Pria']);

        DB::table('layanan')
            ->where('nama_layanan', 'Potong Anak')
            ->update(['nama_layanan' => 'Gunting Anak']);
    }
};
