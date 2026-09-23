@extends('layouts.app')

@section('title', 'Kelola Organisasi — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 md:py-10" x-data="{ rejectingId: null }">

    <h1 class="text-2xl font-extrabold text-ink mb-1">Kelola Organisasi</h1>
    <p class="text-ink-muted text-sm mb-6">Tinjau organisasi yang mendaftar sebelum mereka bisa mulai jualan tiket.</p>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tab status -->
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $key => $label)
            <a href="{{ route('superadmin.organizations.index', ['status' => $key]) }}"
                class="min-h-[40px] px-4 flex items-center rounded-lg text-sm font-semibold transition-colors {{ $status === $key ? 'bg-brand-primary text-white' : 'bg-white border border-gray-300 text-ink hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="flex flex-col gap-4">
        @forelse($organizations as $org)
            <article class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h2 class="font-bold text-ink">{{ $org->name }}</h2>
                            @php
                                $badge = match($org->status) {
                                    'approved' => ['bg-emerald-50', 'text-emerald-700', 'Disetujui'],
                                    'pending' => ['bg-amber-50', 'text-amber-700', 'Menunggu'],
                                    'rejected' => ['bg-rose-50', 'text-rose-700', 'Ditolak'],
                                    default => ['bg-gray-100', 'text-ink-muted', 'Nonaktif'],
                                };
                            @endphp
                            <span class="{{ $badge[0] }} {{ $badge[1] }} text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $badge[2] }}</span>
                        </div>
                        <p class="text-sm text-ink-muted">{{ $org->email }} @if($org->phone) &middot; {{ $org->phone }} @endif</p>
                        @if($org->description)
                            <p class="text-sm text-ink-muted mt-2">{{ $org->description }}</p>
                        @endif
                        @if($org->status === 'rejected' && $org->rejected_reason)
                            <p class="text-xs text-rose-600 bg-rose-50 rounded-lg px-3 py-2 mt-2">Alasan: {{ $org->rejected_reason }}</p>
                        @endif
                    </div>

                    @if($org->status === 'pending')
                        <div class="flex gap-2 shrink-0">
                            <form action="{{ route('superadmin.organizations.approve', $org->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="min-h-[40px] px-4 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 active:scale-[0.97] transition-all">
                                    Setujui
                                </button>
                            </form>
                            <button type="button" @click="rejectingId = rejectingId === {{ $org->id }} ? null : {{ $org->id }}"
                                class="min-h-[40px] px-4 bg-white border border-gray-300 text-ink rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">
                                Tolak
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Form alasan penolakan, muncul saat "Tolak" diklik -->
                <div x-show="rejectingId === {{ $org->id }}" x-cloak class="mt-4 pt-4 border-t border-dashed border-gray-200">
                    <form action="{{ route('superadmin.organizations.reject', $org->id) }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <input type="text" name="reason" placeholder="Alasan penolakan (opsional)"
                            class="w-full min-h-[40px] border border-gray-300 px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20">
                        <button type="submit" class="min-h-[40px] px-4 bg-rose-600 text-white rounded-lg text-sm font-bold hover:bg-rose-700 shrink-0 transition-colors">
                            Kirim Penolakan
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center text-ink-muted">
                Tidak ada organisasi di status ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
