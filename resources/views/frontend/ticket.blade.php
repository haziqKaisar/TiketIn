@extends('layouts.app')

@section('title', 'E-Ticket — TiketIn')

@section('content')
<div class="max-w-3xl mx-auto px-4 lg:px-8 py-8 md:py-12">
    <div class="text-center mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-ink">E-Ticket Kamu</h1>
        <p class="text-ink-muted mt-2">Tunjukkan QR code ini kepada panitia di gerbang masuk</p>
    </div>

    @foreach($order->tickets as $ticket)
        <article class="bg-white rounded-2xl shadow-soft-lg border border-gray-200 overflow-hidden mb-6 flex flex-col md:flex-row"
                 style="break-inside: avoid; page-break-inside: avoid;">

            <!-- Info Event -->
            <div class="bg-brand-primary text-white p-6 md:w-2/3 flex flex-col justify-center relative">
                <span class="inline-block px-3 py-1 bg-white/15 rounded-full text-xs font-semibold mb-3 w-max">
                    Kategori {{ $ticket->ticketCategory->name }}
                </span>
                <h2 class="text-xl md:text-2xl font-extrabold mb-2 leading-snug">{{ $ticket->ticketCategory->event->name }}</h2>

                <div class="mt-4 space-y-2 text-sm text-white/85">
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                        {{ \Carbon\Carbon::parse($ticket->ticketCategory->event->event_date)->translatedFormat('l, d F Y · H:i') }} WIB
                    </p>
                    <p class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.66 17.66 13.4 21.9a2 2 0 0 1-2.83 0l-4.24-4.24a8 8 0 1 1 11.31 0Z"/><circle cx="12" cy="11" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ $ticket->ticketCategory->event->location }}
                    </p>
                    <p class="flex items-center gap-2 pt-3 mt-1 border-t border-white/20">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z"/></svg>
                        Atas nama <span class="font-bold text-white">{{ $order->customer_name }}</span>
                    </p>
                </div>
            </div>

            <!-- Perforasi -->
            <div class="hidden md:block relative w-0 border-l-2 border-dashed border-gray-300">
                <div class="w-6 h-6 bg-[#F7F8FC] rounded-full absolute -top-3 -left-[13px]" aria-hidden="true"></div>
                <div class="w-6 h-6 bg-[#F7F8FC] rounded-full absolute -bottom-3 -left-[13px]" aria-hidden="true"></div>
            </div>
            <div class="md:hidden border-t-2 border-dashed border-gray-300 mx-6"></div>

            <!-- QR Code -->
            <div class="p-6 md:w-1/3 flex flex-col items-center justify-center bg-white">
                <div class="bg-white p-2 rounded-lg border-2 border-gray-100">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->generate($ticket->ticket_code) !!}
                </div>

                <p class="mt-3 text-xs text-ink-muted font-mono bg-gray-100 px-2.5 py-1 rounded tracking-wide">
                    {{ $ticket->ticket_code }}
                </p>

                @if($ticket->status == 'AVAILABLE')
                    <span class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-50 text-emerald-700 text-sm font-bold rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                        Valid
                    </span>
                @else
                    <span class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-rose-50 text-rose-700 text-sm font-bold rounded-full">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        Sudah Terpakai
                    </span>
                @endif
            </div>
        </article>
    @endforeach

    <!-- Tombol Bantuan -->
    <div class="no-print text-center mt-8 flex flex-wrap justify-center gap-3">
        <button onclick="window.print()" class="inline-flex items-center gap-2 min-h-[44px] bg-ink text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-gray-700 active:scale-[0.98] transition-all shadow-soft">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V4h12v5M6 18H4a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-2M6 14h12v7H6v-7Z"/></svg>
            Cetak / Simpan PDF
        </button>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 min-h-[44px] bg-white border border-gray-300 text-ink px-5 py-2.5 rounded-lg font-semibold hover:bg-gray-50 active:scale-[0.98] transition-all shadow-soft">
            Kembali ke Beranda
        </a>
    </div>
</div>
@endsection
