@extends('layouts.app')

@section('title', 'Scanner Gerbang — TiketIn')

@push('styles')
<style>
    #reader { min-height: 260px; background: #000; border-radius: 0.75rem; overflow: hidden; display: flex; align-items: center; justify-content: center; }
    #reader video { width: 100% !important; height: auto !important; }
    #reader img { display: none; } /* sembunyikan ikon default library saat kamera belum aktif */
</style>
@endpush

@section('content')
<div class="max-w-xl mx-auto px-4 lg:px-8 py-10 md:py-14" x-data="scanner()">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold text-ink">Scanner Gerbang</h1>
        <p class="text-ink-muted text-sm mt-1">Pindai QR code dari kamera, atau unggah gambar QR jika kamera tidak tersedia</p>
    </div>

    <div class="bg-ink rounded-2xl shadow-soft-lg p-4 sm:p-6">

        <!-- Tab: Kamera / Upload Gambar -->
        <div class="grid grid-cols-2 gap-2 mb-4" role="tablist" aria-label="Mode pemindaian">
            <button
                type="button" role="tab" :aria-selected="(mode === 'camera').toString()"
                @click="switchMode('camera')"
                :class="mode === 'camera' ? 'bg-brand-primary text-white' : 'bg-white/5 text-white/60 hover:bg-white/10'"
                class="min-h-[44px] rounded-lg font-semibold text-sm flex items-center justify-center gap-2 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 0 1 2-2h1.5l1-1.5h9l1 1.5H20a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><circle cx="12" cy="13" r="3.5"/></svg>
                Kamera
            </button>
            <button
                type="button" role="tab" :aria-selected="(mode === 'file').toString()"
                @click="switchMode('file')"
                :class="mode === 'file' ? 'bg-brand-primary text-white' : 'bg-white/5 text-white/60 hover:bg-white/10'"
                class="min-h-[44px] rounded-lg font-semibold text-sm flex items-center justify-center gap-2 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5M7 9l5-5 5 5M12 4v12"/></svg>
                Upload Gambar
            </button>
        </div>

        <!-- Panel Kamera -->
        <div x-show="mode === 'camera'">
            <div id="reader"></div>
        </div>

        <!-- Panel Upload -->
        <div x-show="mode === 'file'" x-cloak>
            <label for="qr-file-input" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-white/20 rounded-xl py-10 px-4 cursor-pointer hover:border-brand-secondary hover:bg-white/5 transition-colors">
                <svg class="w-8 h-8 text-white/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5M7 9l5-5 5 5M12 4v12"/></svg>
                <span class="text-white/70 text-sm font-medium">Klik untuk pilih gambar QR code</span>
                <span class="text-white/40 text-xs">PNG atau JPG</span>
                <span class="sr-only">Unggah gambar QR code untuk dipindai</span>
            </label>
            <input id="qr-file-input" type="file" accept="image/*" class="sr-only" @change="handleFile($event)">
            <p x-show="fileName" x-text="'File dipilih: ' + fileName" class="text-white/50 text-xs mt-3 text-center break-all"></p>
        </div>

        <!-- Hasil validasi (dipakai bersama oleh kedua mode) -->
        <div
            x-show="result" x-cloak role="status" aria-live="polite"
            class="mt-5 p-4 rounded-xl border"
            :class="{
                'bg-emerald-500/10 border-emerald-500/30 text-emerald-300': result?.kind === 'success',
                'bg-amber-500/10 border-amber-500/30 text-amber-300': result?.kind === 'warning',
                'bg-rose-500/10 border-rose-500/30 text-rose-300': result?.kind === 'error'
            }"
        >
            <div class="flex items-start gap-3">
                <span
                    class="shrink-0 grid place-items-center w-9 h-9 rounded-full"
                    :class="{
                        'bg-emerald-500/20 text-emerald-300': result?.kind === 'success',
                        'bg-amber-500/20 text-amber-300': result?.kind === 'warning',
                        'bg-rose-500/20 text-rose-300': result?.kind === 'error'
                    }"
                    aria-hidden="true"
                >
                    <svg x-show="result?.kind === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                    <svg x-show="result?.kind === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a1 1 0 0 0 .86 1.5h18.64a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0Z"/></svg>
                    <svg x-show="result?.kind === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="font-bold text-base leading-snug" x-text="result?.message"></p>
                    <p class="text-sm mt-0.5 opacity-80 leading-snug" x-text="result?.detail"></p>
                </div>
            </div>
        </div>

        <p x-show="!result" class="text-center text-white/40 text-xs mt-4">Hasil pemindaian akan muncul otomatis di sini</p>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('scanner', () => ({
            mode: 'camera',
            fileName: '',
            html5Qrcode: null,
            cameraRunning: false,
            processing: false,
            result: null, // { kind: 'success' | 'warning' | 'error', message, detail }

            init() {
                this.html5Qrcode = new Html5Qrcode('reader');
                this.startCamera();
            },

            async startCamera() {
                if (this.cameraRunning) return;
                try {
                    await this.html5Qrcode.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => this.onCameraDecoded(decodedText),
                        () => {} // abaikan frame yang belum terbaca, ini normal & sering terjadi
                    );
                    this.cameraRunning = true;
                } catch (err) {
                    this.result = {
                        kind: 'error',
                        message: 'Kamera tidak dapat diakses',
                        detail: 'Izinkan akses kamera di browser, atau gunakan tab Upload Gambar.'
                    };
                }
            },

            async stopCamera() {
                if (!this.cameraRunning) return;
                try { await this.html5Qrcode.stop(); } catch (e) { /* sudah berhenti, aman diabaikan */ }
                this.cameraRunning = false;
            },

            async switchMode(target) {
                if (this.mode === target) return;
                this.mode = target;
                this.result = null;
                if (target === 'camera') {
                    await this.startCamera();
                } else {
                    await this.stopCamera();
                }
            },

            async onCameraDecoded(decodedText) {
                if (this.processing) return;
                this.processing = true;
                await this.stopCamera();
                this.processing = false;
                await this.validateTicket(decodedText);
            },

            async handleFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.fileName = file.name;
                this.result = null;
                await this.stopCamera();
                try {
                    const decodedText = await this.html5Qrcode.scanFile(file, false);
                    await this.validateTicket(decodedText);
                } catch (err) {
                    this.result = {
                        kind: 'error',
                        message: 'QR code tidak terbaca',
                        detail: 'Pastikan gambar jelas dan memuat QR code yang valid.'
                    };
                }
            },

            async validateTicket(ticketCode) {
                try {
                    const response = await fetch("{{ route('scan.validate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ticket_code: ticketCode })
                    });
                    const data = await response.json();
                    const kind = ['success', 'warning', 'error'].includes(data.status) ? data.status : 'error';
                    this.result = { kind, message: data.message, detail: data.detail || '' };
                } catch (e) {
                    this.result = {
                        kind: 'error',
                        message: 'Gagal memproses scan',
                        detail: 'Periksa koneksi internet dan coba lagi.'
                    };
                }

                setTimeout(() => {
                    this.result = null;
                    this.fileName = '';
                    if (this.mode === 'camera') this.startCamera();
                }, 3000);
            },
        }));
    });
</script>
@endsection
