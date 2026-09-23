@extends('layouts.app')

@section('title', 'Daftar Organisasi — TiketIn')

@section('content')
<div class="max-w-xl mx-auto px-4 lg:px-8 py-10 md:py-14">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-extrabold text-ink">Daftar Organisasi / Institusi</h1>
        <p class="text-ink-muted text-sm mt-1">Punya event? Kelola tiketnya sendiri lewat TiketIn.</p>
    </div>

    @if(session('success'))
        <div role="status" class="flex items-start gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 p-3.5 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
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

    <form action="{{ route('organizations.store') }}" method="POST" class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft border border-gray-100 space-y-6">
        @csrf

        <fieldset class="space-y-4">
            <legend class="text-sm font-bold text-brand-primary uppercase tracking-wide mb-1">Data Organisasi</legend>

            <div>
                <label for="organization_name" class="block text-sm font-semibold text-ink mb-1.5">Nama Organisasi / Institusi</label>
                <input id="organization_name" type="text" name="organization_name" value="{{ old('organization_name') }}" required
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="organization_email" class="block text-sm font-semibold text-ink mb-1.5">Email Organisasi</label>
                <input id="organization_email" type="email" name="organization_email" value="{{ old('organization_email') }}" required
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="organization_phone" class="block text-sm font-semibold text-ink mb-1.5">No. Telepon (opsional)</label>
                <input id="organization_phone" type="tel" inputmode="numeric" name="organization_phone" value="{{ old('organization_phone') }}"
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-ink mb-1.5">Deskripsi Singkat (opsional)</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">{{ old('description') }}</textarea>
            </div>
        </fieldset>

        <fieldset class="space-y-4 pt-2 border-t border-dashed border-gray-200">
            <legend class="text-sm font-bold text-brand-primary uppercase tracking-wide mb-1 pt-4">Akun Admin</legend>

            <div>
                <label for="admin_name" class="block text-sm font-semibold text-ink mb-1.5">Nama Kamu</label>
                <input id="admin_name" type="text" name="admin_name" value="{{ old('admin_name') }}" required
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div>
                <label for="admin_email" class="block text-sm font-semibold text-ink mb-1.5">Email Login</label>
                <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}" required autocomplete="email"
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-ink mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password"
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-ink mb-1.5">Ulangi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"
                        class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors">
                </div>
            </div>
        </fieldset>

        <p class="text-xs text-ink-muted">
            Pendaftaran akan ditinjau dulu oleh tim kami sebelum akun bisa dipakai login.
        </p>

        <button type="submit" class="w-full min-h-[44px] bg-brand-primary text-white font-bold py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
            Kirim Pendaftaran
        </button>
    </form>
</div>
@endsection
