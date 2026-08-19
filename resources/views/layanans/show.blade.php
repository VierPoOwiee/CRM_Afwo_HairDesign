@extends('layouts.app')

@section('title', $layanan->nama_layanan)

@section('content')
    @php
        $showTarif = $layanan->hargaLayanan->contains(fn ($h) => $h->tarif_kelebihan_per_10gr !== null);
    @endphp

    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('layanan.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $layanan->nama_layanan }}</h1>
            <p class="mt-1 text-sm text-gray-500">Detail harga &amp; komisi tiap varian.</p>
        </div>
        <div class="ml-auto flex items-center gap-4 text-sm">
            <a href="{{ route('layanan.edit', $layanan) }}" class="font-medium text-violet-600 hover:text-violet-800">
                Edit
            </a>
            <form action="{{ route('layanan.destroy', $layanan) }}" method="POST"
                  onsubmit="return confirm('Hapus layanan &quot;{{ addslashes($layanan->nama_layanan) }}&quot; beserta semua varian harganya?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="font-medium text-red-600 hover:text-red-800">
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Kategori</dt>
                <dd class="text-right text-gray-900">{{ $layanan->kategori }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500">Status</dt>
                <dd class="text-right text-gray-900">
                    @if ($layanan->aktif)
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Aktif</span>
                    @else
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Nonaktif</span>
                    @endif
                    @if ($layanan->termasuk_potong)
                        <span class="ml-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-700">Termasuk Potong</span>
                    @endif
                </dd>
            </div>
            @if ($layanan->produk->isNotEmpty())
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Produk Digunakan</dt>
                    <dd class="text-right text-gray-900">
                        <div class="flex flex-wrap gap-1 justify-end">
                            @foreach ($layanan->produk as $p)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                    {{ $p->merek }} — {{ $p->nama_produk }}
                                </span>
                            @endforeach
                        </div>
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white shadow-sm sm:overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="hidden sm:table-header-group">
                <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Varian</th>
                    <th class="px-4 py-3">Harga</th>
                    @if ($showTarif)
                        <th class="px-4 py-3">Kelebihan /10gr</th>
                    @endif
                    <th class="px-4 py-3">Komisi</th>
                </tr>
            </thead>
            <tbody class="space-y-3 sm:space-y-0 sm:divide-y sm:divide-gray-200">
                @forelse ($layanan->hargaLayanan as $h)
                    <tr class="block space-y-2 rounded-md border border-gray-200 p-4 sm:table-row sm:space-y-0 sm:rounded-none sm:border-0 sm:p-0">
                        <td class="flex items-center justify-between gap-4 sm:table-cell sm:px-4 sm:py-3">
                            <span class="text-gray-500 sm:hidden">Varian</span>
                            <span class="font-medium text-gray-900">{{ $h->varian }}</span>
                        </td>
                        <td class="flex items-center justify-between gap-4 sm:table-cell sm:px-4 sm:py-3">
                            <span class="text-gray-500 sm:hidden">Harga</span>
                            <span class="text-right text-gray-900 sm:text-left">{{ $h->labelHargaDasar() }}</span>
                        </td>
                        @if ($showTarif)
                            <td class="flex items-center justify-between gap-4 sm:table-cell sm:px-4 sm:py-3">
                                <span class="text-gray-500 sm:hidden">Kelebihan /10gr</span>
                                <span class="text-right text-gray-900 sm:text-left">
                                    @if ($h->tarif_kelebihan_per_10gr)
                                        Rp{{ number_format((float) $h->tarif_kelebihan_per_10gr, 0, ',', '.') }}/10gr
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </span>
                            </td>
                        @endif
                        <td class="flex items-center justify-between gap-4 sm:table-cell sm:px-4 sm:py-3">
                            <span class="text-gray-500 sm:hidden">Komisi</span>
                            <span class="text-right text-gray-900 sm:text-left">{{ $h->labelKomisi() ?? '-' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="block sm:table-row">
                        <td colspan="{{ $showTarif ? 4 : 3 }}" class="px-4 py-8 text-center text-sm text-gray-400">
                            Belum ada varian harga.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
