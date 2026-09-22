@extends('layouts.app')

@section('title', 'Kelola Tiket — ' . ($event->name ?? '') . ' — TiketIn')

@section('content')
<div class="max-w-6xl mx-auto px-4 lg:px-8 py-8 md:py-10">

    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-brand-primary font-medium hover:underline min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
            Kembali ke Daftar Event
        </a>
        <h1 class="text-2xl font-extrabold text-ink mt-2">Kelola Tiket: {{ $event->name }}</h1>
    </div>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Form Tambah Tiket -->
        <div class="bg-white p-6 rounded-2xl shadow-soft border border-gray-100 h-fit">
            <h2 class="text-lg font-bold text-ink mb-4">Tambah Kategori Tiket</h2>
            <form action="{{ route('admin.categories.store', $event->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="cat_name" class="block text-sm font-semibold text-ink mb-1.5">Nama Tiket</label>
                    <input id="cat_name" type="text" name="name" placeholder="Misal: VIP, Presale 1" required
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>
                <div>
                    <label for="cat_price" class="block text-sm font-semibold text-ink mb-1.5">Harga (Rp)</label>
                    <input id="cat_price" type="number" name="price" min="0" placeholder="50000" required
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>
                <div>
                    <label for="cat_quota" class="block text-sm font-semibold text-ink mb-1.5">Kuota (Jumlah Tiket)</label>
                    <input id="cat_quota" type="number" name="quota" min="0" placeholder="100" required
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>
                <button type="submit" class="w-full min-h-[44px] bg-brand-primary text-white font-bold py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
                    Simpan Tiket
                </button>
            </form>
        </div>

        <!-- Tampilan tabel (tablet ke atas) -->
        <div class="hidden md:block md:col-span-2 bg-white rounded-2xl shadow-soft border border-gray-100 overflow-x-auto h-fit">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-ink-muted">
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Harga</th>
                        <th class="p-4 font-semibold">Sisa Kuota</th>
                        <th class="p-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/70">
                        <td class="p-4 font-semibold text-ink">{{ $category->name }}</td>
                        <td class="p-4 text-ink">Rp {{ number_format($category->price, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <span class="bg-brand-primary-soft text-brand-primary font-bold px-2.5 py-1 rounded-full text-xs">{{ $category->quota }}</span>
                        </td>
                        <td class="p-4 text-center">
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus tiket ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" aria-label="Hapus kategori {{ $category->name }}" title="Hapus" class="inline-flex items-center justify-center min-h-[36px] min-w-[36px] text-ink-muted hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors px-2.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 12.1a2 2 0 0 1-2 1.9H9.7a2 2 0 0 1-2-1.9L7 7h10Z"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-8 text-center text-ink-muted">Belum ada kategori tiket untuk event ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tampilan kartu (mobile) -->
        <div class="md:hidden flex flex-col gap-3">
            @forelse($categories as $category)
            <article class="bg-white rounded-2xl shadow-soft border border-gray-100 p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-ink truncate">{{ $category->name }}</p>
                    <p class="text-sm text-ink-muted">Rp {{ number_format($category->price, 0, ',', '.') }}</p>
                    <span class="inline-block mt-1 bg-brand-primary-soft text-brand-primary font-bold px-2.5 py-0.5 rounded-full text-xs">Sisa {{ $category->quota }}</span>
                </div>
                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus tiket ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" aria-label="Hapus kategori {{ $category->name }}" class="min-h-[40px] min-w-[40px] flex items-center justify-center text-ink-muted hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m2 0-.7 12.1a2 2 0 0 1-2 1.9H9.7a2 2 0 0 1-2-1.9L7 7h10Z"/></svg>
                    </button>
                </form>
            </article>
            @empty
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-8 text-center text-ink-muted">
                Belum ada kategori tiket untuk event ini.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
