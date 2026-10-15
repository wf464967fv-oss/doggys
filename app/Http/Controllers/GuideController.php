<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * Guide categories in display order, with their icon and colour tone (labels in lang/{locale}/blog.php).
     *
     * @var array<string, array{icon: string, tone: string}>
     */
    public const CATEGORIES = [
        'adoption' => ['icon' => 'home-heart', 'tone' => 'bg-rose-soft text-rose-dark'],
        'health' => ['icon' => 'stethoscope', 'tone' => 'bg-regulated-soft text-regulated'],
        'education' => ['icon' => 'graduation-cap', 'tone' => 'bg-orange-soft text-orange-dark'],
        'daily' => ['icon' => 'sun', 'tone' => 'bg-cream text-brown'],
        'rules' => ['icon' => 'shield-check', 'tone' => 'bg-verified-soft text-verified'],
    ];

    /**
     * Card illustration per guide (translation_key); guides not listed use their category icon.
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'adopt-puppy' => 'heart',
        'puppy-arrival' => 'home',
        'shelter-adoption' => 'home-heart',
        'choose-breeder' => 'award',
        'vaccines-deworming' => 'syringe',
        'senior-dog' => 'cake',
        'emergencies' => 'siren',
        'training-socialisation' => 'graduation-cap',
        'behaviourist' => 'message-circle',
        'dog-food' => 'bone',
        'choose-groomer' => 'scissors',
        'holidays' => 'globe',
        'identification-registration' => 'chip',
    ];

    public function index(): View
    {
        $guides = Post::query()->published()
            ->where('locale', app()->getLocale())
            ->orderBy('published_at')->orderBy('id')
            ->get(['id', 'translation_key', 'slug', 'title', 'excerpt', 'category', 'reading_minutes', 'published_at']);

        // One continuous grid, theme by theme; the theme buttons filter it client-side.
        $order = array_flip(array_keys(self::CATEGORIES));
        $guides = $guides->sortBy(fn (Post $guide) => $order[$guide->category] ?? count($order))->values();

        return view('guides.index', [
            'guides' => $guides,
            'themes' => array_intersect_key(self::CATEGORIES, $guides->pluck('category', 'category')->all()),
        ]);
    }

    public function show(string $slug): View
    {
        $guide = Post::query()->published()
            ->where('locale', app()->getLocale())
            ->where('slug', $slug)
            ->firstOrFail();

        $alternates = $guide->translations()->published()
            ->get(['locale', 'slug'])
            ->sortBy(fn (Post $translation) => array_search($translation->locale, config('app.locales'), true))
            ->mapWithKeys(fn (Post $translation) => [
                $translation->locale => localized_route('guides.show', ['slug' => $translation->slug], $translation->locale),
            ])->all();

        $related = Post::query()->published()
            ->where('locale', $guide->locale)
            ->whereKeyNot($guide->getKey())
            ->orderByRaw('case when category = ? then 0 else 1 end', [$guide->category])
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'translation_key', 'slug', 'title', 'excerpt', 'category', 'reading_minutes']);

        return view('guides.show', [
            'guide' => $guide,
            'body' => $guide->renderBody(),
            'alternates' => $alternates,
            // Same rule as the category page's indexability: only categories with active pros get a link.
            'proCategories' => Category::query()
                ->whereIn('slug', $guide->related_categories ?? [])
                ->withCount(['providers' => fn ($query) => $query->active()])
                ->get()
                ->where('providers_count', '>', 0)
                ->sortByDesc('providers_count')
                ->values(),
            'related' => $related,
        ]);
    }
}
