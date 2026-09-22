@extends('layouts.app')

@section('title', ($event->name ?? 'Detail Event') . ' — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 md:py-12">

    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-brand-primary font-medium mb-6 hover:underline min-h-[44px]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
        Kembali ke Daftar Event
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8 items-start">

        <!-- Informasi Event -->
        <article class="lg:col-span-3 bg-white rounded-2xl shadow-soft border border-gray-100 overflow-hidden">
            <div class="h-36 sm:h-48 bg-gradient-to-br from-brand-secondary to-brand-primary relative" aria-hidden="true">
                <svg class="w-14 h-14 text-white/25 absolute bottom-4 right-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z"/><path d="M13 5v14" stroke-dasharray="2.5 2.5"/></svg>
            </div>

            <div class="p-6 md:p-8">
                <h1 class="text-2xl md:text-3xl font-extrabold text-ink leading-tight">{{ $event->name }}</h1>

                <div class="mt-4 flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-x-6 sm:gap-y-2 text-sm text-ink-muted pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-secondary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                        <span>{{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('l, d F Y · H:i') }} WIB</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-secondary shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.66 17.66 13.4 21.9a2 2 0 0 1-2.83 0l-4.24-4.24a8 8 0 1 1 11.31 0Z"/><circle cx="12" cy="11" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ $event->location }}</span>
                    </div>
                </div>

                <h2 class="text-lg font-bold text-ink mt-6 mb-2">Tentang Acara</h2>
                <p class="text-ink-muted leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
            </div>
        </article>

        <!-- Form Beli Tiket -->
        <aside class="lg:col-span-2 lg:sticky lg:top-24">
            <div
                x-data="{
                    selected: null,
                    quantity: 1,
                    categories: {{ \Illuminate\Support\Js::from($event->ticketCategories->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'price' => (int) $t->price, 'quota' => (int) $t->quota])->values()) }},
                    get chosen() { return this.categories.find(c => c.id === this.selected) },
                    get maxQty() { return this.chosen ? Math.min(this.chosen.quota, 10) : 1 },
                    formatRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID') },
                    decQty() { this.quantity = Math.max(1, this.quantity - 1) },
                    incQty() { this.quantity = Math.min(this.maxQty, this.quantity + 1) }
                }"
                x-init="$watch('selected', () => quantity = 1)"
                class="bg-white rounded-2xl shadow-soft-lg border border-gray-100 overflow-hidden"
            >
                <div class="bg-brand-primary px-6 py-5">
                    <h2 class="text-white font-bold text-lg">Beli Tiket</h2>
                    <p class="text-white/75 text-sm mt-0.5">Isi data dirimu dan pilih kategori tiket</p>
                </div>

                <form action="{{ route('checkout.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    @if(session('error'))
                        <div role="alert" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-3.5 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-semibold text-ink mb-1.5">Nama Lengkap</label>
                            <input id="customer_name" type="text" name="customer_name" required
                                class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors"
                                placeholder="Sesuai KTP">
                        </div>

                        <div>
                            <label for="customer_email" class="block text-sm font-semibold text-ink mb-1.5">Email Aktif</label>
                            <input id="customer_email" type="email" name="customer_email" required
                                class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors"
                                placeholder="E-tiket dikirim ke sini">
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-semibold text-ink mb-1.5">No. WhatsApp</label>
                            <input id="customer_phone" type="tel" inputmode="numeric" pattern="[0-9]*" name="customer_phone" required
                                class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors"
                                placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-semibold text-ink mb-2">Pilih Kategori Tiket</legend>
                        <div class="space-y-2">
                            @foreach($event->ticketCategories as $ticket)
                                @php $soldOut = $ticket->quota <= 0; @endphp
                                <label
                                    :class="selected === {{ $ticket->id }} ? 'border-brand-primary bg-brand-primary-soft ring-1 ring-brand-primary' : 'border-gray-200 hover:border-brand-secondary'"
                                    class="flex items-center justify-between gap-3 border rounded-xl px-4 py-3 min-h-[44px] transition-colors {{ $soldOut ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                                >
                                    <span class="flex items-center gap-3">
                                        <input type="radio" name="ticket_category_id" value="{{ $ticket->id }}"
                                            x-model.number="selected" {{ $soldOut ? 'disabled' : 'required' }}
                                            class="w-4 h-4 accent-brand-primary shrink-0">
                                        <span>
                                            <span class="block font-semibold text-ink text-sm">{{ $ticket->name }}</span>
                                            <span class="block text-xs text-ink-muted mt-0.5">
                                                {{ $soldOut ? 'Habis' : 'Sisa ' . $ticket->quota . ' tiket' }}
                                            </span>
                                        </span>
                                    </span>
                                    <span class="font-bold text-brand-primary text-sm whitespace-nowrap">
                                        Rp {{ number_format($ticket->price, 0, ',', '.') }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <!-- Jumlah tiket, muncul setelah kategori dipilih -->
                    <div x-show="chosen" x-cloak>
                        <label for="quantity" class="block text-sm font-semibold text-ink mb-2">Jumlah Tiket</label>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="decQty()" :disabled="quantity <= 1"
                                class="w-11 h-11 shrink-0 grid place-items-center border border-gray-300 rounded-lg text-ink font-bold text-lg hover:bg-gray-50 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                                aria-label="Kurangi jumlah tiket">
                                &minus;
                            </button>

                            <input id="quantity" type="number" name="quantity" value="1"
                                x-model.number="quantity" min="1" :max="maxQty"
                                class="w-16 min-h-[44px] text-center border border-gray-300 rounded-lg font-bold text-ink focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">

                            <button type="button" @click="incQty()" :disabled="quantity >= maxQty"
                                class="w-11 h-11 shrink-0 grid place-items-center border border-gray-300 rounded-lg text-ink font-bold text-lg hover:bg-gray-50 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                                aria-label="Tambah jumlah tiket">
                                +
                            </button>

                            <span class="text-xs text-ink-muted" x-text="'Maks ' + maxQty + ' tiket/transaksi'"></span>
                        </div>
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-semibold text-ink mb-2">Metode Pembayaran</legend>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 border border-gray-200 has-[:checked]:border-brand-primary has-[:checked]:bg-brand-primary-soft rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" value="QRIS" checked class="w-4 h-4 accent-brand-primary">
                                <span class="text-sm font-medium text-ink">QRIS</span>
                            </label>
                            <label class="flex items-center gap-2 border border-gray-200 has-[:checked]:border-brand-primary has-[:checked]:bg-brand-primary-soft rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" value="BRIVA" class="w-4 h-4 accent-brand-primary">
                                <span class="text-sm font-medium text-ink">VA BRI</span>
                            </label>
                            <label class="flex items-center gap-2 border border-gray-200 has-[:checked]:border-brand-primary has-[:checked]:bg-brand-primary-soft rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" value="BCAVA" class="w-4 h-4 accent-brand-primary">
                                <span class="text-sm font-medium text-ink">VA BCA</span>
                            </label>
                            <label class="flex items-center gap-2 border border-gray-200 has-[:checked]:border-brand-primary has-[:checked]:bg-brand-primary-soft rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors">
                                <input type="radio" name="payment_method" value="MANDIRIVA" class="w-4 h-4 accent-brand-primary">
                                <span class="text-sm font-medium text-ink">VA Mandiri</span>
                            </label>
                        </div>
                    </fieldset>

                    <!-- Ringkasan singkat, muncul setelah kategori dipilih -->
                    <div x-show="chosen" x-cloak x-transition.opacity.duration.150ms class="pt-4 border-t border-dashed border-gray-200 text-sm space-y-1">
                        <div class="flex items-center justify-between text-ink-muted">
                            <span x-text="chosen ? chosen.name + ' × ' + quantity : ''"></span>
                            <span x-text="chosen ? formatRp(chosen.price) + '/tiket' : ''"></span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-ink text-base">
                            <span>Total</span>
                            <span x-text="chosen ? formatRp(chosen.price * quantity) : ''"></span>
                        </div>
                    </div>

                    <button type="submit" class="w-full min-h-[48px] bg-brand-accent text-ink font-bold py-3 rounded-lg hover:bg-brand-accent-dark active:scale-[0.99] transition-all shadow-soft">
                        Lanjut Pembayaran
                    </button>
                    <p class="text-xs text-ink-muted text-center -mt-2">Data kamu aman &amp; hanya dipakai untuk proses tiket.</p>
                </form>
            </div>
        </aside>
    </div>
</div>
@endsection
