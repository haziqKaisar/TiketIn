@extends('layouts.app')

@section('title', 'Kelola Event — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 md:py-10">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-extrabold text-ink">Kelola Event</h1>
        <a href="{{ route('admin.events.create') }}" class="inline-flex items-center justify-center gap-2 min-h-[44px] bg-brand-primary text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Event
        </a>
    </div>

    <!-- Tampilan tabel (tablet ke atas) -->
    <div class="hidden md:block bg-white rounded-2xl shadow-soft border border-gray-100 overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-ink-muted">
                    <th class="py-3.5 px-6 font-semibold">Nama Event</th>
                    <th class="py-3.5 px-6 font-semibold">Tanggal</th>
                    <th class="py-3.5 px-6 font-semibold">Status</th>
                    <th class="py-3.5 px-6 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/70">
                    <td class="py-3.5 px-6 font-semibold text-ink">{{ $event->name }}</td>
                    <td class="py-3.5 px-6 text-ink-muted">{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y · H:i') }}</td>
                    <td class="py-3.5 px-6">
                        <span class="{{ $event->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} py-1 px-3 rounded-full text-xs font-bold">
                            {{ $event->is_active ? 'Aktif' : 'Tutup' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-6">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.events.edit', $event->id) }}" title="Edit event" class="inline-flex items-center justify-center min-h-[36px] min-w-[36px] bg-white border border-gray-300 text-ink rounded-lg hover:bg-gray-50 transition-colors px-3 text-xs font-semibold">
                                Edit
                            </a>
                            <a href="{{ route('admin.categories.index', $event->id) }}" class="inline-flex items-center justify-center min-h-[36px] bg-brand-primary text-white rounded-lg hover:bg-brand-primary-dark transition-colors px-3 text-xs font-semibold">
                                Atur Tiket
                            </a>
                            <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Yakin hapus event ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" aria-label="Hapus {{ $event->name }}" title="Hapus event" class="inline-flex items-center justify-center min-h-[36px] min-w-[36px] text-ink-muted hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors px-2.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 12.1a2 2 0 0 1-2 1.9H9.7a2 2 0 0 1-2-1.9L7 7h10Z"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-8 text-center text-ink-muted">Belum ada event.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Tampilan kartu (mobile) -->
    <div class="md:hidden flex flex-col gap-3">
        @forelse($events as $event)
        <article class="bg-white rounded-2xl shadow-soft border border-gray-100 p-4">
            <div class="flex items-start justify-between gap-3 mb-1">
                <h2 class="font-semibold text-ink">{{ $event->name }}</h2>
                <span class="{{ $event->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }} py-1 px-3 rounded-full text-xs font-bold shrink-0">
                    {{ $event->is_active ? 'Aktif' : 'Tutup' }}
                </span>
            </div>
            <p class="text-sm text-ink-muted mb-3">{{ \Carbon\Carbon::parse($event->event_date)->format('d M Y · H:i') }}</p>

            <div class="flex items-center gap-2 pt-3 border-t border-dashed border-gray-200">
                <a href="{{ route('admin.events.edit', $event->id) }}" class="flex-1 text-center min-h-[40px] flex items-center justify-center bg-white border border-gray-300 text-ink rounded-lg text-sm font-semibold">Edit</a>
                <a href="{{ route('admin.categories.index', $event->id) }}" class="flex-1 text-center min-h-[40px] flex items-center justify-center bg-brand-primary text-white rounded-lg text-sm font-semibold">Atur Tiket</a>
                <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Yakin hapus event ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" aria-label="Hapus {{ $event->name }}" class="min-h-[40px] min-w-[40px] flex items-center justify-center text-ink-muted hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 12.1a2 2 0 0 1-2 1.9H9.7a2 2 0 0 1-2-1.9L7 7h10Z"/></svg>
                    </button>
                </form>
            </div>
        </article>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-8 text-center text-ink-muted">
            Belum ada event.
        </div>
        @endforelse
    </div>
</div>
@endsection
