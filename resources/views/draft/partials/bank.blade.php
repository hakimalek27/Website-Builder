{{-- §Fasa 13 — blok bank verbatim (render LOKAL, bukan AI). --}}
<div data-reka="bank" style="max-width:420px;margin:22px auto 0;text-align:left;background:#fff;border:1px solid {{ $t['bgAlt'] }};border-radius:{{ $t['radius'] }};padding:22px">
    <p style="font-weight:700;margin-bottom:6px;color:{{ $t['primaryDark'] }}">Maklumat Sumbangan</p>
    @if (! empty($bank['bank_name']))<p style="font-size:.9rem">{{ $bank['bank_name'] }}</p>@endif
    <p style="font-size:1.05rem;font-weight:700;color:{{ $t['primary'] }};letter-spacing:.03em">{{ $bank['bank_account'] ?? '' }}</p>
    @if (! empty($bank['account_holder']))<p style="font-size:.85rem;opacity:.7">{{ $bank['account_holder'] }}</p>@endif
</div>
