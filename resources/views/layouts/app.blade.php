<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'TiketIn') . ' — Platform Tiket Event')</title>
    <meta name="description" content="@yield('meta_description', 'Temukan dan beli tiket event favoritmu, aman dan langsung dapat e-ticket.')">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        'brand-primary': '#525EA7',
                        'brand-primary-dark': '#454C8C',
                        'brand-primary-soft': '#EEF0FA',
                        'brand-accent': '#FFC349',
                        'brand-accent-dark': '#E6A928',
                        'brand-secondary': '#5FACD3',
                        'brand-surface': '#97DDE9',
                        'ink': '#1F2937',
                        'ink-muted': '#4B5768',
                    },
                    boxShadow: {
                        'soft': '0 1px 2px rgba(31, 41, 55, 0.04), 0 6px 16px -4px rgba(82, 94, 167, 0.12)',
                        'soft-lg': '0 4px 8px rgba(31, 41, 55, 0.04), 0 16px 32px -8px rgba(82, 94, 167, 0.18)',
                        'inset-line': 'inset 0 1px 0 0 rgba(255,255,255,0.08)',
                    },
                    keyframes: {
                        'rise-in': {
                            '0%':   { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                    animation: {
                        'rise-in': 'rise-in 0.5s cubic-bezier(0.16, 1, 0.3, 1) both',
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background-color: #F7F8FC; color: #1F2937; }

        /* Visible keyboard focus indicator everywhere — never suppressed without a replacement */
        *:focus-visible {
            outline: 2.5px solid #525EA7;
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* Ticket perforation edge — the recurring "torn stub" motif used across the app */
        .ticket-perforation {
            background-image: radial-gradient(circle, #F7F8FC 3px, transparent 3.5px);
            background-size: 14px 14px;
            background-repeat: repeat-y;
            background-position: center;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                transition-duration: 0.001ms !important;
                scroll-behavior: auto !important;
            }
        }

        @media print {
            nav, footer, .no-print { display: none !important; }
        }

        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="antialiased min-h-screen flex flex-col">

    <!-- Skip link: first focusable element, lets keyboard users bypass the nav -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[100] focus:bg-white focus:text-brand-primary focus:font-semibold focus:px-4 focus:py-3 focus:rounded-lg focus:shadow-soft-lg">
        Langsung ke konten utama
    </a>

    <div x-data="{ mobileMenuOpen: false }">
        <!-- Navbar -->
        <nav class="bg-brand-primary sticky top-0 z-50 shadow-soft" aria-label="Navigasi utama">
            <div class="max-w-6xl mx-auto px-4 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-white font-bold text-xl tracking-tight rounded-lg py-2">
                        <span class="grid place-items-center w-9 h-9 bg-brand-accent text-ink rounded-lg" aria-hidden="true">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z"/>
                                <path d="M13 5v14" stroke-dasharray="2.5 2.5"/>
                            </svg>
                        </span>
                        TiketIn
                    </a>

                    <!-- Navigasi desktop -->
                    <div class="hidden md:flex items-center gap-2 text-sm font-medium">
                        <a href="{{ route('ticket.check') }}" class="text-white/85 hover:text-white hover:bg-white/10 transition-colors px-4 py-2.5 rounded-lg flex items-center gap-2 min-h-[44px]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m2.2-5.3a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z"/></svg>
                            Cek Pesanan
                        </a>

                        @auth
                            <div class="h-6 w-px bg-white/20 mx-1" aria-hidden="true"></div>
                            <a href="{{ route('admin.dashboard') }}" class="text-white/85 hover:text-white hover:bg-white/10 px-4 py-2.5 rounded-lg transition-colors min-h-[44px] flex items-center">Dashboard</a>
                            <a href="{{ route('scan.index') }}" class="text-white/85 hover:text-white hover:bg-white/10 px-4 py-2.5 rounded-lg transition-colors min-h-[44px] flex items-center">Scanner Gerbang</a>

                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="text-white/85 hover:text-white hover:bg-white/10 px-4 py-2.5 rounded-lg transition-colors font-medium min-h-[44px]">
                                    Keluar
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="bg-brand-accent text-ink hover:bg-brand-accent-dark active:scale-[0.98] px-5 py-2.5 rounded-lg font-semibold shadow-soft transition-all min-h-[44px] flex items-center ml-1">
                                Login Panitia
                            </a>
                        @endauth
                    </div>

                    <!-- Tombol menu mobile -->
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen.toString()"
                        aria-controls="mobile-menu"
                        class="md:hidden grid place-items-center w-11 h-11 -mr-1 text-white rounded-lg hover:bg-white/10 transition-colors"
                    >
                        <span class="sr-only">Buka menu navigasi</span>
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Menu mobile -->
                <div
                    id="mobile-menu"
                    x-show="mobileMenuOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="md:hidden pb-4 flex flex-col gap-1 border-t border-white/15 pt-3"
                    style="display:none"
                >
                    <a href="{{ route('ticket.check') }}" class="text-white/90 hover:bg-white/10 px-3 py-3 rounded-lg min-h-[44px] flex items-center gap-2">Cek Pesanan</a>
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="text-white/90 hover:bg-white/10 px-3 py-3 rounded-lg min-h-[44px] flex items-center">Dashboard</a>
                        <a href="{{ route('scan.index') }}" class="text-white/90 hover:bg-white/10 px-3 py-3 rounded-lg min-h-[44px] flex items-center">Scanner Gerbang</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left text-white/90 hover:bg-white/10 px-3 py-3 rounded-lg min-h-[44px]">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-brand-accent text-ink px-3 py-3 rounded-lg font-semibold min-h-[44px] flex items-center justify-center mt-1">Login Panitia</a>
                    @endauth
                </div>
            </div>
        </nav>
    </div>

    <!-- Konten Utama -->
    <main id="main-content" class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto no-print">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2.5 text-ink font-bold">
                    <span class="grid place-items-center w-7 h-7 bg-brand-primary-soft text-brand-primary rounded-md" aria-hidden="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8Z"/></svg>
                    </span>
                    TiketIn
                </div>
                <p class="text-sm text-ink-muted text-center md:text-right">
                    &copy; {{ date('Y') }} TiketIn Platform. Semua tiket terverifikasi QR.
                </p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    @stack('scripts')
</body>
</html>
