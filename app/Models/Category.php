<?php

namespace App\Models;

use App\Enums\CategoryGroup;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'group', 'name', 'description', 'icon', 'sort', 'is_regulated'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'group' => CategoryGroup::class,
            'name' => 'array',
            'description' => 'array',
            'is_regulated' => 'boolean',
        ];
    }

    /**
     * Name in the current locale, falling back to French.
     */
    public function label(?string $locale = null): string
    {
        return $this->name[$locale ?? app()->getLocale()] ?? $this->name['fr'] ?? $this->slug;
    }

    /**
     * Description in the current locale, falling back to French.
     */
    public function summary(?string $locale = null): ?string
    {
        return $this->description[$locale ?? app()->getLocale()] ?? $this->description['fr'] ?? null;
    }

    /**
     * @return BelongsToMany<Provider, $this>
     */
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class);
    }
}
