<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiProduk;
use App\Models\Karyawan;
use App\Models\KomisiHarianSpesial;
use App\Models\KomisiTransaksi;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\RiwayatStokProduk;
use App\Models\TransaksiKunjungan;
use App\Services\ProdukModalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $statusFilter = trim((string) $request->query('status'));
        $metodeFilter = trim((string) $request->query('metode'));
        $layananFilter = (int) $request->query('layanan');
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        $transaksis = TransaksiKunjungan::query()
            ->with('pelanggan')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('no_struk', 'like', "%{$q}%")
                    ->orWhereHas('pelanggan', fn ($qr) => $qr->where('nama', 'like', "%{$q}%"));
            })
            ->when($statusFilter !== '' && in_array($statusFilter, ['selesai', 'batal']), function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->when($metodeFilter !== '' && in_array($metodeFilter, ['cash', 'qris_bni', 'qris_bri', 'debit', 'kartu_kredit', 'transfer']), function ($query) use ($metodeFilter) {
                $query->where('metode_pembayaran', $metodeFilter);
            })
            ->when($layananFilter > 0, function ($query) use ($layananFilter) {
                $query->whereHas('details', fn ($qr) => $qr->where('id_layanan', $layananFilter));
            })
            ->when($dari !== '' && $dari !== null, function ($query) use ($dari) {
                $query->where('waktu_kunjungan', '>=', $dari);
            })
            ->when($sampai !== '' && $sampai !== null, function ($query) use ($sampai) {
                $query->where('waktu_kunjungan', '<=', $sampai.' 23:59:59');
            })
            ->orderByDesc('waktu_kunjungan')
            ->paginate(15)
            ->withQueryString();

        $layanans = Layanan::where('aktif', true)->orderBy('nama_layanan')->get();

        return view('transaksis.index', compact('transaksis', 'q', 'statusFilter', 'metodeFilter', 'layananFilter', 'layanans', 'dari', 'sampai'));
    }

    public function create()
    {
        $pelanggans = Pelanggan::orderBy('nama')->get();
        $karyawans = Karyawan::orderBy('nama')->get();
        $produks = Produk::where('aktif', true)
            ->where('kategori_produk', '!=', 'dijual')
            ->orderBy('merek')
            ->orderBy('nama_produk')
            ->get();

        return view('transaksis.create', compact('pelanggans', 'karyawans', 'produks'));
    }

    public function store(Request $request)
    {
        // Convert empty string IDs to null so nullable+exists rules work
        $cleanedItems = array_map(function ($item) {
            foreach (['id_layanan', 'id_produk', 'id_staf_1', 'id_staf_2'] as $field) {
                $item[$field] = ! empty($item[$field]) ? $item[$field] : null;
            }

            return $item;
        }, $request->items);
        $request->merge(['items' => $cleanedItems]);

        $request->validate([
            'id_pelanggan' => ['required', 'exists:pelanggans,id'],
            'jenis_pengerjaan' => ['required', 'in:sendiri,berdua'],
            'waktu_kunjungan' => ['required', 'date'],
            'metode_pembayaran' => ['required', 'in:cash,qris_bni,qris_bri,debit,kartu_kredit,transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.tipe_item' => ['required', 'in:layanan,produk'],
            'items.*.id_staf_1' => ['nullable', 'exists:karyawans,id'],
            'items.*.id_staf_2' => ['nullable', 'exists:karyawans,id'],
            'items.*.id_layanan' => ['nullable', 'exists:layanan,id'],
            'items.*.id_produk' => ['nullable', 'exists:produk,id'],
            'items.*.varian_dipilih' => ['nullable', 'string', 'max:255'],
            'items.*.ketebalan_rambut' => ['nullable', 'string', 'max:255'],
            'items.*.gram_pemakaian_tambahan' => ['nullable', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.harga_saat_transaksi' => ['required', 'numeric', 'min:0'],
            'items.*.diskon' => ['nullable', 'numeric', 'min:0'],
            'items.*.komisi_nominal_1' => ['nullable', 'numeric', 'min:0'],
            'items.*.komisi_nominal_2' => ['nullable', 'numeric', 'min:0'],
            'items.*.catatan' => ['nullable', 'string'],
        ]);

        // Manual cross-field validation
        foreach ($request->items as $idx => $item) {
            if ($item['tipe_item'] === 'layanan' && empty($item['id_layanan'])) {
                return back()->withErrors(['items.'.$idx.'.id_layanan' => 'Wajib dipilih untuk item layanan.'])->withInput();
            }
            if ($item['tipe_item'] === 'layanan' && empty($item['id_staf_1'])) {
                return back()->withErrors(['items.'.$idx.'.id_staf_1' => 'Staf wajib dipilih untuk item layanan.'])->withInput();
            }
            if ($item['tipe_item'] === 'produk' && empty($item['id_produk'])) {
                return back()->withErrors(['items.'.$idx.'.id_produk' => 'Wajib dipilih untuk item produk.'])->withInput();
            }
            if ($request->jenis_pengerjaan === 'berdua' && empty($item['id_staf_2'])) {
                return back()->withErrors(['items.'.$idx.'.id_staf_2' => 'Jenis berdua wajib pilih 2 staf.'])->withInput();
            }
            if ($request->jenis_pengerjaan === 'sendiri' && ! empty($item['id_staf_2'])) {
                return back()->withErrors(['items.'.$idx.'.id_staf_2' => 'Staf ke-2 hanya untuk jenis berdua.'])->withInput();
            }
        }

        $transaksi = DB::transaction(function () use ($request) {
            $noStruk = TransaksiKunjungan::generateNoStruk();
            $isBerdua = $request->jenis_pengerjaan === 'berdua';

            $transaksi = TransaksiKunjungan::create([
                'id_pelanggan' => $request->id_pelanggan,
                'jenis_pengerjaan' => $request->jenis_pengerjaan,
                'no_struk' => $noStruk,
                'waktu_kunjungan' => $request->waktu_kunjungan,
                'metode_pembayaran' => $request->metode_pembayaran,
                'total_bayar' => 0,
            ]);

            foreach ($request->items as $item) {
                $harga = (float) $item['harga_saat_transaksi'];
                $qty = (float) $item['qty'];
                $diskon = ! empty($item['diskon']) ? (float) $item['diskon'] : 0;
                $subtotal = max(0, ($harga * $qty) - $diskon);
                $staf2 = $item['id_staf_2'] ?? null;

                // Snapshot modal untuk penjualan produk retail (sebelum stok dikurangi).
                // Lock produk terlebih dahulu agar nilai modal konsisten.
                $hargaModalSatuan = null;
                $keuntungan = null;
                if ($item['tipe_item'] === 'produk' && ! empty($item['id_produk'])) {
                    $produk = Produk::query()->lockForUpdate()->findOrFail($item['id_produk']);
                    $hargaModalSatuan = round((float) $produk->harga_modal_rata_rata, 2);
                    $keuntungan = round($subtotal - ($hargaModalSatuan * $qty), 2);

                    $produk->decrement('stok', $qty);
                    $produk->refresh();
                    ProdukModalService::catat(
                        $produk,
                        RiwayatStokProduk::JENIS_PENJUALAN,
                        -$qty,
                        $produk->stok,
                        'Penjualan retail '.$qty.' '.($produk->satuan ?? '').' '.$produk->nama_produk,
                        auth()->id()
                    );
                }

                // Detail for staf 1 (primary — carries the subtotal)
                $detail1 = DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id,
                    'id_staf' => $item['id_staf_1'] ?? null,
                    'tipe_item' => $item['tipe_item'],
                    'id_layanan' => $item['id_layanan'] ?? null,
                    'id_produk' => $item['id_produk'] ?? null,
                    'varian_dipilih' => $item['varian_dipilih'] ?? null,
                    'ketebalan_rambut' => $item['ketebalan_rambut'] ?? null,
                    'gram_pemakaian_tambahan' => $item['gram_pemakaian_tambahan'] ?? 0,
                    'qty' => $qty,
                    'harga_saat_transaksi' => $harga,
                    'harga_modal_satuan' => $hargaModalSatuan,
                    'keuntungan' => $keuntungan,
                    'diskon' => $diskon,
                    'subtotal' => $subtotal,
                    'komisi_nominal' => ! empty($item['komisi_nominal_1']) ? $item['komisi_nominal_1'] : null,
                    'catatan' => $item['catatan'] ?? null,
                ]);

                // Detail for staf 2 (berdua — subtotal=0, only komisi tracked)
                if ($isBerdua && $staf2) {
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id,
                        'id_staf' => $staf2,
                        'tipe_item' => $item['tipe_item'],
                        'id_layanan' => $item['id_layanan'] ?? null,
                        'id_produk' => $item['id_produk'] ?? null,
                        'varian_dipilih' => $item['varian_dipilih'] ?? null,
                        'ketebalan_rambut' => $item['ketebalan_rambut'] ?? null,
                        'gram_pemakaian_tambahan' => 0,
                        'qty' => 1,
                        'harga_saat_transaksi' => 0,
                        'subtotal' => 0,
                        'komisi_nominal' => ! empty($item['komisi_nominal_2']) ? $item['komisi_nominal_2'] : null,
                        'catatan' => '(Staf ke-2) '.($item['catatan'] ?? ''),
                    ]);
                }

                // Save product usage for layanan items (deduct stock + track cost).
                if ($item['tipe_item'] === 'layanan' && ! empty($item['produk_penggunaan'])) {
                    foreach ($item['produk_penggunaan'] as $pu) {
                        if (empty($pu['id_produk']) || empty($pu['pemakaian_ml'])) {
                            continue;
                        }
                        $produk = Produk::query()->lockForUpdate()->findOrFail($pu['id_produk']);
                        $pemakaianMl = (float) $pu['pemakaian_ml'];

                        $hargaPerUnit = (float) $produk->harga_per_satuan;
                        $unit = $pemakaianMl / 10;
                        $produkSubtotal = $unit * $hargaPerUnit;
                        $hargaModalTerpakai = round((float) $produk->harga_modal_rata_rata * $unit, 2);

                        DetailTransaksiProduk::create([
                            'id_detail_transaksi' => $detail1->id,
                            'id_produk' => $pu['id_produk'],
                            'pemakaian_ml' => $pemakaianMl,
                            'harga_per_unit' => $hargaPerUnit,
                            'harga_modal_terpakai' => $hargaModalTerpakai,
                            'subtotal' => round($produkSubtotal),
                        ]);

                        // Deduct stock for product used in layanan
                        $produk->decrement('stok', $pemakaianMl);
                        $produk->refresh();
                        ProdukModalService::catat(
                            $produk,
                            RiwayatStokProduk::JENIS_PEMAKAIAN_LAYANAN,
                            -$pemakaianMl,
                            $produk->stok,
                            'Pemakaian layanan '.$pemakaianMl.' ml '.$produk->nama_produk,
                            auth()->id()
                        );
                    }
                }
            }

            // Recalculate total_bayar from sum of all detail subtotals
            $transaksi->recalculateTotal();

            // Sync komisi_transaksi for each unique staff
            KomisiTransaksi::syncForTransaksi($transaksi);

            // Recalculate daily omset commission for persen_omset_harian staff
            $this->syncKomisiHarianUntuk($transaksi->waktu_kunjungan);

            return $transaksi;
        });

        return redirect()
            ->route('transaksi.index')
            ->with('success', 'Transaksi "'.$transaksi->no_struk.'" berhasil disimpan.');
    }

    public function show(TransaksiKunjungan $transaksi)
    {
        $transaksi->load([
            'pelanggan',
            'details.staf',
            'details.layanan',
            'details.produk',
            'details.produkPenggunaan.produk',
            'komisiTransaksi.staf',
        ]);

        return view('transaksis.show', compact('transaksi'));
    }

    public function update(Request $request, TransaksiKunjungan $transaksi)
    {
        //
    }

    /**
     * Change status to batal: restore stock, delete komisi_transaksi.
     */
    public function cancel(TransaksiKunjungan $transaksi)
    {
        if ($transaksi->status === 'batal') {
            return back()->with('error', 'Transaksi sudah dalam status batal.');
        }

        DB::transaction(function () use ($transaksi) {
            // Restore stock (produk retail + pemakaian layanan) beserta riwayat stok
            $transaksi->restoreStock('Restore stok, transaksi '.$transaksi->no_struk.' dibatalkan');

            // Delete komisi_transaksi
            $transaksi->komisiTransaksi()->delete();

            // Update status
            $transaksi->update(['status' => 'batal']);

            // Recalculate daily omset commission for persen_omset_harian staff
            $this->syncKomisiHarianUntuk($transaksi->waktu_kunjungan);
        });

        return back()->with('success', 'Transaksi dibatalkan. Stok produk telah dikembalikan.');
    }

    public function updateKomisi(Request $request, TransaksiKunjungan $transaksi)
    {
        $request->validate([
            'detail_id' => ['required', 'exists:detail_transaksi,id'],
            'komisi_nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $detail = $transaksi->details()->findOrFail($request->detail_id);
        $detail->update(['komisi_nominal' => $request->komisi_nominal]);

        // Re-sync komisi_transaksi after change
        KomisiTransaksi::syncForTransaksi($transaksi);

        return back()->with('success', 'Komisi berhasil diperbarui.');
    }

    /**
     * Update komisi per staf (manual split editing by admin).
     */
    public function updateKomisiStaf(Request $request, TransaksiKunjungan $transaksi)
    {
        $request->validate([
            'komisi_staf_id' => ['required', 'exists:komisi_transaksi,id'],
            'jumlah_komisi' => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $komisiStaf = $transaksi->komisiTransaksi()->findOrFail($request->komisi_staf_id);
        $komisiStaf->update([
            'jumlah_komisi' => $request->jumlah_komisi,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Komisi staf berhasil diperbarui.');
    }

    public function searchPelanggan(Request $request)
    {
        $q = $request->input('q', '');

        $pelanggans = Pelanggan::where('nama', 'like', "%{$q}%")
            ->orWhere('no_wa', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'nama', 'no_wa', 'jenis_rambut']);

        return response()->json($pelanggans);
    }

    /**
     * Store new pelanggan via AJAX from transaksi form.
     */
    public function storePelanggan(Request $request)
    {
        $request->merge(['no_wa' => $request->input('no_wa') ?: null]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_wa' => ['nullable', 'string', 'max:50', 'regex:/^\+[1-9]\d{4,12}$/'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'jenis_rambut' => ['nullable', 'string', 'max:100'],
        ], [
            'no_wa.regex' => 'No. WhatsApp harus diawali kode negara, contoh: +6281234567890.',
        ]);

        $pelanggan = Pelanggan::create($data);

        return response()->json($pelanggan);
    }

    public function searchLayanan(Request $request)
    {
        $q = $request->input('q', '');

        $layanans = Layanan::where('aktif', true)
            ->where('nama_layanan', 'like', "%{$q}%")
            ->with([
                'hargaLayanan' => function ($query) {
                    $query->orderBy('harga_dasar_min')->with('defaultProduk');
                },
                'produk' => function ($query) {
                    $query->where('aktif', true)->orderBy('merek')->orderBy('nama_produk');
                },
            ])
            ->limit(10)
            ->get();

        return response()->json($layanans);
    }

    public function searchProduk(Request $request)
    {
        $q = $request->input('q', '');
        $mode = $request->input('mode', '');

        $produks = Produk::where('aktif', true)
            ->when($mode === 'dijual', fn ($query) => $query->where('kategori_produk', 'dijual'))
            ->when($mode === 'pakai', fn ($query) => $query->where('kategori_produk', '!=', 'dijual'))
            ->where('nama_produk', 'like', "%{$q}%")
            ->limit(10)
            ->get();

        return response()->json($produks);
    }

    public function destroy(TransaksiKunjungan $transaksi)
    {
        DB::transaction(function () use ($transaksi) {
            $transaksi->restoreStock('Stok dikembalikan, transaksi '.$transaksi->no_struk.' dihapus');
            $transaksi->delete();

            // Recalculate daily omset commission for persen_omset_harian staff
            $this->syncKomisiHarianUntuk($transaksi->waktu_kunjungan);
        });

        return redirect()
            ->route('transaksi.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Recalculate daily omset commission (komisi_harian_spesial) for all
     * persen_omset_harian staff who served transactions on the given date.
     */
    private function syncKomisiHarianUntuk($waktuKunjungan): void
    {
        $tanggal = \Carbon\Carbon::parse($waktuKunjungan)->toDateString();

        $idStafAktif = DetailTransaksi::whereHas('transaksi', function ($q) use ($tanggal) {
            $q->whereDate('waktu_kunjungan', $tanggal)
                ->where('status', 'selesai');
        })
            ->whereNotNull('id_staf')
            ->distinct()
            ->pluck('id_staf');

        Karyawan::whereIn('id', $idStafAktif)
            ->where('skema_komisi', 'persen_omset_harian')
            ->where('persen_komisi_harian', '>', 0)
            ->get()
            ->each(function (Karyawan $staf) use ($tanggal) {
                KomisiHarianSpesial::calculateForDate($staf, $tanggal);
            });
    }
}
