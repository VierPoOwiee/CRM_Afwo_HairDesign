@php
    $sesiList = $sesiList ?? collect();
    $totalSesi = $sesiList->count();
@endphp

<div id="overlaySesiChat" class="overlay-sesi-chat hidden" aria-hidden="true"></div>

<div id="modalSesiChat" class="modal-sesi-chat hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Daftar Sesi Percakapan AI">
    <div class="modal-sesi__backdrop"></div>
    <div class="modal-sesi__panel">
        <div class="modal-sesi__header">
            <h2 class="modal-sesi__title">Sesi Percakapan AI</h2>
            <span class="modal-sesi__count">{{ $totalSesi }} sesi</span>
            <button type="button" id="btnTutupSesi" class="modal-sesi__close" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="modal-sesi__body">
            @if ($totalSesi === 0)
                <div class="modal-sesi__empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.4;">
                        <path d="M1 12s4-8 11-8 11 8-11 8-11-8 11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <p class="modal-sesi__empty-text">Belum ada riwayat percakapan.</p>
                    <p class="modal-sesi__empty-sub">Ajukan pertanyaan pertama di halaman utama, atau buat sesi baru.</p>
                </div>
            @else
                <div class="modal-sesi__toolbar">
                    <a href="{{ route('laporan.ai-chat.sesi.create') }}" class="btn btn--primary btn-sesi-baru">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Sesi Baru
                    </a>
                </div>

                <p class="modal-sesi__caption">Pilih sesi untuk melanjutkan obrolan, atau hapus sesi yang tidak diperlukan.</p>

                <div class="modal-sesi__list">
                    @foreach ($sesiList as $sesi)
                        <div class="modal-sesi__item" id="sesi-{{ $sesi['sesi_id'] }}">
                            <div class="modal-sesi__item-head">
                                <div class="modal-sesi__item-info">
                                    <span class="modal-sesi__item-tanggal {{ $sesi['tanggal'] === Carbon\Carbon::today()->toDateString() ? 'is-today' : '' }}">{{ $sesi['label_tanggal'] }}</span>
                                    <span class="modal-sesi__item-waktu">{{ $sesi['waktu_terakhir'] }} &middot; {{ $sesi['jumlah'] }} percakapan</span>
                                </div>
                                <div class="modal-sesi__item-actions">
                                    <a href="{{ route('laporan.ai-chat.sesi.show', $sesi['sesi_id']) }}" class="btn btn--ghost btn-buka-detail" title="Buka percakapan ini">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12h20M12 2v20"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        Buka
                                    </a>
                                    <form method="POST" action="{{ route('laporan.ai-chat.hapus-sesi', $sesi['sesi_id']) }}" class="form-hapus-sesi modal-sesi__item-hapus" onsubmit="return confirm('Yakin ingin menghapus seluruh percakapan sesi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn--ghost btn-hapus-sesi-modal" title="Hapus sesi ini">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Preview pertanyaan terakhir --}}
                            <div class="modal-sesi__preview">
                                <span class="modal-sesi__preview-label">Pertanyaan terakhir:</span>
                                <p class="modal-sesi__preview-text">{{ $sesi['pertanyaan_terakhir'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="modal-sesi__footer">
            <button type="button" id="btnTutupSesiFooter" class="btn btn--primary btn-tutup-modal">Tutup</button>
        </div>
    </div>
</div>

<style>
    .overlay-sesi-chat {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(2px);
        z-index: 60;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .overlay-sesi-chat.hidden {
        display: none;
        opacity: 0;
    }
    .overlay-sesi-chat:not(.hidden) {
        opacity: 1;
    }

    .modal-sesi-chat {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.95);
        width: 92%;
        max-width: 640px;
        max-height: 85vh;
        background-color: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
        z-index: 65;
        display: flex;
        flex-direction: column;
        opacity: 0;
        visibility: hidden;
        transition: transform 0.2s ease, opacity 0.2s ease, visibility 0.2s ease;
    }
    .modal-sesi-chat.hidden {
        display: none;
    }
    .modal-sesi-chat:not(.hidden) {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
        visibility: visible;
    }

    .modal-sesi__backdrop {
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
    }

    .modal-sesi__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--sp-3);
        padding: var(--sp-4) var(--sp-5);
        border-bottom: 1px solid var(--border);
    }

    .modal-sesi__title {
        font-size: var(--fs-16);
        font-weight: var(--fw-bold);
        color: var(--text-1);
        margin: 0;
    }

    .modal-sesi__count {
        font-size: var(--fs-12);
        font-weight: var(--fw-semibold);
        color: var(--text-3);
        background-color: var(--surface-2);
        padding: 2px 10px;
        border-radius: 999px;
    }

    .modal-sesi__close {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: transparent;
        border: none;
        border-radius: var(--radius-md);
        color: var(--text-3);
        cursor: pointer;
        line-height: 0;
    }
    .modal-sesi__close:hover {
        background-color: var(--surface-3);
        color: var(--text-1);
    }

    .modal-sesi__body {
        padding: var(--sp-4) var(--sp-5);
        overflow-y: auto;
        max-height: 60vh;
    }

    .modal-sesi__empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: var(--sp-5) var(--sp-4);
        color: var(--text-3);
    }
    .modal-sesi__empty-text {
        font-size: var(--fs-14);
        font-weight: var(--fw-semibold);
        color: var(--text-2);
        margin-top: var(--sp-2);
        margin-bottom: 4px;
    }
    .modal-sesi__empty-sub {
        font-size: var(--fs-12);
        color: var(--text-3);
    }

    .modal-sesi__toolbar {
        display: flex;
        gap: var(--sp-2);
        margin-bottom: var(--sp-3);
    }

    .modal-sesi__caption {
        font-size: var(--fs-12);
        font-weight: var(--fw-semibold);
        text-transform: uppercase;
        letter-spacing: var(--ls-caps);
        color: var(--text-3);
        margin-bottom: var(--sp-3);
    }

    .modal-sesi__list {
        display: flex;
        flex-direction: column;
        gap: var(--sp-3);
    }

    .modal-sesi__item {
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        background-color: var(--surface);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .modal-sesi__item:hover {
        border-color: var(--accent);
        box-shadow: 0 4px 12px -2px rgba(0,0,0,0.06);
    }

    .modal-sesi__item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: var(--sp-3);
        padding: var(--sp-3) var(--sp-4);
        background-color: var(--surface-2);
        border-bottom: 1px solid var(--border);
    }

    .modal-sesi__item-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .modal-sesi__item-tanggal {
        font-size: var(--fs-13);
        font-weight: var(--fw-bold);
        color: var(--text-1);
    }
    .modal-sesi__item-tanggal.is-today {
        color: var(--text-link);
    }

    .modal-sesi__item-waktu {
        font-size: var(--fs-11);
        color: var(--text-3);
    }

    .modal-sesi__item-actions {
        display: flex;
        gap: var(--sp-1);
        flex-shrink: 0;
    }

    .modal-sesi__preview {
        padding: var(--sp-3) var(--sp-4);
        border-bottom: 1px solid var(--border);
        background-color: var(--surface-inset);
    }
    .modal-sesi__preview-label {
        font-size: var(--fs-11);
        font-weight: var(--fw-bold);
        text-transform: uppercase;
        letter-spacing: var(--ls-caps);
        color: var(--text-3);
        display: block;
        margin-bottom: var(--sp-1);
    }
    .modal-sesi__preview-text {
        font-size: var(--fs-13);
        color: var(--text-2);
        margin: 0;
        white-space: pre-line;
    }

    .btn-sesi-baru {
        font-size: var(--fs-13);
        font-weight: var(--fw-semibold);
        padding: 6px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-sesi-baru svg {
        width: 14px;
        height: 14px;
    }

    .btn-buka-detail {
        font-size: var(--fs-12);
        font-weight: var(--fw-semibold);
        padding: 4px 10px;
        border-radius: var(--radius-md);
        border: 1px solid var(--text-link);
        color: var(--text-link);
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        line-height: 1.4;
        text-decoration: none;
    }
    .btn-buka-detail:hover {
        background-color: var(--text-link);
        color: var(--n-50);
    }
    .btn-buka-detail svg {
        width: 12px;
        height: 12px;
    }

    .btn-hapus-sesi-modal {
        font-size: var(--fs-12);
        font-weight: var(--fw-semibold);
        padding: 4px 10px;
        border-radius: var(--radius-md);
        border: 1px solid var(--status-danger);
        color: var(--status-danger);
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        line-height: 1.4;
        background: transparent;
    }
    .btn-hapus-sesi-modal:hover {
        background-color: var(--status-danger);
        color: var(--n-50);
    }
    .btn-hapus-sesi-modal svg {
        width: 12px;
        height: 12px;
    }

    .btn-buka-sesi {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: var(--fs-13);
        font-weight: var(--fw-semibold);
    }
    .btn-buka-sesi svg {
        width: 16px;
        height: 16px;
    }
    .badge-sesi-count {
        font-size: var(--fs-11);
        font-weight: var(--fw-bold);
        color: var(--n-50);
        background-color: var(--text-link);
        padding: 1px 6px;
        border-radius: 999px;
    }
    .empty-hint {
        font-size: var(--fs-12);
        color: var(--text-3);
        font-style: italic;
        align-self: center;
    }

    .modal-sesi__footer {
        display: flex;
        justify-content: flex-end;
        padding: var(--sp-3) var(--sp-5);
        border-top: 1px solid var(--border);
        background-color: var(--surface-2);
        border-bottom-left-radius: var(--radius-xl);
        border-bottom-right-radius: var(--radius-xl);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnTutupFooter = document.getElementById('btnTutupSesiFooter');
    const modalSesi = document.getElementById('modalSesiChat');
    const overlaySesi = document.getElementById('overlaySesiChat');

    function tutupModal() {
        if (modalSesi) {
            modalSesi.classList.add('hidden');
            modalSesi.setAttribute('aria-hidden', 'true');
        }
        document.body.style.overflow = '';
    }

    if (btnTutupFooter && modalSesi) {
        btnTutupFooter.addEventListener('click', tutupModal);
    }
    if (overlaySesi && modalSesi) {
        overlaySesi.addEventListener('click', tutupModal);
    }
});
</script>
