<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'REKA')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#0F3D27">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="flex min-h-screen flex-col bg-cream">
    <header class="relative overflow-hidden bg-brand-900 text-cream">
        <div class="bg-pattern-islamic absolute inset-0 opacity-40" aria-hidden="true"></div>
        <div class="relative mx-auto flex h-16 w-full max-w-4xl items-center gap-3 px-4">
            <x-ui.logo size="h-8" />
            @isset($project)
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden truncate text-sm text-cream/75 sm:inline">{{ $project->mosque_name }}</span>
                    <x-ui.badge tone="on-dark" class="shrink-0">{{ $project->status->label() }}</x-ui.badge>
                </div>
            @endisset
        </div>
    </header>

    <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-sand py-6 text-center text-xs text-ink/50">
        &copy; {{ date('Y') }} {{ config('reka.business_name') }} · Platform REKA
    </footer>
    @stack('scripts')
</body>

</html>
