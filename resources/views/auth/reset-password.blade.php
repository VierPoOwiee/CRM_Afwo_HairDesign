@extends('layouts.guest')

@section('page_title', 'Atur Ulang Password — AFWO Hair Design')
@section('subtitle', 'Buat password baru')

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="login-form-stack">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @if ($email)
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="login-form-group">
                <label for="reset-email" class="login-form-label">Email</label>
                <div class="login-input-wrapper">
                    <input
                        type="email"
                        id="reset-email"
                        class="login-input-control login-readonly"
                        value="{{ $email }}"
                        readonly
                        aria-readonly="true"
                    >
                </div>
                @error('email')
                    <p class="login-field-error">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div class="login-form-group">
                <label for="reset-email" class="login-form-label">Email</label>
                <div class="login-input-wrapper">
                    <input
                        type="email"
                        id="reset-email"
                        name="email"
                        value="{{ old('email') }}"
                        class="login-input-control"
                        placeholder="nama@email.com"
                        required
                        autofocus
                        autocomplete="email"
                    >
                </div>
                @error('email')
                    <p class="login-field-error">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div class="login-form-group">
            <label for="reset-password" class="login-form-label">Password Baru</label>
            <div class="login-input-wrapper">
                <input
                    type="password"
                    id="reset-password"
                    name="password"
                    class="login-input-control with-toggle"
                    placeholder="Minimal 8 karakter"
                    required
                    minlength="8"
                    @if ($email) autofocus @endif
                    autocomplete="new-password"
                >
                <button type="button" class="btn-toggle-password js-toggle-password" data-target="reset-password" aria-label="Tampilkan atau sembunyikan password baru">
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
            <label for="reset-password-confirmation" class="login-form-label">Konfirmasi Password Baru</label>
            <div class="login-input-wrapper">
                <input
                    type="password"
                    id="reset-password-confirmation"
                    name="password_confirmation"
                    class="login-input-control with-toggle"
                    placeholder="Ulangi password baru"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
                <button type="button" class="btn-toggle-password js-toggle-password" data-target="reset-password-confirmation" aria-label="Tampilkan atau sembunyikan konfirmasi password">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-login-submit">Simpan Password Baru</button>
    </form>

    <div class="login-auth-footer">
        <a href="{{ route('login') }}">Kembali ke halaman login</a>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/password-toggle.js') }}"></script>
@endpush
