<?php

use App\Enums\LeadStatus;
use App\Models\AiProvider;
use App\Models\Lead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Fasa 1 — sulit api_key (§11.5) & prune retensi (§12.8).

it('encrypts ai_provider api_key at rest (§11.5)', function () {
    $plain = 'sk-super-secret-value-123';
    $provider = AiProvider::factory()->create(['api_key' => $plain]);

    // Nilai mentah dalam DB mesti BUKAN plaintext.
    $raw = DB::table('ai_providers')->where('id', $provider->id)->value('api_key');
    expect($raw)->not->toBe($plain);
    expect($raw)->not->toContain($plain);

    // Tetapi model menyahsulit dengan betul.
    expect($provider->fresh()->api_key)->toBe($plain);
});

it('prune removes rejected leads older than 6 months but keeps recent ones (§12.8)', function () {
    // Lead lama (7 bulan lalu) — dicipta dengan masa dibeku.
    Carbon::setTestNow(now()->subMonths(7));
    $oldLead = Lead::factory()->create(['status' => LeadStatus::Rejected]);
    Carbon::setTestNow();

    // Lead ditolak baru-baru ini — mesti kekal.
    $recentLead = Lead::factory()->create(['status' => LeadStatus::Rejected]);

    $this->artisan('reka:prune')->assertSuccessful();

    expect(Lead::find($oldLead->id))->toBeNull();
    expect(Lead::find($recentLead->id))->not->toBeNull();
});
