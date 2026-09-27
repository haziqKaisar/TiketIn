@extends('layouts.app')

@section('title', 'Profil Organisasi — TiketIn')

@section('content')
<div class="max-w-2xl mx-auto px-4 lg:px-8 py-8 md:py-10">
    <h1 class="text-2xl font-extrabold text-ink mb-1">Profil Organisasi</h1>
    <p class="text-ink-muted text-sm mb-6">Informasi ini tampil di halaman event yang kamu buat.</p>

    @if(session('success'))
        <div role="status" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-3.5 mb-5 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.organization.update') }}" method="POST" enctype="multipart/form-data"
        class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-gray-100 space-y-5">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-brand-primary-soft grid place-items-center overflow-hidden shrink-0">
                @if($organization->logo)
                    <img src="{{ Storage::url($organization->logo) }}" alt="Logo {{ $organization->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-7 h-7 text-brand-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 9h6v12M9 9h.01M9 13h.01M9 17h.01"/></svg>
                @endif
            </div>
            <div class="flex-1">
                <label for="logo" class="block text-sm font-semibold text-ink mb-1.5">Logo</label>
                <input id="logo" type="file" name="logo" accept="image/*"
                    class="w-full text-sm text-ink-muted min-h-[44px] rounded-lg border border-gray-300
                        file:mr-4 file:h-full file:px-4 file:border-0 file:bg-brand-primary-soft file:text-brand-primary file:font-semibold file:text-sm
                        hover:file:bg-brand-primary/20 file:cursor-pointer cursor-pointer transition-colors">
            </div>
        </div>

        <div class="pt-2 border-t border-dashed border-gray-200"></div>

        <div>
            <label for="name" class="block text-sm font-semibold text-ink mb-1.5">Nama Organisasi</label>
            <input id="name" type="text" name="name" value="{{ old('name', $organization->name) }}" required
                class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Email Organisasi</label>
            <input id="email" type="email" name="email" value="{{ old('email', $organization->email) }}" required
                class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
        </div>

        <div>
            <label for="phone" class="block text-sm font-semibold text-ink mb-1.5">No. Telepon</label>
            <input id="phone" type="tel" inputmode="numeric" name="phone" value="{{ old('phone', $organization->phone) }}"
                class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
        </div>

        <div>
            <label for="description" class="block text-sm font-semibold text-ink mb-1.5">Deskripsi</label>
            <textarea id="description" name="description" rows="4"
                class="w-full border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">{{ old('description', $organization->description) }}</textarea>
        </div>

        <button type="submit" class="w-full sm:w-auto min-h-[44px] bg-brand-primary text-white font-bold px-6 py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
            Simpan Perubahan
        </button>
    </form>
</div>
@endsection
