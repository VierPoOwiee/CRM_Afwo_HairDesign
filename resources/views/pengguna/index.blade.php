@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('nav-active', 'users')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/worker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pengguna.css') }}">
@endpush

@section('content')

    @if (session('password_s Sementara'))
        <div class="user-temppass-box" id="box-password-sementara">
            <svg class="user-temppass-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <div class="user-temppass-body">
                <div class="user-temppass-title">Password sementara akun {{ session('password_sementara')['username'] }} ({{ session('password_sementara')['aksi'] }})</div>
                <p class="user-temppass-subtitle">Berikan password ini kepada user. User wajib menggantinya saat login berikutnya.</p>

                <div class="user-temppass-line">
                    <div class="user-temppass-item">
                        <span class="user-temppass-label">Username</span>
                        <span class="user-temppass-value">{{ session('password_sementara')['username'] }}</span>
                        <button type="button" class="btn-copy-temppass js-copy" data-copy="{{ session('password_sementara')['username'] }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Salin
                        </button>
                    </div>

                    <div class="user-temppass-item">
                        <span class="user-temppass-label">Password</span>
                        <span class="user-temppass-value">{{ session('password_sementara')['password'] }}</span>
                        <button type="button" class="btn-copy-temppass js-copy" data-copy="{{ session('password_sementara')['password'] }}">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Salin
                        </button>
                    </div>
                </div>

                <p class="user-temppass-note">Catat sekarang, tidak akan ditampilkan lagi.</p>
            </div>
        </div>
    @endif

    <div class="worker-page-header-row">
        <div class="worker-title-group">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 class="worker-page-title">Kelola Pengguna</h1>
                <span class="worker-count-badge">{{ $users->count() }} Pengguna</span>
            </div>
            <p class="worker-page-subtitle">Tambah kasir baru, reset password, dan nonaktifkan atau aktifkan akun.</p>
        </div>
    </div>

    {{-- Form tambah kasir --}}
    <div class="user-form-card">
        <div class="user-form-title">Tambah Kasir Baru</div>
        <p class="user-form-subtitle">Role otomatis Kasir. Password sementara dibuat otomatis dan wajib diganti saat user pertama kali login.</p>

        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf

            <div class="user-form-grid">
                <div class="form-group-item">
                    <label class="form-label" for="nama-kasir">Nama Lengkap</label>
                    <div class="input-box">
                        <input type="text" id="nama-kasir" name="name" value="{{ old('name') }}" class="form-input-control" placeholder="Nama lengkap kasir" required>
                    </div>
                    @error('name')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group-item">
                    <label class="form-label" for="username-kasir">Username</label>
                    <div class="input-box">
                        <input type="text" id="username-kasir" name="username" value="{{ old('username') }}" class="form-input-control" placeholder="misal: kasir01" autocomplete="off" required>
                    </div>
                    @error('username')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-user-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Kasir</span>
                </button>
            </div>
        </form>
    </div>

    <div class="worker-directory-card">
        <div class="table-responsive-wrapper">
            <table class="worker-detail-table">
                <thead>
                    <tr>
                        <th>NAMA</th>
                        <th>USERNAME</th>
                        <th>ROLE</th>
                        <th>STATUS</th>
                        <th style="text-align: right; width: 260px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr>
                            <td>
                                <span class="worker-name-title">{{ $u->name }}</span>
                                @if ($u->wajib_ganti_password)
                                    <div class="user-force-note">Wajib ganti password</div>
                                @endif
                            </td>
                            <td>
                                <span class="user-username-text">{{ $u->username }}</span>
                            </td>
                            <td>
                                <span class="user-role-pill {{ $u->isOwner() ? 'is-owner' : '' }}">{{ $u->isOwner() ? 'Owner' : 'Kasir' }}</span>
                            </td>
                            <td>
                                <span class="user-status-pill {{ $u->aktif ? 'user-status-aktif' : 'user-status-nonaktif' }}">
                                    {{ $u->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                @if (Auth::id() === $u->id)
                                    <span class="worker-phone-num">Gunakan halaman Profil</span>
                                @else
                                    <div class="user-actions-cell">
                                        <form method="POST" action="{{ route('pengguna.reset-password', $u) }}"
                                              onsubmit="return confirm('Reset password akun {{ $u->username }}? Password sementara baru akan ditampilkan sekali.')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-user-action">Reset Password</button>
                                        </form>

                                        <form method="POST" action="{{ route('pengguna.toggle-aktif', $u) }}"
                                              onsubmit="return confirm('{{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->username }}?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-user-action {{ $u->aktif ? 'is-danger' : 'is-success' }}">
                                                {{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.js-copy').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const teks = tombol.dataset.copy || '';
            if (!teks) return;

            const label = tombol.innerHTML;
            const selesai = () => {
                tombol.textContent = 'Tersalin';
                setTimeout(() => { tombol.innerHTML = label; }, 1500);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(teks).then(selesai).catch(() => {});
                return;
            }

            const sementara = document.createElement('textarea');
            sementara.value = teks;
            document.body.appendChild(sementara);
            sementara.select();
            try { document.execCommand('copy'); selesai(); } catch (e) { /* abaikan */ }
            document.body.removeChild(sementara);
        });
    });
})();
</script>
@endpush
