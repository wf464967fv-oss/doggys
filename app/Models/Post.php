<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Blog post / guide. One row per locale; rows sharing a translation_key are translations of each other.
 */
#[Fillable([
    'locale', 'translation_key', 'slug', 'title', 'excerpt', 'body', 'meta_title', 'meta_description',
    'category', 'published_at', 'reading_minutes',
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
}
