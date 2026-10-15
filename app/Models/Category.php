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
     * Options for a category <select>, grouped by family: [group label => [slug => label]].
     *
     * @param  iterable<Category>  $categories
     * @return array<string, array<string, string>>
     */
    public static function groupedOptions(iterable $categories): array
    {
        return collect($categories)
            ->groupBy(fn (Category $category) => $category->group->value)
            ->mapWithKeys(fn ($group, string $key) => [
                __('categories.groups.'.$key) => $group->mapWithKeys(fn (Category $category) => [$category->slug => $category->label()])->all(),
            ])
            ->all();
    }

    /**
     * Translated URL slug of the category page (lang/{locale}/routes.php "category_slugs").
     */
    public function routeSlug(?string $locale = null): string
    {
        return trans('routes.category_slugs', [], $locale)[$this->slug] ?? $this->slug;
    }

    /**
     * Category from its translated URL slug in the current locale.
     */
    public static function findByRouteSlug(string $routeSlug): ?self
    {
        $slug = array_search($routeSlug, (array) trans('routes.category_slugs'), true);

        return $slug === false ? null : static::query()->where('slug', $slug)->first();
    }

    /**
     * Plural name for headings ("Salons de toilettage canin"), falling back to the label.
     * $inSentence lowercases the first letter, except in German where nouns keep their capital.
     */
    public function pluralLabel(?string $locale = null, bool $inSentence = false): string
    {
        $locale ??= app()->getLocale();
        $plural = trans('directory.category_plural', [], $locale)[$this->slug] ?? $this->label($locale);

        return $inSentence && $locale !== 'de' ? mb_lcfirst($plural) : $plural;
    }

    /**
     * @return BelongsToMany<Provider, $this>
     */
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class);
    }
}
