@extends('layouts.app')

@section('title', 'TiketIn — Temukan Event Mendatang')

@section('content')

    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-primary to-brand-primary-dark">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-16 md:py-24 grid md:grid-cols-5 gap-10 items-center">
            <div class="md:col-span-3 animate-rise-in">
                <h1 class="text-3xl sm:text-4xl md:text-[2.75rem] font-extrabold text-white leading-[1.12] tracking-tight">
                    Tiket event, langsung di genggaman.
                </h1>
                <p class="mt-4 text-white/85 text-base sm:text-lg max-w-md leading-relaxed">
                    Cari acara, pesan tiket, dan simpan e-tiketmu — cukup tunjukkan QR code di pintu masuk.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('marketplace.index') }}" class="inline-flex items-center justify-center min-h-[44px] bg-brand-accent text-ink font-semibold px-6 py-3 rounded-lg shadow-soft hover:bg-brand-accent-dark active:scale-[0.98] transition-all">
                        Jelajahi Semua Event
                    </a>
                    <a href="{{ route('ticket.check') }}" class="inline-flex items-center justify-center min-h-[44px] bg-white/10 text-white font-semibold px-6 py-3 rounded-lg border border-white/25 hover:bg-white/20 transition-colors">
                        Cek Tiket Saya
                    </a>
                </div>
            </div>

            <!-- Ilustrasi tumpukan tiket -->
            <div class="md:col-span-2 hidden md:block" aria-hidden="true">
                <div class="relative h-56">
                    <div class="absolute inset-0 m-auto w-60 h-32 bg-brand-secondary rounded-xl -rotate-[9deg] shadow-soft-lg"></div>
                    <div class="absolute inset-0 m-auto w-60 h-32 bg-brand-surface rounded-xl rotate-[6deg] translate-x-5 translate-y-3 shadow-soft-lg"></div>
                    <div class="absolute inset-0 m-auto w-60 h-32 bg-brand-accent rounded-xl -rotate-[1deg] -translate-x-4 -translate-y-3 shadow-soft-lg flex">
                        <span class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-brand-primary-dark rounded-full"></span>
                        <span class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-brand-primary-dark rounded-full"></span>
                        <div class="ml-11 my-4 border-l-2 border-dashed border-ink/25"></div>
                        <div class="flex-1 self-center pl-4 pr-3">
                            <div class="h-2.5 w-3/4 bg-ink/20 rounded-full"></div>
                            <div class="h-2 w-1/2 bg-ink/15 rounded-full mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kenapa Pilih TiketIn -->
    <section class="max-w-6xl mx-auto px-4 lg:px-8 py-12 md:py-16">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h2 class="text-2xl md:text-3xl font-extrabold text-ink">Kenapa Pilih TiketIn?</h2>
            <p class="text-ink-muted mt-2">Satu platform buat cari event, beli tiket, sampai masuk ke lokasi acara.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6">
                <div class="w-11 h-11 rounded-xl bg-brand-primary-soft text-brand-primary grid place-items-center mb-4" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z"/></svg>
                </div>
                <h3 class="font-bold text-ink mb-1.5">E-Tiket Instan</h3>
                <p class="text-sm text-ink-muted leading-relaxed">Begitu pembayaran dikonfirmasi, e-tiket lengkap dengan QR code langsung dikirim ke email — nggak perlu antre cetak tiket.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6">
                <div class="w-11 h-11 rounded-xl bg-brand-primary-soft text-brand-primary grid place-items-center mb-4" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </div>
                <h3 class="font-bold text-ink mb-1.5">Aman &amp; Terverifikasi</h3>
                <p class="text-sm text-ink-muted leading-relaxed">Setiap tiket punya QR unik yang divalidasi langsung oleh panitia di gerbang — meminimalisir tiket palsu atau dipakai dobel.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-soft p-6">
                <div class="w-11 h-11 rounded-xl bg-brand-primary-soft text-brand-primary grid place-items-center mb-4" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 9h6v12"/></svg>
                </div>
                <h3 class="font-bold text-ink mb-1.5">Untuk Semua Penyelenggara</h3>
                <p class="text-sm text-ink-muted leading-relaxed">Institusi, komunitas, atau organisasi apa pun bisa daftar dan kelola event serta penjualan tiketnya sendiri di satu tempat.</p>
            </div>
        </div>
    </section>

    <!-- Cara Kerja -->
    <section class="bg-white border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-12 md:py-16">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl md:text-3xl font-extrabold text-ink">Cara Kerjanya</h2>
                <p class="text-ink-muted mt-2">Tiga langkah, dari cari event sampai masuk lokasi acara.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-5">
                <div class="text-center sm:text-left">
                    <span class="inline-grid place-items-center w-9 h-9 rounded-full bg-brand-primary text-white font-bold mb-3">1</span>
                    <h3 class="font-bold text-ink mb-1.5">Cari &amp; Pilih Event</h3>
                    <p class="text-sm text-ink-muted leading-relaxed">Jelajahi berbagai event dari banyak penyelenggara sekaligus, tinggal cari sesuai minat atau lokasimu.</p>
                </div>
                <div class="text-center sm:text-left">
                    <span class="inline-grid place-items-center w-9 h-9 rounded-full bg-brand-primary text-white font-bold mb-3">2</span>
                    <h3 class="font-bold text-ink mb-1.5">Pesan &amp; Bayar</h3>
                    <p class="text-sm text-ink-muted leading-relaxed">Pilih kategori dan jumlah tiket, lalu bayar lewat QRIS atau Virtual Account — prosesnya cuma hitungan menit.</p>
                </div>
                <div class="text-center sm:text-left">
                    <span class="inline-grid place-items-center w-9 h-9 rounded-full bg-brand-primary text-white font-bold mb-3">3</span>
                    <h3 class="font-bold text-ink mb-1.5">Tunjukkan E-Tiket</h3>
                    <p class="text-sm text-ink-muted leading-relaxed">Cek email kamu, lalu tunjukkan QR code ke panitia saat masuk lokasi. Selesai, tinggal nikmati acaranya.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cuplikan event terdekat -->
    <section class="max-w-6xl mx-auto px-4 lg:px-8 py-12 md:py-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-extrabold text-ink">Event Terdekat</h2>
            <a href="{{ route('marketplace.index') }}" class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-brand-primary hover:underline min-h-[44px]">
                Lihat semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
            </a>
        </div>

        <div class="flex flex-col gap-4">
            @forelse($events as $event)
                <article class="group flex bg-white rounded-2xl shadow-soft hover:shadow-soft-lg transition-shadow duration-200 overflow-hidden border border-gray-100">

                    <div class="w-20 sm:w-28 shrink-0 bg-brand-primary-soft flex flex-col items-center justify-center text-brand-primary py-4" aria-hidden="true">
                        <span class="text-3xl sm:text-4xl font-extrabold leading-none">
                            {{ \Carbon\Carbon::parse($event->event_date)->format('d') }}
                        </span>
                        <span class="text-xs sm:text-sm font-semibold mt-1">
                            {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('M') }}
                        </span>
                    </div>

                    <div class="ticket-perforation w-px shrink-0 hidden sm:block" aria-hidden="true"></div>

                    <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4 justify-between min-w-0">
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-ink truncate">{{ $event->name }}</h3>
                            @if($event->organization)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-brand-primary bg-brand-primary-soft px-2 py-0.5 rounded-full mt-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 9h6v12"/></svg>
                                    {{ $event->organization->name }}
                                </span>
                            @endif
                            <div class="mt-2 space-y-1.5 text-sm text-ink-muted">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-secondary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                                    <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y · H:i') }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-secondary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.66 17.66 13.4 21.9a2 2 0 0 1-2.83 0l-4.24-4.24a8 8 0 1 1 11.31 0Z"/><circle cx="12" cy="11" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span class="truncate">{{ $event->location }}</span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('event.show', $event->id) }}" class="shrink-0 inline-flex items-center justify-center gap-1.5 min-h-[44px] bg-brand-primary text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
                            Lihat Detail
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                    <p class="text-ink font-semibold">Belum ada event mendatang</p>
                    <p class="text-ink-muted text-sm mt-1">Coba cek lagi dalam beberapa hari.</p>
                </div>
            @endforelse
        </div>

        <a href="{{ route('marketplace.index') }}" class="sm:hidden mt-6 w-full inline-flex items-center justify-center min-h-[44px] bg-white border border-gray-300 text-ink font-semibold rounded-lg">
            Lihat Semua Event
        </a>
    </section>

    <!-- CTA Organisasi -->
    <section class="max-w-6xl mx-auto px-4 lg:px-8 pb-16 md:pb-20">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-primary to-brand-primary-dark p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-left">
                <h2 class="text-xl md:text-2xl font-extrabold text-white">Punya Event Sendiri?</h2>
                <p class="text-white/85 mt-2 max-w-md">Daftarkan organisasi atau institusimu, kelola event dan penjualan tiketmu sendiri lewat TiketIn.</p>
            </div>
            <a href="{{ route('organizations.register') }}" class="shrink-0 inline-flex items-center justify-center min-h-[44px] bg-brand-accent text-ink font-semibold px-6 py-3 rounded-lg shadow-soft hover:bg-brand-accent-dark active:scale-[0.98] transition-all">
                Daftarkan Organisasi
            </a>
        </div>
    </section>
@endsection
