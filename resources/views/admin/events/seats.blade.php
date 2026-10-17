@extends('layouts.app')

@section('title', 'Denah Kursi — ' . $event->name . ' — TiketIn')

@section('content')
<div class="max-w-5xl mx-auto px-4 lg:px-8 py-8 md:py-10">

    <a href="{{ route('admin.categories.index', $event->id) }}" class="inline-flex items-center gap-1.5 text-brand-primary font-medium hover:underline min-h-[44px]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
        Kembali ke Kelola Tiket
    </a>
    <h1 class="text-2xl font-extrabold text-ink mt-2">Denah Kursi: {{ $event->name }}</h1>
    <p class="text-ink-muted text-sm mt-1 mb-6">Upload gambar denah venue-mu, lalu klik di atas gambar untuk menandai posisi tiap kursi.</p>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div role="alert" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-3.5 mb-5 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <!-- Upload denah -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-soft border border-gray-100 mb-6">
        <h2 class="font-bold text-ink mb-1">{{ $event->seat_map_image ? 'Ganti Gambar Denah' : 'Upload Gambar Denah' }}</h2>
        <p class="text-xs text-ink-muted mb-3">Format JPG, PNG, atau WebP, maksimal 4 MB. Bentuk venue bebas — bisa foto, sketsa, atau hasil desain sendiri.</p>
        <form action="{{ route('admin.events.seatmap.upload', $event->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <label for="seat_map" class="sr-only">File gambar denah</label>
            <input id="seat_map" type="file" name="seat_map" accept="image/*" required
                class="flex-1 text-sm text-ink-muted min-h-[44px] rounded-lg border border-gray-300
                    file:mr-4 file:h-full file:px-4 file:border-0 file:bg-brand-primary-soft file:text-brand-primary file:font-semibold file:text-sm
                    hover:file:bg-brand-primary/20 file:cursor-pointer cursor-pointer">
            <button type="submit" class="min-h-[44px] bg-brand-primary text-white font-semibold px-6 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
                {{ $event->seat_map_image ? 'Ganti Denah' : 'Upload Denah' }}
            </button>
        </form>
        @if($event->seat_map_image)
            <p class="text-xs text-ink-muted mt-3">Kalau kamu ganti gambar, posisi titik kursi yang sudah ada tetap di koordinat yang sama — cek lagi apakah masih pas dengan gambar barunya.</p>
        @endif
    </div>

    @if(!$event->seat_map_image)
        <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-10 text-center text-ink-muted">
            Upload gambar denah dulu untuk mulai menandai kursi.
        </div>

    @elseif($seatedCategories->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z"/></svg>
            <div>
                <p class="font-semibold text-ink">Belum ada kategori dengan mode "Pilih Kursi"</p>
                <p class="text-sm text-ink-muted mt-0.5">
                    Buat dulu kategori tiket dan pilih mode <strong>Pilih kursi di denah</strong> di halaman
                    <a href="{{ route('admin.categories.index', $event->id) }}" class="text-brand-primary font-semibold hover:underline">Kelola Tiket</a>.
                </p>
            </div>
        </div>

    @else
        <div
            x-data="seatEditor({{ \Illuminate\Support\Js::from([
                'categories' => $seatedCategories->values(),
                'seats'      => $seats,
                'storeUrl'   => route('admin.events.seats.store', $event->id),
                'destroyUrl' => route('admin.seats.destroy', ['seat' => '__ID__']),
                'csrf'       => csrf_token(),
            ]) }})"
            class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start"
        >
            <!-- Kolom kiri: kontrol -->
            <div class="bg-white p-5 rounded-2xl shadow-soft border border-gray-100 space-y-5 lg:order-2">
                <fieldset>
                    <legend class="text-sm font-semibold text-ink mb-2">Kategori yang sedang ditandai</legend>
                    <div class="space-y-2">
                        <template x-for="(cat, i) in categories" :key="cat.id">
                            <label class="flex items-center justify-between gap-2 border rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors"
                                :class="activeCategoryId === cat.id ? 'border-brand-primary bg-brand-primary-soft' : 'border-gray-200 hover:border-brand-secondary'">
                                <span class="flex items-center gap-2 min-w-0">
                                    <input type="radio" name="active_category" :value="cat.id" x-model.number="activeCategoryId" class="w-4 h-4 accent-brand-primary shrink-0">
                                    <span class="w-3 h-3 rounded-full shrink-0" :style="'background:' + colorFor(cat.id)"></span>
                                    <span class="text-sm font-medium text-ink truncate" x-text="cat.name"></span>
                                </span>
                                <span class="text-xs text-ink-muted shrink-0" x-text="countFor(cat.id) + ' kursi'"></span>
                            </label>
                        </template>
                    </div>
                </fieldset>

                <div>
                    <label for="next_label" class="block text-sm font-semibold text-ink mb-1.5">Label kursi berikutnya</label>
                    <input id="next_label" type="text" x-model="nextLabel" maxlength="30"
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors font-mono">
                    <p class="text-xs text-ink-muted mt-1">Angka di ujung label otomatis naik tiap kali kamu menaruh kursi (A1 → A2 → A3). Ubah manual kalau pindah baris.</p>
                </div>

                <div role="status" aria-live="polite" class="text-sm rounded-lg px-3 py-2"
                    x-show="notice" x-cloak
                    :class="noticeKind === 'error' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'"
                    x-text="notice"></div>
            </div>

            <!-- Kolom kanan: denah -->
            <div class="lg:col-span-2 lg:order-1 space-y-4">
                <div class="bg-white p-3 rounded-2xl shadow-soft border border-gray-100">
                    <div x-ref="map" @click="placeSeat($event)"
                        class="relative rounded-xl overflow-hidden bg-gray-50 select-none"
                        :class="busy ? 'cursor-wait' : 'cursor-crosshair'">
                        <img src="{{ Storage::url($event->seat_map_image) }}" alt="Denah venue {{ $event->name }}" class="w-full block pointer-events-none" draggable="false">

                        <template x-for="seat in seats" :key="seat.id">
                            <button type="button"
                                @click.stop="removeSeat(seat)"
                                class="absolute -translate-x-1/2 -translate-y-1/2 w-7 h-7 rounded-full text-[9px] font-bold text-white grid place-items-center border-2 border-white shadow-soft hover:scale-110 transition-transform focus-visible:ring-2"
                                :class="seat.taken ? 'opacity-50' : ''"
                                :style="'left:' + seat.x + '%; top:' + seat.y + '%; background:' + colorFor(seat.category_id)"
                                :title="seat.label + (seat.taken ? ' (sudah dipesan)' : ' — klik untuk hapus')"
                                :aria-label="'Hapus kursi ' + seat.label">
                                <span x-text="seat.label.length > 3 ? seat.label.slice(0, 3) : seat.label"></span>
                            </button>
                        </template>
                    </div>
                    <p class="text-xs text-ink-muted mt-2 px-1">Klik di gambar untuk menaruh kursi. Klik titik yang sudah ada untuk menghapusnya (kursi yang sudah dipesan tidak bisa dihapus).</p>
                </div>

                <!-- Daftar kursi (alternatif teks, juga memudahkan hapus) -->
                <div class="bg-white p-5 rounded-2xl shadow-soft border border-gray-100">
                    <h2 class="font-bold text-ink mb-3">Daftar Kursi</h2>
                    <template x-for="cat in categories" :key="'list-' + cat.id">
                        <div class="mb-4 last:mb-0">
                            <p class="text-sm font-semibold text-ink mb-2 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" :style="'background:' + colorFor(cat.id)"></span>
                                <span x-text="cat.name"></span>
                                <span class="text-xs font-normal text-ink-muted" x-text="'(' + countFor(cat.id) + ')'"></span>
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="seat in seatsOf(cat.id)" :key="seat.id">
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-ink text-xs font-mono rounded-md pl-2 pr-1 py-1">
                                        <span x-text="seat.label"></span>
                                        <button type="button" @click="removeSeat(seat)" :aria-label="'Hapus kursi ' + seat.label"
                                            class="grid place-items-center w-5 h-5 rounded text-ink-muted hover:text-rose-600 hover:bg-rose-50">&times;</button>
                                    </span>
                                </template>
                                <span x-show="countFor(cat.id) === 0" class="text-xs text-ink-muted">Belum ada kursi.</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('seatEditor', (init) => ({
            categories: init.categories,
            seats: init.seats,
            storeUrl: init.storeUrl,
            destroyUrl: init.destroyUrl,
            csrf: init.csrf,
            activeCategoryId: init.categories.length ? init.categories[0].id : null,
            nextLabel: 'A1',
            busy: false,
            notice: '',
            noticeKind: 'info',
            palette: ['#525EA7', '#B45309', '#0E7490', '#BE123C', '#047857', '#6D28D9'],

            colorFor(categoryId) {
                const i = this.categories.findIndex(c => c.id === categoryId);
                return this.palette[(i < 0 ? 0 : i) % this.palette.length];
            },
            seatsOf(categoryId) { return this.seats.filter(s => s.category_id === categoryId); },
            countFor(categoryId) { return this.seatsOf(categoryId).length; },

            // "A9" -> "A10", "VIP-3" -> "VIP-4", "Kursi" -> "Kursi2"
            bumpLabel(label) {
                const m = label.match(/^(.*?)(\d+)$/);
                return m ? m[1] + (parseInt(m[2], 10) + 1) : label + '2';
            },

            say(message, kind = 'info') {
                this.notice = message;
                this.noticeKind = kind;
            },

            errorMessage(data) {
                if (data && data.errors) return Object.values(data.errors)[0][0];
                return (data && data.message) || 'Terjadi kesalahan, coba lagi.';
            },

            headers() {
                return {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                };
            },

            async placeSeat(event) {
                if (this.busy || this.activeCategoryId === null) return;

                const label = this.nextLabel.trim();
                if (!label) { this.say('Isi label kursi dulu sebelum menaruh titik.', 'error'); return; }

                const rect = this.$refs.map.getBoundingClientRect();
                const clamp = (n) => Math.min(100, Math.max(0, n));
                const x = clamp(((event.clientX - rect.left) / rect.width) * 100);
                const y = clamp(((event.clientY - rect.top) / rect.height) * 100);

                this.busy = true;
                try {
                    const res = await fetch(this.storeUrl, {
                        method: 'POST',
                        headers: this.headers(),
                        body: JSON.stringify({ ticket_category_id: this.activeCategoryId, label, x, y }),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(this.errorMessage(data));

                    this.seats.push(data);
                    this.nextLabel = this.bumpLabel(label);
                    this.say('Kursi ' + label + ' ditambahkan.', 'info');
                } catch (e) {
                    this.say(e.message, 'error');
                } finally {
                    this.busy = false;
                }
            },

            async removeSeat(seat) {
                if (this.busy) return;
                if (seat.taken) { this.say('Kursi ' + seat.label + ' sudah dipesan, tidak bisa dihapus.', 'error'); return; }
                if (!confirm('Hapus kursi ' + seat.label + '?')) return;

                this.busy = true;
                try {
                    const res = await fetch(this.destroyUrl.replace('__ID__', seat.id), {
                        method: 'DELETE',
                        headers: this.headers(),
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(this.errorMessage(data));

                    this.seats = this.seats.filter(s => s.id !== seat.id);
                    this.say('Kursi ' + seat.label + ' dihapus.', 'info');
                } catch (e) {
                    this.say(e.message, 'error');
                } finally {
                    this.busy = false;
                }
            },
        }));
    });
</script>
@endpush
