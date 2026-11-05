@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('nav-active', 'users')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/worker.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pengguna.css') }}">
@endpush

@section('content')

    @if (session('password_sementara'))
        <div class="user-temppass-box" id="box-password-sementara">
            <svg class="user-temppass-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <div class="user-temppass-body">
                <div class="user-temppass-title">Password sementara akun {{ session('password_sementara')['username'] }} ({{ session('password_sementara')['aksi'] }})</div>
                <p class="user-temppass-subtitle">Minta user login memakai password ini. Ia akan langsung diarahkan ke halaman Profil untuk membuat password baru.</p>

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

                <p class="user-temppass-hint">Minta user login memakai password ini. Ia akan langsung diarahkan ke halaman Profil untuk membuat password baru.</p>

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
            <p class="worker-page-subtitle">Tambah kasir baru, atur ulang password, dan nonaktifkan atau aktifkan akun.</p>
        </div>
    </div>

    {{-- Form tambah kasir --}}
    <div class="user-form-card">
        <div class="user-form-title">Tambah Kasir Baru</div>
        <p class="user-form-subtitle">Role otomatis Kasir. Isi password awal untuk kasir, dan sampaikan langsung kepadanya.</p>

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

                <div class="form-group-item">
                    <label class="form-label" for="password-kasir">Password</label>
                    <div class="input-box user-password-box">
                        <input type="password" id="password-kasir" name="password" class="form-input-control" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                        <button type="button" class="btn-pw-random" id="btn-password-acak">Buat acak</button>
                        <button type="button" class="btn-pw-toggle js-toggle-password" data-target="password-kasir" aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="icon-eye" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="icon-eye-off" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    @error('password')
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

            <label class="user-form-check" for="wajib-ganti-password">
                <input type="checkbox" id="wajib-ganti-password" name="wajib_ganti_password" value="1"
                       @checked(session()->hasOldInput() ? old('wajib_ganti_password') : true)>
                <span class="user-form-check-text">
                    <span class="user-form-check-title">Wajib ganti password saat login pertama</span>
                    <span class="user-form-check-hint">Jika dimatikan, kasir bisa langsung memakai password ini.</span>
                </span>
            </label>
        </form>
    </div>

    <div class="worker-directory-card">
        <div class="table-responsive-wrapper">
            <table class="worker-detail-table">
                <thead>
                    <tr>
                        <th>NAMA</th>
                        <th>USERNAME</th>
                        <th>EMAIL</th>
                        <th>PERAN</th>
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
                                @if ($u->email)
                                    <span class="user-email-text">{{ $u->email }}</span>
                                @else
                                    <span class="user-email-empty">Belum ada</span>
                                @endif
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
                                              onsubmit="return confirm('Atur ulang password akun {{ $u->username }}? Password sementara baru akan ditampilkan sekali.')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-user-action">Atur Ulang Password</button>
                                        </form>

                                        <form method="POST" action="{{ route('pengguna.toggle-aktif', $u) }}"
                                              onsubmit="return confirm('{{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }} akun {{ $u->username }}?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-user-action {{ $u->aktif ? 'is-danger' : 'is-success' }}">
                                                {{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        @if ($u->isKasir())
                                            <button type="button" class="btn-user-action is-danger js-hapus-kasir"
                                                    data-url="{{ route('pengguna.destroy', $u) }}"
                                                    data-nama="{{ $u->name }}"
                                                    data-username="{{ $u->username }}"
                                                    aria-label="Hapus akun {{ $u->username }}">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal konfirmasi hapus akun kasir --}}
    <div class="user-modal" id="modal-hapus-kasir" hidden aria-hidden="true">
        <div class="user-modal-backdrop" data-close-modal></div>
        <div class="user-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-hapus-title">
            <h2 class="user-modal-title" id="modal-hapus-title">Hapus akun kasir?</h2>
            <p class="user-modal-text" id="modal-hapus-text"></p>

            <form method="POST" id="form-hapus-kasir">
                @csrf
                @method('DELETE')

                <label class="form-label" for="konfirmasi-username">Ketik username untuk konfirmasi</label>
                <div class="input-box">
                    <input type="text" id="konfirmasi-username" autocomplete="off" autocapitalize="none" spellcheck="false" placeholder="Ketik username kasir">
                </div>

                <div class="user-modal-actions">
                    <button type="button" class="btn-user-action" data-close-modal>Batal</button>
                    <button type="submit" class="btn-user-danger" id="btn-hapus-permanen" disabled>Hapus Permanen</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('modals')
    <div id="user-delete-modal" class="user-delete-overlay" role="dialog" aria-modal="true"
         aria-labelledby="user-delete-title" aria-describedby="user-delete-desc" aria-hidden="true">
        <div class="user-delete-card">
            <div class="user-delete-header">
                <h3 class="user-delete-title" id="user-delete-title">Hapus akun kasir?</h3>
                <button type="button" class="user-delete-close" id="user-delete-close" aria-label="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form method="POST" action="#" id="user-delete-form">
                @csrf
                @method('DELETE')

                <div class="user-delete-body">
                    <p class="user-delete-desc" id="user-delete-desc">
                        Akun <strong id="user-delete-name"></strong> (<span id="user-delete-username"></span>)
                        akan dihapus <strong>PERMANEN</strong> dan tidak bisa dikembalikan.
                        Riwayat transaksi tetap tersimpan.
                    </p>

                    <label class="user-delete-label" for="user-delete-confirm">Ketik username untuk konfirmasi</label>
                    <div class="user-delete-input-wrap">
                        <input type="text" id="user-delete-confirm" class="user-delete-input"
                               placeholder="Ketik username..." autocomplete="off" autocapitalize="none" spellcheck="false">
                    </div>
                    <p class="user-delete-hint">Ketik <strong id="user-delete-username-hint"></strong> untuk mengaktifkan tombol Hapus Permanen.</p>
                </div>

                <div class="user-delete-footer">
                    <button type="button" class="btn-user-action" id="user-delete-cancel">Batal</button>
                    <button type="submit" class="btn-user-delete-confirm" id="user-delete-submit" disabled>Hapus Permanen</button>
                </div>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
<script>
(function () {
    var input = document.getElementById('password-kasir');
    var toggle = document.getElementById('btn-toggle-password');
    var generate = document.getElementById('btn-generate-password');
    if (!input) return;

    function setVisible(visible) {
        input.type = visible ? 'text' : 'password';

        if (!toggle) return;

        toggle.setAttribute('aria-pressed', visible ? 'true' : 'false');
        toggle.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');

        var eye = toggle.querySelector('.icon-eye');
        var eyeOff = toggle.querySelector('.icon-eye-off');
        if (eye) eye.classList.toggle('hidden', visible);
        if (eyeOff) eyeOff.classList.toggle('hidden', !visible);
    }

    if (toggle) {
        toggle.addEventListener('click', function () {
            setVisible(input.type === 'password');
            input.focus();
        });
    }

    var hurufBesar = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    var hurufKecil = 'abcdefghijkmnopqrstuvwxyz';
    var angka = '23456789';
    var semua = hurufBesar + hurufKecil + angka;

    function randomInt(max) {
        if (window.crypto && window.crypto.getRandomValues) {
            var batas = Math.floor(4294967296 / max) * max;
            var buffer = new Uint32Array(1);
            do {
                window.crypto.getRandomValues(buffer);
            } while (buffer[0] >= batas);
            return buffer[0] % max;
        }

        return Math.floor(Math.random() * max);
    }

    function ambil(karakter) {
        return karakter.charAt(randomInt(karakter.length));
    }

    function buatPassword() {
        var hasil = [ambil(hurufBesar), ambil(hurufKecil), ambil(angka)];

        while (hasil.length < 10) {
            hasil.push(ambil(semua));
        }

        for (var i = hasil.length - 1; i > 0; i--) {
            var j = randomInt(i + 1);
            var sementara = hasil[i];
            hasil[i] = hasil[j];
            hasil[j] = sementara;
        }

        return hasil.join('');
    }

    if (generate) {
        generate.addEventListener('click', function () {
            input.value = buatPassword();
            setVisible(true);
            input.focus();
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
})();

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

    // Tampilkan / sembunyikan password kasir baru
    const inputPassword = document.getElementById('password-kasir');

    document.querySelectorAll('.js-toggle-password').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const target = document.getElementById(tombol.dataset.target);
            if (!target) return;

            const tampil = target.type === 'password';
            target.type = tampil ? 'text' : 'password';
            tombol.classList.toggle('is-visible', tampil);
            tombol.setAttribute('aria-pressed', tampil ? 'true' : 'false');
            tombol.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    // Buat password acak 10 karakter tanpa karakter ambigu (0/O/1/l/I)
    const tombolAcak = document.getElementById('btn-password-acak');

    if (tombolAcak && inputPassword) {
        tombolAcak.addEventListener('click', function () {
            const hurufBesar = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const hurufKecil = 'abcdefghijkmnopqrstuvwxyz';
            const angka = '23456789';
            const semua = hurufBesar + hurufKecil + angka;

            const acak = function (maks) {
                const buffer = new Uint32Array(1);
                crypto.getRandomValues(buffer);
                return buffer[0] % maks;
            };

            const karakter = [
                hurufBesar[acak(hurufBesar.length)],
                hurufKecil[acak(hurufKecil.length)],
                angka[acak(angka.length)],
            ];

            while (karakter.length < 10) {
                karakter.push(semua[acak(semua.length)]);
            }

            for (let i = karakter.length - 1; i > 0; i--) {
                const j = acak(i + 1);
                const sementara = karakter[i];
                karakter[i] = karakter[j];
                karakter[j] = sementara;
            }

            inputPassword.value = karakter.join('');
            inputPassword.type = 'text';

            const tombolMata = document.querySelector('.js-toggle-password[data-target="password-kasir"]');
            if (tombolMata) {
                tombolMata.classList.add('is-visible');
                tombolMata.setAttribute('aria-pressed', 'true');
                tombolMata.setAttribute('aria-label', 'Sembunyikan password');
            }
        });
    }

    // Modal hapus kasir
    const modal = document.getElementById('modal-hapus-kasir');

    if (modal) {
        const form = document.getElementById('form-hapus-kasir');
        const teks = document.getElementById('modal-hapus-text');
        const konfirmasi = document.getElementById('konfirmasi-username');
        const tombolHapus = document.getElementById('btn-hapus-permanen');
        let usernameTarget = '';
        let pemicu = null;

        const tutupModal = function () {
            modal.hidden = true;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('user-modal-open');

            if (pemicu) {
                pemicu.focus();
                pemicu = null;
            }
        };

        document.querySelectorAll('.js-hapus-kasir').forEach(function (tombol) {
            tombol.addEventListener('click', function () {
                pemicu = tombol;
                usernameTarget = tombol.dataset.username || '';

                form.action = tombol.dataset.url || '';
                teks.textContent = 'Akun ' + (tombol.dataset.nama || '') + ' (' + usernameTarget
                    + ') akan dihapus PERMANEN dan tidak bisa dikembalikan. Riwayat transaksi tetap tersimpan.';

                konfirmasi.value = '';
                tombolHapus.disabled = true;

                modal.hidden = false;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('user-modal-open');

                window.requestAnimationFrame(function () { konfirmasi.focus(); });
            });
        });

        konfirmasi.addEventListener('input', function () {
            tombolHapus.disabled = konfirmasi.value !== usernameTarget;
        });

        modal.querySelectorAll('[data-close-modal]').forEach(function (tombol) {
            tombol.addEventListener('click', tutupModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.hidden) {
                tutupModal();
            }
        });
    }
})();

(function () {
    var modal = document.getElementById('user-delete-modal');
    if (!modal) return;

    var form = document.getElementById('user-delete-form');
    var input = document.getElementById('user-delete-confirm');
    var submit = document.getElementById('user-delete-submit');
    var nameEl = document.getElementById('user-delete-name');
    var usernameEl = document.getElementById('user-delete-username');
    var usernameHintEl = document.getElementById('user-delete-username-hint');
    var closeEl = document.getElementById('user-delete-close');
    var cancelEl = document.getElementById('user-delete-cancel');
    var openButtons = document.querySelectorAll('.js-user-delete');
    var expectedUsername = '';
    var previousFocus = null;

    function syncSubmitState() {
        submit.disabled = !expectedUsername || input.value !== expectedUsername;
    }

    function openModal(button) {
        expectedUsername = button.dataset.userUsername || '';
        form.action = button.dataset.userUrl || '#';
        nameEl.textContent = button.dataset.userName || '';
        usernameEl.textContent = expectedUsername;
        usernameHintEl.textContent = expectedUsername;
        input.value = '';
        syncSubmitState();

        previousFocus = document.activeElement;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('user-delete-open');

        window.setTimeout(function () {
            input.focus();
        }, 30);
    }

    function closeModal() {
        if (!modal.classList.contains('is-open')) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('user-delete-open');
        input.value = '';
        expectedUsername = '';
        form.action = '#';
        syncSubmitState();

        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }
        previousFocus = null;
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            openModal(button);
        });
    });

    if (closeEl) closeEl.addEventListener('click', closeModal);
    if (cancelEl) cancelEl.addEventListener('click', closeModal);

    input.addEventListener('input', syncSubmitState);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    form.addEventListener('submit', function (event) {
        if (input.value !== expectedUsername) {
            event.preventDefault();
            syncSubmitState();
        }
    });
})();
</script>
@endpush
