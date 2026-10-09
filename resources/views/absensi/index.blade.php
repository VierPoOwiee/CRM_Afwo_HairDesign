@extends('layouts.app')

@section('title', 'Absensi Karyawan')

@section('nav-active', 'absensi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/absensi.css') }}">
@endpush

@section('content')
        <div class="absensi-page-stack">

            <div class="absensi-header-section">
                <h1 class="absensi-page-title">Absensi Karyawan</h1>
                <p class="absensi-page-subtitle">Catat kehadiran harian dan rekap uang makan karyawan per bulan.</p>
            </div>

            @if (session('success'))
                <div class="absensi-toast is-success" style="display: block;">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="absensi-toast is-error" style="display: block;">
                    @foreach ($errors->all() as $err)
                        {{ $err }}
                    @endforeach
                </div>
            @endif

            <!-- Card 1: Catat Kehadiran Harian -->
            <form action="{{ route('absensi.store') }}" method="POST" data-field="card-catat-kehadiran">
                @csrf
                <section class="absensi-card">
                    <div class="absensi-card-header">
                        <div class="absensi-card-title-group">
                            <h2 class="absensi-card-title">Catat Kehadiran</h2>
                            <p class="absensi-card-subtitle">Centang karyawan yang hadir, lalu klik Simpan.</p>
                        </div>
                        <div class="absensi-date-picker-wrap">
                            <span class="absensi-picker-label">Tanggal</span>
                            <input type="date" name="tanggal" id="input-attendance-date" class="absensi-date-input"
                                   value="{{ $tanggalInput }}" required
                                   onchange="window.location = '{{ route('absensi.index') }}?tanggal=' + encodeURIComponent(this.value);">
                        </div>
                    </div>

                    @if ($karyawans->isNotEmpty())
                        <div class="absensi-checkall-bar">
                            <label class="absensi-checkall-label">
                                <input type="checkbox" id="check-all-attendance" class="absensi-checkbox">
                                <span>Centang semua</span>
                            </label>
                            <span class="absensi-checkall-count" id="attendance-selected-count"></span>
                        </div>
                    @endif

                    <div class="absensi-table-container">
                        <table class="absensi-table" id="table-catat-kehadiran">
                            <thead>
                                <tr>
                                    <th>KARYAWAN</th>
                                    <th>SKEMA</th>
                                    <th style="text-align: center; width: 100px;">HADIR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($karyawans as $k)
                                    <tr>
                                        <td><span class="worker-name-bold">{{ $k->nama }}</span></td>
                                        <td>
                                            @if ($k->skema_komisi === 'persen_omset_harian')
                                                <span class="badge-skema-omset">% Omset Harian</span>
                                            @else
                                                <span class="badge-skema-layanan">Per Layanan</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="absensi-checkbox-wrap">
                                                <input type="checkbox" name="hadir[{{ $k->id }}]" value="1"
                                                       class="absensi-checkbox js-attendance-check"
                                                       @checked($kehadiranHariIni[$k->id] ?? false)>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 28px 16px; color: var(--text-muted);">
                                            Belum ada karyawan terdaftar. Tambahkan karyawan dahulu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($karyawans->isNotEmpty())
                        <div class="absensi-card-footer">
                            <span class="absensi-footer-note">Uang makan Rp25.000/hari hanya untuk skema Per Layanan.</span>
                            <button type="submit" class="btn btn-primary" id="btn-save-attendance">Simpan Kehadiran</button>
                        </div>
                    @endif
                </section>
            </form>

            <!-- Card 2: Rekap Kehadiran & Uang Makan -->
            <form action="{{ route('absensi.index') }}" method="GET">
                <section class="absensi-card">
                    <div class="absensi-card-header">
                        <div class="absensi-card-title-group">
                            <h2 class="absensi-card-title">Rekap Kehadiran &amp; Uang Makan</h2>
                            <p class="absensi-card-subtitle">Jumlah hadir per karyawan, dengan uang makan Rp25.000 per kehadiran.</p>
                        </div>
                        <div class="absensi-rekap-filter-wrap">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <span class="absensi-picker-label">Bulan</span>
                                <select name="bulan" id="select-rekap-month" class="absensi-select-month">
                                    @foreach ($pilihanBulan as $p)
                                        <option value="{{ $p['value'] }}" {{ $p['value'] === $bulanInput ? 'selected' : '' }}>{{ $p['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-secondary btn-sm" id="btn-filter-rekap">Tampilkan</button>
                        </div>
                    </div>

                    <div class="absensi-table-container">
                        <table class="absensi-table" id="table-rekap-kehadiran">
                            <thead>
                                <tr>
                                    <th>KARYAWAN</th>
                                    <th>SKEMA</th>
                                    <th style="text-align: center; width: 120px;">TOTAL HADIR</th>
                                    <th style="text-align: right; width: 180px;">UANG MAKAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ringkasan as $row)
                                    @php $k = $row['karyawan']; @endphp
                                    <tr>
                                        <td><span class="worker-name-bold">{{ $k->nama }}</span></td>
                                        <td>
                                            @if ($k->skema_komisi === 'persen_omset_harian')
                                                <span class="badge-skema-omset">% Omset Harian</span>
                                            @else
                                                <span class="badge-skema-layanan">Per Layanan</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center; font-weight: 700;">{{ $row['jumlah_hadir'] }}</td>
                                        <td style="text-align: right; font-weight: 700; color: var(--text-primary);">
                                            @if ($row['uang_makan'] > 0)
                                                Rp{{ number_format($row['uang_makan'], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 28px 16px; color: var(--text-muted);">Belum ada data karyawan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($ringkasan->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <td>TOTAL</td>
                                        <td></td>
                                        <td style="text-align: center;" id="rekap-total-hadir">{{ $totalHadir }}</td>
                                        <td style="text-align: right;" id="rekap-total-uang-makan">Rp{{ number_format($totalUangMakan, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </section>
            </form>

        </div>
@endsection

@push('scripts')
<script>
(function () {
    var master = document.getElementById('check-all-attendance');
    var counter = document.getElementById('attendance-selected-count');
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.js-attendance-check'));
    if (!master || boxes.length === 0) return;

    function refresh() {
        var checked = boxes.filter(function (b) { return b.checked; }).length;
        var total = boxes.length;
        master.checked = checked === total;
        master.indeterminate = checked > 0 && checked < total;
        if (counter) counter.textContent = checked + ' dari ' + total + ' terpilih';
    }

    master.addEventListener('change', function () {
        boxes.forEach(function (b) { b.checked = master.checked; });
        refresh();
    });
    boxes.forEach(function (b) { b.addEventListener('change', refresh); });
    refresh();
})();
</script>
@endpush