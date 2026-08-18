<?php

namespace App\Http\Controllers;

use App\Models\HargaLayanan;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LayananController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $kategoriFilter = trim((string) $request->query('kategori'));

        $kategoriList = ['Potong', 'Styling', 'Treatment Rambut', 'Warna Rambut', 'Treatment'];

        $layanans = Layanan::query()
            ->with('hargaLayanan')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama_layanan', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%");
            })
            ->when($kategoriFilter !== '', function ($query) use ($kategoriFilter) {
                $query->where('kategori', $kategoriFilter);
            })
            ->orderBy('kategori')
            ->orderBy('nama_layanan')
            ->paginate(10)
            ->withQueryString();

        return view('layanans.index', compact('layanans', 'q', 'kategoriFilter', 'kategoriList'));
    }

    public function create()
    {
        return view('layanans.create');
    }

    public function show(Layanan $layanan)
    {
        $layanan->load('hargaLayanan');

        return view('layanans.show', compact('layanan'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedMain($request);
        $rows = $this->validatedRows($request);

        $layanan = DB::transaction(function () use ($data, $rows) {
            $layanan = Layanan::create($data);

            foreach ($rows as $row) {
                HargaLayanan::create(['id_layanan' => $layanan->id] + $row);
            }

            return $layanan;
        });

        return redirect()
            ->route('layanan.index')
            ->with('success', 'Layanan "'.$layanan->nama_layanan.'" berhasil ditambahkan.');
    }

    public function edit(Layanan $layanan)
    {
        $layanan->load('hargaLayanan');

        return view('layanans.edit', compact('layanan'));
    }

    public function update(Request $request, Layanan $layanan)
    {
        $data = $this->validatedMain($request);
        $rows = $this->validatedRows($request);

        DB::transaction(function () use ($request, $layanan, $data, $rows) {
            $layanan->update($data);
            $layanan->hargaLayanan()->delete();

            foreach ($rows as $row) {
                HargaLayanan::create(['id_layanan' => $layanan->id] + $row);
            }
        });

        return redirect()
            ->route('layanan.index')
            ->with('success', 'Layanan "'.$layanan->nama_layanan.'" berhasil diperbarui.');
    }

    public function destroy(Layanan $layanan)
    {
        $nama = $layanan->nama_layanan;
        $layanan->delete();

        return redirect()
            ->route('layanan.index')
            ->with('success', 'Layanan "'.$nama.'" berhasil dihapus.');
    }

    private function validatedMain(Request $request): array
    {
        $data = $request->validate([
            'nama_layanan' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:100'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }

    private function validatedRows(Request $request): array
    {
        $data = $request->validate([
            'varian' => ['required', 'array', 'min:1'],
            'varian.*' => ['required', 'string', 'max:255', 'distinct'],
            'harga_dasar_min.*' => ['required', 'numeric', 'min:0'],
            'harga_dasar_max.*' => ['nullable', 'numeric', 'min:0', 'gte:harga_dasar_min.*'],
            'tarif_kelebihan_per_10gr.*' => ['nullable', 'numeric', 'min:0'],
            'komisi_min.*' => ['nullable', 'numeric', 'min:0'],
            'komisi_max.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rows = [];
        foreach ($data['varian'] as $i => $varian) {
            $min = (float) $data['harga_dasar_min'][$i];
            $maxRaw = $data['harga_dasar_max'][$i] ?? null;
            $max = $maxRaw === null || $maxRaw === '' ? $min : (float) $maxRaw;

            $norm = function ($v) {
                return ($v === null || $v === '') ? null : $v;
            };

            $rows[] = [
                'varian' => $varian,
                'harga_dasar_min' => $min,
                'harga_dasar_max' => $max,
                'tarif_kelebihan_per_10gr' => $norm($data['tarif_kelebihan_per_10gr'][$i] ?? null),
                'komisi_min' => $norm($data['komisi_min'][$i] ?? null),
                'komisi_max' => $norm($data['komisi_max'][$i] ?? null),
            ];
        }

        return $rows;
    }
}
