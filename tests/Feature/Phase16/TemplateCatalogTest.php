<?php

use App\Filament\Resources\TemplateCatalogs\Pages\CreateTemplateCatalog;
use App\Filament\Resources\TemplateCatalogs\Pages\ListTemplateCatalogs;
use App\Models\TemplateCatalog;
use App\Models\User;
use Database\Seeders\TemplateCatalogSeeder;
use Livewire\Livewire;

// §Fasa 16 W1/W6 — katalog templat: seeder idempoten, casts, CRUD Filament.

it('seeds the template catalog idempotently', function () {
    $this->seed(TemplateCatalogSeeder::class);
    $count = TemplateCatalog::count();
    expect($count)->toBeGreaterThanOrEqual(14);

    $this->seed(TemplateCatalogSeeder::class);   // seed semula
    expect(TemplateCatalog::count())->toBe($count);
});

it('casts json + boolean columns', function () {
    $t = TemplateCatalog::factory()->create(['categories' => ['masjid', 'ngo'], 'style_tags' => ['moden', 'gelap']]);
    $fresh = $t->fresh();

    expect($fresh->categories)->toBe(['masjid', 'ngo']);
    expect($fresh->style_tags)->toBe(['moden', 'gelap']);
    expect($fresh->is_active)->toBeTrue();
});

it('boots the catalog list and create pages for admin', function () {
    $this->actingAs(User::factory()->create());
    TemplateCatalog::factory()->count(2)->create();

    Livewire::test(ListTemplateCatalogs::class)->assertOk();
    Livewire::test(CreateTemplateCatalog::class)->assertOk();
});

it('creates a catalog entry via the admin form', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateTemplateCatalog::class)
        ->fillForm([
            'name' => 'Ujian Masjid Theme', 'source' => 'themeforest',
            'url' => 'https://themeforest.net/item/ujian/123', 'categories' => ['masjid'],
            'is_active' => true, 'sort' => 5,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(TemplateCatalog::where('name', 'Ujian Masjid Theme')->exists())->toBeTrue();
});
