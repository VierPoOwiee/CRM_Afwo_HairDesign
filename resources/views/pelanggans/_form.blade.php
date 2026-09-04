@php
    $jenisRambut = ['Lurus', 'Ikal', 'Bergelombang', 'Keriting'];
    $kondisiRambut = ['Normal', 'Kering', 'Rusak (Pecah-pecah)', 'Diwarnai / Chemically Treated', 'Mengembang / Frizz / Sulit Diatur'];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $pelanggan->nama ?? '') }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <x-phone-input
            name="no_wa"
            label="No. WhatsApp"
            :required="true"
            :value="$pelanggan->no_wa ?? ''"
            placeholder="812xxxxxxx" />

        <div>
            <label for="username_instagram" class="block text-sm font-medium text-gray-700">Username Instagram</label>
            <input type="text" name="username_instagram" id="username_instagram" value="{{ old('username_instagram', $pelanggan->username_instagram ?? '') }}"
                   placeholder="@username"
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary placeholder:text-text-muted px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <div>
            <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="jenis_kelamin"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <option value="" {{ old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === '' ? 'selected' : '' }}>-- Pilih --</option>
                <option value="L" {{ old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div>
            <label for="jenis_rambut" class="block text-sm font-medium text-gray-700">Jenis Rambut</label>
            <select name="jenis_rambut" id="jenis_rambut"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <option value="" {{ old('jenis_rambut', $pelanggan->jenis_rambut ?? '') === '' ? 'selected' : '' }}>-- Pilih --</option>
                @foreach ($jenisRambut as $r)
                    <option value="{{ $r }}" {{ old('jenis_rambut', $pelanggan->jenis_rambut ?? '') === $r ? 'selected' : '' }}>
                        {{ $r }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="kondisi_rambut" class="block text-sm font-medium text-gray-700">Kondisi Rambut</label>
            <select name="kondisi_rambut" id="kondisi_rambut"
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <option value="" {{ old('kondisi_rambut', $pelanggan->kondisi_rambut ?? '') === '' ? 'selected' : '' }}>-- Pilih --</option>
                @foreach ($kondisiRambut as $r)
                    <option value="{{ $r }}" {{ old('kondisi_rambut', $pelanggan->kondisi_rambut ?? '') === $r ? 'selected' : '' }}>
                        {{ $r }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
            <input type="text" name="alamat" id="alamat" value="{{ old('alamat', $pelanggan->alamat ?? '') }}"
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>
    </div>

    <div>
        <label for="catatan_khusus" class="block text-sm font-medium text-gray-700">Catatan Khusus</label>
        <textarea name="catatan_khusus" id="catatan_khusus" rows="3"
                  class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">{{ old('catatan_khusus', $pelanggan->catatan_khusus ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('pelanggan.index') }}"
           class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>
