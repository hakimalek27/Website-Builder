<!DOCTYPE html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'REKA')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --brand:#1B5E3F; --brand-dark:#0F3D27; --accent:#C9A961; --ink:#1A1A1A; --bg:#FAF7F2; --bg-alt:#EFE8DC; }
        body { background: var(--bg); color: var(--ink); font-family: ui-sans-serif, system-ui, sans-serif; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen flex flex-col antialiased">
    <header class="bg-[#0F3D27] text-white">
        <div class="mx-auto max-w-4xl px-4 h-14 flex items-center gap-2">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#1B5E3F] font-bold">R</span>
            <span class="font-semibold">REKA</span>
            @isset($project)
                <span class="ml-auto text-sm text-[#FAF7F2]/80 truncate">{{ $project->mosque_name }}</span>
            @endisset
        </div>
    </header>

    <main class="flex-1 mx-auto w-full max-w-4xl px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-[#EFE8DC] py-6 text-center text-xs text-[#1A1A1A]/50">
        &copy; {{ date('Y') }} {{ config('reka.business_name') }} · Platform REKA
    </footer>
    @stack('scripts')
</body>
</html>
