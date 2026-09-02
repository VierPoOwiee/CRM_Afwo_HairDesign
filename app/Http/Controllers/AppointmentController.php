<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $tanggal = $request->query('tanggal');

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

        return view('appointments.index', compact('appointments', 'q', 'tanggal'));
    }

    public function create()
    {
        return view('appointments.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

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

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date'],
            'waktu' => ['required', 'date_format:H:i'],
            'nama' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
            'no_wa' => ['nullable', 'string', 'max:255', 'regex:/^\+[1-9]\d{4,12}$/'],
        ], [
            'no_wa.regex' => 'No. WhatsApp maksimal 13 digit dan harus diawali kode negara, contoh: +6281234567890.',
        ]);
    }
}