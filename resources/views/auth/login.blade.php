@extends('layouts.app')

@section('title', 'Login Panitia — TiketIn')

@section('content')
<div class="max-w-md mx-auto px-4 lg:px-8 py-16 md:py-24">
    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-soft-lg border border-gray-100">

        <div class="text-center mb-6">
            <span class="mx-auto grid place-items-center w-12 h-12 rounded-full bg-brand-primary-soft text-brand-primary mb-4" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 1 0-8 0v2"/></svg>
            </span>
            <h1 class="text-2xl font-extrabold text-ink">Login Panitia</h1>
            <p class="text-ink-muted text-sm mt-1">Masuk untuk mengelola event dan tiket</p>
        </div>

        @if($errors->any())
            <div role="alert" class="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-3.5 mb-5 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-5" x-data="{ showPassword: false }">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-ink mb-1.5">Email</label>
                <input
                    id="email" type="email" name="email" value="{{ old('email') }}"
                    required autofocus autocomplete="email"
                    class="w-full min-h-[44px] border border-gray-300 px-3.5 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-ink mb-1.5">Password</label>
                <div class="relative">
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password" name="password"
                        required autocomplete="current-password"
                        class="w-full min-h-[44px] border border-gray-300 pl-3.5 pr-11 py-2.5 rounded-lg focus:outline-none focus:border-brand-primary focus:ring-2 focus:ring-brand-primary/20 transition-colors"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                        class="absolute right-1 top-1/2 -translate-y-1/2 min-h-[36px] min-w-[36px] grid place-items-center text-ink-muted hover:text-ink rounded-lg transition-colors"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.24 4.24M6.6 6.6C4.2 8.2 2.5 12 2.5 12s3.5 7 9.5 7c1.9 0 3.5-.5 4.9-1.3M17.4 17.4C19.5 15.9 21.5 12 21.5 12s-1.4-2.8-3.8-4.7"/></svg>
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-muted min-h-[44px]">
                <input type="checkbox" name="remember" value="1" class="w-4 h-4 accent-brand-primary rounded">
                Ingat saya
            </label>

            <button type="submit" class="w-full min-h-[44px] bg-brand-primary text-white font-bold py-2.5 rounded-lg hover:bg-brand-primary-dark active:scale-[0.98] transition-all">
                Masuk
            </button>
        </form>
    </div>
</div>
@endsection
