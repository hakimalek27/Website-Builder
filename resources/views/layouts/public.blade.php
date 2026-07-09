<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'REKA — Laman Web Masjid')</title>
    <meta name="description" content="@yield('meta_description', 'Platform tempahan laman web rasmi masjid — direka khusus, bukan template.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0F3D27">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen flex-col">
    <header data-site-header class="sticky top-0 z-50 border-b border-white/10 bg-brand-950/70 backdrop-blur-xl transition-shadow">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('landing') }}" class="text-cream transition-opacity hover:opacity-90" aria-label="REKA — laman utama">
                <x-ui.logo size="h-8" />
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-cream/70 md:flex">
                <a href="{{ route('landing') }}#cara" class="transition-colors hover:text-cream">Cara Kerja</a>
                <a href="{{ route('landing') }}#pakej" class="transition-colors hover:text-cream">Pakej Reka</a>
                <a href="{{ route('landing') }}#soalan" class="transition-colors hover:text-cream">Soalan Lazim</a>
            </nav>

            <x-ui.button :href="route('minat.create')" variant="gold" size="sm">
                Daftar Minat
            </x-ui.button>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="relative overflow-hidden bg-brand-950 text-cream">
        <div class="bg-pattern-islamic-lg absolute inset-0 opacity-40" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr]">
                <div>
                    <x-ui.logo size="h-9" class="text-cream" />
                    <p class="mt-4 max-w-xs text-sm/relaxed text-cream/60">
                        Laman web rasmi masjid & surau — direka khusus dengan identiti tersendiri,
                        bukan template seragam.
                    </p>
                </div>
                <div>
                    <h3 class="text-xs font-semibold tracking-wider text-gold-300 uppercase">Navigasi</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-cream/70">
                        <li><a href="{{ route('landing') }}#cara" class="transition-colors hover:text-cream">Cara kerja</a></li>
                        <li><a href="{{ route('landing') }}#pakej" class="transition-colors hover:text-cream">Pakej reka bentuk</a></li>
                        <li><a href="{{ route('landing') }}#soalan" class="transition-colors hover:text-cream">Soalan lazim</a></li>
                        <li><a href="{{ route('minat.create') }}" class="transition-colors hover:text-cream">Daftar minat</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xs font-semibold tracking-wider text-gold-300 uppercase">Perundangan</h3>
                    <ul class="mt-4 space-y-2.5 text-sm text-cream/70">
                        <li><a href="{{ route('privasi') }}" class="transition-colors hover:text-cream">Notis Privasi</a></li>
                        <li><a href="{{ route('terma') }}" class="transition-colors hover:text-cream">Terma Perkhidmatan</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-cream/50 sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ config('reka.business_name') }}. Platform REKA.</p>
                <p class="flex items-center gap-1.5">
                    Dibina dengan ihsan untuk masjid Malaysia
                </p>
            </div>
        </div>
    </footer>
</body>

</html>
