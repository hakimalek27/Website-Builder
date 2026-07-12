<?php

namespace Database\Seeders;

use App\Models\TemplateCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * §Fasa 16 — katalog templat rujukan terkurasi (galeri wizard mod 'template').
 * Data dari carian ThemeForest sebenar (masjid + NGO/amal) + laman Malaysia; URL disahkan.
 * Manifest: database/seeders/template-catalog.json · thumbnail: database/seeders/template-thumbnails/.
 * Idempoten (updateOrCreate ikut url). Thumbnail disalin ke disk `public` (perlu storage:link);
 * thumbnail yang dimuat naik admin TIDAK ditindih semasa seed semula.
 */
class TemplateCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/template-catalog.json');
        if (! is_file($file)) {
            return;
        }
        $manifest = json_decode((string) file_get_contents($file), true) ?: [];
        $thumbDir = database_path('seeders/template-thumbnails');

        foreach ($manifest as $row) {
            if (empty($row['url'])) {
                continue;
            }
            $existing = TemplateCatalog::where('url', $row['url'])->first();

            $attrs = [
                'name' => $row['name'],
                'source' => $row['source'] ?? 'themeforest',
                'categories' => $row['categories'] ?? [],
                'style_tags' => $row['style_tags'] ?? [],
                'description' => $row['description'] ?? null,
                'demo_url' => $row['demo_url'] ?? null,
                'is_active' => $row['is_active'] ?? true,
                'sort' => $row['sort'] ?? 0,
            ];

            // Salin thumbnail ke disk public — hanya set path bila ada & tidak menindih upload admin.
            $thumb = $row['thumbnail'] ?? null;
            if ($thumb && is_file($thumbDir.'/'.$thumb)) {
                Storage::disk('public')->put('template-catalog/'.$thumb, (string) file_get_contents($thumbDir.'/'.$thumb));
                if (! $existing || blank($existing->thumbnail_path)) {
                    $attrs['thumbnail_path'] = 'template-catalog/'.$thumb;
                }
            }

            TemplateCatalog::updateOrCreate(['url' => $row['url']], $attrs);
        }
    }
}
