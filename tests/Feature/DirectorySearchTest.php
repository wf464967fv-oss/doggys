<?php

namespace Tests\Feature;

use App\Enums\ProviderStatus;
use App\Models\Category;
use App\Models\PostalCode;
use App\Models\Provider;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorySearchTest extends TestCase
{
    use RefreshDatabase;

    private const LAT = 50.6337;

    private const LNG = 5.5675;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        PostalCode::factory()->create([
            'code' => '4000', 'name' => 'Liège', 'lat' => self::LAT, 'lng' => self::LNG, 'region' => 'wallonia',
            'name_translations' => ['fr' => 'Liège', 'nl' => 'Luik', 'en' => 'Liège', 'de' => 'Lüttich'],
        ]);
    }

    /**
     * Provider of the given category, $kmNorth kilometres north of Liège.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function providerAt(float $kmNorth, string $category = 'veterinaire', array $attributes = []): Provider
    {
        $provider = Provider::factory()->create(['lat' => self::LAT + $kmNorth / 111.2, 'lng' => self::LNG, 'postal_code' => '4000', 'city' => 'Liège'] + $attributes);
        $provider->categories()->attach(Category::where('slug', $category)->sole());

        return $provider;
    }

    public function test_results_are_sorted_by_distance_within_the_radius(): void
    {
        $far = $this->providerAt(8, attributes: ['name' => 'Far Vet']);
        $near = $this->providerAt(2, attributes: ['name' => 'Near Vet']);
        $this->providerAt(30, attributes: ['name' => 'Out Of Range Vet']);
        $this->providerAt(1, 'animalerie', ['name' => 'Pet Shop']);

        $this->get('/fr/recherche?category=veterinaire&location=4000&radius=10')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSeeInOrder([$near->name, $far->name])
            ->assertDontSee('Out Of Range Vet')
            ->assertDontSee('Pet Shop')
            ->assertSee('2 pros trouvés');

        $this->get('/fr/recherche?category=veterinaire&location=Li%C3%A8ge&radius=50')
            ->assertOk()
            ->assertSee('Out Of Range Vet');
    }

    public function test_browser_coordinates_and_locality_names_in_any_language_are_accepted(): void
    {
        $this->providerAt(1, attributes: ['name' => 'Close Vet']);

        $this->get('/fr/recherche?lat='.self::LAT.'&lng='.self::LNG.'&near_me=1')->assertOk()->assertSee('Close Vet');
        $this->get('/fr/recherche?location=Luik')->assertOk()->assertSee('Close Vet');
    }

    public function test_only_active_providers_are_listed(): void
    {
        $this->providerAt(1, attributes: ['name' => 'Hidden Vet'])->forceFill(['status' => ProviderStatus::Hidden])->save();
        $this->providerAt(1, attributes: ['name' => 'Removed Vet'])->forceFill(['status' => ProviderStatus::Removed])->save();
        $this->providerAt(1, attributes: ['name' => 'Visible Vet']);

        $this->get('/fr/recherche?location=4000')
            ->assertSee('Visible Vet')
            ->assertDontSee('Hidden Vet')
            ->assertDontSee('Removed Vet');
    }

    public function test_mobile_pros_only_appear_when_the_customer_is_within_their_service_radius(): void
    {
        $this->providerAt(20, 'toiletteur-domicile', ['name' => 'Wide Groomer', 'is_mobile' => true, 'service_radius_km' => 30]);
        $this->providerAt(8, 'toiletteur-domicile', ['name' => 'Short Groomer', 'is_mobile' => true, 'service_radius_km' => 5]);
        $this->providerAt(3, 'toiletteur-salon', ['name' => 'Salon Groomer']);

        $this->get('/fr/recherche?location=4000&radius=10')
            ->assertSee('Wide Groomer')
            ->assertDontSee('Short Groomer')
            ->assertSee('Salon Groomer');

        $this->get('/fr/recherche?location=4000&radius=10&mobile=1')
            ->assertSee('Wide Groomer')
            ->assertDontSee('Salon Groomer');
    }

    public function test_verified_and_rating_filters(): void
    {
        $verified = $this->providerAt(2, attributes: ['name' => 'Verified Vet']);
        $verified->forceFill(['verification_level' => 'verified', 'rating_avg' => 4.6, 'reviews_count' => 5])->save();
        $this->providerAt(2, attributes: ['name' => 'Plain Vet']);
        $this->providerAt(2, attributes: ['name' => 'Low Rated Vet'])->forceFill(['rating_avg' => 2.5, 'reviews_count' => 2])->save();

        $this->get('/fr/recherche?location=4000&verified=1')->assertSee('Verified Vet')->assertDontSee('Plain Vet');
        $this->get('/fr/recherche?location=4000&min_rating=4')->assertSee('Verified Vet')->assertDontSee('Low Rated Vet')->assertDontSee('Plain Vet');
    }

    public function test_empty_unknown_and_invalid_searches_show_friendly_states(): void
    {
        $this->get('/fr/recherche')->assertOk()->assertSee('Où cherchez-vous');
        $this->get('/fr/recherche?location=Atlantis')->assertOk()->assertSee('Nous ne trouvons pas');
        $this->get('/fr/recherche?location=4000&radius=5')
            ->assertOk()
            ->assertSee('Aucun pro trouvé')
            ->assertSee('Élargir à 10 km')
            ->assertSee('Ajoutez votre entreprise');
        $this->get('/fr/recherche?location=4000&radius=7')->assertRedirect('/fr/recherche');
    }

    public function test_place_suggestions_endpoint(): void
    {
        $this->getJson('/api/v1/places?q=lie')->assertOk()->assertExactJson(['data' => ['4000 Liège']]);
        $this->getJson('/api/v1/places?q=luik&lang=nl')->assertExactJson(['data' => ['4000 Luik']]);
        $this->getJson('/api/v1/places?q=40')->assertExactJson(['data' => ['4000 Liège']]);
        $this->getJson('/api/v1/places?q=l')->assertExactJson(['data' => []]);
    }
}
