{{-- §Fasa 13/15 — blok bank verbatim (render LOKAL, bukan AI). Kelas kit rk-*. --}}
<div data-reka="bank" class="rk-bank">
    <p class="rk-bank__title">Maklumat Sumbangan</p>
    @if (! empty($bank['bank_name']))<p>{{ $bank['bank_name'] }}</p>@endif
    <p class="rk-bank__acc">{{ $bank['bank_account'] ?? '' }}</p>
    @if (! empty($bank['account_holder']))<p class="rk-bank__holder">{{ $bank['account_holder'] }}</p>@endif
</div>
