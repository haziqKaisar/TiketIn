@extends('layouts.app')

@section('title', ($event->name ?? 'Detail Event') . ' — TiketIn')

@section('content')
@php $seatsData = app(\App\Services\SeatMapService::class)->forEvent($event); @endphp
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
                @if($event->organization)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary bg-brand-primary-soft px-2.5 py-1 rounded-full mb-3">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 9h6v12"/></svg>
                        Diselenggarakan oleh {{ $event->organization->name }}
                    </span>
                @endif
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

                @if($event->ticketCategories->isNotEmpty())
                    <h2 class="text-lg font-bold text-ink mt-8 mb-3">Detail Kategori Tiket</h2>
                    <div class="space-y-3">
                        @foreach($event->ticketCategories as $category)
                            <div class="border border-gray-100 rounded-xl p-4">
                                <div class="flex items-center justify-between gap-3 mb-1">
                                    <h3 class="font-bold text-ink">
                                        {{ $category->name }}
                                        @if($category->seating_mode === 'seated')
                                            <span class="ml-1 align-middle bg-brand-primary-soft text-brand-primary text-[11px] font-bold px-2 py-0.5 rounded-full">Pilih kursi sendiri</span>
                                        @endif
                                    </h3>
                                    <span class="text-sm font-bold text-brand-primary shrink-0">Rp {{ number_format($category->price, 0, ',', '.') }}</span>
                                </div>
                                @if($category->description)
                                    <p class="text-sm text-ink-muted leading-relaxed whitespace-pre-line">{{ $category->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>

        <!-- Form Beli Tiket -->
        <aside class="lg:col-span-2 lg:sticky lg:top-24">
            <div
                x-data="{
                    selected: null,
                    quantity: 1,
                    pickedSeatIds: [],
                    mapOpen: false,
                    seatNotice: '',
                    categories: {{ \Illuminate\Support\Js::from($event->ticketCategories->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'price' => (int) $t->price, 'quota' => (int) $t->quota, 'seated' => $t->seating_mode === 'seated'])->values()) }},
                    seats: {{ \Illuminate\Support\Js::from($seatsData) }},
                    get chosen() { return this.categories.find(c => c.id === this.selected) },
                    get isSeated() { return !!(this.chosen && this.chosen.seated) },
                    get seatsOfChosen() { return this.isSeated ? this.seats.filter(s => s.category_id === this.chosen.id) : [] },
                    get availableCount() { return this.seatsOfChosen.filter(s => !s.taken).length },
                    get qty() { return this.isSeated ? this.pickedSeatIds.length : this.quantity },
                    get pickedLabels() { return this.seats.filter(s => this.pickedSeatIds.includes(s.id)).map(s => s.label) },
                    get maxQty() { return this.chosen ? Math.min(this.chosen.quota, 10) : 1 },
                    isPicked(id) { return this.pickedSeatIds.includes(id) },
                    toggleSeat(seat) {
                        if (seat.taken) return;
                        if (this.isPicked(seat.id)) {
                            this.pickedSeatIds = this.pickedSeatIds.filter(i => i !== seat.id);
                            this.seatNotice = '';
                            return;
                        }
                        if (this.pickedSeatIds.length >= 10) {
                            this.seatNotice = 'Maksimal 10 kursi per transaksi.';
                            return;
                        }
                        this.pickedSeatIds.push(seat.id);
                        this.seatNotice = '';
                    },
                    formatRp(n) { return 'Rp ' + Number(n).toLocaleString('id-ID') },
                    decQty() { this.quantity = Math.max(1, this.quantity - 1) },
                    incQty() { this.quantity = Math.min(this.maxQty, this.quantity + 1) }
                }"
                x-init="$watch('selected', () => { quantity = 1; pickedSeatIds = []; seatNotice = ''; mapOpen = false })"
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
                                                {{ $soldOut ? 'Habis' : 'Sisa ' . $ticket->quota . ($ticket->seating_mode === 'seated' ? ' kursi · pilih di denah' : ' tiket') }}
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

                    <!-- Pemilih kursi: muncul untuk kategori bermode "Pilih kursi" -->
                    <div x-show="isSeated" x-cloak
                        :class="mapOpen ? 'fixed inset-0 z-[60] bg-ink/70 p-4 sm:p-8 overflow-auto' : ''"
                        @keydown.escape.window="mapOpen = false"
                        x-effect="document.body.classList.toggle('overflow-hidden', mapOpen)">
                        <div :class="mapOpen ? 'bg-white rounded-2xl p-5 max-w-4xl mx-auto' : ''"
                            :role="mapOpen ? 'dialog' : null" :aria-modal="mapOpen ? 'true' : null" aria-label="Denah kursi">

                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="block text-sm font-semibold text-ink">Pilih Kursi <span class="font-normal text-ink-muted" x-text="'(' + availableCount + ' tersedia)'"></span></span>
                                @if($event->seat_map_image)
                                    <button type="button" @click="mapOpen = !mapOpen" class="text-sm font-semibold text-brand-primary hover:underline min-h-[44px] px-2" x-text="mapOpen ? 'Tutup' : 'Perbesar denah'"></button>
                                @endif
                            </div>

                            @if($event->seat_map_image)
                                <div class="relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50">
                                    <img src="{{ Storage::url($event->seat_map_image) }}" alt="Denah venue {{ $event->name }}" class="w-full block select-none" draggable="false">
                                    <template x-for="seat in seatsOfChosen" :key="seat.id">
                                        <button type="button"
                                            @click="toggleSeat(seat)"
                                            :disabled="seat.taken"
                                            :aria-pressed="isPicked(seat.id).toString()"
                                            :aria-label="'Kursi ' + seat.label + (seat.taken ? ', sudah dipesan' : (isPicked(seat.id) ? ', dipilih' : ', tersedia'))"
                                            class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full font-bold grid place-items-center border-2 transition-all focus-visible:ring-2"
                                            :class="[
                                                mapOpen ? 'w-9 h-9 text-[11px]' : 'w-6 h-6 text-[9px]',
                                                seat.taken ? 'bg-gray-300 border-gray-400 text-gray-500 cursor-not-allowed' :
                                                    (isPicked(seat.id) ? 'bg-brand-accent border-ink text-ink scale-110 z-10' : 'bg-white border-brand-primary text-brand-primary hover:bg-brand-primary-soft')
                                            ]"
                                            :style="'left:' + seat.x + '%; top:' + seat.y + '%'">
                                            <span x-text="mapOpen ? seat.label : ''"></span>
                                        </button>
                                    </template>
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-muted mt-2">
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-white border-2 border-brand-primary"></span>Tersedia</span>
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-brand-accent border-2 border-ink"></span>Dipilih</span>
                                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-gray-300 border-2 border-gray-400"></span>Sudah dipesan</span>
                            </div>

                            <!-- Daftar kursi: alternatif denah, target sentuh besar & bisa dipakai keyboard -->
                            <details class="mt-3" @if(!$event->seat_map_image) open @endif>
                                <summary class="text-sm font-semibold text-brand-primary cursor-pointer min-h-[44px] flex items-center">Atau pilih dari daftar kursi</summary>
                                <div class="flex flex-wrap gap-2 mt-2 max-h-48 overflow-y-auto">
                                    <template x-for="seat in seatsOfChosen" :key="'chip-' + seat.id">
                                        <button type="button" @click="toggleSeat(seat)" :disabled="seat.taken" :aria-pressed="isPicked(seat.id).toString()"
                                            class="min-h-[44px] min-w-[52px] px-3 rounded-lg border text-sm font-semibold transition-colors"
                                            :class="seat.taken ? 'bg-gray-100 border-gray-200 text-gray-400 cursor-not-allowed line-through' :
                                                (isPicked(seat.id) ? 'bg-brand-accent border-ink text-ink' : 'bg-white border-gray-300 text-ink hover:border-brand-primary')"
                                            x-text="seat.label"></button>
                                    </template>
                                </div>
                            </details>

                            <p x-show="availableCount === 0" x-cloak class="text-sm text-rose-600 mt-2">Semua kursi di kategori ini sudah terisi.</p>
                            <p x-show="seatNotice" x-cloak role="alert" class="text-sm text-rose-600 mt-2" x-text="seatNotice"></p>

                            <div class="mt-3 text-sm" x-show="pickedSeatIds.length > 0" x-cloak>
                                <span class="text-ink-muted">Kursi dipilih:</span>
                                <span class="font-bold text-ink" x-text="pickedLabels.join(', ')"></span>
                            </div>

                            <template x-for="id in pickedSeatIds" :key="'seat-input-' + id">
                                <input type="hidden" name="seat_ids[]" :value="id">
                            </template>

                            <button type="button" x-show="mapOpen" x-cloak @click="mapOpen = false"
                                class="mt-4 w-full min-h-[44px] bg-brand-primary text-white font-semibold rounded-lg hover:bg-brand-primary-dark transition-colors">
                                Selesai memilih
                            </button>
                        </div>
                    </div>

                    <!-- Jumlah tiket, muncul setelah kategori (non-kursi) dipilih -->
                    <div x-show="chosen && !isSeated" x-cloak>
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
                            <span x-text="chosen ? chosen.name + ' × ' + qty : ''"></span>
                            <span x-text="chosen ? formatRp(chosen.price) + '/tiket' : ''"></span>
                        </div>
                        <div class="flex items-center justify-between font-bold text-ink text-base">
                            <span>Total</span>
                            <span x-text="chosen ? formatRp(chosen.price * qty) : ''"></span>
                        </div>
                    </div>

                    <button type="submit" :disabled="isSeated && pickedSeatIds.length === 0"
                        class="w-full min-h-[48px] bg-brand-accent text-ink font-bold py-3 rounded-lg hover:bg-brand-accent-dark active:scale-[0.99] transition-all shadow-soft disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-brand-accent">
                        <span x-text="isSeated && pickedSeatIds.length === 0 ? 'Pilih kursi dulu' : 'Lanjut Pembayaran'"></span>
                    </button>
                    <p class="text-xs text-ink-muted text-center -mt-2">Data kamu aman &amp; hanya dipakai untuk proses tiket.</p>
                </form>
            </div>
        </aside>
    </div>
</div>
@endsection
