<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use App\Support\ProjectDataPresenter;
use Closure;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Fasa 12 W2 — paparan penuh butiran projek untuk admin (semua data wizard, aset,
 * draf/kos, nota). Dirender sebagai Markdown melalui ProjectDataPresenter.
 */
class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan & Hubungi')->schema([
                self::md('overview', fn (Project $record) => self::overviewMd($record)),
                self::md('s0', fn (Project $record) => self::stepMd($record, 0)),
                self::md('s1', fn (Project $record) => self::stepMd($record, 1)),
            ]),
            Section::make('Reka Bentuk')->collapsible()->collapsed()->schema([
                self::md('s2', fn (Project $record) => self::stepMd($record, 2)),
            ]),
            Section::make('Halaman & Kandungan')->collapsible()->collapsed()->schema([
                self::md('s3', fn (Project $record) => self::stepMd($record, 3)),
                self::md('s4', fn (Project $record) => self::stepMd($record, 4)),
            ]),
            Section::make('Ciri & Teknikal')->collapsible()->collapsed()->schema([
                self::md('s5', fn (Project $record) => self::stepMd($record, 5)),
                self::md('s8', fn (Project $record) => self::stepMd($record, 8)),
            ]),
            Section::make('Rujukan & Nota Borang')->collapsible()->collapsed()->schema([
                self::md('s7', fn (Project $record) => self::stepMd($record, 7)),
                self::md('s9', fn (Project $record) => self::stepMd($record, 9)),
            ]),
            Section::make('Aset')->collapsible()->collapsed()->schema([
                self::md('assets', fn (Project $record) => self::assetsMd($record)),
            ]),
            Section::make('Draf & Kos')->collapsible()->collapsed()->schema([
                self::md('gens', fn (Project $record) => self::generationsMd($record)),
            ]),
            Section::make('Nota / Perbualan')->collapsible()->collapsed()->schema([
                self::md('notes', fn (Project $record) => self::notesMd($record)),
            ]),
            Section::make('Jemputan & PIC')->collapsible()->collapsed()->schema([
                self::md('inv', fn (Project $record) => self::invitationMd($record)),
            ]),
        ]);
    }

    private static function md(string $key, Closure $state): TextEntry
    {
        return TextEntry::make($key)->hiddenLabel()->state($state)->markdown()->columnSpanFull();
    }

    /** @return array<int, array{step:int,title:string,subtitle:string,markdown:string}> keyed by step */
    private static function blocks(Project $record): array
    {
        static $cache = [];

        return $cache[$record->getKey()] ??= (new Collection(ProjectDataPresenter::all($record)))->keyBy('step')->all();
    }

    private static function stepMd(Project $record, int $step): string
    {
        $b = self::blocks($record)[$step] ?? null;

        return $b ? $b['markdown'] : '_Tiada data untuk langkah ini._';
    }

    private static function overviewMd(Project $r): string
    {
        $lines = [
            "- **Status:** {$r->status->label()}",
            "- **Jenis (tier):** {$r->tier->value}",
            "- **Kuota AI:** {$r->quota_ai_used} / {$r->quota_ai_total}",
            '- **Kos AI (USD):** '.number_format((float) $r->generations()->sum('cost_estimate'), 4),
        ];
        if ($r->submitted_at) {
            $lines[] = '- **Dihantar:** '.$r->submitted_at->format('d/m/Y H:i');
        }
        if ($r->approved_at) {
            $lines[] = '- **Diluluskan:** '.$r->approved_at->format('d/m/Y H:i');
        }

        return implode("\n", $lines);
    }

    private static function assetsMd(Project $r): string
    {
        $assets = $r->assets()->orderBy('kind')->get();
        if ($assets->isEmpty()) {
            return '_Tiada aset dimuat naik._';
        }
        $lines = ['| Jenis | Nama fail | Saiz | Pautan |', '|---|---|---|---|'];
        foreach ($assets as $a) {
            $size = $a->size ? round($a->size / 1024).' KB' : '—';
            $name = $a->original_name ?? basename((string) $a->path);
            $lines[] = "| {$a->kind} | {$name} | {$size} | [buka](".route('admin.aset', $a).') |';
        }

        return implode("\n", $lines);
    }

    private static function generationsMd(Project $r): string
    {
        $gens = $r->generations()->latest()->get();
        if ($gens->isEmpty()) {
            return '_Belum ada penjanaan draf._';
        }
        $lines = ['| Masa | Jenis | Status | Token (in/out) | Kos USD | Draf |', '|---|---|---|---|---|---|'];
        foreach ($gens as $g) {
            $draf = ($g->status->value === 'succeeded' && $g->rendered_path) ? '[lihat]('.route('admin.draf', $g).')' : '—';
            $lines[] = "| {$g->created_at->format('d/m H:i')} | {$g->type->value} | {$g->status->value} | {$g->tokens_in}/{$g->tokens_out} | "
                .number_format((float) $g->cost_estimate, 4)." | {$draf} |";
        }

        // Butiran saluran HTML (§Fasa 13): prompt jurutera + pecahan kos 2 peringkat + tweak.
        foreach ($gens as $g) {
            $snap = $g->input_snapshot ?? [];
            if (($snap['pipeline'] ?? null) !== 'html') {
                continue;
            }
            $s1 = $snap['stage1'] ?? [];
            $s2 = $snap['stage2'] ?? [];
            $lines[] = "\n#### Draf HTML · {$g->created_at->format('d/m/Y H:i')} ({$g->status->value})";

            if (($s1['source'] ?? null) === 'ai') {
                $lines[] = '- **Peringkat 1 (jurutera prompt):** '.($s1['model'] ?? '—').' · '
                    .($s1['tokens_in'] ?? 0).'/'.($s1['tokens_out'] ?? 0).' tok · USD '.number_format((float) ($s1['cost'] ?? 0), 4);
            } elseif (($s1['source'] ?? null) === 'tweak') {
                $lines[] = '- **Sumber:** tweak daripada draf sebelumnya';
            }
            if ($s2 !== []) {
                $lines[] = '- **Peringkat 2 (jana HTML):** '.($s2['model'] ?? '—').' · '
                    .($s2['tokens_in'] ?? 0).'/'.($s2['tokens_out'] ?? 0).' tok · '.($s2['attempts'] ?? 1).' percubaan';
            }
            if (! empty($snap['tweak'])) {
                $cats = implode(', ', $snap['tweak']['categories'] ?? []);
                $lines[] = '- **Arahan tweak:** '.($cats !== '' ? "({$cats}) " : '').($snap['tweak']['message'] ?? '');
            }

            $dl = [];
            if (filled($snap['engineered_prompt'] ?? null)) {
                $dl[] = '[muat turun prompt]('.route('admin.prompt', $g).')';
            }
            if ($g->rendered_path) {
                $dl[] = '[muat turun HTML]('.route('admin.draf.muat', $g).')';
            }
            if ($dl !== []) {
                $lines[] = '- '.implode(' · ', $dl);
            }

            // Pratonton prompt (penuh: muat turun) — pagar ~~~~ kerana prompt mungkin ada ```.
            if (filled($snap['engineered_prompt'] ?? null)) {
                $lines[] = "\n**Prompt jurutera (pratonton):**\n\n~~~~\n".Str::limit((string) $snap['engineered_prompt'], 800)."\n~~~~";
            }
        }

        return implode("\n", $lines);
    }

    private static function notesMd(Project $r): string
    {
        $notes = $r->notes()->oldest()->get();
        if ($notes->isEmpty()) {
            return '_Tiada nota._';
        }
        $lines = [];
        foreach ($notes as $n) {
            $who = $n->author === 'pic' ? 'PIC' : 'Admin REKA';
            $body = str_replace("\n", "\n> ", (string) $n->body);
            $lines[] = "**{$who}** ({$n->author_name}) · {$n->created_at->format('d/m/Y H:i')}\n> {$body}\n";
        }

        return implode("\n", $lines);
    }

    private static function invitationMd(Project $r): string
    {
        $inv = $r->invitation;
        if ($inv === null) {
            return '_Tiada jemputan._';
        }

        return implode("\n", [
            '- **Nama PIC:** '.($inv->pic_name ?? '—'),
            '- **Telefon PIC:** '.($inv->pic_phone ?? '—'),
            '- **E-mel PIC:** '.($inv->pic_email ?? '—'),
            '- **Dibuka kali pertama:** '.($inv->opened_at?->format('d/m/Y H:i') ?? 'belum dibuka'),
            '- **Bilangan buka:** '.($inv->opens_count ?? 0),
            '- **Luput:** '.($inv->expires_at?->format('d/m/Y') ?? '—'),
        ]);
    }
}
