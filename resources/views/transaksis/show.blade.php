@extends('layouts.app')

@section('title', 'Detail Transaksi '.$transaksi->no_struk)

@section('content')
    <div class="flex flex-wrap items-center gap-3 sm:gap-4">
        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Detail Transaksi</h1>
            <p class="mt-1 text-sm text-gray-500 truncate">{{ $transaksi->no_struk }}</p>
        </div>
        <div class="flex items-center gap-2 text-sm sm:gap-4">
            @if ($transaksi->status === 'selesai')
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">Selesai</span>
            @else
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-700">Batal</span>
            @endif
            <button onclick="window.print()" class="font-medium text-gray-600 hover:text-gray-800">
                Cetak
            </button>
            <a href="{{ route('transaksi.index') }}" class="font-medium text-gray-600 hover:text-gray-800">Batal</a>
            <form action="{{ route('transaksi.destroy', $transaksi) }}" method="POST"
                  onsubmit="return confirm('Hapus transaksi &quot;{{ addslashes($transaksi->no_struk) }}&quot;? Stok produk akan dikembalikan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="font-medium text-red-600 hover:text-red-800">Hapus</button>
            </form>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-gray-200 bg-card p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">Informasi Transaksi</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Pelanggan</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $transaksi->pelanggan->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Waktu Kunjungan</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $transaksi->waktu_kunjungan->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Jenis Pengerjaan</dt>
                        <dd class="mt-0.5 text-gray-900">{{ ucfirst($transaksi->jenis_pengerjaan) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Metode Pembayaran</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $transaksi->labelMetode() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Item Transaksi</h2>
                </div>
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Layanan</th>
                            <th class="px-4 py-3">Produk Digunakan</th>
                            <th class="px-4 py-3">Staf</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($transaksi->details as $i => $d)
                            @php
                                $totalProduk = (float) $d->produkPenggunaan->sum('subtotal');
                                $hargaDasar = (float) $d->harga_saat_transaksi - $totalProduk;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    @if ($d->tipe_item === 'layanan')
                                        <div class="font-medium text-gray-900">
                                            {{ $d->layanan->nama_layanan ?? '-' }}
                                            @if ($d->varian_dipilih)
                                                <span class="text-xs text-gray-400">({{ $d->varian_dipilih }})</span>
                                            @endif
                                        </div>
                                        @if ($d->gram_pemakaian_tambahan > 0)
                                            <div class="text-xs text-gray-500">Tambahan: {{ $d->gram_pemakaian_tambahan }}gr</div>
                                        @endif
                                        @if ($d->ketebalan_rambut)
                                            <div class="text-xs text-gray-400">Rambut: {{ $d->ketebalan_rambut }}</div>
                                        @endif
                                    @else
                                        <div class="font-medium text-gray-900">
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">Produk</span>
                                            {{ $d->produk->nama_produk ?? '-' }}
                                        </div>
                                    @endif
                                    @if ($d->catatan)
                                        <div class="mt-1 text-xs text-gray-400 italic">{{ $d->catatan }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($d->produkPenggunaan->isEmpty())
                                        <span class="text-xs text-gray-400">-</span>
                                    @else
                                        <div class="space-y-1">
                                            @foreach ($d->produkPenggunaan as $pu)
                                                <div class="text-xs">
                                                    <span class="font-medium text-gray-700">{{ $pu->produk->merek ?? '-' }}</span>
                                                    <span class="text-gray-500">{{ $pu->produk->nama_produk ?? '' }}</span>
                                                    <span class="text-gray-400">({{ $pu->pemakaian_ml }}ml)</span>
                                                    <span class="text-gray-400">Rp{{ number_format((float) $pu->subtotal, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $d->staf->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($d->tipe_item === 'layanan' && $totalProduk > 0)
                                        <div class="text-xs text-gray-500">Dasar: Rp{{ number_format($hargaDasar, 0, ',', '.') }}</div>
                                        <div class="text-xs text-emerald-600">+Produk: Rp{{ number_format($totalProduk, 0, ',', '.') }}</div>
                                    @endif
                                    <div class="text-gray-900">Rp{{ number_format((float) $d->harga_saat_transaksi, 0, ',', '.') }}</div>
                                    @if ((float) $d->diskon > 0)
                                        <div class="text-xs text-red-500">-Diskon: Rp{{ number_format((float) $d->diskon, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ $d->qty }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format((float) $d->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total Bayar</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">Rp{{ number_format((float) $transaksi->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
                </div>
            </div>
            </div>

            @php
                $totalHargaDasar = 0;
                $totalBiayaProduk = 0;
                $totalDiskon = 0;
                foreach ($transaksi->details as $d) {
                    $totalProduk = (float) $d->produkPenggunaan->sum('subtotal');
                    $totalBiayaProduk += $totalProduk;
                    $totalHargaDasar += (float) $d->harga_saat_transaksi - $totalProduk;
                    $totalDiskon += (float) $d->diskon;
                }
            @endphp
            @if ($totalBiayaProduk > 0 || $totalDiskon > 0)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Rincian Harga</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Harga Dasar Layanan</span>
                            <span class="font-medium text-gray-900">Rp{{ number_format($totalHargaDasar, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Biaya Produk</span>
                            <span class="font-medium text-emerald-700">+ Rp{{ number_format($totalBiayaProduk, 0, ',', '.') }}</span>
                        </div>
                        @if ($totalDiskon > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Diskon</span>
                                <span class="font-medium text-red-500">- Rp{{ number_format($totalDiskon, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="border-t border-gray-100 pt-2 flex justify-between font-semibold">
                            <span class="text-gray-900">Total Bayar</span>
                            <span class="text-gray-900">Rp{{ number_format((float) $transaksi->total_bayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="rounded-lg border border-gray-200 bg-card p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">Komisi per Staf</h2>

                @if ($transaksi->komisiTransaksi->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data komisi.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($transaksi->komisiTransaksi as $kt)
                            @php
                                $staf = $kt->staf;
                                $isPersen = $staf && $staf->skema_komisi === 'persen_omset_harian';
                            @endphp
                            <div class="rounded-lg border border-gray-100 p-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $staf->nama ?? 'Staf #' . $kt->id_staf }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if ($isPersen)
                                                Skema: {{ $staf->persen_komisi_harian }}% dari omset harian
                                            @else
                                                Skema: per layanan
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <form action="{{ route('transaksi.komisi-staf.update', $transaksi) }}" method="POST" class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-end">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="komisi_staf_id" value="{{ $kt->id }}">
                                    <div class="flex-1">
                                        <label class="block text-[11px] font-medium text-gray-500">Jumlah Komisi (Rp)</label>
                                        <input type="text" inputmode="numeric" name="jumlah_komisi" value="{{ $kt->jumlah_komisi }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[11px] font-medium text-gray-500">Keterangan</label>
                                        <input type="text" name="keterangan" value="{{ $kt->keterangan }}" placeholder="Opsional"
                                               class="mt-1 block w-full rounded-md border-gray-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <button type="submit" class="mb-0.5 rounded-md bg-dark px-3 py-1.5 text-xs font-medium text-white hover:bg-dark-hover">Simpan</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-6 shadow-sm">
                <h2 class="mb-4 text-base font-semibold text-gray-900">Detail Komisi per Item</h2>
                @php
                    $detailsWithKomisi = $transaksi->details->filter(fn ($d) => $d->komisi_nominal !== null);
                @endphp

                @if ($detailsWithKomisi->isEmpty())
                    <p class="text-sm text-gray-400">Belum ada data komisi per item.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($detailsWithKomisi->groupBy('id_staf') as $stafId => $details)
                            @php
                                $staf = $details->first()->staf;
                            @endphp
                            <div>
                                <p class="text-xs font-semibold text-gray-700 mb-1">{{ $staf->nama ?? 'Staf #' . $stafId }}</p>
                                <div class="space-y-1">
                                    @foreach ($details as $d)
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-600">
                                                {{ $d->tipe_item === 'layanan' ? ($d->layanan->nama_layanan ?? 'Layanan') : 'Produk' }}
                                                @if ($d->varian_dipilih)
                                                    <span class="text-gray-400">({{ $d->varian_dipilih }})</span>
                                                @endif
                                            </span>
                                            <div class="flex items-center gap-1">
                                                <form action="{{ route('transaksi.komisi.update', $transaksi) }}" method="POST" class="inline-flex items-center gap-1">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="detail_id" value="{{ $d->id }}">
                                                    <input type="text" inputmode="numeric" name="komisi_nominal" value="{{ $d->komisi_nominal }}"
                                                           class="w-24 rounded-md border-gray-300 px-2 py-1 text-xs text-right">
                                                    <button type="submit" class="text-xs text-accent-text hover:text-accent">OK</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav, header nav, footer, .no-print, form[action*="destroy"],
            form[action*="cancel"], form[action*="komisi"],
            form[action*="komisi-staf"], button[onclick="window.print()"] {
                display: none !important;
            }
            body { background: white !important; }
            .print-only { display: block !important; }
        }
    </style>
@endsection
