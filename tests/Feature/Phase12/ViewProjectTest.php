<?php

use App\Enums\ProjectStatus;
use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Models\Generation;
use App\Models\Note;
use App\Models\ProjectSection;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Fasa 12 W2 — paparan penuh projek di admin + balas nota.

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function adminProject(): array
{
    [$project, $token] = picSession(['status' => ProjectStatus::Submitted]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_1', 'data' => [
        'official_name' => 'Masjid Ujian', 'phone_primary' => '03-1234 5678', 'city' => 'KL',
    ]]);
    ProjectSection::create(['project_id' => $project->id, 'section_key' => 'step_4', 'data' => ['panels' => [
        'ajk' => ['members' => [['name' => 'En. Ali', 'position' => 'Pengerusi']]],
        'infaq' => ['bank_name' => 'Bank Islam', 'bank_account' => '1234567890', 'account_holder' => 'Masjid Ujian'],
    ]]]);
    $project->invitation->update(['pic_name' => 'Ustaz PIC', 'pic_phone' => '0123456789']);
    Note::create(['project_id' => $project->id, 'author' => 'pic', 'author_name' => 'Ustaz PIC', 'kind' => 'general', 'body' => 'Saya nak laman moden.']);

    return [$project, $token];
}

it('shows full project details incl. wizard values, full bank and notes', function () {
    [$project] = adminProject();

    Livewire::test(ViewProject::class, ['record' => $project->id])
        ->assertOk()
        ->assertSee('Masjid Ujian')
        ->assertSee('En. Ali')
        ->assertSee('1234567890')          // bank PENUH untuk admin (tidak bermask)
        ->assertSee('Saya nak laman moden');
});

it('marks unread PIC notes as read on view', function () {
    [$project] = adminProject();
    expect($project->notes()->where('author', 'pic')->whereNull('read_at')->count())->toBe(1);

    Livewire::test(ViewProject::class, ['record' => $project->id])->assertOk();

    expect($project->fresh()->notes()->where('author', 'pic')->whereNull('read_at')->count())->toBe(0);
});

it('lets admin reply to a note and notifies the PIC', function () {
    Setting::put('whatsapp_gateway_url', 'https://wa.test');
    Http::fake(['*wa.test*' => Http::response(['success' => true], 200)]);
    [$project] = adminProject();

    Livewire::test(ViewProject::class, ['record' => $project->id])
        ->callAction('balasNota', ['body' => 'Terima kasih, kami proses.', 'hantar_wa' => true]);

    expect($project->notes()->where('author', 'admin')->count())->toBe(1);
    $this->assertDatabaseHas('notification_logs', ['project_id' => $project->id, 'event' => 'note.admin_replied']);
});

it('shows and copies the engineered prompt when one exists (§Fasa 14)', function () {
    [$project] = adminProject();
    Generation::factory()->succeeded()->for($project)->create([
        'input_snapshot' => ['pipeline' => 'html', 'engineered_prompt' => 'Bina draf HTML Masjid Ujian.'],
    ]);

    Livewire::test(ViewProject::class, ['record' => $project->id])
        ->assertActionVisible('salinPrompt')
        ->callAction('salinPrompt')
        ->assertHasNoActionErrors();
});

it('hides the copy-prompt action when no prompt exists (§Fasa 14)', function () {
    [$project] = adminProject();
    // Hanya penjanaan shell (tiada engineered_prompt).
    Generation::factory()->succeeded()->for($project)->create(['input_snapshot' => ['pipeline' => 'shell']]);

    Livewire::test(ViewProject::class, ['record' => $project->id])
        ->assertActionHidden('salinPrompt');
});

it('serves the admin draft route to authed users and blocks guests', function () {
    Storage::fake('local');
    [$project] = adminProject();
    $gen = Generation::factory()->succeeded()->for($project)->create(['rendered_path' => 'drafts/x/y.html']);
    Storage::disk('local')->put('drafts/x/y.html', '<html>draf</html>');

    $this->get(route('admin.draf', $gen))->assertOk();

    auth()->logout();
    $this->get(route('admin.draf', $gen))->assertForbidden();
});
