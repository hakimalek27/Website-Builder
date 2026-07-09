<x-mail::message>
# Borang Dihantar

PIC bagi **{{ $project->mosque_name }}** telah melengkapkan & menghantar borang wizard.

Status projek kini: **Telah Dihantar**. Sila semak dan proses penjanaan draf.

<x-mail::button :url="config('app.url') . '/admin'">
Buka Panel Admin
</x-mail::button>

Sistem REKA
</x-mail::message>
