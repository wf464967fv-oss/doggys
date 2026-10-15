<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Blog post / guide. One row per locale; rows sharing a translation_key are translations of each other.
 */
#[Fillable([
    'locale', 'translation_key', 'slug', 'title', 'excerpt', 'body', 'meta_title', 'meta_description',
    'category', 'related_categories', 'published_at', 'reading_minutes',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'related_categories' => 'array',
            'published_at' => 'datetime',
            'reading_minutes' => 'integer',
        ];
    }

    /**
     * @param  Builder<Post>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * All locale versions of this post (including itself).
     *
     * @return HasMany<Post, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Post::class, 'translation_key', 'translation_key');
    }

    /**
     * Safe HTML of the Markdown body (raw HTML stripped, unsafe links dropped, external links rel="noopener"),
     * with an id on every H2 and the matching table of contents.
     *
     * @return array{html: string, toc: list<array{id: string, title: string}>}
     */
    public function renderBody(): array
    {
        $html = Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => (string) parse_url((string) config('app.url'), PHP_URL_HOST),
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ], [new ExternalLinkExtension]);

        $toc = [];
        $html = preg_replace_callback('#<h2>(.*?)</h2>#s', function (array $match) use (&$toc): string {
            $title = html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5);
            $id = Str::slug($title) ?: 'section';
            $id = in_array($id, array_column($toc, 'id'), true) ? $id.'-'.(count($toc) + 1) : $id;
            $toc[] = ['id' => $id, 'title' => $title];

            return '<h2 id="'.$id.'">'.$match[1].'</h2>';
        }, $html);

        return ['html' => $html, 'toc' => $toc];
    }
}
