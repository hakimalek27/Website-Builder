{{-- §Fasa 13 — kad waktu solat STATIK berlabel (§8.5/§9.3; masjid sahaja). --}}
<div data-reka="prayer" style="background:#fff;border:1px solid {{ $t['bgAlt'] }};border-radius:{{ $t['radius'] }};padding:20px;margin:20px auto;max-width:720px">
    <p style="font-size:.75rem;color:rgba(0,0,0,.55);text-align:center;margin-bottom:12px">Contoh paparan — waktu sebenar akan diambil terus dari JAKIM e-Solat (zon {{ $zone }})</p>
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;text-align:center">
        @foreach (['Subuh' => '5:55', 'Syuruk' => '7:10', 'Zohor' => '13:15', 'Asar' => '16:38', 'Maghrib' => '19:29', 'Isyak' => '20:42'] as $name => $time)
            <div><span style="display:block;font-size:.7rem;opacity:.6">{{ $name }}</span><span style="display:block;font-weight:700;color:{{ $t['primary'] }}">{{ $time }}</span></div>
        @endforeach
    </div>
</div>
