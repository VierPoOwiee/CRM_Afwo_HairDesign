<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Ubah service & kategori appointment menjadi JSON array supaya satu booking
// bisa memesan lebih dari satu layanan. Pertahankan tipe varchar(255), cukup
// untuk representasi 2-4 layanan per booking.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('appointments')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $update = [];

                if (is_string($row->service) && ! str_starts_with(ltrim($row->service), '[')) {
                    $update['service'] = json_encode([$row->service]);
                }

                if (is_string($row->kategori) && $row->kategori !== '' && ! str_starts_with(ltrim($row->kategori), '[')) {
                    $update['kategori'] = json_encode([$row->kategori]);
                }

                if ($update) {
                    DB::table('appointments')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('appointments')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $update = [];

                if (is_string($row->service) && str_starts_with(ltrim($row->service), '[')) {
                    $services = json_decode($row->service, true);
                    if (is_array($services) && $services) {
                        $update['service'] = $services[0];
                    }
                }

                if (is_string($row->kategori) && str_starts_with(ltrim($row->kategori), '[')) {
                    $kategoris = json_decode($row->kategori, true);
                    if (is_array($kategoris) && $kategoris) {
                        $update['kategori'] = $kategoris[0];
                    }
                }

                if ($update) {
                    DB::table('appointments')->where('id', $row->id)->update($update);
                }
            }
        });
    }
};
