@extends('layouts.app')

@section('title', 'Cek Pesanan — TiketIn')

@section('content')
<div class="max-w-2xl mx-auto px-4 lg:px-8 py-10 md:py-14">

    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-gray-100 text-center">
        <div class="mx-auto w-12 h-12 rounded-full bg-brand-primary-soft text-brand-primary grid place-items-center mb-4" aria-hidden="true">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
        </div>

        @if(session('lookup_sent'))
            <h1 class="text-xl sm:text-2xl font-extrabold text-ink mb-2">Cek email kamu</h1>
            <p class="text-ink-muted text-sm max-w-sm mx-auto leading-relaxed">
                Kalau email itu terdaftar di sistem kami, kami sudah mengirim link untuk melihat tiketmu.
                Cek juga folder <span class="font-medium text-ink">Spam / Promosi</span> kalau belum masuk dalam beberapa menit.
                Link berlaku selama 30 menit.
            </p>
            <a href="{{ route('ticket.check') }}" class="inline-block mt-6 text-brand-primary font-semibold text-sm hover:underline min-h-[44px] leading-[44px]">
                Kirim ulang / pakai email lain
            </a>
        @else
            <h1 class="text-xl sm:text-2xl font-extrabold text-ink mb-1">Cari E-Ticket Kamu</h1>
            <p class="text-ink-muted text-sm mb-6">Masukkan email yang kamu pakai saat membeli tiket, kami akan kirim link untuk melihatnya</p>

            <form action="{{ route('ticket.search') }}" method="POST" class="text-left">
                @csrf
                <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Alamat email</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="pemesan@email.com"
                        required
                        class="w-full min-h-[44px] border border-gray-300 px-4 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors"
                    >
                    <button type="submit" class="min-h-[44px] bg-brand-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all shrink-0">
                        Kirim Link
                    </button>
                </div>
                @error('email')
                    <p class="text-rose-600 text-sm mt-2 text-left">{{ $message }}</p>
                @enderror
            </form>
        @endif
    </div>
</div>
@endsection
