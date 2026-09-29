@extends('layouts.app')

@section('title', 'Pengajuan Penarikan — TiketIn')

@section('content')
<div class="max-w-5xl mx-auto px-4 lg:px-8 py-8 md:py-10" x-data="{ rejectingId: null }">

    <h1 class="text-2xl font-extrabold text-ink mb-1">Pengajuan Penarikan Saldo</h1>
    <p class="text-ink-muted text-sm mb-6">Transfer manual ke rekening yang tertera, lalu tandai selesai di sini.</p>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div role="alert" class="flex items-center gap-2 bg-rose-50 text-rose-700 border border-rose-200 p-3.5 rounded-xl mb-5 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tab status -->
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach(['pending' => 'Menunggu', 'completed' => 'Selesai', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $key => $label)
            <a href="{{ route('superadmin.withdrawals.index', ['status' => $key]) }}"
                class="min-h-[40px] px-4 flex items-center rounded-lg text-sm font-semibold transition-colors {{ $status === $key ? 'bg-brand-primary text-white' : 'bg-white border border-gray-300 text-ink hover:bg-gray-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="flex flex-col gap-4">
        @forelse($withdrawals as $wd)
            <article class="bg-white rounded-2xl shadow-soft border border-gray-100 p-5">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h2 class="font-bold text-ink text-lg">Rp {{ number_format($wd->amount, 0, ',', '.') }}</h2>
                            @php
                                $badge = match($wd->status) {
                                    'completed' => ['bg-emerald-50', 'text-emerald-700', 'Selesai'],
                                    'rejected' => ['bg-rose-50', 'text-rose-700', 'Ditolak'],
                                    default => ['bg-amber-50', 'text-amber-700', 'Menunggu'],
                                };
                            @endphp
                            <span class="{{ $badge[0] }} {{ $badge[1] }} text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $badge[2] }}</span>
                        </div>
                        <p class="text-sm font-semibold text-ink">{{ $wd->organization->name ?? 'Organisasi tidak ditemukan' }}</p>
                        <p class="text-sm text-ink-muted mt-1 font-mono">{{ $wd->bank_name }} — {{ $wd->bank_account_number }}</p>
                        <p class="text-sm text-ink-muted">a.n. {{ $wd->bank_account_name }}</p>
                        <p class="text-xs text-ink-muted mt-2">Diajukan {{ $wd->created_at->translatedFormat('d M Y, H:i') }}</p>
                        @if($wd->status === 'rejected' && $wd->admin_note)
                            <p class="text-xs text-rose-600 bg-rose-50 rounded-lg px-2.5 py-1.5 mt-2">Alasan: {{ $wd->admin_note }}</p>
                        @endif
                    </div>

                    @if($wd->status === 'pending')
                        <div class="flex gap-2 shrink-0">
                            <form action="{{ route('superadmin.withdrawals.complete', $wd->id) }}" method="POST" onsubmit="return confirm('Konfirmasi: sudah beneran transfer ke rekening ini?');">
                                @csrf
                                <button type="submit" class="min-h-[40px] px-4 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 active:scale-[0.97] transition-all">
                                    Tandai Selesai
                                </button>
                            </form>
                            <button type="button" @click="rejectingId = rejectingId === {{ $wd->id }} ? null : {{ $wd->id }}"
                                class="min-h-[40px] px-4 bg-white border border-gray-300 text-ink rounded-lg text-sm font-bold hover:bg-gray-50 transition-colors">
                                Tolak
                            </button>
                        </div>
                    @endif
                </div>

                <div x-show="rejectingId === {{ $wd->id }}" x-cloak class="mt-4 pt-4 border-t border-dashed border-gray-200">
                    <form action="{{ route('superadmin.withdrawals.reject', $wd->id) }}" method="POST" class="flex flex-col sm:flex-row gap-2">
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
                Tidak ada pengajuan di status ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
