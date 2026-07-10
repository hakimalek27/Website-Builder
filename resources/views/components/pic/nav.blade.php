{{-- Nav status-aware PIC. Guna $project & $token yang di-share oleh ResolveInvitation. --}}
@php
    use App\Enums\ProjectStatus;

    $showJana = ! in_array($project->status, [
        ProjectStatus::Invited, ProjectStatus::InProgress, ProjectStatus::Cancelled, ProjectStatus::Expired,
    ], true);
    $draft = $project->latestDraft;

    $items = [
        ['label' => 'Utama', 'href' => route('pic.home', ['token' => $token]), 'active' => request()->routeIs('pic.home')],
        ['label' => 'Borang', 'href' => route('pic.step', ['token' => $token, 'step' => 1]), 'active' => request()->routeIs('pic.step')],
        ['label' => 'Semak', 'href' => route('pic.semak', ['token' => $token]), 'active' => request()->routeIs('pic.semak')],
    ];
    if ($showJana) {
        $items[] = ['label' => 'Jana Draf', 'href' => route('pic.jana', ['token' => $token]), 'active' => request()->routeIs('pic.jana')];
    }
    if ($draft) {
        $items[] = [
            'label' => 'Draf',
            'href' => route('pic.draf', ['token' => $token, 'generation' => $draft->id]),
            'active' => request()->routeIs('pic.draf') || request()->routeIs('pic.draf.raw')
                || request()->routeIs('pic.tweak.*') || request()->routeIs('pic.lulus'),
        ];
    }
    $items[] = ['label' => 'Status & Nota', 'href' => route('pic.status', ['token' => $token]), 'active' => request()->routeIs('pic.status')];
@endphp

<nav class="relative border-t border-cream/10" aria-label="Navigasi borang">
    <div class="mx-auto flex w-full max-w-4xl gap-1 overflow-x-auto px-2 py-1.5">
        @foreach ($items as $item)
            <a href="{{ $item['href'] }}" @class([
                'shrink-0 rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap transition',
                'bg-cream/15 text-cream' => $item['active'],
                'text-cream/65 hover:bg-cream/10 hover:text-cream' => ! $item['active'],
            ]) @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
        @endforeach
    </div>
</nav>
