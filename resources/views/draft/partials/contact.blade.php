{{-- §Fasa 13 — jalur hubungi verbatim (render LOKAL, bukan AI). --}}
<section data-reka="contact" style="padding:40px 20px;text-align:center;background:{{ $t['bgAlt'] }}">
    <h2 style="color:{{ $t['primaryDark'] }};margin-bottom:16px">Hubungi Kami</h2>
    <div style="display:flex;gap:22px;justify-content:center;flex-wrap:wrap;font-size:.95rem">
        @if (! empty($contact['phone']))<span>&#9742; {{ $contact['phone'] }}</span>@endif
        @if (! empty($contact['email']))<span>&#9993; {{ $contact['email'] }}</span>@endif
        @if (! empty($contact['address']))<span>&#128205; {{ $contact['address'] }}</span>@endif
    </div>
    @if (! empty($socials))
        <div style="margin-top:14px;display:flex;gap:16px;justify-content:center;font-size:.85rem;opacity:.85">
            @foreach ($socials as $platform => $url)<a href="{{ $url }}" style="color:{{ $t['primary'] }}">{{ ucfirst($platform) }}</a>@endforeach
        </div>
    @endif
</section>
