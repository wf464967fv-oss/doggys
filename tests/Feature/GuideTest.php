<?php

namespace Tests\Feature;

use App\Http\Controllers\GuideController;
use App\Models\Category;
use App\Models\Post;
use App\Models\Provider;
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

    public function test_article_renders_with_toc_breadcrumb_json_ld_author_and_editorial_date(): void
    {
        $guide = Post::factory()->create([
            'slug' => 'vaccins-du-chien',
            'title' => 'Vaccins du chien',
            'category' => 'health',
            'updated_at' => '2026-09-30 00:00:00',
            'body' => "Intro.\n\n## Le calendrier\n\nTexte.\n\n## Les rappels\n\n> **Bon à savoir** : demandez à votre vétérinaire.\n\n## Sources officielles\n\n- [DogID](https://www.dogid.be/fr)\n- [AFSCA](https://www.favv-afsca.be)",
        ]);
        $other = Post::factory()->create(['category' => 'health', 'title' => 'Le chien senior']);

        $this->get('/fr/guides/vaccins-du-chien')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('<h2 id="le-calendrier">', false)
            ->assertSee('href="#les-rappels"', false)
            ->assertSee('data-toc', false)
            ->assertDontSee('data-toc class="group rounded-3xl bg-white px-5 py-2 ring-1 ring-line shadow-soft lg:sticky lg:top-24 lg:py-4" open', false)
            ->assertSee('<blockquote>', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"dateModified":"2026-09-30', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee(__('blog.author', [], 'fr'))
            ->assertSee('href="#sources-officielles"', false)
            ->assertSee(trans_choice('blog.sources_count', 2, ['count' => 2], 'fr'))
            ->assertSee(__('blog.updated_on', ['date' => '30 septembre 2026'], 'fr'))
            ->assertSee($other->title);
    }

    public function test_find_a_pro_only_links_categories_with_active_pros(): void
    {
        $this->seed(CategorySeeder::class);
        foreach (['veterinaire', 'urgences-24h'] as $slug) {
            Category::where('slug', $slug)->first()->providers()->attach(Provider::factory()->create());
        }
        Category::where('slug', 'pension')->first()->providers()->attach(Provider::factory()->hidden()->create());
        Post::factory()->create([
            'slug' => 'senior',
            'related_categories' => ['veterinaire', 'urgences-24h', 'pension', 'kine-physio'],
        ]);

        $vet = Category::where('slug', 'veterinaire')->first();
        $this->get('/fr/guides/senior')
            ->assertOk()
            ->assertSee(__('blog.find_pro_title', [], 'fr'))
            ->assertSee(localized_route('categories.show', ['category' => $vet->routeSlug('fr')], 'fr'), false)
            ->assertDontSee(Category::where('slug', 'pension')->first()->label('fr'))
            ->assertDontSee(Category::where('slug', 'kine-physio')->first()->label('fr'));
    }

    public function test_find_a_pro_block_is_hidden_without_at_least_two_useful_categories(): void
    {
        $this->seed(CategorySeeder::class);
        Post::factory()->create(['slug' => 'toilettage', 'related_categories' => ['toiletteur-salon', 'toiletteur-domicile']]);

        $this->get('/fr/guides/toilettage')->assertOk()->assertDontSee(__('blog.find_pro_title', [], 'fr'));
    }

    public function test_emergency_guide_starts_with_an_action_box(): void
    {
        $this->seed(CategorySeeder::class);
        Category::where('slug', 'urgences-24h')->first()->providers()->attach(Provider::factory()->create());
        Post::factory()->create(['slug' => 'urgences', 'translation_key' => 'emergencies', 'related_categories' => ['urgences-24h', 'veterinaire']]);

        $this->get('/fr/guides/urgences')
            ->assertOk()
            ->assertSeeInOrder([__('blog.emergency_title', [], 'fr'), Category::where('slug', 'urgences-24h')->first()->label('fr'), 'prose-doggys'], false)
            ->assertSee(localized_route('categories.index', [], 'fr'), false);
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
            'title' => '</script><script>alert("title")</script>',
            'body' => "## Titre <img src=x onerror=alert(1)>\n\n<script>alert('xss')</script>\n\n<iframe src=\"https://evil.test\"></iframe>\n\n"
                ."[clic](javascript:alert(1)) [data](data:text/html;base64,PHNjcmlwdD4=) [entite](&#106;avascript:alert(2)) [ref][r]\n\n[r]: javascript:alert(3)",
        ]);

        $response = $this->get('/fr/guides/piege')->assertOk();

        $response->assertDontSee('<script>alert', false)
            ->assertDontSee('<iframe', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('javascript:', false)
            ->assertDontSee('href="data:', false)
            ->assertSee('&lt;/script&gt;&lt;script&gt;alert(&quot;title&quot;)&lt;/script&gt;', false)
            ->assertSee(trim(json_encode('</script><script>', JSON_HEX_TAG | JSON_UNESCAPED_SLASHES), '"'), false);
    }

    public function test_guide_import_is_idempotent_and_removes_guides_deleted_from_disk(): void
    {
        $this->seed(CategorySeeder::class);
        $removed = Post::factory()->create(['slug' => 'ancien-guide', 'translation_key' => 'removed-guide']);

        $this->artisan('doggys:import-guides')->assertSuccessful();
        $count = Post::count();
        $this->assertGreaterThanOrEqual(40, $count);
        $this->assertModelMissing($removed);
        $this->get('/fr/guides/ancien-guide')->assertNotFound();

        $post = Post::where('locale', 'fr')->where('translation_key', 'dog-food')->first();
        $editorialDate = $post->updated_at->toDateString();
        $post->update(['title' => 'Modifié à la main']);

        $this->artisan('doggys:import-guides')->assertSuccessful();
        $this->assertSame($count, Post::count());
        $this->assertSame(0, Post::where('title', 'Modifié à la main')->count());
        $this->assertSame($editorialDate, $post->fresh()->updated_at->toDateString());
    }

    public function test_parse_accepts_a_bom_and_crlf_line_endings(): void
    {
        $path = $this->guideFile("\xEF\xBB\xBF".str_replace("\n", "\r\n", $this->frontMatter()."\n## Titre\nTexte."));

        $guide = GuideSeeder::parse($path);

        $this->assertSame('un-guide', $guide['slug']);
        $this->assertSame('2026-10-01', $guide['updated_at']->toDateString());
        $this->assertSame("## Titre\nTexte.", $guide['body']);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function invalidGuides(): array
    {
        return [
            'slug' => ['slug: un-guide', 'slug: Mon Slug!', 'Invalid "slug"'],
            'category' => ['category: health', 'category: astrologie', 'Invalid "category"'],
            'date' => ['published_at: 2026-09-01', 'published_at: demain midi', 'Invalid "published_at"'],
            'impossible date' => ['updated_at: 2026-10-01', 'updated_at: 2026-02-31', 'Invalid "updated_at"'],
            'missing title' => ['title: Un guide', 'title:', 'Missing "title"'],
        ];
    }

    #[DataProvider('invalidGuides')]
    public function test_parse_rejects_an_invalid_front_matter(string $valid, string $invalid, string $message): void
    {
        $path = $this->guideFile(str_replace($valid, $invalid, $this->frontMatter())."\n## Titre\n");

        $this->expectExceptionMessage($message);
        GuideSeeder::parse($path);
    }

    public function test_parse_rejects_an_unknown_locale(): void
    {
        $path = $this->guideFile($this->frontMatter()."\n## Titre\n", 'xx');

        $this->expectExceptionMessage('Invalid "locale"');
        GuideSeeder::parse($path);
    }

    private function frontMatter(): string
    {
        return "---\ntitle: Un guide\nslug: un-guide\nexcerpt: Résumé.\nmeta_title: Un guide\nmeta_description: Description.\n"
            ."category: health\nrelated_categories: [veterinaire]\npublished_at: 2026-09-01\nupdated_at: 2026-10-01\n---\n";
    }

    private function guideFile(string $content, string $locale = 'fr'): string
    {
        $directory = sys_get_temp_dir().'/doggys-guide-'.uniqid().'/a-guide';
        mkdir($directory, 0777, true);
        file_put_contents("{$directory}/{$locale}.md", $content);

        return "{$directory}/{$locale}.md";
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
