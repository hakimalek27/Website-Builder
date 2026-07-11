{{-- §Fasa 13/15 — jalur hubungi verbatim (render LOKAL, bukan AI). Kelas kit rk-*. --}}
<section data-reka="contact" class="rk-section rk-section--alt rk-contact">
    <div class="rk-container">
        <span class="rk-eyebrow">Hubungi</span>
        <h2 class="rk-heading-2 rk-mt-2">Hubungi Kami</h2>
        <div class="rk-contact__row rk-mt-3">
            @if (! empty($contact['phone']))<span>&#9742; {{ $contact['phone'] }}</span>@endif
            @if (! empty($contact['email']))<span>&#9993; {{ $contact['email'] }}</span>@endif
            @if (! empty($contact['address']))<span>&#128205; {{ $contact['address'] }}</span>@endif
        </div>
        @if (! empty($socials))
            <div class="rk-contact__social">
                @foreach ($socials as $platform => $url)<a href="{{ $url }}">{{ ucfirst($platform) }}</a>@endforeach
            </div>
        @endif
    </div>
</section>
