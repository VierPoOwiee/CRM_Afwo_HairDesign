<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $skema = (string) $request->query('skema', 'all');
        $sort = (string) $request->query('sort', 'name-asc');

        $karyawans = Karyawan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('kontak', 'like', "%{$q}%");
                });
            })
            ->when(in_array($skema, ['per_layanan', 'persen_omset_harian'], true), function ($query) use ($skema) {
                $query->where('skema_komisi', $skema);
            })
            ->when($sort === 'name-desc', fn ($query) => $query->orderByDesc('nama'))
            ->when($sort === 'salary-desc', fn ($query) => $query->orderByDesc('gaji_pokok'))
            ->when($sort === 'salary-asc', fn ($query) => $query->orderBy('gaji_pokok'))
            ->when($sort === 'scheme', fn ($query) => $query->orderBy('skema_komisi')->orderBy('nama'))
            ->when(! in_array($sort, ['name-desc', 'salary-desc', 'salary-asc', 'scheme'], true), fn ($query) => $query->orderBy('nama'))
            ->paginate(10)
            ->withQueryString();

        return view('karyawans.index', compact('karyawans', 'q', 'skema', 'sort'));
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
            ->with('success', 'Karyawan "'.$nama.'" berhasil dihapus (diarsipkan).');
    }

    public function arsip(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $karyawans = Karyawan::onlyTrashed()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('kontak', 'like', "%{$q}%");
            })
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('karyawans.arsip', compact('karyawans', 'q'));
    }

    public function restore(int $karyawan)
    {
        $k = Karyawan::onlyTrashed()->findOrFail($karyawan);
        $nama = $k->nama;
        $k->restore();

        return redirect()
            ->route('karyawan.arsip')
            ->with('success', 'Karyawan "'.$nama.'" berhasil dipulihkan.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $gaji = $request->input('gaji_pokok');
        if ($gaji !== null) {
            $s = str_replace(['.', ','], '', (string) $gaji);
            $request->merge(['gaji_pokok' => $s === '' ? null : $s]);
        }

        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'kontak' => ['nullable', 'string', 'max:255', 'regex:/^\+[1-9]\d{4,12}$/'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'skema_komisi' => ['required', 'in:per_layanan,persen_omset_harian'],
            'persen_komisi_harian' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:skema_komisi,persen_omset_harian',
            ],
        ];

        $messages = [
            'kontak.regex' => 'Kontak harus diawali kode negara, contoh: +6281234567890.',
        ];

        $data = $request->validate($rules, $messages);

        $data['gaji_pokok'] = $data['gaji_pokok'] ?? 0;

        if ($data['skema_komisi'] === 'per_layanan') {
            $data['persen_komisi_harian'] = null;
        }

        return $data;
    }
}
