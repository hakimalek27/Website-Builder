<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'REKA — Laman Web Masjid')</title>
    <meta name="description" content="@yield('meta_description', 'Platform tempahan laman web rasmi masjid — direka khusus, bukan template.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand: #1B5E3F;
            --brand-dark: #0F3D27;
            --accent: #C9A961;
            --ink: #1A1A1A;
            --bg: #FAF7F2;
            --bg-alt: #EFE8DC;
        }
        body { background: var(--bg); color: var(--ink); font-family: ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">
    <header class="sticky top-0 z-40 bg-[#FAF7F2]/90 backdrop-blur border-b border-[#EFE8DC]">
        <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-2 font-bold text-[#0F3D27] text-lg">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#1B5E3F] text-white">R</span>
                REKA
            </a>
            <a href="{{ route('minat.create') }}"
               class="rounded-lg bg-[#1B5E3F] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0F3D27] transition">
                Daftar Minat
            </a>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-[#EFE8DC] bg-[#EFE8DC]/40">
        <div class="mx-auto max-w-6xl px-4 py-8 text-sm text-[#1A1A1A]/70 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p>&copy; {{ date('Y') }} {{ config('reka.business_name') }}. Platform REKA.</p>
            <nav class="flex gap-4">
                <a href="{{ route('privasi') }}" class="hover:text-[#1B5E3F]">Notis Privasi</a>
                <a href="{{ route('terma') }}" class="hover:text-[#1B5E3F]">Terma</a>
            </nav>
        </div>
    </footer>
</body>
</html>
