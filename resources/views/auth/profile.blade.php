@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
@endpush

@section('content')

    <div class="profil-page-stack">
        <div class="profil-header-section">
            <h1 class="profil-page-title">Profil Saya</h1>
            <p class="profil-page-subtitle">Kelola informasi akun dan kata sandi Anda.</p>
        </div>

        {{-- User Info Card --}}
        <section class="profil-card">
            <div class="profil-user-header">
                <div class="profil-avatar-badge">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="profil-user-details">
                    <div class="profil-user-fullname">{{ Auth::user()->name }}</div>
                    <div class="profil-user-email-text">{{ Auth::user()->email }}</div>
                    <span class="profil-role-pill">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </div>

            @if ($errors->any())
                <div class="profil-form-alert is-error">Periksa kembali isian form di bawah.</div>
            @endif

            <form method="POST" action="{{ route('profile.info.update') }}">
                @csrf
                @method('PUT')

                <div class="profil-form-grid">
                    <div class="profil-form-group">
                        <label for="name" class="profil-label">Nama <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required class="profil-input">
                        </div>
                        @error('name')
                            <p style="font-size: .78rem; color: #DC2626; margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="email" class="profil-label">Email <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required class="profil-input">
                        </div>
                        @error('email')
                            <p style="font-size: .78rem; color: #DC2626; margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-notice-banner">
                        Untuk mengubah nama atau email, masukkan password Anda saat ini.
                    </div>

                    <div class="profil-form-group">
                        <label for="current_password_info" class="profil-label">Password Saat Ini <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="current_password_info" name="current_password" required autocomplete="current-password"
                                   class="profil-input input-tinted" placeholder="Masukkan password untuk konfirmasi">
                        </div>
                        @error('current_password')
                            <p style="font-size: .78rem; color: #DC2626; margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="profil-card-actions">
                    <button type="submit" class="btn-profil-submit">Simpan Perubahan</button>
                </div>
            </form>
        </section>

        {{-- Password Card --}}
        <section class="profil-card">
            <h2 class="profil-card-title">Ubah Password</h2>
            <p class="profil-card-subtitle">Pastikan password baru minimal 8 karakter.</p>

            @if (!empty($errors->getBag('password')->all()))
                <div class="profil-form-alert is-error">Periksa kembali password yang Anda masukkan.</div>
            @endif

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <div class="profil-form-grid">
                    <div class="profil-form-group">
                        <label for="current_password_pw" class="profil-label">Password Saat Ini <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="current_password_pw" name="current_password" required autocomplete="current-password"
                                   class="profil-input" placeholder="Masukkan password saat ini">
                        </div>
                        @error('password.current_password')
                            <p style="font-size: .78rem; color: #DC2626; margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="password" class="profil-label">Password Baru <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                   class="profil-input" placeholder="Minimal 8 karakter">
                        </div>
                        @error('password.password')
                            <p style="font-size: .78rem; color: #DC2626; margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="password_confirmation" class="profil-label">Konfirmasi Password Baru <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                   class="profil-input" placeholder="Ulangi password baru">
                        </div>
                    </div>
                </div>

                <div class="profil-card-actions">
                    <button type="submit" class="btn-profil-submit">Perbarui Password</button>
                </div>
            </form>
        </section>
    </div>

@endsection