{{-- §Fasa 13/15 — kad waktu solat STATIK berlabel (§8.5/§9.3; masjid). Grid auto-fit (rk-*). --}}
<div data-reka="prayer" class="rk-prayer">
    <p class="rk-prayer__label">Contoh paparan — waktu sebenar akan diambil terus dari JAKIM e-Solat (zon {{ $zone }})</p>
    <div class="rk-prayer__grid">
        @foreach (['Subuh' => '5:55', 'Syuruk' => '7:10', 'Zohor' => '13:15', 'Asar' => '16:38', 'Maghrib' => '19:29', 'Isyak' => '20:42'] as $name => $time)
            <div><span class="rk-prayer__name">{{ $name }}</span><span class="rk-prayer__time">{{ $time }}</span></div>
        @endforeach
    </div>
</div>
