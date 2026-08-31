<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $kategoriFilter = trim((string) $request->query('kategori'));

        $kategoriList = ['dijual' => 'Dijual Per PCS', 'dipakai_layanan' => 'Dipakai Layanan'];

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
        $produk->delete();

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk "'.$nama.'" berhasil dihapus.');
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
            'merek' => ['nullable', 'required_if:kategori_produk,dipakai_layanan', 'in:Alfaparf,Milbon,Keaune'],
            'kategori_produk' => ['required', 'in:dijual,dipakai_layanan'],
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
