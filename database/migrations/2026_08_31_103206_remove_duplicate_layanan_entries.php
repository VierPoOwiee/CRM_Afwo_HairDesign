<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Duplicate layanan ids to remove (from a second seeder run).
    // Keep canonical ids 1-22 and the two new combo ids 27, 28.
    private array $duplicateIds = [
        23, 24, 25, 26, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46,
    ];

    private string $bLayanan = '_backup_layanan_dup';
    private string $bHarga = '_backup_harga_layanan_dup';

    public function up(): void
    {
        // Snapshot full duplicate rows so the migration is reversible.
        Schema::create($this->bLayanan, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('nama_layanan', 255);
            $table->string('kategori', 255);
            $table->boolean('aktif');
            $table->boolean('termasuk_potong');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create($this->bHarga, function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('id_layanan');
            $table->string('varian', 255);
            $table->decimal('harga_dasar_min', 15, 2);
            $table->decimal('harga_dasar_max', 15, 2);
            $table->decimal('tarif_kelebihan_per_10gr', 15, 2)->nullable();
            $table->decimal('komisi_min', 15, 2)->nullable();
            $table->decimal('komisi_max', 15, 2)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        DB::table($this->bLayanan)->insert(
            DB::table('layanan')->whereIn('id', $this->duplicateIds)->get()->map(fn ($r) => (array) $r)->all()
        );
        DB::table($this->bHarga)->insert(
            DB::table('harga_layanan')->whereIn('id_layanan', $this->duplicateIds)->get()->map(fn ($r) => (array) $r)->all()
        );

        // Delete duplicates (harga_layanan rows follow via foreign key cascade).
        DB::table('layanan')->whereIn('id', $this->duplicateIds)->delete();
    }

    public function down(): void
    {
        // Restore the exact deleted rows (ids preserved) if not already present.
        foreach (DB::table($this->bLayanan)->get() as $row) {
            $exists = DB::table('layanan')->where('id', $row->id)->exists();
            if (! $exists) {
                DB::table('layanan')->insert((array) $row);
            }
        }
        foreach (DB::table($this->bHarga)->get() as $row) {
            $exists = DB::table('harga_layanan')->where('id', $row->id)->exists();
            if (! $exists) {
                DB::table('harga_layanan')->insert((array) $row);
            }
        }

        Schema::dropIfExists($this->bHarga);
        Schema::dropIfExists($this->bLayanan);
    }
};
