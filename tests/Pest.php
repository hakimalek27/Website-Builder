<?php

use App\Models\Invitation;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Cipta sesi PIC ujian: Project + Invitation + token plaintext.
 *
 * @return array{0: Project, 1: string}
 */
function picSession(array $projectAttrs = []): array
{
    $token = Invitation::generateToken();
    $project = Project::factory()->create($projectAttrs);
    Invitation::factory()->for($project)->withToken($token)->create();

    return [$project, $token];
}

/** Hidupkan senarai page_key untuk projek (project_pages). */
function enablePages(Project $project, array $pageKeys): void
{
    $sort = 0;
    foreach ($pageKeys as $key) {
        $project->pages()->updateOrCreate(['page_key' => $key], ['enabled' => true, 'sort' => $sort++]);
    }
}
