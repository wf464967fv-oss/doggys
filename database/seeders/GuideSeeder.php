<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

/**
 * Imports the versioned guides of database/data/guides/{translation_key}/{locale}.md into `posts`.
 * Idempotent: one row per (locale, translation_key), updated in place. Also run by `php artisan doggys:import-guides`.
 */
class GuideSeeder extends Seeder
{
    public function run(): void
    {
        foreach (glob(database_path('data/guides/*/*.md')) as $path) {
            $guide = self::parse($path);

            Post::updateOrCreate(
                ['locale' => $guide['locale'], 'translation_key' => $guide['translation_key']],
                collect($guide)->except(['locale', 'translation_key'])->all(),
            );
        }
    }

    /**
     * Reads one guide file: a simple front-matter block ("key: value", lists as "[a, b]") followed by Markdown.
     *
     * @return array{locale: string, translation_key: string, slug: string, title: string, excerpt: string, meta_title: string, meta_description: string, category: string, related_categories: list<string>, published_at: Carbon, reading_minutes: int, body: string}
     */
    public static function parse(string $path): array
    {
        $raw = str_replace("\r\n", "\n", (string) file_get_contents($path));

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
            if (empty($meta[$required])) {
                throw new RuntimeException("Missing \"{$required}\" in {$path}.");
            }
        }

        $body = trim($match[2]);
        $words = count(preg_split('/\s+/u', trim(preg_replace('/[#>*_\[\]()|-]+/', ' ', $body))));

        return [
            'locale' => pathinfo($path, PATHINFO_FILENAME),
            'translation_key' => basename(dirname($path)),
            'slug' => $meta['slug'],
            'title' => $meta['title'],
            'excerpt' => $meta['excerpt'],
            'meta_title' => $meta['meta_title'],
            'meta_description' => $meta['meta_description'],
            'category' => $meta['category'],
            'related_categories' => $meta['related_categories'] ?? [],
            'published_at' => Carbon::parse($meta['published_at']),
            'reading_minutes' => (int) ($meta['reading_minutes'] ?? max(1, (int) round($words / 200))),
            'body' => $body,
        ];
    }
}
