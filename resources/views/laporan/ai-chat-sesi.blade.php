@extends('layouts.app')

@section('title', 'Sesi Percakapan AI')

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
                <a href="{{ route('laporan.ai-chat') }}" class="btn btn--ghost btn-kembali" style="margin-bottom: 10px; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <line x1="15" y1="18" x2="6" y2="12"></line>
                        <line x1="6" y1="6" x2="15" y2="12"></line>
                    </svg>
                    Kembali ke Daftar Sesi
                </a>
                <h1 class="page-header__title" id="report-page-title">Sesi Chat</h1>
                <p class="page-header__subtitle">Lanjutkan obrolan dalam sesi ini. AI menjawab berdasarkan data bisnis periode aktif.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="report-empty-note" style="background-color: var(--status-ok-bg); color: var(--status-ok-fg); border: 1px solid var(--status-ok-border); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="report-empty-note" style="background-color: var(--status-danger-bg); color: var(--status-danger-fg); border: 1px solid var(--status-danger-border); border-radius: 12px; margin-bottom: 18px; padding: 12px 16px; text-align: left;">{{ session('error') }}</div>
    @endif

    {{-- ============ RIWAYAT PERCAKAPAN ============ --}}
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

        @if ($messages->isNotEmpty())
            <div id="riwayatChat" class="chat-history-box">
                @foreach ($messages as $item)
                    <div>
                        <div class="chat-row-user">
                            <div class="chat-bubble-user">{{ $item->pertanyaan }}</div>
                        </div>
                        <div class="chat-row-ai">
                            <div class="chat-bubble-ai">{{ $item->jawaban }}</div>
                        </div>
                        <p class="chat-time">{{ $item->dibuat_pada->format('d M Y H:i') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <div class="insight-note-empty no-print">Belum ada percakapan dalam sesi ini. Ajukan pertanyaan pertama di bawah ini.</div>
        @endif

        {{-- Form lanjutkan obrolan --}}
        <form method="POST" action="{{ route('laporan.ai-chat.sesi.tanya', $sesiId) }}" id="formTanyaAiSesi" style="margin-top: 16px;">
            @csrf
            <textarea name="pertanyaan" rows="2" maxlength="500" required
                placeholder="Ketik pertanyaan lanjutan Anda di sini…"
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
            </div>
        </form>

        @if ($kuotaAi['habis'])
            <p style="font-size: 0.78rem; color: var(--status-danger); margin-top: 10px;">
                Kuota AI hari ini habis ({{ $kuotaAi['terpakai'] }}/{{ $kuotaAi['batas'] }}). Lanjut besok &mdash; diperbarui {{ $kuotaAi['label_reset'] }}.
            </p>
        @endif
    </section>

@endsection

@push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', function () {
    // Cooldown tombol Tanya AI: disable 3 detik setelah kirim
    const formTanya = document.getElementById('formTanyaAiSesi');
    if (formTanya) {
        formTanya.addEventListener('submit', function () {
            const btn = formTanya.querySelector('button[type="submit"]');
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            setTimeout(() => { btn.disabled = false; }, 3000);
        });
    }

    // Scroll chat ke pesan terbaru
    const riwayatChat = document.getElementById('riwayatChat');
    if (riwayatChat) {
        riwayatChat.scrollTop = riwayatChat.scrollHeight;
    }
});
    </script>
@endpush
