<?php

namespace Tests\Feature;

use App\Enums\ProviderStatus;
use App\Models\Category;
use App\Models\Commune;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Services\ProviderSearch;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        $liege = Commune::factory()->named(['fr' => 'Liège', 'nl' => 'Luik', 'en' => 'Liège', 'de' => 'Lüttich'])
            ->create(['lat' => self::LAT, 'lng' => self::LNG]);
        PostalCode::factory()->create([
            'code' => '4000', 'name' => 'Liège', 'lat' => self::LAT, 'lng' => self::LNG, 'region' => 'wallonia', 'commune_id' => $liege->id,
            'name_translations' => ['fr' => 'Liège', 'nl' => 'Luik', 'en' => 'Liège', 'de' => 'Lüttich'],
        ]);
        PostalCode::factory()->create(['code' => '4020', 'name' => 'Liège', 'lat' => self::LAT, 'lng' => self::LNG, 'region' => 'wallonia', 'commune_id' => $liege->id]);
        PostalCode::factory()->create(['code' => '4000', 'name' => 'Glain', 'lat' => 50.6477, 'lng' => 5.5438, 'region' => 'wallonia', 'commune_id' => $liege->id]);
    }

    /**
     * Provider of the given category, $kmNorth kilometres north of Liège.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function providerAt(float $kmNorth, string $category = 'veterinaire', array $attributes = []): Provider
    {
        $provider = Provider::factory()->create($attributes + ['lat' => self::LAT + $kmNorth / 111.2, 'lng' => self::LNG, 'postal_code' => '4000', 'city' => 'Liège']);
        $provider->categories()->attach(Category::where('slug', $category)->sole());

        return $provider;
    }

    public function test_results_are_sorted_by_distance_within_the_radius(): void
    {
        $far = $this->providerAt(8, attributes: ['name' => 'Far Vet']);
        $near = $this->providerAt(2, attributes: ['name' => 'Near Vet']);
        $this->providerAt(30, attributes: ['name' => 'Out Of Range Vet']);
        $this->providerAt(1, 'animalerie', ['name' => 'Pet Shop']);
        // Inside the square bounding box but outside the 10 km circle (diagonal corner).
        $this->providerAt(8, attributes: ['name' => 'Corner Vet', 'lng' => self::LNG + 0.11]);

        $this->get('/fr/recherche?category=veterinaire&location=4000&radius=10')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSeeInOrder([$near->name, $far->name])
            ->assertDontSee('Out Of Range Vet')
            ->assertDontSee('Corner Vet')
            ->assertDontSee('Pet Shop')
            ->assertSee('2 pros trouvés');

        $this->get('/fr/recherche?category=veterinaire&location=Li%C3%A8ge&radius=50')
            ->assertOk()
            ->assertSee('Out Of Range Vet');
    }

    public function test_search_hydrates_only_one_page_of_rows(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->providerAt(0.1 * $i, attributes: ['name' => 'Vet '.$i]);
        }

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });

        $page = ProviderSearch::paginate(ProviderSearch::query(self::LAT, self::LNG, 10), self::LAT, self::LNG, 20);

        $this->assertCount(20, $page->items());
        $this->assertSame(30, $page->total());
        $page = collect($queries)->first(fn (string $sql) => str_contains($sql, 'order by (((lat'));
        $this->assertNotNull($page, 'Results are sorted by distance in SQL.');
        $this->assertStringContainsString('limit 20', $page, 'Only one page of ids is read.');
        $this->assertStringStartsWith('select "providers"."id" from', $page, 'Sorting works on narrow rows.');
        $this->assertNotNull(
            collect($queries)->first(fn (string $sql) => str_starts_with($sql, 'select * from "providers" where "providers"."id" in')),
            'Full rows are loaded for the displayed page only.',
        );
    }

    public function test_browser_coordinates_and_place_names_in_any_language_are_accepted(): void
    {
        $this->providerAt(1, attributes: ['name' => 'Close Vet']);

        $this->get('/fr/recherche?lat='.self::LAT.'&lng='.self::LNG.'&near_me=1')->assertOk()->assertSee('Close Vet');
        $this->get('/fr/recherche?location=Luik')->assertOk()->assertSee('Close Vet');
        $this->get('/fr/recherche?location=Glain')->assertOk()->assertSee('Close Vet');
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
        $this->providerAt(80, 'toiletteur-domicile', ['name' => 'Far Groomer', 'is_mobile' => true, 'service_radius_km' => 100]);
        $this->providerAt(3, 'toiletteur-salon', ['name' => 'Salon Groomer']);

        $this->get('/fr/recherche?location=4000&radius=10')
            ->assertSee('Wide Groomer')
            ->assertSee('Far Groomer')
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

    public function test_filters_are_only_offered_when_a_listing_can_match_them(): void
    {
        $this->providerAt(2);

        $this->get('/fr/recherche?location=4000')->assertDontSee('name="mobile"', false)->assertDontSee('name="verified"', false);

        $this->providerAt(3, 'toiletteur-domicile', ['is_mobile' => true]);
        Cache::forget('directory.available-filters');
        $this->get('/fr/recherche?location=4000')->assertSee('name="mobile"', false);
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
        $this->get('/fr/recherche?location=4000&radius=5&verified=1')
            ->assertOk()
            ->assertSee('Retirer les filtres')
            ->assertDontSee('Élargir à 10 km');
        $this->get('/fr/recherche?location=4000&radius=7')->assertRedirect('/fr/recherche');
        $this->get('/fr/recherche?location=4000&page=9')->assertNotFound();
    }

    public function test_search_pages_are_rate_limited(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/fr/recherche?location=4000')->assertOk();
        }

        $this->get('/fr/recherche?location=4000')->assertTooManyRequests();
    }

    public function test_place_suggestions_endpoint(): void
    {
        $this->getJson('/api/v1/places?q=lie')->assertOk()->assertExactJson(['data' => ['4000 Liège']])
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->getJson('/api/v1/places?q=luik&lang=nl')->assertExactJson(['data' => ['4000 Luik']]);
        $this->getJson('/api/v1/places?q=luettich&lang=de')->assertExactJson(['data' => ['4000 Lüttich']]);
        $this->getJson('/api/v1/places?q=gla')->assertExactJson(['data' => ['4000 Glain']]);
        $this->getJson('/api/v1/places?q=40')->assertExactJson(['data' => ['4000 Liège', '4000 Glain', '4020 Liège']]);
        $this->getJson('/api/v1/places?q=l')->assertExactJson(['data' => []]);
        $this->getJson('/api/v1/places?q[]=1')->assertUnprocessable();
    }

    public function test_place_names_get_their_official_casing(): void
    {
        $this->assertSame('Vaux-sur-Sûre', PostalCode::placeCase('Vaux-Sur-Sûre'));
        $this->assertSame("Braine-l'Alleud", PostalCode::placeCase("Braine-L'Alleud"));
        $this->assertSame('Beveren-aan-den-IJzer', PostalCode::placeCase('Beveren-Aan-Den-Ijzer'));
        $this->assertSame("'s Herenelderen", PostalCode::placeCase("'S Herenelderen"));
    }
}
