{{-- Tab navigasi antar halaman Laporan --}}
@php
    $laporanTabs = [
        ['route' => 'laporan.penjualan', 'label' => 'Laporan Penjualan'],
        ['route' => 'laporan.pelanggan-aktif', 'label' => 'Pelanggan Aktif'],
        ['route' => 'laporan.rekap-komisi', 'label' => 'Rekap Komisi'],
        ['route' => 'laporan.pendapatan-karyawan', 'label' => 'Pendapatan Bulanan'],
    ];
@endphp

<div class="no-print mb-6 flex flex-wrap items-center gap-1 border-b border-card-warm-border pb-3">
    @foreach ($laporanTabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="whitespace-nowrap rounded-lg px-3 py-2 text-sm {{ request()->routeIs($tab['route']) ? 'bg-accent font-semibold text-[#3A1820]' : 'font-medium text-text-secondary hover:bg-accent-light hover:text-accent-text' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
