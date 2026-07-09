<x-mail::message>
# Penjanaan Draf Gagal

Penjanaan draf bagi **{{ $generation->project->mosque_name }}** gagal selepas 3 percubaan.

**Jenis:** {{ $generation->type->value }}
**Ralat:** {{ \Illuminate\Support\Str::limit($generation->error, 300) }}

Kuota PIC TIDAK ditolak (dipulangkan automatik). Sila cuba semula dari panel.

<x-mail::button :url="config('app.url') . '/admin'">
Buka Panel Admin
</x-mail::button>

Sistem REKA
</x-mail::message>
