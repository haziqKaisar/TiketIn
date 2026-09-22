@extends('layouts.app')

@section('title', (isset($event) ? 'Edit Event' : 'Tambah Event') . ' — TiketIn')

@section('content')
<div class="max-w-2xl mx-auto px-4 lg:px-8 py-8 md:py-10">
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-soft border border-gray-100">
        <h1 class="text-2xl font-extrabold text-ink mb-6">{{ isset($event) ? 'Edit Event' : 'Tambah Event Baru' }}</h1>

        <form action="{{ isset($event) ? route('admin.events.update', $event->id) : route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if(isset($event)) @method('PUT') @endif

            <div>
                <label for="name" class="block text-sm font-semibold text-ink mb-1.5">Nama Event</label>
                <input id="name" type="text" name="name" value="{{ $event->name ?? old('name') }}" required
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="location" class="block text-sm font-semibold text-ink mb-1.5">Lokasi</label>
                <input id="location" type="text" name="location" value="{{ $event->location ?? old('location') }}" required
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="event_date" class="block text-sm font-semibold text-ink mb-1.5">Tanggal Pelaksanaan</label>
                <input id="event_date" type="datetime-local" name="event_date"
                    value="{{ isset($event) ? date('Y-m-d\TH:i', strtotime($event->event_date)) : old('event_date') }}" required
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-ink mb-1.5">Deskripsi</label>
                <textarea id="description" name="description" rows="4"
                    class="w-full border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">{{ $event->description ?? old('description') }}</textarea>
            </div>

            <div>
                <label for="poster" class="block text-sm font-semibold text-ink mb-1.5">Upload Poster (Opsional)</label>
                <input id="poster" type="file" name="poster" accept="image/*"
                    class="w-full text-sm text-ink-muted min-h-[44px] rounded-lg border border-gray-300
                        file:mr-4 file:h-full file:px-4 file:border-0 file:bg-brand-primary-soft file:text-brand-primary file:font-semibold file:text-sm
                        hover:file:bg-brand-primary/20 file:cursor-pointer cursor-pointer transition-colors">
            </div>

            @if(isset($event))
            <fieldset>
                <legend class="block text-sm font-semibold text-ink mb-1.5">Status Event</legend>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center justify-center gap-2 border rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors {{ $event->is_active ? 'border-brand-primary bg-brand-primary-soft' : 'border-gray-200 hover:border-brand-secondary' }}">
                        <input type="radio" name="is_active" value="1" {{ $event->is_active ? 'checked' : '' }} class="w-4 h-4 accent-brand-primary">
                        <span class="text-sm font-medium text-ink">Aktif (Buka Pendaftaran)</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 border rounded-lg px-3 py-2.5 min-h-[44px] cursor-pointer transition-colors {{ !$event->is_active ? 'border-brand-primary bg-brand-primary-soft' : 'border-gray-200 hover:border-brand-secondary' }}">
                        <input type="radio" name="is_active" value="0" {{ !$event->is_active ? 'checked' : '' }} class="w-4 h-4 accent-brand-primary">
                        <span class="text-sm font-medium text-ink">Tutup</span>
                    </label>
                </div>
            </fieldset>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 sm:flex-none min-h-[44px] bg-brand-primary text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
                    Simpan Event
                </button>
                <a href="{{ route('admin.events.index') }}" class="flex-1 sm:flex-none text-center min-h-[44px] flex items-center justify-center bg-white border border-gray-300 text-ink font-semibold px-6 py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
