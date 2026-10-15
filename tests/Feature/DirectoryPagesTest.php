<?php

namespace Tests\Feature;

use App\Enums\ProviderReportType;
use App\Enums\ProviderStatus;
use App\Models\Category;
use App\Models\Commune;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Models\Review;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryPagesTest extends TestCase
{
    use RefreshDatabase;

    private Commune $liege;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        $this->liege = Commune::factory()->named(['fr' => 'Liège', 'nl' => 'Luik', 'en' => 'Liège', 'de' => 'Lüttich'])
            ->create(['lat' => 50.6337, 'lng' => 5.5675, 'population' => 197000]);
        PostalCode::factory()->create(['code' => '4000', 'name' => 'Liège', 'lat' => 50.6337, 'lng' => 5.5675, 'region' => 'wallonia', 'commune_id' => $this->liege->id]);
        PostalCode::factory()->create(['code' => '4000', 'name' => 'Glain', 'lat' => 50.6477, 'lng' => 5.5438, 'region' => 'wallonia', 'commune_id' => $this->liege->id]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function vet(array $attributes = [], string $category = 'veterinaire'): Provider
    {
        $provider = Provider::factory()->create($attributes + [
            'lat' => 50.64, 'lng' => 5.57, 'postal_code' => '4000', 'city' => 'Liège', 'phone' => '+3242223344',
            'website' => 'https://vet.example', 'opening_hours' => 'Mo-Fr 09:00-12:00,14:00-18:00; Sa 09:00-12:00 "sur rendez-vous"; Su off',
            'commune_id' => $this->liege->id,
        ]);
        $provider->categories()->attach(Category::where('slug', $category)->sole());

        return $provider;
    }

    public function test_provider_profile_shows_details_actions_and_local_business_json_ld(): void
    {
        $this->vet(['name' => 'Clinique des Coteaux', 'slug' => 'clinique-des-coteaux-liege']);

        $response = $this->get('/fr/pro/clinique-des-coteaux-liege')
            ->assertOk()
            ->assertSee('<title>Clinique des Coteaux – Vétérinaire à Liège | Doggys</title>', false)
            ->assertSee('Clinique des Coteaux, Vétérinaire à Liège. Adresse, téléphone, site web et horaires sur Doggys.', false)
            ->assertSee('href="tel:+3242223344"', false)
            ->assertSee('Appeler le 04 222 33 44')
            ->assertSee('https://www.openstreetmap.org/directions?route=', false)
            ->assertSee('rel="nofollow noopener"', false)
            ->assertSee('lun–ven 09:00–12:00, 14:00–18:00')
            ->assertSee('<span lang="und"', false)
            ->assertSee('/fr/pro/clinique-des-coteaux-liege/reclamer', false)
            ->assertDontSee('content="noindex', false)
            ->assertSee('rel="alternate" hreflang="nl" href="'.url('/nl/pro/clinique-des-coteaux-liege').'"', false);

        $this->assertStringContainsString('"@type":"VeterinaryCare"', $response->getContent());
        $this->assertStringContainsString('"openingHours":["Mo-Fr 09:00-12:00,14:00-18:00"]', $response->getContent());
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $response->getContent());
        $this->assertStringNotContainsString('AggregateRating', $response->getContent());

        $this->get('/nl/pro/clinique-des-coteaux-liege')->assertOk()
            ->assertSee('<title>Clinique des Coteaux – Dierenarts in Liège | Doggys</title>', false)
            ->assertSee('ma–vr 09:00–12:00, 14:00–18:00');
    }

    public function test_thin_listings_are_noindex_and_places_have_no_claim_block(): void
    {
        $this->vet(['slug' => 'thin-park', 'street' => null, 'phone' => null, 'email' => null, 'website' => null, 'opening_hours' => null], 'parc-a-chiens');

        $this->get('/fr/pro/thin-park')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('Fiche et localisation sur Doggys')
            ->assertSee('ce lieu')
            ->assertDontSee('C’est votre entreprise ?');
    }

    public function test_unsafe_websites_are_never_linked(): void
    {
        $provider = $this->vet(['slug' => 'unsafe-site', 'website' => 'javascript:alert(document.cookie)']);

        $this->get('/fr/pro/unsafe-site')->assertOk()->assertDontSee('javascript:alert', false);
        $this->getJson('/api/v1/providers/'.$provider->slug)->assertJsonPath('data.website', null);
    }

    public function test_json_ld_cannot_be_broken_out_of(): void
    {
        $this->vet(['slug' => 'evil', 'name' => '</script><script>alert(1)</script>']);

        $this->get('/fr/pro/evil')->assertOk()->assertDontSee('</script><script>alert(1)', false);
        $this->get('/fr/veterinaire')->assertOk()->assertDontSee('</script><script>alert(1)', false);
    }

    public function test_aggregate_rating_only_with_published_reviews(): void
    {
        $provider = $this->vet(['slug' => 'rated-vet']);
        Review::factory()->for($provider)->create(['body' => 'Une équipe adorable avec notre vieux labrador.', 'rating' => 5]);
        $provider->forceFill(['rating_avg' => 4.5, 'reviews_count' => 2])->save();

        $response = $this->get('/fr/pro/rated-vet')->assertOk();
        $this->assertStringContainsString('"@type":"AggregateRating","ratingValue":4.5,"reviewCount":2', $response->getContent());
    }

    public function test_hidden_and_removed_profiles_are_not_found(): void
    {
        $this->vet(['slug' => 'hidden-vet'])->forceFill(['status' => ProviderStatus::Hidden])->save();
        $this->vet(['slug' => 'removed-vet'])->forceFill(['status' => ProviderStatus::Removed])->save();

        $this->get('/fr/pro/hidden-vet')->assertNotFound();
        $this->get('/fr/pro/removed-vet')->assertNotFound();
        $this->get('/fr/pro/unknown-vet')->assertNotFound();
    }

    public function test_visitors_can_report_an_error_without_an_account(): void
    {
        $provider = $this->vet(['slug' => 'report-me']);

        $this->post('/fr/pro/report-me/signaler', ['type' => 'error', 'message' => 'Le numéro de téléphone a changé.'])
            ->assertRedirect('/fr/pro/report-me')
            ->assertSessionHas('status');

        $report = $provider->reports()->sole();
        $this->assertSame(ProviderReportType::Error, $report->type);
        $this->assertNull($report->email);
    }

    public function test_removal_requests_require_an_email_and_reports_are_validated(): void
    {
        $this->vet(['slug' => 'remove-me']);

        $this->post('/fr/pro/remove-me/signaler', ['type' => 'removal_request', 'message' => 'Je suis le propriétaire, merci de retirer.'])
            ->assertSessionHasErrors('email');
        $this->post('/fr/pro/remove-me/signaler', ['type' => 'spam', 'message' => 'court'])
            ->assertSessionHasErrors([
                'type' => __('directory.report.errors.type'),
                'message' => __('directory.report.errors.message'),
            ]);
        $this->post('/fr/pro/remove-me/signaler', ['type' => 'removal_request', 'message' => 'Je suis le propriétaire, merci de retirer.', 'email' => 'owner@example.com'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('provider_reports', 1);
    }

    public function test_reports_are_protected_by_honeypot_and_their_own_throttle(): void
    {
        $this->vet(['slug' => 'busy-vet']);
        $payload = ['type' => 'error', 'message' => 'Cette adresse n’existe plus.'];

        $this->post('/fr/pro/busy-vet/signaler', $payload + ['website_url' => 'http://spam.example'])->assertSessionHasErrors('form');
        $this->assertDatabaseCount('provider_reports', 0);

        // A visitor who just used the place autocomplete can still report (one counter per use).
        for ($i = 0; $i < 10; $i++) {
            $this->getJson('/api/v1/places?q=li')->assertOk();
        }

        for ($i = 0; $i < 4; $i++) {
            $this->post('/fr/pro/busy-vet/signaler', $payload)->assertRedirect();
        }
        $this->post('/fr/pro/busy-vet/signaler', $payload)->assertTooManyRequests();
        $this->assertDatabaseCount('provider_reports', 4);
    }

    public function test_category_pages_are_translated_and_only_link_to_indexable_cities(): void
    {
        foreach (['Vet One', 'Vet Two', 'Vet Three'] as $name) {
            $this->vet(['name' => $name]);
        }
        $this->artisan('doggys:refresh-city-pages')->assertSuccessful();

        $this->get('/fr/categories')->assertOk()->assertSee('/fr/veterinaire', false)->assertSee('Bientôt sur Doggys');
        $this->get('/fr/veterinaire')
            ->assertOk()
            ->assertSee('Vétérinaires en Belgique')
            ->assertSee('Vet One')
            ->assertSee('Bien choisir parmi les vétérinaires')
            ->assertSee('/fr/veterinaire/liege', false)
            ->assertSee('rel="alternate" hreflang="nl" href="'.url('/nl/dierenarts').'"', false)
            ->assertDontSee('noindex');
        $this->get('/nl/dierenarts')->assertOk()->assertSee('Dierenartsen in België');
        $this->get('/fr/dierenarts')->assertNotFound();
        $this->get('/fr/veterinaire?page=2')->assertNotFound();

        $this->get('/fr/promeneur-de-chiens')->assertOk()->assertSee('noindex', false);
    }

    public function test_city_pages_are_built_per_commune(): void
    {
        $this->vet(['name' => 'Vet One']);
        $this->vet(['name' => 'Vet Two', 'lat' => 50.70]);
        $this->vet(['name' => 'Vet Three', 'lat' => 50.60, 'opening_hours' => 'Sa 09:00-12:00']);
        $this->vet(['name' => 'Namur Vet', 'lat' => 50.46, 'lng' => 4.86]);
        $this->artisan('doggys:refresh-city-pages')->assertSuccessful();

        $response = $this->get('/fr/veterinaire/liege')
            ->assertOk()
            ->assertSee('Vétérinaires à Liège</h1>', false)
            ->assertSee('Doggys recense 3 adresses de vétérinaires')
            ->assertSee('Questions fréquentes')
            ->assertSee('Le samedi, vers quelles adresses se tourner à Liège ?')
            ->assertSee(trans('category_texts.veterinaire.choose'))
            ->assertSee('Vet Two')
            ->assertDontSee('Namur Vet')
            ->assertSee('rel="alternate" hreflang="de" href="'.url('/de/tierarzt/luettich').'"', false)
            ->assertDontSee('content="noindex', false);

        $this->assertStringContainsString('"@type":"ItemList"', $response->getContent());
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $response->getContent());

        $this->get('/nl/dierenarts/luik')->assertOk()->assertSee('Dierenartsen in Luik');
        // A section of the commune redirects permanently to the commune page.
        $this->get('/fr/veterinaire/glain')->assertRedirect('/fr/veterinaire/liege')->assertStatus(301);
        $this->get('/fr/veterinaire/atlantis')->assertNotFound();
        $this->get('/fr/animalerie/liege')->assertNotFound();
        $this->get('/fr/veterinaire/liege?page=2')->assertNotFound();
    }

    public function test_a_city_page_repeating_a_bigger_neighbour_is_noindex_and_not_linked(): void
    {
        Commune::factory()->named('Seraing')->create(['lat' => 50.6, 'lng' => 5.5, 'population' => 64000]);
        foreach (['Vet A', 'Vet B', 'Vet C', 'Vet D'] as $index => $name) {
            $this->vet(['name' => $name, 'lat' => 50.62 + $index / 1000]);
        }
        $this->artisan('doggys:refresh-city-pages')->assertSuccessful();

        $this->get('/fr/veterinaire/seraing')->assertOk()->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->get('/fr/veterinaire/liege')->assertOk()->assertDontSee('content="noindex', false)->assertDontSee('/fr/veterinaire/seraing', false);
        $this->get('/fr/veterinaire')->assertDontSee('/fr/veterinaire/seraing', false);
    }

    public function test_city_pages_are_rate_limited_more_loosely_than_search(): void
    {
        $this->vet();

        for ($i = 0; $i < 31; $i++) {
            $this->get('/fr/veterinaire/liege')->assertOk();
        }
    }

    public function test_api_lists_and_shows_providers(): void
    {
        $this->vet(['name' => 'Api Vet', 'slug' => 'api-vet']);
        $this->vet(['name' => 'Hidden Api Vet', 'slug' => 'hidden-api-vet'])->forceFill(['status' => ProviderStatus::Hidden])->save();

        $this->getJson('/api/v1/providers?location=4000&category=veterinaire&radius=5')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'api-vet')
            ->assertJsonPath('data.0.categories.0.slug', 'veterinaire')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.radius_km', 5);

        $this->getJson('/api/v1/providers?lat=50.63&lng=5.56&lang=nl')->assertOk()->assertJsonPath('data.0.categories.0.name', 'Dierenarts');
        $this->getJson('/api/v1/providers')->assertUnprocessable()->assertJsonValidationErrors('location');
        $this->getJson('/api/v1/providers?location=4000&radius=3')->assertUnprocessable()->assertJsonValidationErrors('radius');
        $this->getJson('/api/v1/providers?lat=95&lng=5')->assertUnprocessable()->assertJsonValidationErrors('lat');
        $this->getJson('/api/v1/providers?location[]=4000')->assertUnprocessable()->assertJsonValidationErrors('location');

        $this->getJson('/api/v1/providers/api-vet')->assertOk()->assertJsonPath('data.name', 'Api Vet')->assertJsonPath('data.rating', null);
        $this->getJson('/api/v1/providers/hidden-api-vet')->assertNotFound();
    }

    public function test_sitemap_lists_only_indexable_pages_and_robots_points_to_it(): void
    {
        foreach (['Vet One', 'Vet Two', 'Vet Three'] as $name) {
            $this->vet(['name' => $name, 'slug' => str($name)->slug()->toString()]);
        }
        $this->vet(['slug' => 'thin-vet', 'street' => null, 'phone' => null, 'email' => null, 'website' => null]);
        $this->artisan('doggys:refresh-city-pages')->assertSuccessful();

        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(url('/sitemap-nl-1.xml'), false);
        $this->get('/sitemap-fr-1.xml')->assertOk()
            ->assertSee('<loc>'.url('/fr/veterinaire').'</loc>', false)
            ->assertSee('<loc>'.url('/fr/veterinaire/liege').'</loc>', false)
            ->assertSee('<loc>'.url('/fr/pro/vet-one').'</loc>', false)
            ->assertDontSee('thin-vet')
            ->assertDontSee('/fr/recherche');
        $this->get('/sitemap-fr-9.xml')->assertNotFound();

        $this->assertStringContainsString('Sitemap: https://doggys.be/sitemap.xml', (string) file_get_contents(public_path('robots.txt')));
    }
}
