<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiProduk;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Services\ProdukModalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $mode = (string) $request->query('mode', 'layanan');
        if (! in_array($mode, ['layanan', 'pcs'], true)) {
            $mode = 'layanan';
        }
        $kategoriFilter = trim((string) $request->query('kategori'));

        $produks = Produk::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_produk', 'like', "%{$q}%")
                        ->orWhere('merek', 'like', "%{$q}%");
                });
            });

        if ($mode === 'pcs') {
            $produks->where('kategori_produk', 'dijual');
        } else {
            $produks->where('kategori_produk', '!=', 'dijual');
        }

        $kategoriList = [];
        if ($mode === 'layanan') {
            $kategoriList = array_combine(Produk::kategoriLayanan(), Produk::kategoriLayanan());
            if ($kategoriFilter !== '' && array_key_exists($kategoriFilter, $kategoriList)) {
                $produks->where('kategori_produk', $kategoriFilter);
            } else {
                $kategoriFilter = '';
            }
        }

        $produks = $produks
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        $stokMenipis = Produk::where('aktif', true)
            ->where('kategori_produk', 'dijual')
            ->where('stok', '<=', Produk::STOK_MENIPIS)
            ->count();

        return view('produks.index', compact('produks', 'q', 'mode', 'kategoriFilter', 'kategoriList', 'stokMenipis'));
    }

    public function create()
    {
        return view('produks.create');
    }

    public function show(Produk $produk)
    {
        return view('produks.show', compact('produk'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Produk::create($data);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk "'.$data['nama_produk'].'" berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        return view('produks.edit', compact('produk'));
    }

    /**
     * Form restock (pembelian stok) satu produk beserta riwayat pembelian.
     */
    public function restockForm(Produk $produk)
    {
        $riwayat = $produk->pembelianProduk()
            ->with('dicatatOleh')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return view('produks.restock', compact('produk', 'riwayat'));
    }

    /**
     * Simpan restock: update stok + harga_modal_rata_rata, catat pembelian & riwayat stok.
     */
    public function restock(Request $request, Produk $produk)
    {
        $harga = $request->input('harga_beli');
        if ($harga !== null) {
            $s = str_replace(['.', ','], '', (string) $harga);
            $request->merge(['harga_beli' => $s === '' ? null : $s]);
        }

        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.integer' => 'Jumlah harus bilangan bulat.',
            'qty.min' => 'Jumlah minimal 1.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
            'harga_beli.numeric' => 'Harga beli harus berupa angka.',
            'harga_beli.min' => 'Harga beli tidak boleh kurang dari 0.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh di masa depan.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        ProdukModalService::restock(
            $produk,
            (float) $data['qty'],
            (float) $data['harga_beli'],
            $data['keterangan'] ?? null,
            $data['tanggal'],
            auth()->id()
        );

        return redirect()
            ->route('produk.show', $produk)
            ->with('success', 'Restock "'.$produk->nama_produk.'" sebanyak '.rtrim(rtrim($data['qty'], '0'), '.').' '.$produk->satuan.' berhasil disimpan.');
    }

    public function update(Request $request, Produk $produk)
    {
        $data = $this->validated($request);

        $produk->update($data);

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk "'.$data['nama_produk'].'" berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        $nama = $produk->nama_produk;
        $jumlahTransaksi = 0;

        DB::transaction(function () use ($produk, &$jumlahTransaksi) {
            $idTransaksis = DetailTransaksi::where('id_produk', $produk->id)
                ->distinct()
                ->pluck('id_transaksi');

            $idDetailPemakaian = DetailTransaksiProduk::where('id_produk', $produk->id)
                ->distinct()
                ->pluck('id_detail_transaksi');

            $idTransaksis = $idTransaksis
                ->merge(
                    DetailTransaksi::whereIn('id', $idDetailPemakaian)
                        ->distinct()
                        ->pluck('id_transaksi')
                )
                ->unique();

            foreach (TransaksiKunjungan::whereIn('id', $idTransaksis)->get() as $transaksi) {
                $transaksi->restoreStock();
                $transaksi->delete();
                $jumlahTransaksi++;
            }

            $produk->delete();
        });

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk "'.$nama.'" dan '.$jumlahTransaksi.' transaksi terkait berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $harga = $request->input('harga_per_satuan');
        if ($harga !== null) {
            $s = str_replace(['.', ','], '', (string) $harga);
            $request->merge(['harga_per_satuan' => $s === '' ? null : $s]);
        }

        $modeMerek = $request->input('mode_merek', 'pilih');
        if ($modeMerek === 'baru') {
            $request->merge(['merek' => trim((string) $request->input('merek_baru'))]);
        }

        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'required_unless:kategori_produk,dijual', 'string', 'max:255'],
            'kategori_produk' => ['required', 'in:'.implode(',', ['dijual', 'dipakai_layanan'] + Produk::kategoriLayanan())],
            'satuan' => ['required', 'in:pcs,/10ml'],
            'harga_per_satuan' => ['required', 'numeric', 'min:0'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        $data['aktif'] = $request->boolean('aktif');
        $data['stok'] = $data['stok'] ?? 0;

        if (array_key_exists('merek', $data) && ($data['merek'] ?? '') === '') {
            $data['merek'] = null;
        }

        return $data;
    }
}
