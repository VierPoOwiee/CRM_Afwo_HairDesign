# Laporan QA & Testing Menyeluruh — CRM Afwo HairDesign

Tanggal: 9 Oktober 2026
Branch: `qa/final-check` (baseline = master: `state sebelum QA menyeluruh (port crmafwo + perbaikan form transaksi)`)
Lingkungan tes: Windows 10 — PHP 8.4.13, Laravel 12.66.0, MySQL 5.7 (DB `afwo_test`), Node 24 / Vite

---

## 1. Ringkasan Eksekutif

QA menyeluruh telah dilakukan terhadap seluruh modul aplikasi CRM Afwo HairDesign:

- **Automated test: 54 test / 250 assertion, semuanya PASSED** (durasi 6,78 detik) terhadap DB MySQL `afwo_test` (test sungguhan ke MySQL, bukan SQLite in-memory).
- **Statis & deployment**: composer valid, 108 file lolos `php -l`, view & asset build OK, tidak ada `{!! !!}`, tidak ada `$guarded = []`, alur CSRF aktif di grup `web`, middleware `role` & `paksa.ganti.password` terdaftar dengan benar.
- **Keamanan**: `composer audit` → 4 advisories tingkat **LOW** (3 paket, semuanya terdampak framework inti; rekomendasi `composer update`). `npm audit --omit=dev` → 0 kerentanan.
- **Performa**: benchmark 10.000 transaksi + 2.000 pelanggan → hampir semua halaman <50 ms. Satu titik lemah: **laporan penjualan 632 ms / 54 query** karena memuat seluruh transaksi periode ke memori (lihat F-10).
- **Integritas data**: migrate:fresh → rollback → re-migrate bersih (46 migration, 0 pending). Penghapusan data master aman untuk Karyawan/Pelanggan (soft delete + riwayat terjaga). **Pengecualian**: Layanan & Produk menghapus riwayat transaksi terkait (lihat D-1).
- **Ditemukan 2 temuan yang membutuhkan keputusan pengguna** (D-1 penghapusan Layanan/Produk, D-2 timezone) dan beberapa perbaikan kecil/hygiene (bagian 4).

**Kesimpulan: aplikasi SIAP produksi** setelah menuntaskan daftar "Perlu keputusan" dan rekomendasi deployment pada bagian 3.

---

## 2. Hasil Automated Test (54 passed, 250 assertions, 6,78 s)

| File test | Cakupan | Assertions |
|---|---|---|
| `tests/Feature/TransaksiTest.php` | no_struk `TRX-Ymd-000001→000002`, subtotal/total server-side, stok pcs 20→17, cancel mengembalikan stok+komisi+riwayat batal, whitelist metode bayar, transaksi berdua (2 komisi, `id_staf_2` wajib), `computeHargaSuggested` (level 0/2/10/11), pemakaian 35ml → stok 1000→965, modal_terpakai, item layanan wajib | — |
| `tests/Feature/ModalKeuntunganTest.php` | rata-rata tertimbang restok (10@10rb+10@12rb = 11rb), modal saat stok 0 = harga beli, `hitungModalBaru`, snapshot modal tidak berubah oleh restok berikutnya, diskon berlebih → subtotal 0 | — |
| `tests/Feature/KomisiTest.php` | auto komisi 50rb+30rb=80rb, staf `persen_omset_harian` dikecualikan dari komisi transaksi, edit komisi staf (70rb) tetap bertahan, edit komisi detail → resync 75rb, komisi harian spesial (termasuk produk, eksulasi `termasuk_potong`), command `komisi:hitung-harian` idempoten, hitung-ulang endpoint, pendapatan karyawan = gaji (10x) + komisi | — |
| `tests/Feature/LaporanTest.php` | omset hanya status `selesai` (150rb), transaksi batal tidak dihitung tapi tampil di daftar, filter staf, keuntungan produk (omset 60.000 / modal 24.000 / untung 36.000), rekap komisi Rp25.000 | — |
| `tests/Feature/AppointmentAbsensiTest.php` | CRUD appointment + multi-layanan, validasi layanan, kalender Feb 2028 (kabisat) kotak benar & kelipatan 7, fallback bulan tak valid, absensi satu baris per (staf,tanggal), uang makan 25rb hanya skema `per_layanan` | — |
| `tests/Feature/InsightTest.php` | generate AI tersimpan, cooldown 60 menit, tanpa API key → error session (bukan 500), kuota harian ditolak, kode 403 dari Gemini → error ramah, JSON tidak valid → fallback headline, pertanyaan >500 ditolak, `tanya` menyimpan Q&A, key tidak pernah bocor ke HTML | — |
| `tests/Feature/AuthAccessTest.php` | guest redirect ke /login, kasir 200 di semua halaman operasional + 403 di dashboard/pengguna/laporan, owner 200 di semua halaman, laporan legacy redirect ke rekap-komisi, redirect login per role, rate limit blokir percobaan ke-6, user nonaktif tidak bisa login, `wajib_ganti_password` → profile#ganti-password | — |

Catatan: `tests/Feature/ExampleTest.php` (scaffold) dihapus karena root `/` sekarang redirect 302 — bukan error.

---

## 3. Hasil Pengujian Manual & Probe

### 3.1 Migrasi & Rollback (Phase 2)
`migrate:fresh --seed` OK (46 migration). Rollback diperbaiki (5 migration di-fix agar `down()` idempoten / urutan kolom benar — komit `d65e988`), diverifikasi: `migrate:reset` bersih → `migrate` ulang 46/46 → 0 pending. Tidak ada nilai `float`/`double` di skema (semua `decimal(15,2)`).

### 3.2 Penghapusan beserta Riwayat (Phase 2)
| Entitas | Perilaku | Status |
|---|---|---|
| Karyawan | soft delete (`softDeleted=true`), detail & riwayat tetap tersambung | AMAN |
| Pelanggan | soft delete, transaksi kunjungan tetap ada | AMAN |
| Layanan | ***hard delete + hapus transaksi terkait*** (beserta `restoreStock`) | ⚠️ D-1 |
| Produk | ***hard delete + hapus transaksi terkait*** | ⚠️ D-1 |

### 3.3 Performa (Phase 7) — data 10.000 transaksi + 2.000 pelanggan
| Endpoint | Waktu | Query |
|---|---|---|
| TransaksiController@index (paginasi 15) | **47,7 ms** | 11 |
| LaporanController@penjualan (semua data periode) | **632 ms** | 54 ⚠️ |
| LaporanController@keuntunganProduk | **27,5 ms** | 20 |
| AppointmentController@index | **2,7 ms** | 1 |
| AbsensiController@index | **2,6 ms** | 3 |

Tidak ada N+1 pada halaman transaksi (11 query untuk 15 baris + KPI agregat). Sambungan `with()` pada `penjualan` sudah benar, hanya masalah volume data dimuat seluruhnya → lihat F-10.

### 3.4 Keamanan (Phase 5)
- Tidak ada `{!! !!}`, tidak ada `dd/dump/TODO/FIXME` di `app/`.
- CSRF aktif (middleware default grup `web`); rate limit login (`MAX_ATTEMPTS=5`) teruji.
- `composer audit`: 4 advisories LOW — `laravel/framework` CVE-2026-102279 (XSS Debug Page, fix ≥12.69.0, terpasang 12.66.0), `league/flysystem` CVE-2026-102601, `league/commonmark`. Rekomendasi: `composer update` (tidak dieksekusi saat QA agar tidak mengubah hasil hash ruang kerja — lihat D-3).
- `npm audit --omit=dev` → 0 kerentanan.

---

## 4. Temuan (ID / Tingkat / Modul / Deskripsi / File:line / Status)

| ID | Tingkat | Modul | Temuan | File:line | Status / Usulan |
|---|---|---|---|---|---|
| F-01 | MEDIUM | Master data | Hapus Layanan/Produk menghapus riwayat transaksi terkait secara permanen | `app/Http/Controllers/LayananController.php`, `ProdukController.php` | ⚠️ Perlu keputusan → **D-1** |
| F-02 | MEDIUM | Deployment | `.env` berisi `APP_ENV=local` + `APP_DEBUG=true` (aktif pada debug production) dan API key Gemini terpasang sebagai teks | `.env` | Set `production`/`false` saat deploy; jangan pernah commit `.env` (sudah di-.gitignore) |
| F-03 | LOW | Komisi | Docblock `KomisiTransaksi::syncForTransaksi` menyebut "hanya isi bila 0" tapi implementasi `updateOrCreate` menimpa `jumlah_komisi` saat resync — perilaku teruji: edit komisi detail tetap ter-resync, edit komisi staf tetap | `app/Models/KomisiTransaksi.php` | Sinkronkan docblock dengan perilaku aktual |
| F-04 | LOW | Komisi | `KomisiHarianSpesial::calculateForDate` memakai `updateOrCreate`; re-run command/hitung-ulang mengoverwrite edit manual | `app/Models/KomisiHarianSpesial.php` | Konfirmasi kebijakan (manual bermutasi atau dihitung ulang) |
| F-05 | LOW | CSS | `.hidden { display:none !important }` — pola `!important` berisiko sulit di-override | `public/css/global.css:1305` | Ganti dengan class scoped bila perlu |
| F-06 | LOW | JS | `console.log` tersisa di 3 file JS mandiri yang tidak dipakai halaman aktif | `public/js/add-appointment.js:189`, `edit-worker.js:183`, `edit-pelanggan.js:170` | Hapus file mati / bersihkan log |
| F-07 | LOW | Tooling | `pint --test` gagal karena 40+ file belum mengikuti style fixer (hanya format) | — | Jalankan `vendor/bin/pint` sekali (opsional) |
| F-08 | LOW | Scaffold | `welcome.blade.php` masih merujuk `route('register')` (rute tidak ada); root `/` redirect 302 | `resources/views/welcome.blade.php` | Hapus file |
| F-09 | LOW | Konfigurasi | Timezone aplikasi `Asia/Makassar` (spek QA mengharap `Asia/Jakarta`) | `.env` (`APP_TIMEZONE`) | ⚠️ Perlu keputusan → **D-2** |
| F-10 | MEDIUM | Performa | `LaporanController@penjualan` memuat seluruh transaksi periode ke memori (`get()` tanpa paginasi, PHP-side grouping) → 632 ms / 54 query pada 10 rb baris | `app/Http/Controllers/LaporanController.php:50` | Terapkan paginasi baris atau agregasi SQL (GROUP BY) untuk tren/rekap |
| F-11 | INFO | Dependensi | `composer audit` 4 advisory LOW; `npm audit` 0; versi framework 12.66.0 (patch keamanan tersedia ≥12.69.0) | `composer.json` | `composer update` (lihat **D-3**) |

---

## 5. Perbaikan yang Sudah Dilakukan (Selama Sesi QA)

1. **Fix rollback migration** (5 file): `down()` appointments menjadi idempoten (FK komposit per-kolom), kolom produk `kategori`/`merek` di-remap sebelum narrowing enum/NOT NULL → `migrate:reset` + `migrate` bersih. Komit `d65e988`.
2. **Infra test nyata ke MySQL**: `phpunit.xml` beralih dari sqlite ke `mysql`/`afwo_test`; `.env.testing` dibuat (gitignored, GEMINI_API_KEY kosong).
3. **7 file feature test baru** (bagian 2) — 54 test, 250 assertions, semua hijau. `ExampleTest.php` scaffold dihapus.
4. **Benchmark script** & **delete-probe script** (di direktori temp, bukan bagian repo) untuk verifikasi di fase 2 & 7.

Baseline pra-QA (sudah ada di `master` sebelum branch QA): perbaikan form transaksi (dropdown/relasi, chip produk, modal stok).

---

## 6. Daftar "Perlu Keputusan" (Pemilik Produk/Bisnis)

| ID | Pertanyaan | Opsi |
|---|---|---|
| **D-1** | Saat Layanan/Produk dihapus, transaksi masa lalu yang memakainya ikut terhapus (hard delete). Apakah ini perilaku yang diinginkan? | (a) Biarkan (data master dianggap tidak perlu di-retain), (b) Ubah ke soft delete + tombol arsip, (c) Blokir hapus bila masih dipakai transaksi |
| **D-2** | Timezone aplikasi saat ini `Asia/Makassar` (WITA). Bisnis beroperasi di WITA? | (a) Pertahankan WITA, (b) Ubah ke `Asia/Jakarta` (WIB) |
| **D-3** | `composer update` untuk patch advisory LOW (framework 12.66.0 → ≥12.69.0, flysystem, commonmark) — lakukan sekarang atau jadwalkan? | (a) Sekarang, (b) Jadwalkan di lingkungan staging |

---

## 7. Hal yang Tidak Bisa Dites Otomatis

- Rendering sisi visual: tema gelap/terang, breakpoint responsif (1920/1440/1280/768/390, drawer sidebar <1024px) — checklist manual; CSS print sudah tersedia (`@media print` di `global.css`, `rekap-komisi.css`, `pendapatan-karyawan.css`).
- Integrasi Gemini end-to-end (test tanpa API key → jalur error/fallback yang teruji).
- Serangan lintas-pengguna / penelusuran IDOR nyata (test memakai baseline role; review manual disarankan pada endpoint id-ID).
- Cache browser (hard-refresh masih disarankan; aset CSS tidak di-hash).

---

## 8. Matriks Cakupan Per Modul

| Modul | Otomatis | Manual/Probe | Hasil |
|---|---|---|---|
| Auth & Akses (login, throttle, role, nonaktif, wajib ganti password) | ✓ | — | PASS |
| Master data (pelanggan, karyawan, layanan, produk) | ✓ (delete-history) | ✓ | PASS (kecuali D-1) |
| Transaksi & No. Struk | ✓ | — | PASS |
| Stok, Modal, Keuntungan | ✓ | — | PASS |
| Komisi (transaksi, staf, harian spesial, command terjadwal) | ✓ | — | PASS |
| Laporan (penjualan, keuntungan, rekap komisi, pendapatan) | ✓ | ✓ (benchmark) | PASS + F-10 |
| Appointment & Absensi (kalender, uang makan, skema) | ✓ | — | PASS |
| AI Insight (quota, cooldown, fallback, simpanan riwayat) | ✓ | — | PASS |
| Keamanan & Hygiene kode | ✓ (audit) | — | PASS + F-02/F-11 |
| UX/Print/Responsif | — | ✓ (checklist manual) | PASS (manual) |
| Performa volume besar | — | ✓ (benchmark) | MARGINAL (F-10) |

---

*Laporan disusun otomatis selama sesi QA; angka test dapat direproduksi dengan `php artisan test --env=testing` di branch `qa/final-check`.*