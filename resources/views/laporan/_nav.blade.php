{{-- Tab navigasi antar halaman Laporan --}}
@php
    $laporanTabs = [
        ['route' => 'laporan.penjualan', 'label' => 'Laporan Penjualan'],
        ['route' => 'laporan.pelanggan-aktif', 'label' => 'Pelanggan Aktif'],
        ['route' => 'laporan.rekap-komisi', 'label' => 'Rekap Komisi'],
        ['route' => 'laporan.pendapatan-karyawan', 'label' => 'Pendapatan Bulanan'],
    ];
@endphp

<div class="no-print mb-6 flex flex-wrap items-center gap-1 border-b border-gray-200 pb-3">
    @foreach ($laporanTabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="whitespace-nowrap rounded-md px-3 py-2 text-sm {{ request()->routeIs($tab['route']) ? 'bg-dark font-semibold text-white shadow-sm' : 'font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
