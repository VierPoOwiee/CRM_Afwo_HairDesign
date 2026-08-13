<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $pelanggans = Pelanggan::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_wa', 'like', "%{$q}%")
                    ->orWhere('username_instagram', 'like', "%{$q}%")
                    ->orWhere('alamat', 'like', "%{$q}%");
            })
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggans.index', compact('pelanggans', 'q'));
    }

    public function create()
    {
        return view('pelanggans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Pelanggan::create($data);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan "'.$data['nama'].'" berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggans.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $data = $this->validated($request, $pelanggan->id);

        $pelanggan->update($data);

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Data pelanggan "'.$data['nama'].'" berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $nama = $pelanggan->nama;
        $pelanggan->delete();

        return redirect()
            ->route('pelanggan.index')
            ->with('success', 'Pelanggan "'.$nama.'" berhasil dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $noWaUnique = 'unique:pelanggans,no_wa';
        if ($ignoreId) {
            $noWaUnique .= ','.$ignoreId;
        }

        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:255', $noWaUnique],
            'username_instagram' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'jenis_rambut' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'catatan_khusus' => ['nullable', 'string'],
        ];

        return $request->validate($rules);
    }
}
