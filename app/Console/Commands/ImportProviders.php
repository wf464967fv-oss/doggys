<?php

namespace App\Console\Commands;

use App\Enums\ProviderStatus;
use App\Models\Category;
use App\Models\PostalCode;
use App\Models\Provider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports providers.json (built by scripts/data/build.mjs, legal sources only) into the providers table.
 * Replayable: rows are matched on (source, source_ref). Slugs never change once created (public URLs).
 * Listings claimed by a professional or removed on request are never touched again.
 */
#[Signature('doggys:import-providers {path=database/data/providers.json : JSON file, relative to the project root}')]
#[Description('Import or refresh professionals from the legal-source data file (idempotent)')]
class ImportProviders extends Command
{
    /**
     * Columns refreshed from the source on every run.
     */
    private const SOURCE_FIELDS = [
        'name', 'street', 'house_number', 'postal_code', 'region', 'phone', 'email', 'website',
        'opening_hours', 'is_mobile', 'bce_number', 'regulatory_number',
    ];

    public function handle(): int
    {
        $path = base_path($this->argument('path'));
        $rows = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        if (! is_array($rows)) {
            $this->error("Cannot read a JSON array from {$path}.");

            return self::FAILURE;
        }

        $categoryIds = Category::query()->pluck('id', 'slug');
        $centroids = PostalCode::query()->get(['code', 'lat', 'lng'])->groupBy('code')
            ->map(fn ($group) => [round($group->avg('lat'), 7), round($group->avg('lng'), 7)]);
        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'protected' => 0, 'invalid' => 0];

        DB::transaction(function () use ($rows, $categoryIds, $centroids, &$counts) {
            foreach ($rows as $row) {
                $counts[$this->importRow($row, $categoryIds->all(), $centroids->all())]++;
            }
        });

        $this->info(sprintf(
            '%d rows: %d created, %d updated, %d unchanged, %d protected (claimed or removed), %d invalid.',
            count($rows), $counts['created'], $counts['updated'], $counts['unchanged'], $counts['protected'], $counts['invalid'],
        ));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $categoryIds
     * @param  array<string, array{0: float, 1: float}>  $centroids
     * @return 'created'|'updated'|'unchanged'|'protected'|'invalid'
     */
    private function importRow(array $row, array $categoryIds, array $centroids): string
    {
        if (blank($row['source'] ?? null) || blank($row['source_ref'] ?? null) || blank($row['name'] ?? null)) {
            return 'invalid';
        }

        [$lat, $lng] = isset($row['lat'], $row['lng'])
            ? [$row['lat'], $row['lng']]
            : ($centroids[$row['postal_code'] ?? ''] ?? [null, null]);

        if ($lat === null) {
            return 'invalid';
        }

        $provider = Provider::withTrashed()
            ->where('source', $row['source'])
            ->where('source_ref', $row['source_ref'])
            ->first();

        if ($provider && ($provider->claimed_by_user_id !== null || $provider->status === ProviderStatus::Removed)) {
            return 'protected';
        }

        $isNew = ! $provider;
        $provider ??= new Provider(['source' => $row['source'], 'source_ref' => $row['source_ref']]);
        $provider->fill(array_intersect_key($row, array_flip(self::SOURCE_FIELDS)) + [
            'city' => $this->cityName($row),
            'lat' => $lat,
            'lng' => $lng,
        ]);
        $provider->is_mobile = (bool) ($row['is_mobile'] ?? false);

        if ($isNew) {
            $provider->slug = $this->uniqueSlug($provider->name, $provider->city);
        }

        $categories = array_values(array_filter(array_map(fn (string $slug) => $categoryIds[$slug] ?? null, $row['categories'] ?? [])));
        $changed = $provider->isDirty();

        if ($changed) {
            $provider->imported_at = now();
            $provider->save();
        }

        $synced = $provider->categories()->sync($categories);
        $changed = $changed || array_filter($synced) !== [];

        return $isNew ? 'created' : ($changed ? 'updated' : 'unchanged');
    }

    /**
     * Clean locality: OSM sometimes lists every sub-municipality ("Sint-Truiden - Aalst - Brustem")
     * or both languages ("Uccle - Ukkel"); the first one is kept. Falls back to the postal code's locality.
     *
     * @param  array<string, mixed>  $row
     */
    private function cityName(array $row): ?string
    {
        $city = trim((string) preg_split('/\s+[-+]\s+/u', (string) ($row['city'] ?? ''))[0]);

        if ($city !== '') {
            return $city;
        }

        $locality = PostalCode::cityFor($row['postal_code'] ?? null);

        return $locality ? $locality['names'][($row['region'] ?? null) === 'flanders' ? 'nl' : 'fr'] : null;
    }

    /**
     * Readable unique slug "name-city", suffixed -2, -3… on collision.
     */
    private function uniqueSlug(string $name, ?string $city): string
    {
        $base = Str::slug(Str::limit($name, 80, '')) ?: 'pro';

        if ($city && ! str_contains($base, Str::slug($city))) {
            $base .= '-'.Str::slug($city);
        }

        $slug = $base;
        $suffix = 2;

        while (Provider::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
