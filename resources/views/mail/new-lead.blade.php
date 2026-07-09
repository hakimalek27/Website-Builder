<x-mail::message>
# Lead Baharu Diterima

Satu permohonan minat baharu telah masuk ke sistem REKA.

**Masjid/Surau:** {{ $lead->mosque_name }}
**Negeri:** {{ $lead->state }}
**Wakil (PIC):** {{ $lead->pic_name }}
**Telefon:** {{ $lead->pic_phone }}
@if ($lead->pic_email)
**E-mel:** {{ $lead->pic_email }}
@endif
@if ($lead->current_website)
**Laman sedia ada:** {{ $lead->current_website }}
@endif
@if ($lead->notes)

**Catatan:** {{ $lead->notes }}
@endif

<x-mail::button :url="config('app.url') . '/admin'">
Buka Panel Admin
</x-mail::button>

Terima kasih,<br>
Sistem REKA
</x-mail::message>
