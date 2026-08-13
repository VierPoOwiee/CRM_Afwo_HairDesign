@php
    $jenisRambut = ['Lurus', 'Ikal', 'Bergelombang', 'Keriting'];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $pelanggan->nama ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>

        <div>
            <label for="no_wa" class="block text-sm font-medium text-gray-700">No. WhatsApp <span class="text-red-500">*</span></label>
            <input type="text" name="no_wa" id="no_wa" value="{{ old('no_wa', $pelanggan->no_wa ?? '') }}" required
                   placeholder="+62 812 3456 7890"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>

        <div>
            <label for="username_instagram" class="block text-sm font-medium text-gray-700">Username Instagram</label>
            <input type="text" name="username_instagram" id="username_instagram" value="{{ old('username_instagram', $pelanggan->username_instagram ?? '') }}"
                   placeholder="@username"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>

        <div>
            <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
            <select name="jenis_kelamin" id="jenis_kelamin"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                <option value="" {{ old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === '' ? 'selected' : '' }}>-- Pilih --</option>
                <option value="L" {{ old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>

        <div>
            <label for="jenis_rambut" class="block text-sm font-medium text-gray-700">Jenis Rambut</label>
            <select name="jenis_rambut" id="jenis_rambut"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                <option value="" {{ old('jenis_rambut', $pelanggan->jenis_rambut ?? '') === '' ? 'selected' : '' }}>-- Pilih --</option>
                @foreach ($jenisRambut as $r)
                    <option value="{{ $r }}" {{ old('jenis_rambut', $pelanggan->jenis_rambut ?? '') === $r ? 'selected' : '' }}>
                        {{ $r }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
            <input type="text" name="alamat" id="alamat" value="{{ old('alamat', $pelanggan->alamat ?? '') }}"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>
    </div>

    <div>
        <label for="catatan_khusus" class="block text-sm font-medium text-gray-700">Catatan Khusus</label>
        <textarea name="catatan_khusus" id="catatan_khusus" rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">{{ old('catatan_khusus', $pelanggan->catatan_khusus ?? '') }}</textarea>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-md bg-violet-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('pelanggan.index') }}"
           class="rounded-md bg-white px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>
