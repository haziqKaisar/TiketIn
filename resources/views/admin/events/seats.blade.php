@extends('layouts.app')

@section('title', 'Denah Kursi — ' . $event->name . ' — TiketIn')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 md:py-10">
    <a href="{{ route('admin.categories.index', $event->id) }}" class="inline-flex items-center gap-1.5 text-brand-primary font-medium hover:underline min-h-[44px]">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
        Kembali ke Kelola Tiket
    </a>
    <h1 class="text-2xl font-extrabold text-ink mt-2">Editor Denah Kursi</h1>
    <p class="text-ink-muted text-sm mt-1 mb-6">{{ $event->name }} · buat ratusan kursi sekaligus, kelompokkan kursi berdasarkan kategori, atau coba deteksi berbantuan.</p>

    @if(session('success'))
        <div role="status" class="bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-3.5 mb-5 text-sm">
            <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-soft border border-gray-100 mb-6">
        <h2 class="font-bold text-ink mb-1">{{ $event->seat_map_image ? 'Gambar referensi venue' : 'Upload gambar referensi venue' }}</h2>
        <p class="text-xs text-ink-muted mb-3">Gambar hanya menjadi referensi posisi. Kursi yang dipilih pembeli adalah objek digital yang disimpan di database.</p>
        <form action="{{ route('admin.events.seatmap.upload', $event->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <label for="seat_map" class="sr-only">File gambar denah</label>
            <input id="seat_map" type="file" name="seat_map" accept="image/jpeg,image/png,image/webp" required class="flex-1 text-sm text-ink-muted min-h-[44px] rounded-lg border border-gray-300 file:mr-4 file:px-4 file:py-2 file:border-0 file:bg-brand-primary-soft file:text-brand-primary file:font-semibold cursor-pointer">
            <button type="submit" class="min-h-[44px] bg-brand-primary text-white font-semibold px-6 rounded-lg hover:bg-brand-primary-dark">{{ $event->seat_map_image ? 'Ganti Gambar' : 'Upload Gambar' }}</button>
        </form>
    </div>

    @if(!$event->seat_map_image)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-sm text-amber-800">Upload gambar denah terlebih dahulu. Generator massal dan deteksi berbantuan menggunakan gambar sebagai bidang referensi posisi.</div>
    @elseif($seatedCategories->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-sm text-amber-800">Belum ada kategori dengan mode <strong>Pilih kursi</strong>. Buat atau ubah kategori melalui halaman <a href="{{ route('admin.categories.index', $event->id) }}" class="underline font-semibold">Kelola Tiket</a>.</div>
    @else
        <div x-data="seatEditor({{ \Illuminate\Support\Js::from([
            'categories' => $seatedCategories->values(),
            'seats' => $seats,
            'storeOneUrl' => route('admin.events.seats.store', $event->id),
            'bulkUrl' => route('admin.events.seats.bulk', $event->id),
            'bulkCategoryUrl' => route('admin.events.seats.bulk-category', $event->id),
            'destroyUrl' => route('admin.seats.destroy', ['seat' => '__ID__']),
            'csrf' => csrf_token(),
        ]) }})" class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
            <section class="xl:col-span-8 space-y-4 min-w-0">
                <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-soft border border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <h2 class="font-bold text-ink">Bidang denah</h2>
                            <p class="text-xs text-ink-muted">Mode pilih: klik kursi atau seret area kosong untuk memilih banyak kursi.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="mode = mode === 'select' ? 'manual' : 'select'" class="min-h-[40px] px-3 rounded-lg border text-sm font-semibold" :class="mode === 'manual' ? 'bg-brand-primary text-white border-brand-primary' : 'bg-white text-ink border-gray-300'" x-text="mode === 'manual' ? 'Mode tambah manual: aktif' : 'Tambah manual'"></button>
                            <button type="button" @click="showReference = !showReference" class="min-h-[40px] px-3 rounded-lg border border-gray-300 text-sm font-semibold text-ink" x-text="showReference ? 'Sembunyikan gambar' : 'Tampilkan gambar'"></button>
                        </div>
                    </div>
                    <div x-ref="map" class="relative rounded-xl overflow-hidden bg-slate-50 border border-slate-200 select-none touch-none"
                         :class="mode === 'manual' ? 'cursor-crosshair' : 'cursor-default'"
                         @pointerdown.self="startAreaSelection($event)" @pointermove.self="moveAreaSelection($event)" @pointerup.self="finishAreaSelection($event)" @pointercancel.self="cancelAreaSelection()" @click.self="clickEmptyMap($event)">
                        <img x-ref="mapImage" src="{{ Storage::url($event->seat_map_image) }}" alt="Gambar referensi denah {{ $event->name }}" class="w-full block pointer-events-none select-none transition-opacity duration-150" :class="showReference ? 'opacity-100' : 'opacity-0'" draggable="false">
                        <div class="absolute inset-0 pointer-events-none" :class="showReference ? 'bg-white/10' : 'bg-gradient-to-b from-slate-50 to-white'"></div>
                        <template x-for="seat in seats" :key="'seat-' + seat.id">
                            <button type="button" @click.stop="mode === 'manual' ? removeSeat(seat) : toggleSelection(seat)"
                                class="absolute -translate-x-1/2 -translate-y-1/2 z-10 grid place-items-center border font-bold shadow-sm transition-transform focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-secondary rounded-[5px]"
                                :class="[
                                    seat.taken ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110',
                                    isSelected(seat.id) ? 'ring-2 ring-amber-400 ring-offset-1' : 'border-white/80'
                                ]"
                                :style="seatStyle(seat)"
                                :title="seat.label + ' · ' + categoryName(seat.category_id) + (seat.taken ? ' · sudah terikat tiket' : '')"
                                :aria-label="(mode === 'manual' ? 'Hapus ' : 'Pilih ') + 'kursi ' + seat.label">
                                <span x-text="seat.label.length > 5 ? seat.label.slice(-4) : seat.label"></span>
                            </button>
                        </template>
                        <template x-for="candidate in detectedDraft" :key="candidate.key">
                            <button type="button" @click.stop="removeCandidate(candidate.key)" class="absolute -translate-x-1/2 -translate-y-1/2 z-20 rounded-md border-2 border-dashed border-fuchsia-600 bg-fuchsia-100/80 text-fuchsia-900 text-[8px] font-bold grid place-items-center" :style="seatStyle(candidate, true)" :title="'Kandidat ' + candidate.label + ' — klik untuk mengabaikan'" x-text="candidate.label"></button>
                        </template>
                        <div x-show="areaSelection" x-cloak class="absolute z-30 border-2 border-dashed border-brand-primary bg-blue-300/20 pointer-events-none" :style="selectionStyle()"></div>
                        <div x-show="seats.length === 0 && detectedDraft.length === 0" class="absolute inset-0 grid place-items-center pointer-events-none p-6 text-center">
                            <div class="bg-white/90 border border-slate-200 shadow-sm rounded-xl px-4 py-3"><p class="font-semibold text-ink text-sm">Belum ada kursi digital</p><p class="text-xs text-ink-muted mt-1">Gunakan generator massal di panel sebelah, atau mode tambah manual.</p></div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-ink-muted mt-3">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-brand-primary"></span>Kategori kursi</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border-2 border-amber-400"></span>Dipilih</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-300"></span>Terkunci/telah dipesan</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded border-2 border-dashed border-fuchsia-600"></span>Kandidat deteksi</span>
                    </div>
                    <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
                        <div class="text-sm"><strong x-text="selectedSeats.length"></strong> kursi dipilih <span class="text-ink-muted">· klik kursi atau seret area pada denah</span></div>
                        <div class="flex gap-2">
                            <button type="button" @click="selectAllAvailable()" class="min-h-[40px] px-3 rounded-lg border border-gray-300 bg-white text-sm font-semibold">Pilih semua yang tersedia</button>
                            <button type="button" @click="selectedSeatIds = []" class="min-h-[40px] px-3 rounded-lg border border-gray-300 bg-white text-sm">Kosongkan</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-soft border border-gray-100">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                        <div><h2 class="font-bold text-ink">Kursi yang sudah dibuat</h2><p class="text-xs text-ink-muted mt-1">Klik kursi untuk memilih banyak sekaligus. Di mode tambah manual, klik kursi untuk menghapus kursi yang belum dipesan.</p></div>
                        <span class="text-sm font-semibold text-ink" x-text="seats.length + ' kursi' "></span>
                    </div>
                    <div class="space-y-4">
                        <template x-for="cat in categories" :key="'list-' + cat.id">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2"><p class="font-semibold text-sm text-ink" x-text="cat.name"></p><span class="text-xs text-ink-muted" x-text="countFor(cat.id) + ' kursi'"></span></div>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="seat in seatsOf(cat.id)" :key="'chip-' + seat.id">
                                        <button type="button" @click="toggleSelection(seat)" :disabled="seat.taken" class="min-h-[36px] min-w-[42px] px-2 rounded-md border text-xs font-mono font-semibold" :class="isSelected(seat.id) ? 'bg-amber-100 border-amber-400 text-ink' : 'bg-white border-gray-200 text-ink hover:border-brand-primary'" x-text="seat.label" :title="seat.taken ? 'Kursi terikat tiket' : 'Pilih kursi'"></button>
                                    </template>
                                    <span x-show="countFor(cat.id) === 0" class="text-xs text-ink-muted">Belum ada kursi.</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <aside class="xl:col-span-4 space-y-4">
                <section class="bg-white p-5 rounded-2xl shadow-soft border border-gray-100 space-y-3">
                    <div><h2 class="font-bold text-ink">1. Buat kursi massal</h2><p class="text-xs text-ink-muted mt-1">Buat satu blok sekali jalan. Untuk area terpisah/lorong, buat blok kedua dengan koordinat berbeda.</p></div>
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1" for="bulk_category">Kategori baru</label>
                        <select id="bulk_category" x-model.number="activeCategoryId" class="w-full min-h-[44px] border border-gray-300 rounded-lg px-3 bg-white">
                            <template x-for="cat in categories" :key="'bulk-cat-' + cat.id"><option :value="cat.id" x-text="cat.name"></option></template>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label for="row_count" class="block text-xs font-semibold mb-1">Jumlah baris</label><input id="row_count" type="number" min="1" max="50" x-model.number="generator.rows" class="w-full min-h-[42px] border border-gray-300 rounded-lg px-3"></div>
                        <div><label for="row_seats" class="block text-xs font-semibold mb-1">Kursi per baris</label><input id="row_seats" type="number" min="1" max="50" x-model.number="generator.seatsPerRow" class="w-full min-h-[42px] border border-gray-300 rounded-lg px-3"></div>
                        <div><label for="row_start" class="block text-xs font-semibold mb-1">Baris mulai</label><input id="row_start" type="text" maxlength="2" x-model="generator.rowStart" class="w-full min-h-[42px] border border-gray-300 rounded-lg px-3 uppercase" placeholder="A"></div>
                        <div><label for="row_prefix" class="block text-xs font-semibold mb-1">Awalan label (opsional)</label><input id="row_prefix" type="text" maxlength="10" x-model="generator.rowPrefix" class="w-full min-h-[42px] border border-gray-300 rounded-lg px-3" placeholder="VIP-"></div>
                        <div><label for="seat_start" class="block text-xs font-semibold mb-1">Nomor awal kursi</label><input id="seat_start" type="number" min="1" max="9999" x-model.number="generator.seatStart" class="w-full min-h-[42px] border border-gray-300 rounded-lg px-3"></div>
                        <div class="flex items-end text-xs text-ink-muted pb-2" x-text="Math.max(0, Number(generator.rows) * Number(generator.seatsPerRow)) + ' kursi akan dibuat'"></div>
                    </div>
                    <div class="border-t border-gray-100 pt-3"><p class="text-sm font-semibold mb-2">Area blok di denah (%)</p><p class="text-xs text-ink-muted mb-2">X = kiri ke kanan, Y = atas ke bawah. Atur beberapa blok untuk memberi lorong di tengah.</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div><label for="x_start" class="block text-xs mb-1">X kiri</label><input id="x_start" type="number" min="0" max="100" x-model.number="generator.xStart" class="w-full min-h-[40px] border border-gray-300 rounded-lg px-2"></div>
                            <div><label for="x_end" class="block text-xs mb-1">X kanan</label><input id="x_end" type="number" min="0" max="100" x-model.number="generator.xEnd" class="w-full min-h-[40px] border border-gray-300 rounded-lg px-2"></div>
                            <div><label for="y_start" class="block text-xs mb-1">Y atas</label><input id="y_start" type="number" min="0" max="100" x-model.number="generator.yStart" class="w-full min-h-[40px] border border-gray-300 rounded-lg px-2"></div>
                            <div><label for="y_end" class="block text-xs mb-1">Y bawah</label><input id="y_end" type="number" min="0" max="100" x-model.number="generator.yEnd" class="w-full min-h-[40px] border border-gray-300 rounded-lg px-2"></div>
                        </div>
                    </div>
                    <button type="button" @click="generateBulk()" :disabled="busy" class="w-full min-h-[46px] bg-brand-primary text-white font-bold rounded-lg hover:bg-brand-primary-dark disabled:opacity-50" x-text="busy ? 'Memproses…' : 'Buat semua kursi' "></button>
                </section>

                <section class="bg-white p-5 rounded-2xl shadow-soft border border-gray-100 space-y-3">
                    <div><h2 class="font-bold text-ink">2. Deteksi kursi dari gambar</h2><p class="text-xs text-ink-muted mt-1">Eksperimental untuk gambar dengan simbol kursi yang jelas. Foto venue umum mungkin tidak terdeteksi dengan baik. Hasil hanya kandidat, belum menjadi kursi aktif.</p></div>
                    <button type="button" @click="detectSeats()" :disabled="detecting || busy" class="w-full min-h-[44px] border border-brand-primary text-brand-primary font-semibold rounded-lg hover:bg-brand-primary-soft disabled:opacity-50" x-text="detecting ? 'Menganalisis gambar…' : 'Cari kandidat kursi' "></button>
                    <div x-show="detectedDraft.length > 0" x-cloak class="space-y-2">
                        <p class="text-sm text-ink"><strong x-text="detectedDraft.length"></strong> kandidat terdeteksi. Klik kandidat berwarna ungu di denah untuk membuang kandidat yang keliru.</p>
                        <button type="button" @click="saveDetected()" :disabled="busy" class="w-full min-h-[44px] bg-brand-accent text-ink font-bold rounded-lg disabled:opacity-50" x-text="'Simpan ' + detectedDraft.length + ' kandidat ke kategori ' + categoryName(activeCategoryId)"></button>
                        <button type="button" @click="detectedDraft = []" class="w-full min-h-[40px] border border-gray-300 rounded-lg text-sm">Hapus semua kandidat</button>
                    </div>
                </section>

                <section class="bg-white p-5 rounded-2xl shadow-soft border border-gray-100 space-y-3">
                    <div><h2 class="font-bold text-ink">3. Atur kategori sekaligus</h2><p class="text-xs text-ink-muted mt-1">Pilih kursi dengan klik atau seret area di denah, lalu pindahkan kursi yang dipilih ke kategori tujuan. Kursi yang sudah punya tiket dikunci.</p></div>
                    <div><label for="target_category" class="block text-sm font-semibold text-ink mb-1">Pindahkan ke kategori</label><select id="target_category" x-model.number="targetCategoryId" class="w-full min-h-[44px] border border-gray-300 rounded-lg px-3 bg-white"><template x-for="cat in categories" :key="'target-cat-' + cat.id"><option :value="cat.id" x-text="cat.name"></option></template></select></div>
                    <button type="button" @click="bulkAssign()" :disabled="busy || selectedSeats.length === 0" class="w-full min-h-[44px] bg-brand-secondary text-white font-bold rounded-lg disabled:opacity-50">Terapkan kategori ke kursi terpilih</button>
                </section>

                <div x-show="notice" x-cloak role="status" aria-live="polite" class="p-3 rounded-xl text-sm border" :class="noticeKind === 'error' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'" x-text="notice"></div>
            </aside>
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
            storeOneUrl: init.storeOneUrl,
            bulkUrl: init.bulkUrl,
            bulkCategoryUrl: init.bulkCategoryUrl,
            destroyUrl: init.destroyUrl,
            csrf: init.csrf,
            activeCategoryId: init.categories.length ? Number(init.categories[0].id) : null,
            targetCategoryId: init.categories.length ? Number(init.categories[0].id) : null,
            selectedSeatIds: [],
            mode: 'select',
            showReference: false,
            busy: false,
            detecting: false,
            notice: '',
            noticeKind: 'info',
            areaSelection: null,
            detectedDraft: [],
            nextLabel: 'A1',
            generator: { rows: 10, seatsPerRow: 12, rowStart: 'A', rowPrefix: '', seatStart: 1, xStart: 12, xEnd: 88, yStart: 20, yEnd: 80 },
            palette: ['#525EA7', '#B45309', '#0E7490', '#BE123C', '#047857', '#6D28D9'],

            get selectedSeats() { return this.seats.filter(s => this.selectedSeatIds.includes(Number(s.id))); },
            colorFor(categoryId) {
                const i = this.categories.findIndex(c => Number(c.id) === Number(categoryId));
                return this.palette[(i < 0 ? 0 : i) % this.palette.length];
            },
            categoryName(categoryId) { return this.categories.find(c => Number(c.id) === Number(categoryId))?.name || 'Kategori'; },
            seatsOf(categoryId) { return this.seats.filter(s => Number(s.category_id) === Number(categoryId)); },
            countFor(categoryId) { return this.seatsOf(categoryId).length; },
            isSelected(id) { return this.selectedSeatIds.includes(Number(id)); },
            say(message, kind = 'info') { this.notice = message; this.noticeKind = kind; },
            headers() { return { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf }; },
            async responseJson(res) {
                let data = {};
                try { data = await res.json(); } catch (_) {}
                if (!res.ok) {
                    let message = data.message || 'Terjadi kesalahan, coba lagi.';
                    if (data.errors) message = Object.values(data.errors).flat()[0] || message;
                    throw new Error(message);
                }
                return data;
            },
            seatSizePx() {
                const width = this.$refs.map?.clientWidth || 800;
                const rows = new Map();
                this.seats.forEach(s => {
                    const key = Math.round(Number(s.y) * 1.5) / 1.5;
                    rows.set(key, (rows.get(key) || 0) + 1);
                });
                const maxPerRow = Math.max(1, ...rows.values());
                return Math.max(9, Math.min(28, (width * 0.82 / maxPerRow) * 0.82));
            },
            seatStyle(seat, candidate = false) {
                const size = candidate ? Math.max(10, this.seatSizePx() * 0.8) : this.seatSizePx();
                const font = size < 15 ? 6 : (size < 20 ? 8 : 10);
                return `left:${Number(seat.x)}%;top:${Number(seat.y)}%;width:${size}px;height:${size}px;font-size:${font}px;${seat.category_id ? 'background:' + (seat.taken ? '#9CA3AF' : this.colorFor(seat.category_id)) + ';color:white;' : ''}`;
            },
            selectionStyle() {
                if (!this.areaSelection) return '';
                const a = this.areaSelection;
                return `left:${Math.min(a.x1,a.x2)}%;top:${Math.min(a.y1,a.y2)}%;width:${Math.abs(a.x2-a.x1)}%;height:${Math.abs(a.y2-a.y1)}%`;
            },
            point(event) {
                const rect = this.$refs.map.getBoundingClientRect();
                return { x: Math.max(0, Math.min(100, (event.clientX - rect.left) / rect.width * 100)), y: Math.max(0, Math.min(100, (event.clientY - rect.top) / rect.height * 100)) };
            },
            startAreaSelection(event) {
                if (this.mode !== 'select' || this.busy) return;
                const p = this.point(event);
                this.areaSelection = { x1: p.x, x2: p.x, y1: p.y, y2: p.y };
                try { this.$refs.map.setPointerCapture(event.pointerId); } catch (_) {}
            },
            moveAreaSelection(event) {
                if (!this.areaSelection) return;
                const p = this.point(event);
                this.areaSelection.x2 = p.x; this.areaSelection.y2 = p.y;
            },
            finishAreaSelection(event) {
                if (!this.areaSelection) return;
                const p = this.point(event); this.areaSelection.x2 = p.x; this.areaSelection.y2 = p.y;
                const a = this.areaSelection;
                if (Math.abs(a.x2-a.x1) > 1 || Math.abs(a.y2-a.y1) > 1) {
                    const minX = Math.min(a.x1,a.x2), maxX = Math.max(a.x1,a.x2), minY = Math.min(a.y1,a.y2), maxY = Math.max(a.y1,a.y2);
                    this.selectedSeatIds = this.seats.filter(s => !s.taken && Number(s.x) >= minX && Number(s.x) <= maxX && Number(s.y) >= minY && Number(s.y) <= maxY).map(s => Number(s.id));
                    this.say(this.selectedSeatIds.length + ' kursi tersedia terpilih.');
                }
                this.areaSelection = null;
            },
            cancelAreaSelection() { this.areaSelection = null; },
            toggleSelection(seat) {
                if (seat.taken) { this.say('Kursi ' + seat.label + ' sudah terikat ke tiket dan tidak bisa diubah.', 'error'); return; }
                const id = Number(seat.id);
                this.selectedSeatIds = this.isSelected(id) ? this.selectedSeatIds.filter(n => n !== id) : [...this.selectedSeatIds, id];
            },
            selectAllAvailable() { this.selectedSeatIds = this.seats.filter(s => !s.taken).map(s => Number(s.id)); this.say(this.selectedSeatIds.length + ' kursi tersedia terpilih.'); },
            async generateBulk() {
                if (this.busy || !this.activeCategoryId) return;
                const g = this.generator;
                const total = Number(g.rows) * Number(g.seatsPerRow);
                if (!Number.isInteger(Number(g.rows)) || !Number.isInteger(Number(g.seatsPerRow)) || total < 1 || total > 1000) { this.say('Jumlah kursi harus 1–1.000 per proses.', 'error'); return; }
                if (Number(g.xStart) >= Number(g.xEnd) || Number(g.yStart) >= Number(g.yEnd)) { this.say('Koordinat akhir harus lebih besar dari koordinat awal.', 'error'); return; }
                this.busy = true;
                try {
                    const res = await fetch(this.bulkUrl, { method: 'POST', headers: this.headers(), body: JSON.stringify({ mode:'generate', ticket_category_id:this.activeCategoryId, rows:Number(g.rows), seats_per_row:Number(g.seatsPerRow), row_start:String(g.rowStart).toUpperCase(), row_prefix:g.rowPrefix, seat_start:Number(g.seatStart), x_start:Number(g.xStart), x_end:Number(g.xEnd), y_start:Number(g.yStart), y_end:Number(g.yEnd) }) });
                    const data = await this.responseJson(res);
                    this.seats.push(...data.seats); this.selectedSeatIds = [];
                    this.say(data.message + ' Sesuaikan batas area dan ulangi untuk blok lainnya.');
                } catch (e) { this.say(e.message, 'error'); }
                finally { this.busy = false; }
            },
            async bulkAssign() {
                if (this.busy || !this.selectedSeatIds.length || !this.targetCategoryId) return;
                if (this.selectedSeats.some(s => s.taken)) { this.say('Pilihan berisi kursi yang sudah dipesan. Keluarkan kursi tersebut dulu.', 'error'); return; }
                this.busy = true;
                try {
                    const res = await fetch(this.bulkCategoryUrl, { method:'PATCH', headers:this.headers(), body:JSON.stringify({ ticket_category_id:Number(this.targetCategoryId), seat_ids:this.selectedSeatIds }) });
                    const data = await this.responseJson(res);
                    if (data.updated_ids?.length) {
                        this.seats = this.seats.map(s => data.updated_ids.map(Number).includes(Number(s.id)) ? {...s, category_id:Number(data.category_id)} : s);
                    }
                    this.selectedSeatIds = []; this.say(data.message);
                } catch (e) { this.say(e.message, 'error'); }
                finally { this.busy = false; }
            },
            bumpLabel(label) { const m = label.match(/^(.*?)(\d+)$/); return m ? m[1] + (parseInt(m[2],10)+1) : label + '2'; },
            clickEmptyMap(event) { if (this.mode === 'manual') this.placeSeat(event); },
            async placeSeat(event) {
                if (this.busy || this.activeCategoryId === null) return;
                const label = this.nextLabel.trim(); if (!label) { this.say('Isi label kursi terlebih dahulu.', 'error'); return; }
                const p = this.point(event); this.busy = true;
                try {
                    const res = await fetch(this.storeOneUrl,{method:'POST',headers:this.headers(),body:JSON.stringify({ticket_category_id:Number(this.activeCategoryId),label,x:p.x,y:p.y})});
                    const seat = await this.responseJson(res); this.seats.push(seat); this.nextLabel = this.bumpLabel(label); this.say('Kursi ' + label + ' ditambahkan.');
                } catch(e) { this.say(e.message,'error'); } finally { this.busy = false; }
            },
            async removeSeat(seat) {
                if (this.busy) return;
                if (seat.taken) { this.say('Kursi ' + seat.label + ' sudah terikat tiket, tidak bisa dihapus.', 'error'); return; }
                if (!confirm('Hapus kursi ' + seat.label + '?')) return;
                this.busy = true;
                try {
                    const res=await fetch(this.destroyUrl.replace('__ID__',seat.id),{method:'DELETE',headers:this.headers()}); await this.responseJson(res);
                    this.seats=this.seats.filter(s=>Number(s.id)!==Number(seat.id)); this.selectedSeatIds=this.selectedSeatIds.filter(id=>id!==Number(seat.id)); this.say('Kursi ' + seat.label + ' dihapus.');
                } catch(e) { this.say(e.message,'error'); } finally { this.busy=false; }
            },
            removeCandidate(key) { this.detectedDraft = this.detectedDraft.filter(c => c.key !== key); },
            async loadOpenCv() {
                if (window.cv && window.cv.Mat) return window.cv;
                if (window.cv && typeof window.cv.then === 'function') return await window.cv;
                return await new Promise((resolve,reject) => {
                    const script=document.createElement('script'); script.async=true; script.src='https://docs.opencv.org/4.x/opencv.js';
                    const timer=setTimeout(()=>reject(new Error('Pustaka deteksi belum siap. Periksa koneksi internet atau gunakan generator massal.')),25000);
                    script.onerror=()=>{clearTimeout(timer);reject(new Error('Pustaka deteksi gagal dimuat. Generator massal tetap bisa digunakan.'));};
                    script.onload=()=>{
                        if (window.cv && window.cv.Mat) {clearTimeout(timer);resolve(window.cv);return;}
                        if (window.cv) window.cv.onRuntimeInitialized=()=>{clearTimeout(timer);resolve(window.cv);};
                        else {clearTimeout(timer);reject(new Error('Pustaka deteksi tidak tersedia di browser.'));}
                    };
                    document.head.appendChild(script);
                });
            },
            alphaLabel(n) { let s=''; while(n>0){n--;s=String.fromCharCode(65+n%26)+s;n=Math.floor(n/26);} return s; },
            async detectSeats() {
                if (this.detecting || this.busy) return;
                this.detecting=true; this.detectedDraft=[];
                let mats=[];
                try {
                    const cv=await this.loadOpenCv();
                    const img=this.$refs.mapImage;
                    if (!img || !img.complete || !img.naturalWidth) throw new Error('Gambar denah belum siap. Refresh halaman lalu coba lagi.');
                    const scale=Math.min(1,1400/img.naturalWidth);
                    const canvas=document.createElement('canvas'); canvas.width=Math.round(img.naturalWidth*scale); canvas.height=Math.round(img.naturalHeight*scale);
                    const ctx=canvas.getContext('2d',{willReadFrequently:true}); ctx.drawImage(img,0,0,canvas.width,canvas.height);
                    let src=cv.imread(canvas),gray=new cv.Mat(),blur=new cv.Mat(),edges=new cv.Mat(),dilated=new cv.Mat(),contours=new cv.MatVector(),hierarchy=new cv.Mat();
                    mats=[src,gray,blur,edges,dilated,contours,hierarchy];
                    cv.cvtColor(src,gray,cv.COLOR_RGBA2GRAY); cv.GaussianBlur(gray,blur,new cv.Size(3,3),0); cv.Canny(blur,edges,45,130);
                    const kernel=cv.Mat.ones(3,3,cv.CV_8U); mats.push(kernel); cv.dilate(edges,dilated,kernel);
                    cv.findContours(dilated,contours,hierarchy,cv.RETR_LIST,cv.CHAIN_APPROX_SIMPLE);
                    const w=canvas.width,h=canvas.height, minW=Math.max(7,w*.009),maxW=w*.09,minH=Math.max(7,h*.009),maxH=h*.09;
                    let raw=[];
                    for(let i=0;i<contours.size();i++){
                        const contour=contours.get(i); const peri=cv.arcLength(contour,true); const approx=new cv.Mat();
                        cv.approxPolyDP(contour,approx,.035*peri,true);
                        const rect=cv.boundingRect(approx); const area=rect.width*rect.height; const ratio=rect.width/Math.max(1,rect.height);
                        if(approx.rows>=4 && approx.rows<=8 && rect.width>=minW && rect.width<=maxW && rect.height>=minH && rect.height<=maxH && ratio>=.5 && ratio<=2.0 && area>=60) raw.push({x:rect.x,y:rect.y,w:rect.width,h:rect.height,area});
                        approx.delete(); contour.delete();
                    }
                    raw.sort((a,b)=>b.area-a.area);
                    const unique=[];
                    const overlap=(a,b)=>{const iw=Math.max(0,Math.min(a.x+a.w,b.x+b.w)-Math.max(a.x,b.x));const ih=Math.max(0,Math.min(a.y+a.h,b.y+b.h)-Math.max(a.y,b.y));const inter=iw*ih;return inter/Math.max(1,a.w*a.h+b.w*b.h-inter);};
                    for(const r of raw){if(!unique.some(u=>overlap(r,u)>.55)) unique.push(r); if(unique.length>=1000)break;}
                    if(unique.length<2) throw new Error('Belum menemukan cukup kandidat kotak kursi pada gambar. Gunakan generator massal atau mode tambah manual.');
                    unique.sort((a,b)=>a.y-b.y || a.x-b.x);
                    const medianH=unique.map(r=>r.h).sort((a,b)=>a-b)[Math.floor(unique.length/2)];
                    const rowGroups=[];
                    for(const rect of unique){let row=rowGroups.find(g=>Math.abs(g.y-rect.y)<medianH*.65); if(!row){row={y:rect.y,items:[]};rowGroups.push(row);} row.items.push(rect);row.y=row.items.reduce((sum,q)=>sum+q.y,0)/row.items.length;}
                    rowGroups.sort((a,b)=>a.y-b.y);
                    const draft=[];let idx=0;
                    rowGroups.forEach((row,ri)=>{row.items.sort((a,b)=>a.x-b.x);row.items.forEach((r,ci)=>{draft.push({key:'d'+(++idx),label:this.alphaLabel(ri+1)+(ci+1),x:Math.round((r.x+r.w/2)/w*10000)/100,y:Math.round((r.y+r.h/2)/h*10000)/100});});});
                    this.detectedDraft=draft; this.say('Deteksi menghasilkan ' + draft.length + ' kandidat. Periksa seluruh kandidat di denah; kandidat salah bisa diklik untuk dihapus.');
                } catch(e) { this.say(e.message || 'Deteksi gagal.', 'error'); }
                finally { mats.forEach(m=>{try{m.delete();}catch(_){}}); this.detecting=false; }
            },
            async saveDetected() {
                if (this.busy || !this.detectedDraft.length || !this.activeCategoryId) return;
                if (!confirm('Simpan ' + this.detectedDraft.length + ' kandidat sebagai kursi aktif di kategori ' + this.categoryName(this.activeCategoryId) + '? Periksa kandidat terlebih dahulu.')) return;
                this.busy=true;
                try {
                    const prefix=String(this.generator.rowPrefix || '').trim();
                    const seats=this.detectedDraft.map(({label,x,y})=>({label:prefix + label,x,y}));
                    const res=await fetch(this.bulkUrl,{method:'POST',headers:this.headers(),body:JSON.stringify({mode:'detected',ticket_category_id:Number(this.activeCategoryId),seats})});
                    const data=await this.responseJson(res); this.seats.push(...data.seats); this.detectedDraft=[]; this.say(data.message);
                } catch(e){this.say(e.message,'error');} finally{this.busy=false;}
            }
        }));
    });
</script>
@endpush
