<?php

namespace Tests\Feature;

use App\Enums\ProviderStatus;
use App\Models\PostalCode;
use App\Models\Provider;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportProvidersTest extends TestCase
{
    use RefreshDatabase;

    private string $path = 'storage/framework/testing/providers-import.json';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        PostalCode::factory()->create(['code' => '3560', 'name' => 'Lummen', 'lat' => 50.98, 'lng' => 5.19, 'region' => 'flanders']);
    }

    protected function tearDown(): void
    {
        @unlink(base_path($this->path));

        parent::tearDown();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function writeData(array $rows): void
    {
        @mkdir(dirname(base_path($this->path)), 0777, true);
        file_put_contents(base_path($this->path), json_encode($rows));
    }

    /**
     * @return array<string, mixed>
     */
    private function row(array $overrides = []): array
    {
        return $overrides + [
            'source' => 'osm', 'source_ref' => 'node/1', 'name' => 'Trimsalon Bello', 'categories' => ['toiletteur-salon'],
            'street' => 'Dorpstraat', 'house_number' => '1', 'postal_code' => '2910', 'city' => 'Essen - Wildert', 'region' => 'flanders',
            'lat' => 51.45, 'lng' => 4.45, 'phone' => '+32478737647', 'email' => null, 'website' => null,
            'opening_hours' => null, 'is_mobile' => false, 'bce_number' => null, 'regulatory_number' => null,
            'license' => 'ODbL', 'fetched_at' => '2026-10-15T07:29:33.166Z',
        ];
    }

    public function test_import_creates_providers_and_is_idempotent(): void
    {
        $this->writeData([
            $this->row(),
            $this->row(['source_ref' => 'node/2', 'categories' => ['animalerie', 'toiletteur-salon']]),
            $this->row(['source' => 'registry', 'source_ref' => 'HK-1', 'name' => 'Dierenasiel De Pootjes', 'categories' => ['refuge'],
                'postal_code' => '3560', 'city' => null, 'lat' => null, 'lng' => null]),
        ]);

        $this->artisan('doggys:import-providers', ['path' => $this->path])
            ->expectsOutputToContain('3 created, 0 updated, 0 unchanged')
            ->assertSuccessful();

        $first = Provider::where('source_ref', 'node/1')->sole();
        $this->assertSame('trimsalon-bello-essen', $first->slug);
        $this->assertSame('Essen', $first->city);
        $this->assertSame('trimsalon-bello-essen-2', Provider::where('source_ref', 'node/2')->sole()->slug);
        $this->assertSame(['animalerie', 'toiletteur-salon'], Provider::where('source_ref', 'node/2')->sole()->categories->pluck('slug')->sort()->values()->all());

        $shelter = Provider::where('source_ref', 'HK-1')->sole();
        $this->assertSame('50.9800000', $shelter->lat, 'Falls back to the postal code centroid.');
        $this->assertSame('Lummen', $shelter->city);

        $this->artisan('doggys:import-providers', ['path' => $this->path])
            ->expectsOutputToContain('0 created, 0 updated, 3 unchanged')
            ->assertSuccessful();
        $this->assertDatabaseCount('providers', 3);

        $this->writeData([$this->row(['name' => 'Trimsalon Bello & Co', 'categories' => ['toiletteur-domicile']])]);
        $this->artisan('doggys:import-providers', ['path' => $this->path])->expectsOutputToContain('1 updated')->assertSuccessful();

        $first->refresh();
        $this->assertSame('Trimsalon Bello & Co', $first->name);
        $this->assertSame('trimsalon-bello-essen', $first->slug, 'Slugs never change: they are public URLs.');
        $this->assertSame(['toiletteur-domicile'], $first->categories->pluck('slug')->all());
    }

    public function test_claimed_and_removed_listings_are_never_overwritten(): void
    {
        $this->writeData([$this->row(), $this->row(['source_ref' => 'node/2', 'name' => 'Other'])]);
        $this->artisan('doggys:import-providers', ['path' => $this->path])->assertSuccessful();

        Provider::where('source_ref', 'node/1')->sole()->forceFill(['claimed_by_user_id' => User::factory()->create()->id, 'name' => 'Edited by the pro'])->save();
        Provider::where('source_ref', 'node/2')->sole()->forceFill(['status' => ProviderStatus::Removed])->save();

        $this->writeData([$this->row(['name' => 'From OSM']), $this->row(['source_ref' => 'node/2', 'name' => 'From OSM too'])]);
        $this->artisan('doggys:import-providers', ['path' => $this->path])
            ->expectsOutputToContain('2 protected')
            ->assertSuccessful();

        $this->assertSame('Edited by the pro', Provider::where('source_ref', 'node/1')->sole()->name);
        $this->assertSame('Other', Provider::where('source_ref', 'node/2')->sole()->name);
    }

    public function test_missing_file_fails(): void
    {
        $this->artisan('doggys:import-providers', ['path' => 'storage/framework/testing/missing.json'])->assertFailed();
    }
}
