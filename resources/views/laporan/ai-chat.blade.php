@extends('layouts.app')

@section('title', 'AI Asisten Bisnis')

@section('nav-active', 'reports-ai-chat')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
@endpush

@section('content')

    {{-- ===== Page Header ===== --}}
    <div class="page-header">
        <span class="page-header__eyebrow">AI Asisten Bisnis</span>
        <div class="page-header__row">
            <div>
                <h1 class="page-header__title" id="report-page-title">Tanya AI</h1>
                <p class="page-header__subtitle">Ajukan pertanyaan bebas tentang data bisnis Anda. AI menjawab langsung dari ringkasan data periode aktif.</p>
            </div>
        </div>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.ai-chat'),
        'resetUrl' => route('laporan.ai-chat', ['preset' => 'bulan-ini']),
    ])

    @if (session('success'))
        <div class="report-empty-note" style="background-color: var(--status-ok-bg); color: var(--status-ok-fg); border: 1px solid var(--status-ok-border); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="report-empty-note" style="background-color: var(--status-danger-bg); color: var(--status-danger-fg); border: 1px solid var(--status-danger-border); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('error') }}</div>
    @endif

    {{-- ============ TANYA AI (chat) ============ --}}
    <section class="card ai-chat-card" id="ai-chat-card" data-field="ai-chat-card">
        <p class="insight-quota-line">
            Kuota AI hari ini:
            <strong>{{ $kuotaAi['terpakai'] }}/{{ $kuotaAi['batas'] }}</strong>
            @if ($kuotaAi['habis'])
                <span class="warn">&middot; habis, diperbarui {{ $kuotaAi['label_reset'] }}</span>
            @elseif ($kuotaAi['hampir_habis'])
                <span class="warn">&middot; sisa {{ $kuotaAi['sisa'] }}, diperbarui {{ $kuotaAi['label_reset'] }}</span>
            @else
                &middot; diperbarui {{ $kuotaAi['label_reset'] }}
            @endif
        </p>

        <form method="POST" action="{{ route('laporan.insight.tanya') }}" id="formTanyaAi" style="margin-top: 10px;">
            @csrf
            <textarea name="pertanyaan" rows="2" maxlength="500" required
                placeholder="Contoh: kenapa omset kategori Warna Rambut turun drastis?"
                class="tanya-field">{{ old('pertanyaan') }}</textarea>
            @error('pertanyaan')
                <p style="font-size: 0.76rem; color: var(--status-danger); margin-top: 6px;">{{ $message }}</p>
            @enderror
            <div class="tanya-actions-bar">
                <span class="tanya-char-hint">Maksimal 500 karakter</span>
                <button type="submit"
                    @if ($kuotaAi['habis']) disabled title="Kuota AI hari ini habis, diperbarui {{ $kuotaAi['label_reset'] }}" @endif
                    class="btn btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    Kirim
                </button>
                <a href="{{ route('laporan.ai-chat.sesi.create') }}" class="btn btn--ghost btn-baru-sesi" style="margin-left: auto;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Sesi Baru
                </a>
            </div>
        </form>

        @if ($kuotaAi['habis'])
            <p style="font-size: 0.78rem; color: var(--status-danger); margin-top: 10px;">
                Kuota AI hari ini habis ({{ $kuotaAi['terpakai'] }}/{{ $kuotaAi['batas'] }}). Lanjut besok &mdash; diperbarui {{ $kuotaAi['label_reset'] }}.
            </p>
        @endif

        {{-- Tombol buka daftar sesi percakapan (modal) --}}
        <div style="margin-top: 16px; display: flex; gap: 8px;">
            <button type="button" class="btn btn--ghost btn-buka-sesi" id="btnBukaSesi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M1 12s4-8 11-8 11 8-11 8-11-8-11-8 11-8 11 8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Sesi Chat</span>
                <span class="badge-sesi-count">{{ $sesiList->count() }}</span>
            </button>
            @if ($sesiList->isEmpty())
                <span class="empty-hint">Belum ada riwayat percakapan.</span>
            @endif
        </div>
    </section>

    @include('laporan._modal_sesi_chat', [
        'sesiList' => $sesiList ?? collect(),
    ])
@endsection

@push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', function () {
    // Cooldown tombol Tanya AI: disable 3 detik setelah kirim
    const formTanya = document.getElementById('formTanyaAi');
    if (formTanya) {
        formTanya.addEventListener('submit', function () {
            const btn = formTanya.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            setTimeout(() => { btn.disabled = false; }, 3000);
        });
    }

    // ===== Modal Sesi Chat =====
    const btnBukaSesi = document.getElementById('btnBukaSesi');
    const modalSesi = document.getElementById('modalSesiChat');
    const btnTutupSesi = document.getElementById('btnTutupSesi');
    const overlaySesi = document.getElementById('overlaySesiChat');

    function bukaModalSesi() {
        if (!modalSesi) return;
        modalSesi.classList.remove('hidden');
        modalSesi.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function tutupModalSesi() {
        if (!modalSesi) return;
        modalSesi.classList.add('hidden');
        modalSesi.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (btnBukaSesi && modalSesi && btnTutupSesi && overlaySesi) {
        btnBukaSesi.addEventListener('click', bukaModalSesi);
        btnTutupSesi.addEventListener('click', tutupModalSesi);
        overlaySesi.addEventListener('click', tutupModalSesi);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modalSesi.classList.contains('hidden')) {
                tutupModalSesi();
            }
        });
    }

    // Konfirmasi hapus sesi
    document.querySelectorAll('.form-hapus-sesi').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Yakin ingin menghapus seluruh percakapan pada sesi ini?')) {
                e.preventDefault();
            }
        });
    });
});
    </script>
@endpush
