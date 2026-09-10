<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiProduk;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $kategoriFilter = trim((string) $request->query('kategori'));

        $kategoriList = ['dijual' => 'Dijual Per PCS'] + array_combine(Produk::kategoriLayanan(), Produk::kategoriLayanan());

        $produks = Produk::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama_produk', 'like', "%{$q}%")
                    ->orWhere('merek', 'like', "%{$q}%");
            })
            ->when($kategoriFilter !== '' && array_key_exists($kategoriFilter, $kategoriList), function ($query) use ($kategoriFilter) {
                $query->where('kategori_produk', $kategoriFilter);
            })
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        $stokMenipis = Produk::where('aktif', true)
            ->where('kategori_produk', 'dijual')
            ->where('stok', '<=', Produk::STOK_MENIPIS)
            ->count();

        return view('produks.index', compact('produks', 'q', 'kategoriFilter', 'kategoriList', 'stokMenipis'));
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

        $data = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'merek' => ['nullable', 'required_unless:kategori_produk,dijual', 'in:Alfaparf,Milbon,Keaune,Omni,Matrix'],
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
