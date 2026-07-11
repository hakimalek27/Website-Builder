{{-- §Fasa 13/15 — grid AJK verbatim (render LOKAL; cap 12). Grid auto-fit responsif (rk-*). --}}
<div data-reka="ajk" class="rk-ajk">
    <div class="rk-ajk__grid">
        @foreach ($members as $m)
            <div class="rk-ajk__card">
                <p class="rk-ajk__name">{{ $m['name'] ?? '' }}</p>
                <p class="rk-ajk__role">{{ $m['position'] ?? '' }}</p>
            </div>
        @endforeach
    </div>
    @if ($total > count($members))
        <p class="rk-ajk__more">Senarai penuh ({{ $total }} ahli) akan dipaparkan di laman sebenar.</p>
    @endif
</div>
