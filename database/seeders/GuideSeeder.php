<?php

namespace Database\Seeders;

use App\Http\Controllers\GuideController;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Imports the versioned guides of database/data/guides/{translation_key}/{locale}.md into `posts`.
 * Idempotent and atomic: every file is validated first, then rows are created or updated in place, and guides
 * (or single translations) removed from disk are deleted. Also run by `php artisan doggys:import-guides`.
 */
class GuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = array_map(self::parse(...), glob(database_path('data/guides/*/*.md')));
        $this->validateRelatedCategories($guides);

        $deleted = DB::transaction(function () use ($guides): int {
            // Removed guides go first, so a renamed folder can reuse its slugs.
            $seen = array_map(fn (array $guide) => $guide['locale'].'|'.$guide['translation_key'], $guides);
            $stale = Post::query()->get(['id', 'locale', 'translation_key'])
                ->reject(fn (Post $post) => in_array($post->locale.'|'.$post->translation_key, $seen, true));
            Post::query()->whereKey($stale->modelKeys())->delete();

            foreach ($guides as $guide) {
                $post = Post::query()->firstOrNew(['locale' => $guide['locale'], 'translation_key' => $guide['translation_key']]);

                // The front-matter dates are the editorial ones: no automatic timestamps.
                $post->timestamps = false;
                $post->forceFill(collect($guide)->except(['locale', 'translation_key'])->all() + [
                    'created_at' => $post->created_at ?? $guide['published_at'],
                ])->save();
            }

            return $stale->count();
        });

        $this->command?->info(count($guides).' guide files imported, '.$deleted.' removed guide(s) deleted.');
    }

    /**
     * Reads and validates one guide file: a simple front-matter block ("key: value", lists as "[a, b]")
     * followed by Markdown. Throws a RuntimeException naming the file and the faulty field.
     *
     * @return array{locale: string, translation_key: string, slug: string, title: string, excerpt: string, meta_title: string, meta_description: string, category: string, related_categories: list<string>, published_at: Carbon, updated_at: Carbon, reading_minutes: int, body: string}
     */
    public static function parse(string $path): array
    {
        $raw = preg_replace(['/\A\xEF\xBB\xBF/', '/\r\n?/'], ['', "\n"], (string) file_get_contents($path));

        if (! preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', $raw, $match)) {
            throw new RuntimeException("Missing front-matter in {$path}.");
        }

        $meta = [];
        foreach (explode("\n", trim($match[1])) as $line) {
            [$key, $value] = array_map('trim', explode(':', $line, 2) + [1 => '']);
            $meta[$key] = str_starts_with($value, '[')
                ? array_values(array_filter(array_map('trim', explode(',', trim($value, '[]')))))
                : preg_replace('/^"(.*)"$/s', '$1', $value);
        }

        foreach (['title', 'slug', 'excerpt', 'meta_title', 'meta_description', 'category', 'published_at'] as $required) {
            if (empty($meta[$required]) || ! is_string($meta[$required])) {
                throw new RuntimeException("Missing \"{$required}\" in {$path}.");
            }
        }

        $locale = pathinfo($path, PATHINFO_FILENAME);
        $checks = [
            'locale' => in_array($locale, config('app.locales'), true),
            'slug' => (bool) preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $meta['slug']),
            'category' => array_key_exists($meta['category'], GuideController::CATEGORIES),
            'related_categories' => is_array($meta['related_categories'] ?? []),
        ];
        foreach ($checks as $field => $valid) {
            if (! $valid) {
                throw new RuntimeException("Invalid \"{$field}\" in {$path}.");
            }
        }

        $body = trim($match[2]);
        $words = count(preg_split('/\s+/u', trim(preg_replace('/[#>*_\[\]()|-]+/', ' ', $body))));
        $publishedAt = self::date($meta['published_at'], 'published_at', $path);

        return [
            'locale' => $locale,
            'translation_key' => basename(dirname($path)),
            'slug' => $meta['slug'],
            'title' => $meta['title'],
            'excerpt' => $meta['excerpt'],
            'meta_title' => $meta['meta_title'],
            'meta_description' => $meta['meta_description'],
            'category' => $meta['category'],
            'related_categories' => $meta['related_categories'] ?? [],
            'published_at' => $publishedAt,
            'updated_at' => empty($meta['updated_at']) ? $publishedAt : self::date($meta['updated_at'], 'updated_at', $path),
            'reading_minutes' => (int) ($meta['reading_minutes'] ?? max(1, (int) round($words / 200))),
            'body' => $body,
        ];
    }

    private static function date(mixed $value, string $field, string $path): Carbon
    {
        try {
            $date = Carbon::createFromFormat('!Y-m-d', (string) $value);
        } catch (Throwable) {
            $date = null;
        }

        if (! $date || $date->format('Y-m-d') !== $value) {
            throw new RuntimeException("Invalid \"{$field}\" in {$path} (expected YYYY-MM-DD).");
        }

        return $date;
    }

    /**
     * @param  list<array{related_categories: list<string>, locale: string, translation_key: string}>  $guides
     */
    private function validateRelatedCategories(array $guides): void
    {
        $known = Category::query()->pluck('slug')->all();

        foreach ($guides as $guide) {
            $unknown = array_diff($guide['related_categories'], $known);
            if ($unknown !== []) {
                throw new RuntimeException('Unknown related categories ('.implode(', ', $unknown).") in {$guide['translation_key']}/{$guide['locale']}.md. Seed the categories first.");
            }
        }
    }
}
