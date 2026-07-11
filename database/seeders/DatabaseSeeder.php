<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Data rujukan teras (idempoten — updateOrCreate).
        $this->call([
            JakimZoneSeeder::class,      // 59 zon §16.A
            DesignPackageSeeder::class,  // 5 pakej §7.2
            VerseLibrarySeeder::class,   // 1 entri §9.2
            SettingsSeeder::class,       // nilai lalai §5.3
            TemplateCatalogSeeder::class, // §Fasa 16 — katalog templat rujukan
        ]);

        // Admin dev (satu-satunya pengguna berdaftar §3). 2FA dipaksa semasa login pertama.
        User::firstOrCreate(
            ['email' => 'admin@reka.test'],
            ['name' => 'Azan', 'password' => bcrypt('password')],
        );
    }
}
