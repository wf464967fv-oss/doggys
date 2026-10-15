<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Post;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * sitemap.xml (index) and sitemap-{locale}-{page}.xml: only pages meant to be indexed
 * (no search, no thin listings, no city page flagged noindex). Cached for an hour.
 */
class SitemapController extends Controller
{
    private const CHUNK = 40000;

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.index', now()->addHour(), function () {
            $pages = (int) max(1, ceil($this->count() / self::CHUNK));
            $entries = '';

            foreach (config('app.locales') as $locale) {
                for ($page = 1; $page <= $pages; $page++) {
                    $entries .= '<sitemap><loc>'.e(route('sitemap.locale', ['locale' => $locale, 'page' => $page])).'</loc></sitemap>';
                }
            }

            return '<?xml version="1.0" encoding="UTF-8"?>'
                .'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$entries.'</sitemapindex>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function show(string $locale, int $page): Response
    {
        abort_if($page < 1 || $page > max(1, ceil($this->count() / self::CHUNK)), 404);

        $xml = Cache::remember("sitemap.{$locale}.{$page}", now()->addHour(), function () use ($locale, $page) {
            $previous = app()->getLocale();
            app()->setLocale($locale);

            $urls = collect($this->urls($locale))->slice(($page - 1) * self::CHUNK, self::CHUNK);
            $entries = $urls->map(fn (string $url) => '<url><loc>'.e($url).'</loc></url>')->implode('');

            app()->setLocale($previous);

            return '<?xml version="1.0" encoding="UTF-8"?>'
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$entries.'</urlset>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Indexable URLs of one locale.
     *
     * @return \Generator<int, string>
     */
    private function urls(string $locale): \Generator
    {
        yield route($locale.'.home');
        yield route($locale.'.categories.index');
        yield route($locale.'.guides');

        $categories = Category::query()->withCount(['providers' => fn ($query) => $query->active()])->orderBy('sort')->get()->keyBy('id');
        foreach ($categories->where('providers_count', '>', 0) as $category) {
            yield route($locale.'.categories.show', ['category' => $category->routeSlug($locale)]);
        }

        $communes = Commune::query()->get(['id', 'slugs'])->keyBy('id');
        foreach (DB::table('city_pages')->where('is_indexable', true)->orderBy('category_id')->orderBy('commune_id')->get() as $page) {
            yield route($locale.'.categories.city', [
                'category' => $categories[$page->category_id]->routeSlug($locale),
                'city' => $communes[$page->commune_id]->slug($locale),
            ]);
        }

        foreach (Post::query()->published()->where('locale', $locale)->orderBy('id')->pluck('slug') as $slug) {
            yield route($locale.'.guides.show', ['slug' => $slug]);
        }

        foreach ($this->indexableProviders()->orderBy('id')->lazyById(2000, 'id') as $provider) {
            yield route($locale.'.providers.show', ['provider' => $provider->slug]);
        }
    }

    private function count(): int
    {
        return 3 + Category::query()->whereHas('providers', fn ($query) => $query->active())->count()
            + DB::table('city_pages')->where('is_indexable', true)->count()
            + Post::query()->published()->groupBy('locale')->selectRaw('COUNT(*) AS total')->toBase()->get()->max('total')
            + $this->indexableProviders()->count();
    }

    /**
     * Active listings with a street or a way to get in touch (see Provider::isThin()).
     *
     * @return Builder<Provider>
     */
    private function indexableProviders(): Builder
    {
        return Provider::query()->select(['id', 'slug'])->active()->substantial();
    }
}
