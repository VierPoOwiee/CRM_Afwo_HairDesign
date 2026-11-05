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

        @if (Auth::user()->wajib_ganti_password)
            <div class="profil-force-banner" id="banner-wajib-ganti" role="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <p>Password Anda masih sementara. Buat password baru sekarang untuk melanjutkan.</p>
            </div>
        @endif

        {{-- User Info Card --}}
        <section class="profil-card">
            <div class="profil-user-header">
                <div class="profil-avatar-badge">{{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="profil-user-details">
                    <div class="profil-user-fullname">{{ Auth::user()->name }}</div>
                    <div class="profil-user-email-text">{{ '@'.Auth::user()->username }}</div>
                    <span class="profil-role-pill">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </div>

            @if ($errors->getBag('default')->any())
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
                            <p style="font-size: .78rem; color: var(--status-danger); margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="username" class="profil-label">Username <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="text" id="username" name="username" value="{{ old('username', Auth::user()->username) }}" required autocomplete="username" class="profil-input">
                        </div>
                        @error('username')
                            <p style="font-size: .78rem; color: var(--status-danger); margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-notice-banner">
                        Untuk mengubah nama atau username, masukkan password Anda saat ini.
                    </div>

                    <div class="profil-form-group">
                        <label for="current_password_info" class="profil-label">Password Saat Ini <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="current_password_info" name="current_password" required autocomplete="current-password"
                                   class="profil-input input-tinted" placeholder="Masukkan password untuk konfirmasi">
                        </div>
                        @error('current_password')
                            <p style="font-size: .78rem; color: var(--status-danger); margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="profil-card-actions">
                    <button type="submit" class="btn-profil-submit">Simpan Perubahan</button>
                </div>
            </form>
        </section>

        {{-- Email Pemulihan Card --}}
        <section class="profil-card" id="email-pemulihan">
            <h2 class="profil-card-title">Email Pemulihan</h2>
            <p class="profil-card-subtitle">Email ini dipakai untuk menerima tautan atur ulang password saat Anda lupa password.</p>

            @if ($errors->getBag('email')->any())
                <div class="profil-form-alert is-error">Periksa kembali isian email pemulihan.</div>
            @endif

            @if (Auth::user()->email)
                <div class="profil-email-status is-active">
                    <span class="profil-email-status-label">Email aktif</span>
                    <span class="profil-email-status-value">{{ Auth::user()->email }}</span>
                </div>
            @else
                <div class="profil-email-status is-empty">
                    <span class="profil-email-status-label">Belum ada email pemulihan</span>
                    <span class="profil-email-status-value">Tanpa email, reset password dapat dilakukan oleh owner.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.email.update') }}">
                @csrf
                @method('PUT')

                <div class="profil-form-grid">
                    <div class="profil-form-group">
                        <label for="email_pemulihan" class="profil-label">Email Pemulihan</label>
                        <div class="profil-input-box">
                            <input type="email" id="email_pemulihan" name="email" value="{{ old('email', Auth::user()->email) }}"
                                   class="profil-input" placeholder="nama@email.com" autocomplete="email">
                        </div>
                        @error('email', 'email')
                            <p class="profil-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="current_password_email" class="profil-label">Password Saat Ini <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="current_password_email" name="current_password" required autocomplete="current-password"
                                   class="profil-input input-tinted" placeholder="Masukkan password untuk konfirmasi">
                        </div>
                        @error('current_password', 'email')
                            <p class="profil-field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="profil-card-actions">
                    <button type="submit" class="btn-profil-submit">Simpan Email</button>
                </div>
            </form>
        </section>

        {{-- Password Card --}}
        <section class="profil-card" id="ganti-password">
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
                            <p style="font-size: .78rem; color: var(--status-danger); margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="password" class="profil-label">Password Baru <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                   class="profil-input" placeholder="Minimal 8 karakter">
                        </div>
                        @error('password.password')
                            <p style="font-size: .78rem; color: var(--status-danger); margin: 2px 0 0;">{{ $message }}</p>
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

@push('scripts')
<script>
(function () {
    // Password masih sementara: arahkan perhatian ke form ganti password.
    const bannerWajibGanti = document.getElementById('banner-wajib-ganti');
    if (bannerWajibGanti) {
        const sectionGanti = document.getElementById('ganti-password');
        const inputSaatIni = document.getElementById('current_password_pw');

        window.addEventListener('load', function () {
            if (sectionGanti) {
                sectionGanti.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            if (inputSaatIni) {
                inputSaatIni.focus({ preventScroll: true });
            }
        });
    }
})();
</script>
@endpush