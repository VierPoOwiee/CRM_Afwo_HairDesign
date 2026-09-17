<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Support\AppointmentColor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $bulanParam = (string) $request->query('bulan', now()->format('Y-m'));

        try {
            $bulanAktif = Carbon::createFromFormat('Y-m', $bulanParam)->startOfMonth();
        } catch (\Throwable $e) {
            $bulanAktif = now()->copy()->startOfMonth();
        }

        $awalBulan = $bulanAktif->copy()->startOfMonth();
        $akhirBulan = $bulanAktif->copy()->endOfMonth();

        $appointments = Appointment::with(['pelanggan', 'karyawan', 'layanans'])
            ->whereBetween('tanggal', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        // Index per tanggal, untuk isi pill di tiap sel kalender
        $perTanggal = $appointments->groupBy(fn ($a) => $a->tanggal?->format('Y-m-d'));

        // Grid hari: mulai Senin (startOfWeek(MONDAY)) s.d. Minggu,
        // jumlahnya pasti kelipatan 7 sehingga aman langsung di-`grid-cols-7`.
        $mulaiGrid = $awalBulan->copy()->startOfWeek(Carbon::MONDAY);
        $selesaiGrid = $akhirBulan->copy()->endOfWeek(Carbon::SUNDAY);

        $hariGrid = collect();
        $cursor = $mulaiGrid->copy();
        while ($cursor->lte($selesaiGrid)) {
            $hariGrid->push([
                'tanggal' => $cursor->copy(),
                'dalamBulanIni' => $cursor->month === $bulanAktif->month,
                'isHariIni' => $cursor->isToday(),
            ]);
            $cursor->addDay();
        }

        // Klien (pelanggan) yang punya appointment di bulan ini saja
        $klienList = $appointments
            ->whereNotNull('id_pelanggan')
            ->groupBy('id_pelanggan')
            ->map(fn ($g) => [
                'id' => (int) $g->first()->id_pelanggan,
                'nama' => $g->first()->pelanggan?->nama,
                'warna' => $g->first()->pelanggan
                    ? AppointmentColor::forId((int) $g->first()->pelanggan->id)
                    : 'orange',
                'jumlah' => $g->count(),
            ])
            ->values()
            ->sortByDesc('jumlah')
            ->values();

        // Stylist/karyawan yang punya appointment di bulan ini saja
        $stylistList = $appointments
            ->whereNotNull('id_karyawan')
            ->groupBy('id_karyawan')
            ->map(fn ($g) => [
                'id' => (int) $g->first()->id_karyawan,
                'nama' => $g->first()->karyawan?->nama,
                'warna' => AppointmentColor::forId((int) $g->first()->id_karyawan),
                'jumlah' => $g->count(),
            ])
            ->values()
            ->sortByDesc('jumlah')
            ->values();

        $statusCounts = $appointments->groupBy('status')->map->count();

        $prevBulan = $awalBulan->copy()->subMonth()->format('Y-m');
        $nextBulan = $akhirBulan->copy()->addMonth()->format('Y-m');
        $today = now()->toDateString();
        $jumlahBulan = $appointments->count();

        return view('appointments.index', compact(
            'bulanAktif',
            'bulanParam',
            'hariGrid',
            'perTanggal',
            'klienList',
            'stylistList',
            'statusCounts',
            'prevBulan',
            'nextBulan',
            'today',
            'jumlahBulan',
        ));
    }

    public function create(Request $request)
    {
        $tanggal = $request->query('tanggal');
        if (! $tanggal || ! strtotime($tanggal)) {
            $tanggal = now()->toDateString();
        }

        $appointment = new Appointment(['tanggal' => $tanggal]);

        return view('appointments.create', compact('appointment'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $layananIds = $data['id_layanan'];
        unset($data['id_layanan']);

        $appointment = Appointment::create($data);
        $appointment->layanans()->sync($layananIds);

        return redirect()
            ->route('appointment.index', ['bulan' => Carbon::parse($appointment->tanggal)->format('Y-m')])
            ->with('success', 'Appointment untuk "'.$appointment->load('pelanggan')->pelanggan?->nama.'" berhasil ditambahkan.');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['pelanggan', 'karyawan', 'layanans']);

        return response()->json($this->payload($appointment));
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load(['pelanggan', 'karyawan', 'layanans']);
        $selectedPelanggan = $appointment->pelanggan;
        $selectedKaryawan = $appointment->karyawan;

        return view('appointments.edit', compact('appointment', 'selectedPelanggan', 'selectedKaryawan'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validated($request);
        $layananIds = $data['id_layanan'];
        unset($data['id_layanan']);

        $appointment->update($data);
        $appointment->layanans()->sync($layananIds);

        return redirect()
            ->route('appointment.index', ['bulan' => Carbon::parse($appointment->tanggal)->format('Y-m')])
            ->with('success', 'Appointment untuk "'.$appointment->load('pelanggan')->pelanggan?->nama.'" berhasil diperbarui.');
    }

    public function markSelesai(Appointment $appointment)
    {
        $appointment->update(['status' => 'selesai']);

        return redirect()
            ->route('appointment.index', ['bulan' => $appointment->tanggal?->format('Y-m')])
            ->with('success', 'Appointment "'.$appointment->layananNama().'" ditandai selesai.');
    }

    public function destroy(Appointment $appointment)
    {
        $bulan = $appointment->tanggal?->format('Y-m');
        $judul = $appointment->layananNama();
        $appointment->delete();

        return redirect()
            ->route('appointment.index', ['bulan' => $bulan])
            ->with('success', 'Appointment "'.$judul.'" berhasil dihapus.');
    }

    public function hari(string $tanggal)
    {
        $date = Carbon::createFromFormat('Y-m-d', $tanggal);
        abort_if(! $date, 422, 'Tanggal tidak valid.');

        $list = Appointment::with(['pelanggan', 'karyawan', 'layanans'])
            ->whereDate('tanggal', $date->toDateString())
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn ($a) => $this->payload($a));

        $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return response()->json([
            'tanggal' => $date->toDateString(),
            'hari_pendek' => strtoupper(substr($namaHari[$date->dayOfWeek], 0, 3)),
            'hari' => $date->day,
            'judul' => $namaHari[$date->dayOfWeek].', '.$date->day.' '.$namaBulan[$date->month - 1].' '.$date->year,
            'jumlah' => $list->count(),
            'jam_buka' => Appointment::JAM_BUKA,
            'jam_tutup' => Appointment::JAM_TUTUP,
            'appointments' => $list->values(),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'id_pelanggan' => ['required', 'exists:pelanggans,id'],
            'id_karyawan' => ['required', 'exists:karyawans,id'],
            'id_layanan' => ['required', 'array', 'min:1'],
            'id_layanan.*' => ['exists:layanan,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'durasi_menit' => ['required', 'integer', 'min:10', 'max:1440'],
            'preferensi' => ['nullable', 'string'],
        ], [
            'id_pelanggan.required' => 'Pelanggan wajib dipilih.',
            'id_karyawan.required' => 'Stylist/Karyawan wajib dipilih.',
            'id_layanan.required' => 'Layanan wajib dipilih.',
            'id_layanan.array' => 'Pilih minimal satu layanan.',
            'id_layanan.min' => 'Pilih minimal satu layanan.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'tanggal.required' => 'Tanggal wajib diisi.',
        ]);
    }

    /**
     * Pencarian layanan aktif untuk combobox multi-select di form appointment.
     */
    public function searchLayanan(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        return response()->json(
            Layanan::where('aktif', true)
                ->when($q !== '', fn ($query) => $query->where('nama_layanan', 'like', "%{$q}%"))
                ->orderBy('nama_layanan')
                ->limit(10)
                ->get(['id', 'nama_layanan', 'kategori'])
        );
    }

    /**
     * Pencarian karyawan untuk combobox di form appointment.
     */
    public function searchKaryawan(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        return response()->json(
            Karyawan::when($q !== '', fn ($query) => $query->where('nama', 'like', "%{$q}%"))
                ->orderBy('nama')
                ->limit(10)
                ->get(['id', 'nama'])
        );
    }

    private function payload(Appointment $a): array
    {
        $warna = 'gray';
        if ($a->pelanggan) {
            $warna = AppointmentColor::forId((int) $a->pelanggan->id);
        }

        $namaLayanan = $a->layanans->pluck('nama_layanan');

        return [
            'id' => $a->id,
            'id_pelanggan' => (int) $a->id_pelanggan,
            'id_karyawan' => (int) $a->id_karyawan,
            'pelanggan' => $a->pelanggan?->nama,
            'no_wa' => $a->pelanggan?->no_wa,
            'karyawan' => $a->karyawan?->nama,
            'layanans' => $namaLayanan->values()->toArray(),
            'layanan' => $namaLayanan->implode(', '),
            'jumlah_layanan' => $a->layanans->count(),
            'tanggal' => $a->tanggal?->format('Y-m-d'),
            'jam_mulai' => $a->jam_mulai?->format('H:i'),
            'jam_selesai' => $a->jamSelesai()?->format('H:i'),
            'durasi_menit' => (int) $a->durasi_menit,
            'preferensi' => $a->preferensi,
            'status' => $a->status,
            'warna' => $warna,
        ];
    }
}
