<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $karyawans = Karyawan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('kontak', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('karyawans.index', compact('karyawans', 'q'));
    }

    public function create()
    {
        return view('karyawans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Karyawan::create($data);

        return redirect()
            ->route('karyawan.index')
            ->with('success', 'Karyawan "'.$data['nama'].'" berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawans.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $this->validated($request, $karyawan->id);

        $karyawan->update($data);

        return redirect()
            ->route('karyawan.index')
            ->with('success', 'Data karyawan "'.$data['nama'].'" berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $nama = $karyawan->nama;
        $karyawan->delete();

        return redirect()
            ->route('karyawan.index')
            ->with('success', 'Karyawan "'.$nama.'" berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:255'],
            'skema_komisi' => ['required', 'in:per_layanan,persen_omset_harian'],
            'persen_komisi_harian' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:skema_komisi,persen_omset_harian',
            ],
        ];

        $data = $request->validate($rules);

        if ($data['skema_komisi'] === 'per_layanan') {
            $data['persen_komisi_harian'] = null;
        }

        return $data;
    }
}
