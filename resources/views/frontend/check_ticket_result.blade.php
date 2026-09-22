@extends('layouts.app')

@section('title', 'Tiket Kamu — TiketIn')

@section('content')
<div class="max-w-2xl mx-auto px-4 lg:px-8 py-10 md:py-14">

    <div class="flex items-center justify-between gap-3 mb-4">
        <h1 class="text-base font-bold text-ink truncate">
            Pesanan untuk <span class="text-brand-primary">{{ $email }}</span>
        </h1>
        <a href="{{ route('ticket.check') }}" class="text-sm text-brand-primary font-medium hover:underline shrink-0">Cari email lain</a>
    </div>

    <div class="flex flex-col gap-4">
        @forelse($orders as $order)
            @php
                $statusStyle = match($order->status) {
                    'PAID' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Lunas'],
                    'UNPAID' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'label' => 'Belum Bayar'],
                    default => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'label' => $order->status],
                };
            @endphp
            <article class="flex bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">

                <!-- Stub status -->
                <div class="w-20 sm:w-24 shrink-0 {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} flex flex-col items-center justify-center py-4 px-2 text-center">
                    @if($order->status === 'PAID')
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                    @elseif($order->status === 'UNPAID')
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>
                    @else
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    @endif
                    <span class="text-[11px] font-bold leading-tight">{{ $statusStyle['label'] }}</span>
                </div>

                <div class="ticket-perforation w-px shrink-0 hidden sm:block" aria-hidden="true"></div>

                <!-- Detail order -->
                <div class="flex-1 p-5 flex flex-col sm:flex-row sm:items-center gap-4 justify-between min-w-0">
                    <div class="min-w-0">
                        <span class="inline-block font-mono text-xs font-bold bg-gray-100 text-ink-muted px-2 py-0.5 rounded mb-1.5">
                            {{ $order->merchant_ref }}
                        </span>

                        @if($order->tickets->isNotEmpty())
                            <h3 class="text-base font-bold text-ink truncate">
                                {{ $order->tickets->first()->ticketCategory->event->name }}
                            </h3>
                            <p class="text-sm text-ink-muted">
                                {{ $order->tickets->count() }}x {{ $order->tickets->first()->ticketCategory->name }}
                            </p>
                        @endif
                        <p class="text-sm text-ink-muted mt-1">
                            Total <span class="font-semibold text-ink">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </p>
                    </div>

                    <div class="shrink-0">
                        @if($order->status === 'PAID')
                            <a href="{{ route('ticket.show', $order->merchant_ref) }}" class="inline-flex items-center justify-center min-h-[44px] bg-brand-primary text-white font-semibold px-4 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all text-sm w-full sm:w-auto">
                                Lihat E-Ticket
                            </a>
                        @elseif($order->status === 'UNPAID' && $order->checkout_url)
                            <a href="{{ $order->checkout_url }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center min-h-[44px] bg-brand-accent text-ink font-semibold px-4 py-2.5 rounded-lg hover:bg-brand-accent-dark active:scale-[0.98] transition-all text-sm w-full sm:w-auto">
                                Lanjut Bayar
                            </a>
                        @else
                            <span class="text-xs text-ink-muted">Tidak tersedia</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center">
                <p class="text-ink font-semibold">Tidak ada pesanan ditemukan</p>
                <p class="text-ink-muted text-sm mt-1">Link ini mungkin sudah kedaluwarsa. Coba cari lagi lewat halaman cek tiket.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
