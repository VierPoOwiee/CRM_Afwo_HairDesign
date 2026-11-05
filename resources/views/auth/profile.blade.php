@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
@endpush

@section('content')

    <div class="profil-page-stack">
        @if (Auth::user()->wajib_ganti_password)
            <div class="profil-force-banner" role="alert">
                <svg class="profil-force-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <div class="profil-force-banner-text">Password Anda masih sementara. Buat password baru sekarang untuk melanjutkan.</div>
            </div>
        @endif

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
                    <div class="profil-user-email-text">{{ '@'.Auth::user()->username }}</div>
                    <span class="profil-role-pill">{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </div>

            @if ($errors->has('name') || $errors->has('username') || ($errors->has('current_password') && ! $errors->has('password')))
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
            <p class="profil-card-subtitle">Email ini dipakai untuk menerima tautan reset password saat Anda lupa password.</p>

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
                    <span class="profil-email-status-value">Tanpa email, reset mandiri hanya bisa lewat kode pemulihan.</span>
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
            <p class="profil-card-subtitle">Pastikan password baru minimal 8 karakter dan berbeda dari password saat ini.</p>

            @if ($errors->has('password'))
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
                        @error('current_password')
                            <p style="font-size: .78rem; color: var(--status-danger); margin: 2px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="profil-form-group">
                        <label for="password" class="profil-label">Password Baru <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                   class="profil-input" placeholder="Minimal 8 karakter">
                        </div>
                        @error('password')
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

        {{-- Kode Pemulihan Card --}}
        <section class="profil-card" id="kode-pemulihan">
            <h2 class="profil-card-title">Kode Pemulihan</h2>
            <p class="profil-card-subtitle">Kode sekali pakai sebagai cadangan jika Anda tidak bisa mengakses email.</p>

            @if (session('recovery_codes'))
                <div class="profil-recovery-box" role="region" aria-label="Kode pemulihan baru">
                    <div class="profil-recovery-warning">
                        Simpan di tempat aman. Kode ini hanya ditampilkan sekali dan tidak akan bisa dilihat lagi.
                    </div>

                    <ul class="profil-recovery-grid">
                        @foreach (session('recovery_codes') as $kode)
                            <li class="profil-recovery-item">
                                <code class="profil-recovery-code">{{ $kode }}</code>
                                <button type="button" class="btn-profil-copy js-copy-code" data-code="{{ $kode }}"
                                        aria-label="Salin kode pemulihan {{ $kode }}">Salin</button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="profil-recovery-actions">
                        <button type="button" class="btn-profil-secondary" id="btn-download-codes"
                                data-codes="{{ json_encode(session('recovery_codes')) }}">
                            Unduh .txt
                        </button>
                    </div>
                </div>
            @endif

            <div class="profil-recovery-status {{ ($sisaKode ?? 0) > 0 ? 'is-active' : '' }}">
                @if (($sisaKode ?? 0) > 0)
                    {{ $sisaKode }} dari 8 kode pemulihan belum dipakai.
                @else
                    Belum ada kode pemulihan yang tersisa.
                @endif
            </div>

            @if ($errors->getBag('recovery')->any())
                <div class="profil-form-alert is-error">Periksa password konfirmasi Anda.</div>
            @endif

            <form method="POST" action="{{ route('profile.recovery-codes.generate') }}">
                @csrf

                <div class="profil-notice-banner">
                    Membuat kode baru akan menghapus semua kode lama.
                </div>

                <div class="profil-form-grid">
                    <div class="profil-form-group">
                        <label for="current_password_recovery" class="profil-label">Password Saat Ini <span class="req-star">*</span></label>
                        <div class="profil-input-box">
                            <input type="password" id="current_password_recovery" name="current_password" required autocomplete="current-password"
                                   class="profil-input input-tinted" placeholder="Masukkan password untuk konfirmasi">
                        </div>
                        @error('current_password', 'recovery')
                            <p class="profil-field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="profil-card-actions">
                    <button type="submit" class="btn-profil-submit">Buat Kode Pemulihan</button>
                </div>
            </form>
        </section>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    @if (Auth::user()->wajib_ganti_password)
        // Arahkan perhatian ke form ganti password saat akun masih memakai
        // password sementara. Scroll dulu, lalu fokus agar posisi tidak lompat.
        const bagianPassword = document.getElementById('ganti-password');
        const inputPassword = document.getElementById('current_password_pw');

        if (bagianPassword) {
            bagianPassword.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        if (inputPassword) {
            window.setTimeout(function () {
                try {
                    inputPassword.focus({ preventScroll: true });
                } catch (e) {
                    inputPassword.focus();
                }
            }, 400);
        }
    @endif

    document.querySelectorAll('.js-copy-code').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const kode = tombol.dataset.code || '';
            if (!kode) return;

            const selesai = function () {
                tombol.textContent = 'Tersalin';
                setTimeout(function () { tombol.textContent = 'Salin'; }, 1500);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(kode).then(selesai).catch(function () {});
                return;
            }

            const sementara = document.createElement('textarea');
            sementara.value = kode;
            document.body.appendChild(sementara);
            sementara.select();
            try { document.execCommand('copy'); selesai(); } catch (e) { /* abaikan */ }
            document.body.removeChild(sementara);
        });
    });

    const unduh = document.getElementById('btn-download-codes');
    if (unduh) {
        unduh.addEventListener('click', function () {
            let kode = [];
            try { kode = JSON.parse(unduh.dataset.codes || '[]'); } catch (e) { kode = []; }

            const isi = 'Kode Pemulihan AFWO Hair Design\n'
                + 'Simpan di tempat aman. Setiap kode hanya bisa dipakai sekali.\n\n'
                + kode.join('\n') + '\n';

            const blob = new Blob([isi], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const tautan = document.createElement('a');
            tautan.href = url;
            tautan.download = 'kode-pemulihan-afwo.txt';
            document.body.appendChild(tautan);
            tautan.click();
            tautan.remove();
            URL.revokeObjectURL(url);
        });
    }
})();
</script>
@endpush