<?php

namespace Tests\Feature;

use App\Enums\ProviderReportType;
use App\Enums\ProviderStatus;
use App\Models\Category;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Models\Review;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        PostalCode::factory()->create([
            'code' => '4000', 'name' => 'Liège', 'lat' => 50.6337, 'lng' => 5.5675, 'region' => 'wallonia',
            'name_translations' => ['fr' => 'Liège', 'nl' => 'Luik', 'en' => 'Liège', 'de' => 'Lüttich'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function vet(array $attributes = []): Provider
    {
        $provider = Provider::factory()->create($attributes + [
            'lat' => 50.64, 'lng' => 5.57, 'postal_code' => '4000', 'city' => 'Liège', 'phone' => '+3242223344',
            'website' => 'https://vet.example', 'opening_hours' => 'Mo-Fr 09:00-18:00; Su off',
        ]);
        $provider->categories()->attach(Category::where('slug', 'veterinaire')->sole());

        return $provider;
    }

    public function test_provider_profile_shows_details_and_local_business_json_ld(): void
    {
        $provider = $this->vet(['name' => 'Clinique des Coteaux', 'slug' => 'clinique-des-coteaux-liege']);

        $response = $this->get('/fr/pro/clinique-des-coteaux-liege')
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Clinique des Coteaux')
            ->assertSee('href="tel:+3242223344"', false)
            ->assertSee('04 222 33 44')
            ->assertSee('rel="nofollow noopener"', false)
            ->assertSee('lun–ven 09:00–18:00')
            ->assertSee('/fr/pro/clinique-des-coteaux-liege/reclamer', false)
            ->assertSee('rel="alternate" hreflang="nl" href="'.url('/nl/pro/clinique-des-coteaux-liege').'"', false);

        $this->assertStringContainsString('"@type":"VeterinaryCare"', $response->getContent());
        $this->assertStringContainsString('"openingHours":["Mo-Fr 09:00-18:00"]', $response->getContent());
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $response->getContent());
        $this->assertStringNotContainsString('AggregateRating', $response->getContent());

        $this->get('/nl/pro/clinique-des-coteaux-liege')->assertOk()->assertSee('ma–vr 09:00–18:00');
    }

    public function test_aggregate_rating_only_with_published_reviews(): void
    {
        $provider = $this->vet(['slug' => 'rated-vet']);
        $provider->forceFill(['rating_avg' => 4.5, 'reviews_count' => 2])->save();
        Review::factory()->for($provider)->create(['body' => 'Une équipe adorable avec notre vieux labrador.', 'rating' => 5]);

        $response = $this->get('/fr/pro/rated-vet')->assertOk()->assertSee('Une équipe adorable');
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
            ->assertSessionHasErrors(['type', 'message']);
        $this->post('/fr/pro/remove-me/signaler', ['type' => 'removal_request', 'message' => 'Je suis le propriétaire, merci de retirer.', 'email' => 'owner@example.com'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('provider_reports', 1);
    }

    public function test_reports_are_protected_by_honeypot_and_throttle(): void
    {
        $this->vet(['slug' => 'busy-vet']);
        $payload = ['type' => 'error', 'message' => 'Cette adresse n’existe plus.'];

        $this->post('/fr/pro/busy-vet/signaler', $payload + ['website_url' => 'http://spam.example'])->assertSessionHasErrors('form');
        $this->assertDatabaseCount('provider_reports', 0);

        // The rejected bot attempt counts towards the limit too (5 per hour and per IP).
        for ($i = 0; $i < 4; $i++) {
            $this->post('/fr/pro/busy-vet/signaler', $payload)->assertRedirect();
        }
        $this->post('/fr/pro/busy-vet/signaler', $payload)->assertTooManyRequests();
        $this->assertDatabaseCount('provider_reports', 4);
    }

    public function test_category_pages_are_translated_and_link_to_cities(): void
    {
        $this->vet(['name' => 'Vet One']);

        $this->get('/fr/categories')->assertOk()->assertSee('/fr/veterinaire', false);
        $this->get('/fr/veterinaire')
            ->assertOk()
            ->assertSee('Vétérinaires en Belgique')
            ->assertSee('Vet One')
            ->assertSee('/fr/veterinaire/liege', false)
            ->assertSee('rel="alternate" hreflang="nl" href="'.url('/nl/dierenarts').'"', false)
            ->assertDontSee('noindex');
        $this->get('/nl/dierenarts')->assertOk()->assertSee('Dierenartsen in België');
        $this->get('/fr/dierenarts')->assertNotFound();

        $this->get('/fr/promeneur-de-chiens')->assertOk()->assertSee('noindex', false);
    }

    public function test_city_pages_list_providers_around_the_city(): void
    {
        $this->vet(['name' => 'Vet One']);
        $this->vet(['name' => 'Vet Two', 'lat' => 50.70]);
        $this->vet(['name' => 'Vet Three', 'lat' => 50.60]);
        $this->vet(['name' => 'Namur Vet', 'lat' => 50.46, 'lng' => 4.86]);

        $response = $this->get('/fr/veterinaire/liege')
            ->assertOk()
            ->assertSee('<h1 class="mt-4 font-display text-3xl leading-tight font-semibold text-brown sm:text-4xl lg:text-5xl">Vétérinaires à Liège</h1>', false)
            ->assertSee('Doggys recense 3 adresses de vétérinaires')
            ->assertSee('Questions fréquentes')
            ->assertSee('Vet Two')
            ->assertDontSee('Namur Vet')
            ->assertSee('rel="alternate" hreflang="de" href="'.url('/de/tierarzt/luttich').'"', false)
            ->assertDontSee('content="noindex', false);

        $this->assertStringContainsString('"@type":"ItemList"', $response->getContent());
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $response->getContent());

        $this->get('/nl/dierenarts/luik')->assertOk()->assertSee('Dierenartsen in Luik');
        $this->get('/fr/veterinaire/atlantis')->assertNotFound();
        $this->get('/fr/animalerie/liege')->assertNotFound();
    }

    public function test_api_lists_and_shows_providers(): void
    {
        $this->vet(['name' => 'Api Vet', 'slug' => 'api-vet']);
        $this->vet(['slug' => 'hidden-api-vet'])->forceFill(['status' => ProviderStatus::Hidden])->save();

        $this->getJson('/api/v1/providers?location=4000&category=veterinaire&radius=5')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'api-vet')
            ->assertJsonPath('data.0.categories.0.slug', 'veterinaire')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.radius_km', 5);

        $this->getJson('/api/v1/providers?lat=50.63&lng=5.56&lang=nl')->assertOk()->assertJsonPath('data.0.categories.0.name', 'Dierenarts');
        $this->getJson('/api/v1/providers')->assertUnprocessable()->assertJsonValidationErrors('location');
        $this->getJson('/api/v1/providers?location=4000&radius=3')->assertUnprocessable()->assertJsonValidationErrors('radius');

        $this->getJson('/api/v1/providers/api-vet')->assertOk()->assertJsonPath('data.name', 'Api Vet')->assertJsonPath('data.rating', null);
        $this->getJson('/api/v1/providers/hidden-api-vet')->assertNotFound();
    }
}
