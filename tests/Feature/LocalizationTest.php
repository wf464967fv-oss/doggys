<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Provider;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_the_browser_language(): void
    {
        $this->get('/', ['Accept-Language' => 'nl-BE,nl;q=0.9,en;q=0.5'])
            ->assertRedirect('/nl')
            ->assertHeader('Vary', 'Accept-Language');

        $this->get('/', ['Accept-Language' => 'de-DE,de;q=0.9'])->assertRedirect('/de');
    }

    public function test_root_falls_back_to_french(): void
    {
        $this->get('/', ['Accept-Language' => 'es-ES,es;q=0.9'])->assertRedirect('/fr');
        $this->get('/', ['Accept-Language' => ''])->assertRedirect('/fr');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function localizedLoginPages(): array
    {
        return [
            'fr' => ['fr', '/fr/connexion'],
            'nl' => ['nl', '/nl/inloggen'],
            'en' => ['en', '/en/login'],
            'de' => ['de', '/de/anmelden'],
        ];
    }

    #[DataProvider('localizedLoginPages')]
    public function test_pages_are_served_under_translated_urls(string $locale, string $path): void
    {
        $this->assertSame(url($path), route($locale.'.login'));

        $this->get($path)
            ->assertOk()
            ->assertSee('<html lang="'.$locale.'">', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('href="'.url('/nl/inloggen').'" hreflang="nl"', false)
            ->assertSee(__('auth.login_title', [], $locale));
    }

    public function test_home_page_renders_in_each_locale(): void
    {
        $this->seed(CategorySeeder::class);
        // The home page only shows specialities that already have listings.
        Provider::factory()->create()->categories()->attach(Category::where('slug', 'veterinaire')->sole());

        foreach (config('app.locales') as $locale) {
            $this->get('/'.$locale)
                ->assertOk()
                ->assertSee('<html lang="'.$locale.'">', false)
                ->assertSee(__('home.hero_title_highlight', [], $locale))
                ->assertSee(__('categories.groups.health', [], $locale))
                ->assertSee('<link rel="canonical" href="'.url('/'.$locale).'">', false)
                ->assertSee('<link rel="alternate" hreflang="nl" href="'.url('/nl').'">', false)
                ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/fr').'">', false);
        }
    }

    public function test_unknown_page_shows_a_localized_404(): void
    {
        $this->get('/de/gibt-es-nicht')
            ->assertNotFound()
            ->assertSee('<html lang="de">', false)
            ->assertSee(__('errors.404_title', [], 'de'));
    }

    public function test_server_error_page_renders_without_session_or_database(): void
    {
        app()->setLocale('nl');

        $html = view('errors.500', ['exception' => new HttpException(500)])->render();

        $this->assertStringContainsString(__('errors.500_title', [], 'nl'), $html);
        $this->assertStringNotContainsString('action="'.url('/nl/uitloggen'), $html);
    }

    public function test_unprefixed_pages_do_not_exist(): void
    {
        $this->get('/connexion')->assertNotFound();
    }

    public function test_every_locale_has_the_same_translation_keys(): void
    {
        foreach (glob(lang_path('fr/*.php')) as $file) {
            $group = basename($file, '.php');
            $reference = array_keys(Arr::dot(require $file));

            foreach (['nl', 'en', 'de'] as $locale) {
                $path = lang_path("{$locale}/{$group}.php");
                $this->assertFileExists($path);
                $keys = array_keys(Arr::dot(require $path));

                $this->assertSame([], array_values(array_diff($reference, $keys)), "Missing keys in {$locale}/{$group}.php");
                $this->assertSame([], array_values(array_diff($keys, $reference)), "Extra keys in {$locale}/{$group}.php");
            }
        }
    }
}
