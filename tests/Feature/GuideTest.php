<?php

namespace Tests\Feature;

use App\Http\Controllers\GuideController;
use App\Models\Category;
use App\Models\Post;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GuideTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function guideIndexes(): array
    {
        return [
            'fr' => ['fr', '/fr/guides'],
            'nl' => ['nl', '/nl/gidsen'],
            'en' => ['en', '/en/guides'],
            'de' => ['de', '/de/ratgeber'],
        ];
    }

    #[DataProvider('guideIndexes')]
    public function test_index_lists_the_published_guides_of_its_locale_only(string $locale, string $path): void
    {
        $guide = Post::factory()->create(['locale' => $locale, 'category' => 'health', 'title' => 'Guide '.$locale]);
        Post::factory()->create(['locale' => $locale, 'category' => 'health', 'title' => 'Draft guide', 'published_at' => null]);
        Post::factory()->create(['locale' => $locale === 'fr' ? 'nl' : 'fr', 'category' => 'health', 'title' => 'Other language guide']);

        $this->get($path)
            ->assertOk()
            ->assertSee('<html lang="'.$locale.'">', false)
            ->assertSee(__('blog.title', [], $locale))
            ->assertSee(__('blog.categories.health', [], $locale))
            ->assertSee(localized_route('guides.show', ['slug' => $guide->slug], $locale), false)
            ->assertDontSee('Draft guide')
            ->assertDontSee('Other language guide')
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_index_shows_an_empty_state_without_guides(): void
    {
        $this->get('/fr/guides')->assertOk()->assertSee(__('blog.empty_title', [], 'fr'));
    }

    public function test_article_renders_with_toc_breadcrumb_json_ld_and_pro_links(): void
    {
        $this->seed(CategorySeeder::class);
        $guide = Post::factory()->create([
            'slug' => 'vaccins-du-chien',
            'title' => 'Vaccins du chien',
            'category' => 'health',
            'related_categories' => ['veterinaire'],
            'body' => "Intro.\n\n## Le calendrier\n\nTexte.\n\n## Les rappels\n\n> **Bon à savoir** : demandez à votre vétérinaire.\n\n[DogID](https://www.dogid.be/fr)",
        ]);
        $other = Post::factory()->create(['category' => 'health', 'title' => 'Le chien senior']);

        $this->get('/fr/guides/vaccins-du-chien')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('<h2 id="le-calendrier">', false)
            ->assertSee('href="#les-rappels"', false)
            ->assertSee('<blockquote>', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee(__('blog.find_pro_title', [], 'fr'))
            ->assertSee('Vétérinaire')
            ->assertSee($other->title)
            ->assertSee(__('blog.updated_on', ['date' => $guide->updated_at->locale('fr')->isoFormat('LL')], 'fr'));
    }

    public function test_article_is_not_found_when_unpublished_scheduled_or_in_another_language(): void
    {
        Post::factory()->create(['slug' => 'brouillon', 'published_at' => null]);
        Post::factory()->create(['slug' => 'plus-tard', 'published_at' => now()->addDay()]);
        Post::factory()->create(['slug' => 'puppy-checklist', 'locale' => 'en']);

        $this->get('/fr/guides/brouillon')->assertNotFound();
        $this->get('/fr/guides/plus-tard')->assertNotFound();
        $this->get('/fr/guides/puppy-checklist')->assertNotFound();
        $this->get('/en/guides/puppy-checklist')->assertOk();
    }

    public function test_article_alternates_point_to_each_published_translation(): void
    {
        foreach (['fr' => 'adopter-un-chiot', 'nl' => 'een-pup-adopteren', 'de' => 'welpen-adoptieren'] as $locale => $slug) {
            Post::factory()->create(['locale' => $locale, 'translation_key' => 'adopt-puppy', 'slug' => $slug]);
        }
        Post::factory()->create(['locale' => 'en', 'translation_key' => 'adopt-puppy', 'slug' => 'adopt-a-puppy', 'published_at' => null]);

        $this->get('/nl/gidsen/een-pup-adopteren')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/nl/gidsen/een-pup-adopteren').'">', false)
            ->assertSee('<link rel="alternate" hreflang="fr" href="'.url('/fr/guides/adopter-un-chiot').'">', false)
            ->assertSee('<link rel="alternate" hreflang="de" href="'.url('/de/ratgeber/welpen-adoptieren').'">', false)
            ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/fr/guides/adopter-un-chiot').'">', false)
            ->assertDontSee('adopt-a-puppy');
    }

    public function test_markdown_html_and_unsafe_links_are_neutralised(): void
    {
        Post::factory()->create([
            'slug' => 'piege',
            'title' => '<script>alert("title")</script>',
            'body' => "## Titre <img src=x onerror=alert(1)>\n\n<script>alert('xss')</script>\n\n<iframe src=\"https://evil.test\"></iframe>\n\n[clic](javascript:alert(1))",
        ]);

        $response = $this->get('/fr/guides/piege')->assertOk();

        $response->assertDontSee('<script>alert', false)
            ->assertDontSee('<iframe', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('javascript:alert', false)
            ->assertSee('&lt;script&gt;alert(&quot;title&quot;)&lt;/script&gt;', false);
    }

    public function test_guide_import_is_idempotent_and_keeps_rows_up_to_date(): void
    {
        $this->artisan('doggys:import-guides')->assertSuccessful();
        $count = Post::count();
        $this->assertGreaterThanOrEqual(40, $count);

        Post::where('locale', 'fr')->first()->update(['title' => 'Modifié à la main']);

        $this->artisan('doggys:import-guides')->assertSuccessful();
        $this->assertSame($count, Post::count());
        $this->assertSame(0, Post::where('title', 'Modifié à la main')->count());
    }

    public function test_every_guide_file_is_complete_in_four_languages_and_seo_ready(): void
    {
        $this->seed(CategorySeeder::class);
        $categorySlugs = Category::pluck('slug')->all();
        $directories = glob(database_path('data/guides/*'), GLOB_ONLYDIR);
        $this->assertGreaterThanOrEqual(10, count($directories));

        $slugs = [];
        foreach ($directories as $directory) {
            foreach (config('app.locales') as $locale) {
                $path = "{$directory}/{$locale}.md";
                $this->assertFileExists($path);
                $guide = GuideSeeder::parse($path);
                $words = count(preg_split('/\s+/u', trim(strip_tags($guide['body']))));
                $label = basename($directory)."/{$locale}";

                $this->assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $guide['slug'], $label);
                $this->assertArrayNotHasKey($locale.$guide['slug'], $slugs, "{$label}: duplicate slug");
                $slugs[$locale.$guide['slug']] = true;
                $this->assertLessThanOrEqual(50, mb_strlen($guide['meta_title']), "{$label}: meta_title + ' | Doggys' must fit in 60 characters");
                $this->assertLessThanOrEqual(155, mb_strlen($guide['meta_description']), "{$label}: meta_description");
                $this->assertArrayHasKey($guide['category'], GuideController::CATEGORIES, $label);
                $this->assertEmpty(array_diff($guide['related_categories'], $categorySlugs), "{$label}: unknown related category");
                $this->assertGreaterThanOrEqual(900, $words, "{$label}: too short");
                $this->assertLessThanOrEqual(1600, $words, "{$label}: too long");
                $this->assertDoesNotMatchRegularExpression('/^# /m', $guide['body'], "{$label}: the page already has an H1");
                $this->assertMatchesRegularExpression('/^## /m', $guide['body'], $label);
                $this->assertStringContainsString('](https://', $guide['body'], "{$label}: cite official sources");
            }
        }
    }
}
