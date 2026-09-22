@extends('layouts.app')

@section('title', 'Riwayat Transaksi — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 md:py-10">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-extrabold text-ink">Riwayat Transaksi</h1>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-brand-primary font-medium hover:underline min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
            Kembali ke Dashboard
        </a>
    </div>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Tampilan tabel (tablet ke atas) -->
    <div class="hidden md:block bg-white rounded-2xl shadow-soft border border-gray-100 overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-ink-muted">
                    <th class="p-4 font-semibold">Ref Invoice</th>
                    <th class="p-4 font-semibold">Pembeli</th>
                    <th class="p-4 font-semibold">Event &amp; Tiket</th>
                    <th class="p-4 font-semibold">Total</th>
                    <th class="p-4 font-semibold">Status</th>
                    <th class="p-4 font-semibold text-center">Aksi Manual</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/70">
                    <td class="p-4 font-mono font-bold text-ink">{{ $order->merchant_ref }}</td>
                    <td class="p-4">
                        <p class="font-semibold text-ink">{{ $order->customer_name }}</p>
                        <p class="text-xs text-ink-muted">{{ $order->customer_email }}</p>
                    </td>
                    <td class="p-4">
                        @if($order->tickets->isNotEmpty())
                            <p class="font-semibold text-ink">{{ $order->tickets->first()->ticketCategory->event->name }}</p>
                            <p class="text-xs text-ink-muted">{{ $order->tickets->count() }}x {{ $order->tickets->first()->ticketCategory->name }}</p>
                        @endif
                    </td>
                    <td class="p-4 font-bold text-ink">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    <td class="p-4">
                        @if($order->status === 'PAID')
                            <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">LUNAS</span>
                        @else
                            <span class="bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ $order->status }}</span>
                        @endif
                    </td>
                    <td class="p-4 text-center">
                        @if($order->status === 'UNPAID')
                            <form action="{{ route('admin.orders.markPaid', $order->id) }}" method="POST" onsubmit="return confirm('Tandai pesanan ini sebagai LUNAS?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 min-h-[36px] bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-700 active:scale-[0.97] transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                    Set Lunas
                                </button>
                            </form>
                        @else
                            <a href="{{ route('ticket.show', $order->merchant_ref) }}" class="text-brand-primary text-xs font-bold hover:underline">
                                Lihat Tiket
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-8 text-center text-ink-muted">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Tampilan kartu (mobile) -->
    <div class="md:hidden flex flex-col gap-3">
        @forelse($orders as $order)
        <article class="bg-white rounded-2xl shadow-soft border border-gray-100 p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <span class="font-mono text-xs font-bold bg-gray-100 text-ink px-2 py-1 rounded">{{ $order->merchant_ref }}</span>
                @if($order->status === 'PAID')
                    <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full shrink-0">LUNAS</span>
                @else
                    <span class="bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full shrink-0">{{ $order->status }}</span>
                @endif
            </div>

            <p class="font-semibold text-ink">{{ $order->customer_name }}</p>
            <p class="text-xs text-ink-muted mb-2">{{ $order->customer_email }}</p>

            @if($order->tickets->isNotEmpty())
                <p class="text-sm text-ink">{{ $order->tickets->first()->ticketCategory->event->name }}</p>
                <p class="text-xs text-ink-muted">{{ $order->tickets->count() }}x {{ $order->tickets->first()->ticketCategory->name }}</p>
            @endif

            <div class="flex items-center justify-between mt-3 pt-3 border-t border-dashed border-gray-200">
                <span class="font-bold text-ink">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>

                @if($order->status === 'UNPAID')
                    <form action="{{ route('admin.orders.markPaid', $order->id) }}" method="POST" onsubmit="return confirm('Tandai pesanan ini sebagai LUNAS?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 min-h-[36px] bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-emerald-700 active:scale-[0.97] transition-all">
                            Set Lunas
                        </button>
                    </form>
                @else
                    <a href="{{ route('ticket.show', $order->merchant_ref) }}" class="text-brand-primary text-xs font-bold hover:underline">
                        Lihat Tiket
                    </a>
                @endif
            </div>
        </article>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-8 text-center text-ink-muted">
            Belum ada transaksi.
        </div>
        @endforelse
    </div>
</div>
@endsection
