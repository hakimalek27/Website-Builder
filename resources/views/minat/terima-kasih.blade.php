@extends('layouts.public')

@section('title', 'Terima kasih — REKA')

@section('content')
    <section class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-64 bg-gradient-to-b from-brand-50 to-transparent" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-xl px-4 py-20 text-center sm:py-28">
            <div class="animate-scale-in mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-brand-600 text-white shadow-glow">
                {!! \App\Support\Lucide::svg('Check', 2.5, 'h-10 w-10') !!}
            </div>
            <h1 class="mt-8 font-display text-4xl font-bold text-brand-800">Terima kasih!</h1>
            <p class="mx-auto mt-4 max-w-md text-ink/65">
                Permohonan anda telah kami terima. Pasukan kami akan menghubungi anda dalam masa
                <strong class="text-ink/80">2 hari bekerja</strong> untuk langkah seterusnya.
            </p>

            <div class="mx-auto mt-10 max-w-sm rounded-2xl bg-white p-6 text-left shadow-soft ring-1 ring-sand">
                <h2 class="text-xs font-semibold tracking-wider text-gold-600 uppercase">Apa berlaku seterusnya</h2>
                <ol class="mt-4 space-y-4">
                    @foreach ([
                        ['Phone', 'Kami hubungi anda', 'Untuk sahkan maklumat & keperluan masjid.'],
                        ['Mail', 'Pautan wizard dihantar', 'Pautan peribadi & selamat untuk mengisi maklumat.'],
                        ['Sparkles', 'Draf laman dijana', 'Anda semak, tweak & luluskan.'],
                    ] as $i => [$icon, $title, $desc])
                        <li class="flex gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600">
                                {!! \App\Support\Lucide::svg($icon, 1.75, 'h-4 w-4') !!}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-brand-800">{{ $title }}</p>
                                <p class="text-xs text-ink/55">{{ $desc }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <x-ui.button :href="route('landing')" variant="outline" class="mt-10">
                {!! \App\Support\Lucide::svg('ArrowLeft', 2, 'h-4 w-4') !!}
                Kembali ke laman utama
            </x-ui.button>
        </div>
    </section>
@endsection
