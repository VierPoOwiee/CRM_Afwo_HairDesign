<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\HargaLayanan;
use App\Models\Karyawan;
use App\Models\KomisiTransaksi;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TransaksiDummySeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // 1. Staf dummy — campuran dua skema komisi, semua punya gaji pokok
        // ============================================================
        $stafPerLayanan = collect([
            ['nama' => 'Rina Kartika', 'kontak' => '+6281112202001', 'gaji_pokok' => 3500000],
            ['nama' => 'Sari Melati', 'kontak' => '+6281112202002', 'gaji_pokok' => 3000000],
            ['nama' => 'Dewi Anggraini', 'kontak' => '+6281112202003', 'gaji_pokok' => 2750000],
        ])->map(fn ($d) => Karyawan::updateOrCreate(
            ['nama' => $d['nama']],
            [
                'kontak' => $d['kontak'],
                'skema_komisi' => 'per_layanan',
                'persen_komisi_harian' => null,
                'gaji_pokok' => $d['gaji_pokok'],
            ]
        ));

        $stafPersenHarian = collect([
            ['nama' => 'Budi Santoso', 'kontak' => '+6281112202004', 'persen' => 8, 'gaji_pokok' => 3000000],
            ['nama' => 'Agus Pratama', 'kontak' => '+6281112202005', 'persen' => 10, 'gaji_pokok' => 4000000],
        ])->map(fn ($d) => Karyawan::updateOrCreate(
            ['nama' => $d['nama']],
            [
                'kontak' => $d['kontak'],
                'skema_komisi' => 'persen_omset_harian',
                'persen_komisi_harian' => $d['persen'],
                'gaji_pokok' => $d['gaji_pokok'],
            ]
        ));

        $semuaStaf = $stafPerLayanan->merge($stafPersenHarian)->values();

        // Karyawan lain di luar 5 dummy di atas: pastikan gaji pokoknya terisi juga
        $gajiSisa = [2800000, 3200000, 3000000];
        $i = 0;
        Karyawan::where('gaji_pokok', '<=', 0)->orderBy('id')->get()->each(function ($k) use (&$i, $gajiSisa) {
            $k->update(['gaji_pokok' => $gajiSisa[$i++ % count($gajiSisa)]]);
        });

        // ============================================================
        // 2. Pelanggan dummy
        // ============================================================
        $pelangganData = [
            ['nama' => 'Andini Putri', 'jenis_kelamin' => 'P', 'no_wa' => '+6281311100001', 'jenis_rambut' => 'Lurus, normal'],
            ['nama' => 'Bella Safira', 'jenis_kelamin' => 'P', 'no_wa' => '+6281311100002', 'jenis_rambut' => 'Bergelombang, tebal'],
            ['nama' => 'Citra Lestari', 'jenis_kelamin' => 'P', 'no_wa' => '+6281311100003', 'jenis_rambut' => 'Ikal, kering'],
            ['nama' => 'Dinda Ayu', 'jenis_kelamin' => 'P', 'no_wa' => '+6281311100004', 'jenis_rambut' => 'Lurus, tipis'],
            ['nama' => 'Eka Ramadhani', 'jenis_kelamin' => 'P', 'no_wa' => '+6281311100005', 'jenis_rambut' => 'Bergelombang, normal'],
            ['nama' => 'Farhan Maulana', 'jenis_kelamin' => 'L', 'no_wa' => '+6281311100006', 'jenis_rambut' => 'Lurus, normal'],
            ['nama' => 'Gilang Prakasa', 'jenis_kelamin' => 'L', 'no_wa' => '+6281311100007', 'jenis_rambut' => 'Keriting, tebal'],
            ['nama' => 'Hendra Wijaya', 'jenis_kelamin' => 'L', 'no_wa' => '+6281311100008', 'jenis_rambut' => 'Lurus, kasar'],
            ['nama' => 'Intan Permata', 'jenis_kelamin' => 'P', 'no_wa' => '+6281311100009', 'jenis_rambut' => 'Lurus, berwarna'],
            ['nama' => 'Joko Susilo', 'jenis_kelamin' => 'L', 'no_wa' => '+6281311100010', 'jenis_rambut' => 'Bergelombang, normal'],
        ];

        $pelanggans = collect($pelangganData)->map(fn ($d) => Pelanggan::updateOrCreate(
            ['nama' => $d['nama']],
            [
                'jenis_kelamin' => $d['jenis_kelamin'],
                'no_wa' => $d['no_wa'],
                'jenis_rambut' => $d['jenis_rambut'],
            ]
        ));

        // ============================================================
        // 3. Transaksi dummy — 10 kunjungan tersebar dalam sebulan terakhir
        // ============================================================
        if (TransaksiKunjungan::where('no_struk', 'like', 'TRX-DUMMY-%')->exists()) {
            $this->command->info('Transaksi dummy sudah ada (TRX-DUMMY-*), lewati pembuatan transaksi.');

            return;
        }

        $layananList = Layanan::with('hargaLayanan')->orderBy('id')->get();
        $produkList = Produk::where('aktif', true)->where('kategori_produk', 'dijual')->orderBy('id')->get();

        if ($layananList->isEmpty() || $produkList->isEmpty()) {
            $this->command->error('Data layanan/produk belum ada. Jalankan seeder utama dulu.');

            return;
        }

        // Tanggal sengaja tidak seragam supaya breakdown harian/mingguan bervariasi
        $offsetHari = [2, 4, 6, 9, 11, 14, 17, 21, 25, 28];
        $jam = ['10:30', '13:00', '15:45', '11:15', '16:20', '14:05', '10:00', '17:35', '12:40', '18:10'];
        $metode = ['cash', 'qris', 'debit', 'cash', 'transfer', 'qris', 'debit', 'kartu_kredit', 'cash', 'qris'];

        $tanggalDipakai = [];

        DB::transaction(function () use ($layananList, $produkList, $pelanggans, $semuaStaf, $offsetHari, $jam, $metode, &$tanggalDipakai) {
            foreach ($offsetHari as $i => $offset) {
                $waktu = now()->subDays($offset)->setTimeFromTimeString($jam[$i]);
                $tanggalDipakai[] = $waktu->toDateString();

                $transaksi = TransaksiKunjungan::create([
                    'id_pelanggan' => $pelanggans[$i % $pelanggans->count()]->id,
                    'jenis_pengerjaan' => 'sendiri',
                    'no_struk' => sprintf('TRX-DUMMY-%04d', $i + 1),
                    'waktu_kunjungan' => $waktu,
                    'metode_pembayaran' => $metode[$i],
                    'total_bayar' => 0,
                ]);

                // 1-3 baris detail per transaksi: selalu ada layanan, produk menyusul kalau kuota masih sisa
                $jumlahItem = ($i % 3) + 1;
                $punyaProduk = $i % 2 === 1 && $jumlahItem >= 2;

                // --- Item layanan ---
                $layanan = $layananList[$i % $layananList->count()];
                $varian = $layanan->hargaLayanan->sortBy('harga_dasar_min')->first();
                $stafLayanan = $semuaStaf[$i % $semuaStaf->count()];

                if ($varian) {
                    $hargaLayanan = (float) $varian->harga_dasar_min;
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id,
                        'id_staf' => $stafLayanan->id,
                        'tipe_item' => 'layanan',
                        'id_layanan' => $layanan->id,
                        'varian_dipilih' => $varian->varian,
                        'qty' => 1,
                        'harga_saat_transaksi' => $hargaLayanan,
                        'subtotal' => $hargaLayanan,
                        // Komisi wajar: ambil dari rentang komisi varian, fallback estimasi 7%
                        'komisi_nominal' => $this->komisiWajar($varian, $hargaLayanan),
                        'catatan' => 'Dummy data',
                    ]);
                }

                // --- Item produk (dijual pcs) ---
                if ($punyaProduk) {
                    $produk = $produkList[$i % $produkList->count()];
                    $stafProduk = $semuaStaf[($i + 2) % $semuaStaf->count()];
                    $qty = 1;
                    $subtotal = (float) $produk->harga_per_satuan * $qty;

                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id,
                        'id_staf' => $stafProduk->id,
                        'tipe_item' => 'produk',
                        'id_produk' => $produk->id,
                        'qty' => $qty,
                        'harga_saat_transaksi' => $produk->harga_per_satuan,
                        'subtotal' => $subtotal,
                        'catatan' => 'Dummy data',
                    ]);

                    $produk->decrement('stok', $qty);
                }

                // --- Item layanan kedua/ketiga (kalau jumlahItem > 1 dan bukan produk) ---
                for ($j = 1; $j < $jumlahItem && ! $punyaProduk; $j++) {
                    $layananTambahan = $layananList[($i + $j * 3) % $layananList->count()];
                    $varianTambahan = $layananTambahan->hargaLayanan->sortBy('harga_dasar_min')->first();
                    $stafTambahan = $semuaStaf[($i + $j) % $semuaStaf->count()];

                    if (! $varianTambahan) {
                        break;
                    }

                    $hargaTambahan = (float) $varianTambahan->harga_dasar_min;
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id,
                        'id_staf' => $stafTambahan->id,
                        'tipe_item' => 'layanan',
                        'id_layanan' => $layananTambahan->id,
                        'varian_dipilih' => $varianTambahan->varian,
                        'qty' => 1,
                        'harga_saat_transaksi' => $hargaTambahan,
                        'subtotal' => $hargaTambahan,
                        'komisi_nominal' => $this->komisiWajar($varianTambahan, $hargaTambahan),
                        'catatan' => 'Dummy data',
                    ]);
                }

                $transaksi->recalculateTotal();
                KomisiTransaksi::syncForTransaksi($transaksi);
            }
        });

        // ============================================================
        // 4. Hitung komisi harian untuk staf skema persen_omset_harian
        //    (reuse command yang sama seperti dipakai manual)
        // ============================================================
        foreach (array_unique($tanggalDipakai) as $tanggal) {
            Artisan::call('komisi:hitung-harian', ['tanggal' => $tanggal]);
        }

        $this->command->info('Seeder selesai: '.count($offsetHari).' transaksi dummy dibuat, komisi harian dihitung untuk '.count(array_unique($tanggalDipakai)).' tanggal.');
    }

    private function komisiWajar(HargaLayanan $varian, float $harga): float
    {
        if ($varian->komisi_min !== null) {
            return $varian->komisi_max > $varian->komisi_min
                ? (float) mt_rand((int) $varian->komisi_min, (int) $varian->komisi_max)
                : (float) $varian->komisi_min;
        }

        return round($harga * 0.07);
    }
}
