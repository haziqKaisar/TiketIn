@extends('layouts.app')

@section('title', 'Dashboard Admin — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 md:py-10">

    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-ink">Dashboard Admin</h1>
            <p class="text-ink-muted text-sm mt-0.5">Ringkasan performa penjualan tiketmu</p>
        </div>

        <nav aria-label="Aksi cepat" class="flex flex-wrap gap-2">
            <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-2 min-h-[44px] bg-brand-primary text-white font-semibold px-4 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                Kelola Event
            </a>
            <a href="{{ route('scan.index') }}" class="inline-flex items-center gap-2 min-h-[44px] bg-ink text-white font-semibold px-4 py-2.5 rounded-lg hover:bg-gray-700 active:scale-[0.98] transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 0 1 2-2h1.5l1-1.5h9l1 1.5H20a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><circle cx="12" cy="13" r="3.5"/></svg>
                Buka Scanner
            </a>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 min-h-[44px] bg-white border border-gray-300 text-ink font-semibold px-4 py-2.5 rounded-lg hover:bg-gray-50 active:scale-[0.98] transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 8h6m-6 4h6"/></svg>
                Daftar Transaksi
            </a>
        </nav>
    </header>

    <section aria-label="Ringkasan statistik" class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-gray-100">
            <div class="flex items-center gap-3 mb-3">
                <span class="grid place-items-center w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.66 0-3 .9-3 2s1.34 2 3 2 3 .9 3 2-1.34 2-3 2m0-8v8m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2"/><circle cx="12" cy="12" r="9"/></svg>
                </span>
                <h3 class="text-ink-muted text-sm font-semibold">Total Pendapatan</h3>
            </div>
            <p class="text-3xl font-extrabold text-emerald-600 tracking-tight">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-soft border border-gray-100">
            <div class="flex items-center gap-3 mb-3">
                <span class="grid place-items-center w-10 h-10 rounded-xl bg-brand-primary-soft text-brand-primary" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z"/></svg>
                </span>
                <h3 class="text-ink-muted text-sm font-semibold">Tiket Terjual Lunas</h3>
            </div>
            <p class="text-3xl font-extrabold text-brand-primary tracking-tight">
                {{ $ticketsSold }} <span class="text-base text-ink-muted font-medium">lembar</span>
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-soft border border-gray-100">
            <div class="flex items-center gap-3 mb-3">
                <span class="grid place-items-center w-10 h-10 rounded-xl bg-sky-50 text-brand-secondary" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Zm7-4 2 2 4-4"/></svg>
                </span>
                <h3 class="text-ink-muted text-sm font-semibold">Pengunjung Hadir (Check-in)</h3>
            </div>
            <p class="text-3xl font-extrabold text-brand-secondary tracking-tight">
                {{ $checkedIn }} <span class="text-base text-ink-muted font-medium">orang</span>
            </p>
        </div>
    </section>
</div>
@endsection
