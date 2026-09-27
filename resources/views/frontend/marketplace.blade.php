@extends('layouts.app')

@section('title', 'Jelajahi Event — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-10 md:py-14">

    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-ink mb-1">Jelajahi Event</h1>
        <p class="text-ink-muted">Temukan acara yang cocok buat kamu, dari berbagai penyelenggara.</p>
    </div>

    <!-- Pencarian -->
    <form action="{{ route('marketplace.index') }}" method="GET" class="mb-8">
        <div class="relative">
            <svg class="w-5 h-5 text-ink-muted absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m2.2-5.3a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
            <label for="q" class="sr-only">Cari event</label>
            <input
                id="q" type="search" name="q" value="{{ $q ?? '' }}"
                placeholder="Cari nama event atau lokasi..."
                class="w-full min-h-[48px] border border-gray-300 pl-12 pr-28 py-3 rounded-xl focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors bg-white shadow-soft"
            >
            <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-[40px] px-5 bg-brand-primary text-white font-semibold rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all text-sm">
                Cari
            </button>
        </div>
    </form>

    @if(!empty($q))
        <p class="text-sm text-ink-muted mb-4">
            {{ $events->total() }} hasil untuk "<span class="font-semibold text-ink">{{ $q }}</span>"
            <a href="{{ route('marketplace.index') }}" class="text-brand-primary hover:underline ml-1">(hapus pencarian)</a>
        </p>
    @endif

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
                @if(!empty($q))
                    <p class="text-ink font-semibold">Tidak ada event untuk "{{ $q }}"</p>
                    <p class="text-ink-muted text-sm mt-1">Coba kata kunci lain, atau lihat semua event.</p>
                @else
                    <p class="text-ink font-semibold">Belum ada event mendatang</p>
                    <p class="text-ink-muted text-sm mt-1">Coba cek lagi dalam beberapa hari.</p>
                @endif
            </div>
        @endforelse
    </div>

    @if($events->hasPages())
        <div class="mt-8">
            {{ $events->links() }}
        </div>
    @endif
</div>
@endsection
