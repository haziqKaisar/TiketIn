@extends('layouts.app')

@section('title', 'Status Organisasi — TiketIn')

@section('content')
<div class="max-w-md mx-auto px-4 lg:px-8 py-16 md:py-24 text-center">
    <div class="bg-white p-8 rounded-2xl shadow-soft border border-gray-100">

        @if(!$organization)
            <div class="mx-auto w-12 h-12 rounded-full bg-rose-50 text-rose-600 grid place-items-center mb-4" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3 2 21h20L12 3Z"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-ink mb-2">Akun Tidak Terhubung ke Organisasi</h1>
            <p class="text-ink-muted text-sm">Hubungi admin TiketIn kalau ini terasa keliru.</p>

        @elseif($organization->status === 'pending')
            <div class="mx-auto w-12 h-12 rounded-full bg-amber-50 text-amber-600 grid place-items-center mb-4" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-ink mb-2">Menunggu Persetujuan</h1>
            <p class="text-ink-muted text-sm">
                Pendaftaran <strong>{{ $organization->name }}</strong> sedang kami tinjau. Kamu akan bisa login penuh
                begitu organisasimu disetujui.
            </p>

        @elseif($organization->status === 'rejected')
            <div class="mx-auto w-12 h-12 rounded-full bg-rose-50 text-rose-600 grid place-items-center mb-4" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-ink mb-2">Pendaftaran Ditolak</h1>
            <p class="text-ink-muted text-sm">
                Maaf, pendaftaran <strong>{{ $organization->name }}</strong> belum bisa kami setujui.
            </p>
            @if($organization->rejected_reason)
                <p class="text-sm text-ink bg-gray-50 border border-gray-200 rounded-lg p-3 mt-4 text-left">
                    {{ $organization->rejected_reason }}
                </p>
            @endif

        @elseif($organization->status === 'suspended')
            <div class="mx-auto w-12 h-12 rounded-full bg-rose-50 text-rose-600 grid place-items-center mb-4" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.36 6.64a9 9 0 1 1-12.73 0M12 3v9"/></svg>
            </div>
            <h1 class="text-xl font-extrabold text-ink mb-2">Organisasi Dinonaktifkan Sementara</h1>
            <p class="text-ink-muted text-sm">Hubungi tim TiketIn untuk informasi lebih lanjut.</p>
        @endif

        <form action="{{ route('logout') }}" method="POST" class="mt-6">
            @csrf
            <button type="submit" class="text-brand-primary font-semibold text-sm hover:underline min-h-[44px]">Keluar</button>
        </form>
    </div>
</div>
@endsection
