<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $kondisi = (string) $request->query('kondisi', 'all');
        $sort = (string) $request->query('sort', 'name-asc');

        $kondisiMap = [
            'sehat' => 'Sehat',
            'agak-rusak' => 'Agak Rusak',
            'rusak' => 'Rusak',
        ];

        $pelanggans = Pelanggan::query()
            ->whereNull('deleted_at')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('no_wa', 'like', "%{$q}%")
                        ->orWhere('username_instagram', 'like', "%{$q}%")
                        ->orWhere('alamat', 'like', "%{$q}%");
                });
            })
            ->when(array_key_exists($kondisi, $kondisiMap), function ($query) use ($kondisi, $kondisiMap) {
                $query->where('kondisi_rambut', $kondisiMap[$kondisi]);
            })
            ->when($sort === 'name-desc', fn ($query) => $query->orderByDesc('nama'))
            ->when($sort === 'hair-type', fn ($query) => $query->orderBy('jenis_rambut')->orderBy('nama'))
            ->when($sort === 'condition', fn ($query) => $query->orderBy('kondisi_rambut')->orderBy('nama'))
            ->when(! in_array($sort, ['name-desc', 'hair-type', 'condition'], true), fn ($query) => $query->orderBy('nama'))
            ->paginate(10)
            ->withQueryString();

        return view('pelanggans.index', compact('pelanggans', 'q', 'kondisi', 'sort', 'kondisiMap'));
    }

    public function create()
    {
        return view('pelanggans.create');
    }

    public function show(Pelanggan $pelanggan)
    {
        $pelanggan->load([
            'transaksiKunjungan' => fn ($t) => $t
                ->with('details.layanan', 'details.produk')
                ->orderByDesc('waktu_kunjungan')
                ->limit(15),
        ]);

        return view('pelanggans.show', compact('pelanggan'));
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
            ->with('success', 'Pelanggan "'.$nama.'" berhasil dihapus (diarsipkan).');
    }

    public function arsip(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $pelanggans = Pelanggan::onlyTrashed()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_wa', 'like', "%{$q}%")
                    ->orWhere('username_instagram', 'like', "%{$q}%")
                    ->orWhere('alamat', 'like', "%{$q}%");
            })
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        return view('pelanggans.arsip', compact('pelanggans', 'q'));
    }

    public function restore(int $pelanggan)
    {
        $p = Pelanggan::onlyTrashed()->findOrFail($pelanggan);
        $nama = $p->nama;
        $p->restore();

        return redirect()
            ->route('pelanggan.arsip')
            ->with('success', 'Pelanggan "'.$nama.'" berhasil dipulihkan.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $noWaUnique = 'unique:pelanggans,no_wa';
        if ($ignoreId) {
            $noWaUnique .= ','.$ignoreId;
        }

        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'no_wa' => ['required', 'string', 'max:255', $noWaUnique, 'regex:/^\+[1-9]\d{4,12}$/'],
            'username_instagram' => ['nullable', 'string', 'max:255'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'jenis_rambut' => ['nullable', 'string', 'max:255'],
            'kondisi_rambut' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'catatan_khusus' => ['nullable', 'string'],
        ];

        $messages = [
            'no_wa.regex' => 'No. WhatsApp harus diawali kode negara, contoh: +6281234567890.',
        ];

        return $request->validate($rules, $messages);
    }
}
