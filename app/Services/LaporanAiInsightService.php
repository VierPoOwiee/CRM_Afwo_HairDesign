<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Appointment;
use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiProduk;
use App\Models\InsightAi;
use App\Models\Karyawan;
use App\Models\KomisiHarianSpesial;
use App\Models\KomisiTransaksi;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LaporanAiInsightService
{
    public function generateUntukBulan(Carbon $bulan): InsightAi
    {
        $dataRingkasan = $this->agregasiData($bulan);
        $kontenInsight = $this->panggilGeminiApi($dataRingkasan);

        return InsightAi::updateOrCreate(
            ['periode' => $bulan->copy()->startOfMonth()->toDateString()],
            [
                'data_ringkasan' => $dataRingkasan,
                'konten_insight' => $kontenInsight,
                'dibuat_pada' => now(),
            ]
        );
    }

    public function agregasiData(Carbon $bulan): array
    {
        $bulanIni = $bulan->copy()->startOfMonth();
        $bulanLalu = $bulan->copy()->subMonth()->startOfMonth();

        $omsetIni = $this->hitungOmset($bulanIni);
        $omsetLalu = $this->hitungOmset($bulanLalu);

        $jumlahTransaksiIni = $this->hitungJumlahTransaksi($bulanIni);
        $jumlahTransaksiLalu = $this->hitungJumlahTransaksi($bulanLalu);

        return [
            'periode_label' => $bulanIni->format('F Y'),
            'omset_bulan_ini' => (int) $omsetIni,
            'omset_bulan_lalu' => (int) $omsetLalu,
            'jumlah_transaksi_ini' => (int) $jumlahTransaksiIni,
            'jumlah_transaksi_lalu' => (int) $jumlahTransaksiLalu,
            'pelanggan_baru_ini' => (int) $this->hitungPelangganBaru($bulanIni),
            'pelanggan_baru_lalu' => (int) $this->hitungPelangganBaru($bulanLalu),
            'rata_rata_transaksi_ini' => (int) round($jumlahTransaksiIni > 0 ? $omsetIni / $jumlahTransaksiIni : 0),
            'rata_rata_transaksi_lalu' => (int) round($jumlahTransaksiLalu > 0 ? $omsetLalu / $jumlahTransaksiLalu : 0),
            'breakdown_kategori' => $this->hitungBreakdownKategori($bulanIni, $bulanLalu),
            'metode_pembayaran' => $this->hitungMetodePembayaran($bulanIni),
            'jenis_pengerjaan' => $this->hitungJenisPengerjaan($bulanIni),
            'layanan_terlaris' => $this->hitungLayananTerlaris($bulanIni),
            'produk_terjual' => $this->hitungProdukTerjual($bulanIni),
            'produk_dipakai' => $this->hitungProdukDipakai($bulanIni),
            'stok_menipis' => $this->hitungStokMenipis(),
            'appointment' => $this->hitungAppointment($bulanIni),
            'karyawan' => $this->hitungDataKaryawan($bulanIni),
            'karyawan_lalu' => $this->hitungDataKaryawan($bulanLalu),
            'rincian_komisi_ini' => $this->hitungRincianKomisi($bulanIni),
            'rincian_komisi_lalu' => $this->hitungRincianKomisi($bulanLalu),
            'rincian_per_hari_ini' => $this->hitungRincianHarian($bulanIni),
            'rincian_per_hari_lalu' => $this->hitungRincianHarian($bulanLalu),
            'rincian_layanan_ini' => $this->hitungRincianLayanan($bulanIni),
            'rincian_layanan_lalu' => $this->hitungRincianLayanan($bulanLalu),
            'pelanggan_teratas' => $this->hitungPelangganTeratas($bulanIni),
        ];
    }

    private function transaksiSelesaiPada(Carbon $bulan): Builder
    {
        return TransaksiKunjungan::where('waktu_kunjungan', '>=', $bulan->copy()->startOfMonth())
            ->where('waktu_kunjungan', '<=', $bulan->copy()->endOfMonth())
            ->where('status', 'selesai');
    }

    private function scopeSelesaiBulan(Builder $query, Carbon $bulan): void
    {
        $query->where('waktu_kunjungan', '>=', $bulan->copy()->startOfMonth())
            ->where('waktu_kunjungan', '<=', $bulan->copy()->endOfMonth())
            ->where('status', 'selesai');
    }

    private function hitungOmset(Carbon $bulan): float
    {
        return (float) $this->transaksiSelesaiPada($bulan)->sum('total_bayar');
    }

    private function hitungJumlahTransaksi(Carbon $bulan): int
    {
        return $this->transaksiSelesaiPada($bulan)->count();
    }

    private function hitungPelangganBaru(Carbon $bulan): int
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        return Pelanggan::where('created_at', '>=', $awal)
            ->where('created_at', '<=', $akhir)
            ->count();
    }

    private function hitungBreakdownKategori(Carbon $bulanIni, Carbon $bulanLalu): array
    {
        $awalIni = $bulanIni->copy()->startOfMonth();
        $akhirIni = $bulanIni->copy()->endOfMonth();
        $awalLalu = $bulanLalu->copy()->startOfMonth();
        $akhirLalu = $bulanLalu->copy()->endOfMonth();

        $kategoriIni = DetailTransaksi::whereHas('transaksi', function ($q) use ($awalIni, $akhirIni) {
            $q->where('waktu_kunjungan', '>=', $awalIni)
                ->where('waktu_kunjungan', '<=', $akhirIni)
                ->where('status', 'selesai');
        })
            ->where('tipe_item', 'layanan')
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.kategori, sum(detail_transaksi.subtotal) as total_subtotal')
            ->groupBy('layanan.kategori')
            ->pluck('total_subtotal', 'kategori');

        $kategoriLalu = DetailTransaksi::whereHas('transaksi', function ($q) use ($awalLalu, $akhirLalu) {
            $q->where('waktu_kunjungan', '>=', $awalLalu)
                ->where('waktu_kunjungan', '<=', $akhirLalu)
                ->where('status', 'selesai');
        })
            ->where('tipe_item', 'layanan')
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.kategori, sum(detail_transaksi.subtotal) as total_subtotal')
            ->groupBy('layanan.kategori')
            ->pluck('total_subtotal', 'kategori');

        $semuaKategori = $kategoriIni->keys()->merge($kategoriLalu->keys())->unique();

        $breakdown = $semuaKategori->map(function ($kategori) use ($kategoriIni, $kategoriLalu) {
            return [
                'kategori' => $kategori,
                'omset_ini' => (int) ($kategoriIni->get($kategori, 0)),
                'omset_lalu' => (int) ($kategoriLalu->get($kategori, 0)),
            ];
        })->sortByDesc('omset_ini')->take(5)->values()->toArray();

        return $breakdown;
    }

    private function hitungMetodePembayaran(Carbon $bulan): array
    {
        return $this->transaksiSelesaiPada($bulan)
            ->selectRaw('metode_pembayaran, count(*) as jumlah, sum(total_bayar) as total')
            ->groupBy('metode_pembayaran')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'metode' => $this->labelMetode((string) $r->metode_pembayaran),
                'jumlah' => (int) $r->jumlah,
                'total' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungJenisPengerjaan(Carbon $bulan): array
    {
        return $this->transaksiSelesaiPada($bulan)
            ->selectRaw('jenis_pengerjaan, count(*) as jumlah, sum(total_bayar) as total')
            ->groupBy('jenis_pengerjaan')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'jenis' => $this->labelJenisPengerjaan((string) $r->jenis_pengerjaan),
                'jumlah' => (int) $r->jumlah,
                'total' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungLayananTerlaris(Carbon $bulan): array
    {
        return DetailTransaksi::where('tipe_item', 'layanan')
            ->whereHas('transaksi', fn (Builder $q) => $this->scopeSelesaiBulan($q, $bulan))
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.nama_layanan, layanan.kategori, count(*) as jumlah, sum(detail_transaksi.subtotal) as total')
            ->groupBy('layanan.id', 'layanan.nama_layanan', 'layanan.kategori')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'layanan' => (string) $r->nama_layanan,
                'kategori' => (string) $r->kategori,
                'jumlah' => (int) $r->jumlah,
                'total' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungProdukTerjual(Carbon $bulan): array
    {
        return DetailTransaksi::where('tipe_item', 'produk')
            ->whereNotNull('id_produk')
            ->whereHas('transaksi', fn (Builder $q) => $this->scopeSelesaiBulan($q, $bulan))
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id')
            ->selectRaw('produk.nama_produk, count(*) as jumlah, sum(detail_transaksi.subtotal) as total')
            ->groupBy('produk.id', 'produk.nama_produk')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'produk' => (string) $r->nama_produk,
                'jumlah' => (int) $r->jumlah,
                'total' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungProdukDipakai(Carbon $bulan): array
    {
        return DetailTransaksiProduk::whereHas('detailTransaksi', function ($q) use ($bulan) {
            $q->where('tipe_item', 'layanan')
                ->whereHas('transaksi', fn (Builder $tq) => $this->scopeSelesaiBulan($tq, $bulan));
        })
            ->join('produk', 'detail_transaksi_produk.id_produk', '=', 'produk.id')
            ->selectRaw('produk.nama_produk, produk.kategori_produk, sum(detail_transaksi_produk.subtotal) as total, count(distinct detail_transaksi_produk.id_detail_transaksi) as jumlah')
            ->groupBy('produk.id', 'produk.nama_produk', 'produk.kategori_produk')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'produk' => (string) $r->nama_produk,
                'kategori' => (string) $r->kategori_produk,
                'jumlah' => (int) $r->jumlah,
                'total' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungStokMenipis(): array
    {
        return Produk::where('aktif', true)
            ->where('stok', '<=', Produk::STOK_MENIPIS)
            ->orderBy('stok')
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'produk' => (string) $p->nama_produk,
                'kategori' => (string) $p->labelKategori(),
                'satuan' => (string) $p->satuan,
                'stok' => (int) $p->stok,
            ])
            ->values()
            ->toArray();
    }

    private function hitungAppointment(Carbon $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth()->toDateString();
        $akhir = $bulan->copy()->endOfMonth()->toDateString();

        $perKategori = Appointment::with('layanans')
            ->whereBetween('tanggal', [$awal, $akhir])
            ->get()
            ->flatMap(fn ($a) => $a->layanans->pluck('nama_layanan'))
            ->countBy()
            ->sortDesc()
            ->map(fn ($jumlah, $nama) => [
                'kategori' => (string) $nama,
                'jumlah' => (int) $jumlah,
            ]);

        return [
            'total' => Appointment::whereBetween('tanggal', [$awal, $akhir])->count(),
            'per_kategori' => $perKategori->values()->toArray(),
        ];
    }

    private function hitungDataKaryawan(Carbon $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth()->toDateString();
        $akhir = $bulan->copy()->endOfMonth()->toDateString();

        $komisiPerLayanan = KomisiTransaksi::whereHas('transaksi', function ($q) use ($awal, $akhir) {
            $q->where('waktu_kunjungan', '>=', $awal)
                ->where('waktu_kunjungan', '<=', $akhir)
                ->where('status', 'selesai');
        })
            ->selectRaw('id_staf, sum(jumlah_komisi) as total')
            ->groupBy('id_staf')
            ->pluck('total', 'id_staf');

        $komisiHarian = KomisiHarianSpesial::whereBetween('tanggal', [$awal, $akhir])
            ->selectRaw('id_staf, sum(jumlah_komisi) as total')
            ->groupBy('id_staf')
            ->pluck('total', 'id_staf');

        $hadirPerStaf = Absensi::whereBetween('tanggal', [$awal, $akhir])
            ->where('hadir', true)
            ->selectRaw('id_staf, count(*) as jumlah')
            ->groupBy('id_staf')
            ->pluck('jumlah', 'id_staf');

        $statistikStaf = DetailTransaksi::whereNotNull('id_staf')
            ->whereHas('transaksi', fn (Builder $q) => $this->scopeSelesaiBulan($q, $bulan))
            ->selectRaw('id_staf, count(distinct detail_transaksi.id_transaksi) as jumlah_transaksi, sum(detail_transaksi.subtotal) as omset')
            ->groupBy('id_staf')
            ->get()
            ->keyBy('id_staf');

        $uangMakanPerHari = \App\Http\Controllers\AbsensiController::UANG_MAKAN_PER_HARI;

        return Karyawan::orderBy('nama')->get()
            ->map(function (Karyawan $k) use ($komisiPerLayanan, $komisiHarian, $hadirPerStaf, $statistikStaf, $uangMakanPerHari) {
                $perLayanan = (float) ($komisiPerLayanan[$k->id] ?? 0);
                $persenHarian = (float) ($komisiHarian[$k->id] ?? 0);
                $gajiPokok = (float) $k->gaji_pokok;
                $jumlahHadir = (int) ($hadirPerStaf[$k->id] ?? 0);
                $uangMakan = $k->skema_komisi === 'persen_omset_harian' ? 0 : $jumlahHadir * $uangMakanPerHari;
                $stat = $statistikStaf[$k->id] ?? null;

                return [
                    'nama' => $k->nama,
                    'skema_komisi' => $k->skema_komisi,
                    'gaji_pokok' => (int) round($gajiPokok),
                    'jumlah_transaksi' => (int) ($stat->jumlah_transaksi ?? 0),
                    'omset_dikerjakan' => (int) round((float) ($stat->omset ?? 0)),
                    'jumlah_hadir' => $jumlahHadir,
                    'uang_makan' => (int) round($uangMakan),
                    'komisi_per_layanan' => (int) round($perLayanan),
                    'komisi_persen_harian' => (int) round($persenHarian),
                    'total_komisi' => (int) round($perLayanan + $persenHarian),
                    'total_pendapatan' => (int) round($perLayanan + $persenHarian + $gajiPokok + $uangMakan),
                ];
            })
            ->values()
            ->toArray();
    }

    private function hitungRincianKomisi(Carbon $bulan): array
    {
        $awal = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        $rincian = collect();

        KomisiTransaksi::whereHas('transaksi', function ($q) use ($awal, $akhir) {
            $q->where('waktu_kunjungan', '>=', $awal)
                ->where('waktu_kunjungan', '<=', $akhir)
                ->where('status', 'selesai');
        })
            ->with('staf', 'transaksi')
            ->orderBy('id_transaksi')
            ->get()
            ->each(function ($kt) use ($rincian) {
                $transaksi = $kt->transaksi;

                $rincian->push([
                    'staf' => $kt->staf->nama ?? 'Staf #'.$kt->id_staf,
                    'tanggal' => $transaksi ? $transaksi->waktu_kunjungan->toDateString() : '',
                    'sumber' => 'komisi_per_layanan',
                    'no_struk' => (string) ($transaksi->no_struk ?? ''),
                    'jumlah_komisi' => (int) round((float) $kt->jumlah_komisi),
                    'keterangan' => (string) ($kt->keterangan ?? ''),
                ]);
            });

        KomisiHarianSpesial::whereBetween('tanggal', [$awal->toDateString(), $akhir->toDateString()])
            ->with('staf')
            ->orderBy('tanggal')
            ->get()
            ->each(function ($row) use ($rincian) {
                $rincian->push([
                    'staf' => $row->staf->nama ?? 'Staf #'.$row->id_staf,
                    'tanggal' => $row->tanggal->toDateString(),
                    'sumber' => 'komisi_persen_harian',
                    'jumlah_komisi' => (int) round((float) $row->jumlah_komisi),
                    'omset_dasar' => (int) round((float) $row->total_omset_dasar),
                    'persen' => (float) $row->persen,
                ]);
            });

        return $rincian
            ->sortBy('tanggal')
            ->values()
            ->toArray();
    }

    private function hitungRincianHarian(Carbon $bulan): array
    {
        return $this->transaksiSelesaiPada($bulan)
            ->selectRaw('DATE(waktu_kunjungan) as tanggal, count(*) as jumlah, sum(total_bayar) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(fn ($r) => [
                'tanggal' => (string) $r->tanggal,
                'jumlah_transaksi' => (int) $r->jumlah,
                'omset' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungRincianLayanan(Carbon $bulan): array
    {
        return DetailTransaksi::where('tipe_item', 'layanan')
            ->whereHas('transaksi', fn (Builder $q) => $this->scopeSelesaiBulan($q, $bulan))
            ->join('layanan', 'detail_transaksi.id_layanan', '=', 'layanan.id')
            ->selectRaw('layanan.nama_layanan, layanan.kategori, count(*) as jumlah, sum(detail_transaksi.subtotal) as total')
            ->groupBy('layanan.id', 'layanan.nama_layanan', 'layanan.kategori')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'layanan' => (string) $r->nama_layanan,
                'kategori' => (string) $r->kategori,
                'jumlah' => (int) $r->jumlah,
                'total' => (int) round((float) $r->total),
            ])
            ->values()
            ->toArray();
    }

    private function hitungPelangganTeratas(Carbon $bulan): array
    {
        $rows = $this->transaksiSelesaiPada($bulan)
            ->selectRaw('id_pelanggan, count(*) as jumlah, sum(total_bayar) as total')
            ->groupBy('id_pelanggan')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $namaPelanggan = Pelanggan::whereIn('id', $rows->pluck('id_pelanggan'))
            ->pluck('nama', 'id');

        return $rows->map(fn ($r) => [
            'nama' => (string) ($namaPelanggan[$r->id_pelanggan] ?? 'Pelanggan #'.$r->id_pelanggan),
            'jumlah_transaksi' => (int) $r->jumlah,
            'total_belanja' => (int) round((float) $r->total),
        ])->values()->toArray();
    }

    private function panggilGeminiApi(array $dataRingkasan): array
    {
        $systemPrompt = 'Kamu adalah analis bisnis senior untuk salon kecantikan "Afwo Hair Design". '
            .'Kamu diberi data ringkasan bisnis periode ini dibanding periode sebelumnya: omset, jumlah transaksi, '
            .'pelanggan baru, rata-rata transaksi, pendapatan per kategori layanan, layanan terlaris, produk '
            .'terjual/dipakai, stok menipis, metode pembayaran, appointment, dan kinerja tiap karyawan '
            .'(kinerja per karyawan periode ini ada di kunci "karyawan", sedangkan periode sebelumnya di kunci '
            .'"karyawan_lalu" — dua-duanya memuat gaji_pokok, omset_dikerjakan, komisi_per_layanan, '
            .'komisi_persen_harian, total_komisi, dan total_pendapatan). '
            .'Tugasmu: buat analisa ringkas, tajam, dan berbobot dalam Bahasa Indonesia. '
            .'ATURAN TAMPILAN: '
            .'- Tulis angka dengan format Rupiah Indonesia tanpa desimal, contoh: Rp4.000.000. '
            .'- TANPA tanda bintang (**), tanpa karakter "#", tanpa markdown apa pun. '
            .'- Jangan mengarang angka di luar data yang diberikan; jika suatu data kosong atau nol, jangan '
            .'menyebutkannya seolah tersedia. '
            .'Balas HANYA satu objek JSON valid, tanpa markdown code fence, tanpa teks pembuka/penutup. '
            .'Struktur JSON persis:'
            .'{'
            .'"headline": "satu kalimat pendek maks 15 kata merangkum inti performa periode ini, contoh: Omset naik 64% didorong Treatment Rambut",'
            .'"sentiment": "positive ATAU negative ATAU neutral (berdasarkan tren omset dominan, bukan sekadar headline)",'
            .'"sorotan": ['
            .'  {"teks": "poin singkat maks 15 kata tanpa tanda bintang, contoh: Treatment Rambut naik jadi Rp4.000.000", "trend": "up ATAU down ATAU neutral"},'
            .'  ... (3-4 sorotan paling signifikan, urutkan dari paling penting)'
            .'],'
            .'"rekomendasi": ["rekomendasi singkat maks 14 kata, actionable, tanpa tanda bintang", ...] (maksimal 3 item)'
            .'} '
            .'Fokus sorotan dan rekomendasi pada hal paling menonjol di data: kenaikan/penurunan omset, '
            .'kategori/layanan/produk yang naik-turun, performa karyawan, tren transaksi dan pelanggan. '
            .'Hindari pernyataan datar seperti "omset berjalan normal" atau "bisnis berjalan baik".';

        $userMessage = 'Data ringkasan periode ini vs periode sebelumnya:\n\n'.json_encode($dataRingkasan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $konten = $this->postKeGemini($systemPrompt, $userMessage);

        return $this->parseInsightJson($konten);
    }

    public function tanyaJawab(Carbon $bulan, string $pertanyaan): string
    {
        $dataRingkasan = $this->agregasiData($bulan);

        $systemPromptTanyaJawab = 'Kamu adalah analis bisnis senior salon kecantikan "Afwo Hair Design" yang '
            .'memahami seluruh data bisnisnya. Owner akan bertanya apa saja terkait data bisnis. '
            .'Kamu akan menerima data ringkasan bisnis lengkap dalam bentuk JSON di pesan user (mencakup periode '
            .'berjalan, periode sebelumnya, serta rincian komisi/harian/layanan untuk dua periode). '
            .'Data kinerja per karyawan tersedia untuk DUA periode: kunci "karyawan" (periode berjalan) dan '
            .'kunci "karyawan_lalu" (periode sebelumnya) — keduanya memuat gaji_pokok, jumlah_transaksi, '
            .'omset_dikerjakan, komisi_per_layanan, komisi_persen_harian, total_komisi, dan total_pendapatan '
            .'tiap staf. Owner boleh bertanya komisi/pendapatan karyawan untuk periode berjalan maupun periode '
            .'sebelumnya. '
            .'PENCOCOKAN NAMA STAF: nama yang tersimpan memakai nama lengkap (contoh: "Agus Pratama"). '
            .'Jika owner menyebut nama sebagian, panggilan, atau tanpa gelar/spasi (contoh: "Agus", "agus", '
            .'"budi santoso"), padankan secara fleksibel ke nama lengkap yang ada (mengandung nama itu, case '
            .'insensitive) LALU gunakan data staf tsb dan jawab dengan nama lengkapnya — jangan menganggap data '
            .'tidak ada hanya karena namanya tidak cocok persis. Jika nama yang dimaksud benar-benar absen dari '
            .'semua nama lengkap yang tersedia, baru katakan tidak ada. '
            .'CARA MENJAWAB: '
            .'- Jawab LANGSUNG pertanyaan owner dalam Bahasa Indonesia, tanpa basa-basi pembuka seperti '
            .'"Berdasarkan data yang diberikan...". Untuk pertanyaan umum, cukup jawab padat (3-6 kalimat atau '
            .'beberapa poin singkat). Untuk pertanyaan yang meminta rincian/detail, jawab rinci dan berstruktur '
            .'(lihat aturan RINCIAN di bawah). '
            .'- Gunakan poin (diawali "-") bila jawaban memuat lebih dari satu hal. '
            .'- TANDA BINTIK (**) DAN TANDA BINTANG LAINNYA DILARANG. Angka ditulis tanpa desimal, format Rupiah '
            .'Indonesia, contoh Rp1.500.000. '
            .'RINCIAN: data juga memuat data granular sebagai berikut: '
            .'"rincian_komisi_ini" dan "rincian_komisi_lalu" (baris per staf per tanggal: sumber '
            .'komisi_per_layanan dengan no_struknya, atau komisi_persen_harian dengan omset_dasar dan persen), '
            .'"rincian_per_hari_ini" dan "rincian_per_hari_lalu" (omset & jumlah transaksi tiap tanggal), '
            .'"rincian_layanan_ini" dan "rincian_layanan_lalu" (semua layanan: nama, jumlah pemakaian, total). '
            .'Saat owner meminta rincian/detail/breakdown/per tanggal/per layanan atau per staf, jawab berpoin '
            .'satu baris per item dan urutkan per tanggal, contoh: '
            .'"- 06 Ags 2026 - Agus Pratama: Rp480.000 (komisi persen harian dari omset Rp1.600.000, persen 30%)" '
            .'atau "- 12 Ags 2026 - Rina Kartika: Rp125.000 (komisi per layanan, TRX-20260812-000045)". '
            .'Jangan ragu mengutip nomor struk, tanggal, dan angka persen yang memang tersedia di rincian. '
            .'- Hanya gunakan angka yang benar-benar ada di data. Jika data yang diminta tidak tersedia, katakan '
            .'terus terang "Data ... tidak tersedia pada ringkasan ini" dan tawarkan menanyakan hal lain yang tersedia. '
            .'JANGAN PERNAH mengarang angka, nama, atau asumsi sendiri. '
            .'- Jika pertanyaan meminta opini atau strategi, jawab singkat berbasis angka yang ada, jangan bertele-tele.';

        $namaStaf = collect(array_merge($dataRingkasan['karyawan'] ?? [], $dataRingkasan['karyawan_lalu'] ?? []))
            ->pluck('nama')
            ->unique()
            ->sort()
            ->values()
            ->join(', ');

        $userMessage = 'Periode: '.($dataRingkasan['periode_label'] ?? '').'. '
            .'Nama staf yang tersedia pada data (nama lengkap): '.$namaStaf.'. '
            ."Berikut data ringkasan lengkap dalam JSON:\n"
            .json_encode($dataRingkasan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ."\nPertanyaan owner: ".$pertanyaan;

        return $this->bersihkanMarkdown($this->postKeGemini($systemPromptTanyaJawab, $userMessage));
    }

    private function postKeGemini(string $systemPrompt, string $userMessage): string
    {
        $apiKey = config('services.gemini.key');

        if (empty($apiKey)) {
            throw new \RuntimeException(
                'GEMINI_API_KEY belum diisi. Tambahkan API key Gemini (dari Google AI Studio) pada file .env: GEMINI_API_KEY=...'
            );
        }

        $response = Http::timeout(30)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/'
                .config('services.gemini.model', 'gemini-3.6-flash')
                .':generateContent?key='.$apiKey,
            [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $userMessage],
                        ],
                    ],
                ],
            ]
        );

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            Log::error('Gemini API gagal', [
                'status' => $status,
                'body' => $body,
            ]);

            $pesan = 'Gemini API gagal merespons (status: '.$status.')';

            if ($status === 403) {
                $pesan .= '. Periksa API key di .env (GEMINI_API_KEY) serta pastikan API "Generative Language" aktif dan kuota tersedia.';
            } elseif ($status === 400 || $status === 404) {
                $pesan .= '. Periksa nilai GEMINI_MODEL di .env (saat ini: '.config('services.gemini.model', 'gemini-3.6-flash').').';
            }

            throw new \RuntimeException($pesan.' Detail: '.$body);
        }

        $konten = $response->json('candidates.0.content.parts.0.text');

        if (empty($konten)) {
            Log::warning('Gemini API mengembalikan response kosong', ['response' => $response->json()]);

            throw new \RuntimeException('Gemini API mengembalikan konten kosong');
        }

        return $konten;
    }

    private function parseInsightJson(string $konten): array
    {
        try {
            $hasil = json_decode($this->ekstrakJsonObject($konten), true);

            if (! is_array($hasil) || empty($hasil['headline'])) {
                throw new \RuntimeException('Struktur JSON insight tidak valid');
            }

            $sorotan = collect($hasil['sorotan'] ?? [])
                ->map(function ($item) {
                    $trend = $item['trend'] ?? 'neutral';

                    return [
                        'teks' => $this->bersihkanMarkdown(trim((string) ($item['teks'] ?? ''))),
                        'trend' => in_array($trend, ['up', 'down', 'neutral'], true) ? $trend : 'neutral',
                    ];
                })
                ->filter(fn ($item) => $item['teks'] !== '')
                ->take(4)
                ->values()
                ->toArray();

            $rekomendasi = collect($hasil['rekomendasi'] ?? [])
                ->map(fn ($item) => $this->bersihkanMarkdown(trim((string) $item)))
                ->filter(fn ($item) => $item !== '')
                ->take(3)
                ->values()
                ->toArray();

            $sentiment = $hasil['sentiment'] ?? 'neutral';

            return [
                'headline' => $this->bersihkanMarkdown(trim((string) $hasil['headline'])),
                'sentiment' => in_array($sentiment, ['positive', 'negative', 'neutral'], true) ? $sentiment : 'neutral',
                'sorotan' => $sorotan,
                'rekomendasi' => $rekomendasi,
            ];
        } catch (\Throwable $e) {
            Log::warning('Gagal parse JSON insight dari Gemini', [
                'error' => $e->getMessage(),
                'raw' => mb_substr($konten, 0, 500),
            ]);

            return $this->fallbackInsight();
        }
    }

    private function ekstrakJsonObject(string $konten): string
    {
        $awal = strpos($konten, '{');
        $akhir = strrpos($konten, '}');

        if ($awal === false || $akhir === false || $akhir < $awal) {
            return $konten;
        }

        return substr($konten, $awal, $akhir - $awal + 1);
    }

    private function bersihkanMarkdown(string $teks): string
    {
        // "**teks tebal**" -> "teks tebal"
        $teks = (string) preg_replace('/\*\*([^*]+)\*\*/u', '$1', $teks);
        // "*teks miring*" -> "teks miring" (hanya yang menempel pada kata)
        $teks = (string) preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/u', '$1', $teks);
        // bullet "* item" -> "- item"
        $teks = (string) preg_replace('/^\s*\*\s+/mu', '- ', $teks);
        // heading "### teks" -> "teks"
        $teks = (string) preg_replace('/^#{1,6}\s*/mu', '', $teks);
        // sisa tanda bintang & backtick yang tidak terpasang
        $teks = str_replace(['*', '`'], '', $teks);
        // rapikan spasi ganda
        $teks = (string) preg_replace('/[ \t]+/u', ' ', $teks);

        return trim($teks);
    }

    private function fallbackInsight(): array
    {
        return [
            'headline' => 'Gagal memproses analisa, coba generate ulang.',
            'sentiment' => 'neutral',
            'sorotan' => [],
            'rekomendasi' => [],
        ];
    }

    private function labelMetode(string $metode): string
    {
        return match ($metode) {
            'cash' => 'Cash',
            'qris_bni' => 'QRIS BNI',
            'qris_bri' => 'QRIS BRI',
            'debit' => 'Debit',
            'kartu_kredit' => 'Kartu Kredit',
            'transfer' => 'Transfer',
            default => $metode,
        };
    }

    private function labelJenisPengerjaan(string $jenis): string
    {
        return match ($jenis) {
            'sendiri' => 'Sendiri',
            'berdua' => 'Berdua',
            default => $jenis,
        };
    }
}