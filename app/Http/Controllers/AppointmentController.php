<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $tanggal = $request->query('tanggal');

        Appointment::whereDate('tanggal', '<', now()->toDateString())->delete();

        $appointments = Appointment::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('service', 'like', "%{$q}%")
                        ->orWhere('no_wa', 'like', "%{$q}%");
                });
            })
            ->when($tanggal !== null && $tanggal !== '', function ($query) use ($tanggal) {
                $query->whereDate('tanggal', $tanggal);
            })
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu')
            ->paginate(20)
            ->withQueryString();

        $tglRingkasan = ($tanggal !== null && $tanggal !== '')
            ? $tanggal
            : now()->toDateString();

        $ringkasanHarian = Appointment::whereDate('tanggal', $tglRingkasan)
            ->orderBy('waktu')
            ->get();

        return view('appointments.index', compact('appointments', 'q', 'tanggal', 'tglRingkasan', 'ringkasanHarian'));
    }

    public function create()
    {
        return view('appointments.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->ensureKuotaCukup($data);

        Appointment::create($data);

        return redirect()
            ->route('appointment.index')
            ->with('success', 'Appointment untuk "'.$data['nama'].'" berhasil ditambahkan.');
    }

    public function edit(Appointment $appointment)
    {
        return view('appointments.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validated($request);
        $this->ensureKuotaCukup($data, $appointment->id);

        $appointment->update($data);

        return redirect()
            ->route('appointment.index')
            ->with('success', 'Appointment "'.$data['nama'].'" berhasil diperbarui.');
    }

    public function destroy(Appointment $appointment)
    {
        $nama = $appointment->nama;
        $appointment->delete();

        return redirect()
            ->route('appointment.index')
            ->with('success', 'Appointment "'.$nama.'" berhasil dihapus.');
    }

    public function kuota(Request $request)
    {
        $tanggal = $request->query('tanggal');
        $waktu = $request->query('waktu');
        $excludeId = $request->query('exclude_id');

        if (! $tanggal || ! $waktu) {
            return response()->json(['terpakai' => 0, 'sisa' => Appointment::KUOTA_MAKSIMAL, 'maksimal' => Appointment::KUOTA_MAKSIMAL]);
        }

        $terpakai = Appointment::kuotaTerpakai($tanggal, $waktu, $excludeId ? (int) $excludeId : null);

        return response()->json([
            'terpakai' => $terpakai,
            'sisa' => max(0, Appointment::KUOTA_MAKSIMAL - $terpakai),
            'maksimal' => Appointment::KUOTA_MAKSIMAL,
        ]);
    }

    public function slotKuota(Request $request)
    {
        $tanggal = $request->query('tanggal');
        $excludeId = $request->query('exclude_id');

        if (! $tanggal) {
            return response()->json(['slots' => [], 'jam_buka' => Appointment::JAM_BUKA, 'jam_tutup' => Appointment::JAM_TUTUP]);
        }

        return response()->json([
            'slots' => Appointment::slotKuota($tanggal, $excludeId ? (int) $excludeId : null),
            'jam_buka' => Appointment::JAM_BUKA,
            'jam_tutup' => Appointment::JAM_TUTUP,
        ]);
    }

    private function ensureKuotaCukup(array $data, ?int $excludeId = null): void
    {
        $waktu = $data['waktu'];
        $tanggal = $data['tanggal'] instanceof \DateTimeInterface
            ? $data['tanggal']->format('Y-m-d')
            : (string) $data['tanggal'];

        $terpakai = Appointment::kuotaTerpakai($tanggal, $waktu, $excludeId);
        $bobot = Appointment::bobot($data['kategori'] ?? null);
        $sisa = Appointment::KUOTA_MAKSIMAL - $terpakai;

        if ($bobot > $sisa) {
            throw ValidationException::withMessages([
                'waktu' => 'Kuota jam '.$waktu.' sudah tidak cukup untuk layanan ini (sisa '.
                    max(0, $sisa).' dari '.Appointment::KUOTA_MAKSIMAL.'). Pilih jam lain yang tersedia.',
            ]);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'waktu' => ['required', 'date_format:H:i'],
            'nama' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:'.implode(',', array_keys(Appointment::BOBOT_KATEGORI))],
            'no_wa' => ['nullable', 'string', 'max:255', 'regex:/^\+[1-9]\d{4,12}$/'],
        ], [
            'kategori.required' => 'Pilih layanan dari daftar yang muncul, supaya kategori & kuota bisa dihitung otomatis.',
            'no_wa.regex' => 'No. WhatsApp maksimal 13 digit dan harus diawali kode negara, contoh: +6281234567890.',
        ]);
    }
}