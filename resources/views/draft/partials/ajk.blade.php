{{-- §Fasa 13 — grid AJK verbatim (render LOKAL, bukan AI; cap 12). --}}
<div data-reka="ajk" style="max-width:1080px;margin:0 auto;padding:12px 20px">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">
        @foreach ($members as $m)
            <div style="background:#fff;border:1px solid {{ $t['bgAlt'] }};border-radius:{{ $t['radius'] }};padding:22px;text-align:center">
                <p style="font-weight:700;color:{{ $t['primaryDark'] }}">{{ $m['name'] ?? '' }}</p>
                <p style="opacity:.65;font-size:.85rem">{{ $m['position'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
    @if ($total > count($members))
        <p style="text-align:center;margin-top:16px;opacity:.6;font-size:.85rem">Senarai penuh ({{ $total }} ahli) akan dipaparkan di laman sebenar.</p>
    @endif
</div>
