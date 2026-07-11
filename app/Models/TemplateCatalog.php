<?php

namespace App\Models;

use App\Enums\Tier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

// §Fasa 16 — katalog templat rujukan terkurasi (galeri wizard mod 'template').
class TemplateCatalog extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'template_catalog';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'style_tags' => 'array',
            'screenshots' => 'array',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    /** @param  Builder<TemplateCatalog>  $query */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }

    /**
     * Templat aktif untuk tier projek. Tapis kategori DALAM PHP (elak whereJsonContains —
     * beza dialek SQLite dev vs MySQL prod; katalog kecil ~20 baris).
     *
     * @return Collection<int, TemplateCatalog>
     */
    public static function forTier(Tier $tier): Collection
    {
        $wanted = $tier->isNgo() ? 'ngo' : 'masjid';

        return static::query()->active()->get()
            ->filter(function (self $t) use ($wanted): bool {
                $cats = $t->categories ?? [];

                return $cats === [] || in_array($wanted, $cats, true);
            })
            ->values();
    }
}
