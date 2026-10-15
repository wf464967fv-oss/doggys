<?php

namespace App\Console\Commands;

use App\Enums\ProviderSource;
use App\Enums\ProviderStatus;
use App\Enums\Region;
use App\Models\Category;
use App\Models\Commune;
use App\Models\PostalCode;
use App\Models\Provider;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Imports providers.json (built by scripts/data/build.mjs, legal sources only) into the providers table.
 * Replayable: rows are matched on (source, source_ref). Slugs never change once created (public URLs).
 * Listings claimed by a professional or removed on request are never touched again; imported listings
 * that disappeared from their source (closed shops) are hidden. Each row is validated first, so one bad
 * row is skipped instead of aborting the whole import on MySQL.
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

        if (! is_array($rows) || ! array_is_list($rows)) {
            $this->error("Cannot read a JSON array from {$path}.");

            return self::FAILURE;
        }

        $categoryIds = Category::query()->pluck('id', 'slug')->all();
        $communeIds = Commune::query()->pluck('id', 'ref')->all();
        $localities = PostalCode::query()->get(['code', 'name', 'lat', 'lng', 'commune_id']);
        $centroids = $localities->groupBy('code')->map(fn ($group) => [round($group->avg('lat'), 7), round($group->avg('lng'), 7)])->all();
        $communeOfLocality = $localities->mapWithKeys(fn (PostalCode $row) => [$row->code.'|'.Str::slug($row->name) => $row->commune_id])->all();
        $communeOfCode = $localities->groupBy('code')->map(fn ($group) => $group->first()->commune_id)->all();

        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'protected' => 0, 'invalid' => 0, 'hidden' => 0];
        $seen = [];

        DB::transaction(function () use ($rows, $categoryIds, $communeIds, $centroids, $communeOfLocality, $communeOfCode, &$counts, &$seen) {
            foreach ($rows as $index => $row) {
                $errors = $this->validationErrors($row, array_keys($categoryIds));

                if ($errors !== null) {
                    $this->warn("Row {$index} skipped: {$errors}");
                    $counts['invalid']++;

                    continue;
                }

                $seen[$row['source']][$row['source_ref']] = true;
                $commune = $communeIds[$row['commune'] ?? ''] ?? $communeOfLocality[$row['postal_code'].'|'.Str::slug((string) $row['city'])]
                    ?? $communeOfCode[$row['postal_code'] ?? ''] ?? null;
                $counts[$this->importRow($row, $categoryIds, $centroids, $commune)]++;
            }

            $counts['hidden'] = $this->hideVanished($seen);
        });

        PostalCode::forgetPlaceIndex();
        $this->call('doggys:refresh-city-pages');

        $this->info(sprintf(
            '%d rows: %d created, %d updated, %d unchanged, %d protected (claimed or removed), %d invalid, %d hidden (gone from source).',
            count($rows), $counts['created'], $counts['updated'], $counts['unchanged'], $counts['protected'], $counts['invalid'], $counts['hidden'],
        ));

        return self::SUCCESS;
    }

    /**
     * Same limits as the database columns, so MySQL strict mode never rejects a row mid-import.
     *
     * @param  list<string>  $categories
     */
    private function validationErrors(mixed $row, array $categories): ?string
    {
        if (! is_array($row)) {
            return 'not an object';
        }

        $validator = Validator::make($row, [
            'source' => ['required', Rule::in(['osm', 'bce', 'registry'])],
            'source_ref' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['string', Rule::in($categories)],
            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:16'],
            'postal_code' => ['nullable', 'string', 'regex:/^[1-9]\d{3}$/'],
            'city' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', Rule::enum(Region::class)],
            'lat' => ['nullable', 'numeric', 'between:49,52', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:2,7', 'required_with:lat'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'opening_hours' => ['nullable', 'string', 'max:2000'],
            'is_mobile' => ['boolean'],
            'bce_number' => ['nullable', 'string', 'max:16'],
            'regulatory_number' => ['nullable', 'string', 'max:64'],
            'commune' => ['nullable', 'string', 'max:16'],
        ]);

        return $validator->fails() ? implode(' ', $validator->errors()->all()) : null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, int>  $categoryIds
     * @param  array<string, array{0: float, 1: float}>  $centroids
     * @return 'created'|'updated'|'unchanged'|'protected'|'invalid'
     */
    private function importRow(array $row, array $categoryIds, array $centroids, ?int $communeId): string
    {
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
            'city' => $this->cityName($row, $communeId),
            'lat' => $lat,
            'lng' => $lng,
            'commune_id' => $communeId,
        ]);
        $provider->is_mobile = (bool) ($row['is_mobile'] ?? false);

        if ($isNew) {
            $provider->slug = $this->uniqueSlug($provider->name, $provider->city);
        }

        $categories = array_values(array_filter(array_map(fn (string $slug) => $categoryIds[$slug] ?? null, $row['categories'])));
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
     * Hides active imported listings that are no longer in the file (closed businesses), except claimed
     * ones: a professional now looks after those.
     *
     * @param  array<string, array<string, true>>  $seen
     */
    private function hideVanished(array $seen): int
    {
        $hidden = 0;

        Provider::query()
            ->where('source', '!=', ProviderSource::Manual)
            ->where('status', ProviderStatus::Active)
            ->whereNull('claimed_by_user_id')
            ->select(['id', 'source', 'source_ref', 'status'])
            ->chunkById(1000, function ($providers) use ($seen, &$hidden) {
                $gone = $providers->reject(fn (Provider $provider) => isset($seen[$provider->source->value][$provider->source_ref]))->modelKeys();
                if ($gone !== []) {
                    $hidden += Provider::query()->whereKey($gone)->update(['status' => ProviderStatus::Hidden]);
                }
            });

        return $hidden;
    }

    /**
     * Clean locality: OSM sometimes lists every sub-municipality ("Sint-Truiden - Aalst - Brustem")
     * or both languages ("Uccle - Ukkel"); the first one is kept. Falls back to the municipality name in its
     * official language (Dutch in Flanders, French elsewhere).
     *
     * @param  array<string, mixed>  $row
     */
    private function cityName(array $row, ?int $communeId): ?string
    {
        $city = trim((string) preg_split('/\s+[-+]\s+/u', (string) ($row['city'] ?? ''))[0]);

        if ($city !== '') {
            return PostalCode::placeCase($city);
        }

        $commune = $communeId ? Commune::query()->find($communeId) : null;

        return $commune?->label(($row['region'] ?? null) === 'flanders' ? 'nl' : 'fr')
            ?? PostalCode::query()->where('code', $row['postal_code'] ?? '')->orderBy('id')->value('name');
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
