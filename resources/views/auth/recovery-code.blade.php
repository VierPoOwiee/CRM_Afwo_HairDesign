@extends('layouts.guest')

@section('page_title', 'Kode Pemulihan — AFWO Hair Design')
@section('subtitle', 'Reset dengan kode pemulihan')

@section('content')
    <form method="POST" action="{{ route('password.recovery.submit') }}" class="login-form-stack">
        @csrf

        <p class="login-helper-text">
            Masukkan username dan salah satu kode pemulihan yang pernah Anda simpan. Setiap kode hanya bisa dipakai sekali.
        </p>

        <div class="login-form-group">
            <label for="recovery-username" class="login-form-label">Username</label>
            <div class="login-input-wrapper">
                <input
                    type="text"
                    id="recovery-username"
                    name="username"
                    value="{{ old('username') }}"
                    class="login-input-control"
                    placeholder="contoh: kasir01"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('username')
                <p class="login-field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="login-form-group">
            <label for="recovery-code" class="login-form-label">Kode Pemulihan</label>
            <div class="login-input-wrapper">
                <input
                    type="text"
                    id="recovery-code"
                    name="code"
                    value="{{ old('code') }}"
                    class="login-input-control login-code-input"
                    placeholder="XXXX-XXXX"
                    required
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                >
            </div>
            @error('code')
                <p class="login-field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="login-form-group">
            <label for="recovery-password" class="login-form-label">Password Baru</label>
            <div class="login-input-wrapper">
                <input
                    type="password"
                    id="recovery-password"
                    name="password"
                    class="login-input-control with-toggle"
                    placeholder="Minimal 8 karakter"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
                <button type="button" class="btn-toggle-password js-toggle-password" data-target="recovery-password" aria-label="Tampilkan atau sembunyikan password baru">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="login-field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="login-form-group">
            <label for="recovery-password-confirmation" class="login-form-label">Konfirmasi Password Baru</label>
            <div class="login-input-wrapper">
                <input
                    type="password"
                    id="recovery-password-confirmation"
                    name="password_confirmation"
                    class="login-input-control with-toggle"
                    placeholder="Ulangi password baru"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
                <button type="button" class="btn-toggle-password js-toggle-password" data-target="recovery-password-confirmation" aria-label="Tampilkan atau sembunyikan konfirmasi password">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login-submit">Ganti Password</button>
    </form>

    <div class="login-auth-footer">
        <a href="{{ route('password.request') }}">Kembali ke lupa password</a>
        &nbsp;·&nbsp;
        <a href="{{ route('login') }}">Masuk</a>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/password-toggle.js') }}"></script>
@endpush
