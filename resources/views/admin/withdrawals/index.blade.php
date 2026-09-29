@extends('layouts.app')

@section('title', 'Penarikan Saldo — TiketIn')

@section('content')
<div class="max-w-3xl mx-auto px-4 lg:px-8 py-8 md:py-10">
    <h1 class="text-2xl font-extrabold text-ink mb-1">Penarikan Saldo</h1>
    <p class="text-ink-muted text-sm mb-6">Ajukan pencairan hasil penjualan tiket organisasimu.</p>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div role="alert" class="flex items-center gap-2 bg-rose-50 text-rose-700 border border-rose-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- Kartu Saldo -->
    <div class="bg-gradient-to-br from-brand-primary to-brand-primary-dark rounded-2xl p-6 md:p-7 mb-6 text-white shadow-soft-lg">
        <p class="text-white/75 text-sm">Saldo Tersedia</p>
        <p class="text-3xl md:text-4xl font-extrabold mt-1">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>
        <p class="text-white/60 text-xs mt-2">Sudah dikurangi pengajuan yang masih diproses.</p>
    </div>

    @if($hasPending)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>
            <div>
                <p class="font-semibold text-ink">Ada pengajuan yang masih diproses</p>
                <p class="text-sm text-ink-muted mt-0.5">Tunggu pengajuan sebelumnya selesai diproses admin TiketIn sebelum mengajukan lagi.</p>
            </div>
        </div>
    @else
        <!-- Form Pengajuan -->
        <div class="bg-white p-6 sm:p-7 rounded-2xl shadow-soft border border-gray-100 mb-8">
            <h2 class="font-bold text-ink mb-4">Ajukan Penarikan</h2>
            <form action="{{ route('admin.withdrawals.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="amount" class="block text-sm font-semibold text-ink mb-1.5">Jumlah Penarikan (Rp)</label>
                    <input id="amount" type="number" name="amount" min="50000" max="{{ $availableBalance }}" value="{{ old('amount') }}" required
                        placeholder="Minimal Rp 50.000"
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="bank_name" class="block text-sm font-semibold text-ink mb-1.5">Nama Bank</label>
                        <input id="bank_name" type="text" name="bank_name" value="{{ old('bank_name') }}" required
                            placeholder="mis. BCA, BRI, Mandiri"
                            class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                    </div>
                    <div>
                        <label for="bank_account_number" class="block text-sm font-semibold text-ink mb-1.5">Nomor Rekening</label>
                        <input id="bank_account_number" type="text" inputmode="numeric" name="bank_account_number" value="{{ old('bank_account_number') }}" required
                            class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                    </div>
                </div>

                <div>
                    <label for="bank_account_name" class="block text-sm font-semibold text-ink mb-1.5">Nama Pemilik Rekening</label>
                    <input id="bank_account_name" type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" required
                        placeholder="Sesuai buku tabungan/rekening"
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>

                <button type="submit" {{ $availableBalance < 50000 ? 'disabled' : '' }}
                    class="w-full min-h-[44px] bg-brand-primary text-white font-bold py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    Ajukan Penarikan
                </button>
                @if($availableBalance < 50000)
                    <p class="text-xs text-ink-muted text-center">Saldo minimal Rp 50.000 untuk bisa mengajukan penarikan.</p>
                @endif
            </form>
        </div>
    @endif

    <!-- Riwayat -->
    <h2 class="font-bold text-ink mb-3">Riwayat Pengajuan</h2>
    <div class="flex flex-col gap-3">
        @forelse($requests as $req)
            @php
                $badge = match($req->status) {
                    'completed' => ['bg-emerald-50', 'text-emerald-700', 'Selesai'],
                    'rejected' => ['bg-rose-50', 'text-rose-700', 'Ditolak'],
                    default => ['bg-amber-50', 'text-amber-700', 'Diproses'],
                };
            @endphp
            <div class="bg-white rounded-xl border border-gray-100 p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-ink">Rp {{ number_format($req->amount, 0, ',', '.') }}</p>
                    <p class="text-xs text-ink-muted mt-0.5">{{ $req->bank_name }} — {{ $req->bank_account_number }} a.n. {{ $req->bank_account_name }}</p>
                    <p class="text-xs text-ink-muted">{{ $req->created_at->translatedFormat('d M Y, H:i') }}</p>
                    @if($req->status === 'rejected' && $req->admin_note)
                        <p class="text-xs text-rose-600 bg-rose-50 rounded-lg px-2.5 py-1.5 mt-2">Alasan: {{ $req->admin_note }}</p>
                    @endif
                </div>
                <span class="{{ $badge[0] }} {{ $badge[1] }} text-xs font-bold px-2.5 py-1 rounded-full shrink-0">{{ $badge[2] }}</span>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-8 text-center text-ink-muted text-sm">
                Belum ada riwayat pengajuan penarikan.
            </div>
        @endforelse
    </div>
</div>
@endsection
