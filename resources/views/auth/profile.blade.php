@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola informasi akun dan kata sandi Anda.</p>
        </div>

        {{-- User Info Card --}}
        <div class="rounded-lg border border-gray-200 bg-card p-6 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-accent/15 text-lg font-bold text-accent-text">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    <span class="mt-1 inline-flex items-center rounded-full bg-accent-light px-2 py-0.5 text-[11px] font-medium text-accent-text">
                        {{ ucfirst(Auth::user()->role) }}
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.info.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3">
                    <p class="text-xs font-medium text-amber-700">Untuk mengubah nama atau email, masukkan password Anda saat ini.</p>
                </div>

                <div>
                    <label for="current_password_info" class="block text-sm font-medium text-gray-700">Password Saat Ini <span class="text-red-500">*</span></label>
                    <input type="password" id="current_password_info" name="current_password" required autocomplete="current-password"
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none placeholder:text-text-muted"
                           placeholder="Masukkan password untuk konfirmasi">
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Password Card --}}
        <div class="rounded-lg border border-gray-200 bg-card p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Ubah Password</h2>
            <p class="text-xs text-gray-500 mb-5">Pastikan password baru minimal 8 karakter.</p>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password_pw" class="block text-sm font-medium text-gray-700">Password Saat Ini <span class="text-red-500">*</span></label>
                    <input type="password" id="current_password_pw" name="current_password" required autocomplete="current-password"
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none placeholder:text-text-muted"
                           placeholder="Masukkan password saat ini">
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none placeholder:text-text-muted"
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none placeholder:text-text-muted"
                           placeholder="Ulangi password baru">
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                        Perbarui Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
