<x-mail::message>
# Draf Diluluskan

PIC bagi **{{ $project->mosque_name }}** telah **meluluskan** draf laman.

Status kini: **Diluluskan**. Sila eksport pakej serahan dari panel admin.

<x-mail::button :url="config('app.url') . '/admin'">
Eksport Pakej Serahan
</x-mail::button>

Sistem REKA
</x-mail::message>
