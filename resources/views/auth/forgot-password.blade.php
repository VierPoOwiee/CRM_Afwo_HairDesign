@extends('layouts.guest')

@section('page_title', 'Lupa Password — AFWO Hair Design')
@section('subtitle', 'Pemulihan akun')

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="login-form-stack">
        @csrf

        <p class="login-helper-text">
            Masukkan username atau email pemulihan Anda. Kami akan mengirim tautan untuk mengatur ulang password.
            Tautan berlaku 30 menit.
        </p>

        <div class="login-form-group">
            <label for="login-identifier" class="login-form-label">Username atau Email</label>
            <div class="login-input-wrapper">
                <input
                    type="text"
                    id="login-identifier"
                    name="login"
                    value="{{ old('login') }}"
                    class="login-input-control"
                    placeholder="contoh: owner atau nama@email.com"
                    required
                    autofocus
                    autocomplete="username"
                    aria-describedby="login-identifier-help"
                >
            </div>
            @error('login')
                <p class="login-field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-login-submit">Kirim Tautan Atur Ulang</button>
    </form>

    <div class="login-auth-footer">
        <a href="{{ route('login') }}">Kembali ke halaman login</a>
    </div>

    <div class="login-hint" id="login-identifier-help">
        <strong>Tidak punya email pemulihan?</strong>
        Hubungi owner untuk reset password.
    </div>
@endsection
