<?php

namespace Tests\Feature;

use App\Models\Dog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DogTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_a_dog_with_vaccinations(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/fr/mon-compte/chiens', [
            'name' => 'Filou',
            'breed' => 'Border collie',
            'sex' => 'male',
            'birth_date' => '2021-04-12',
            'microchip_number' => '967000012345678',
            'is_neutered' => '1',
            'weight_kg' => '18.5',
            'vaccinations' => [
                ['name' => 'Rage', 'date' => '2025-03-02'],
                ['name' => '', 'date' => ''],
            ],
            'health_notes' => 'Allergique au poulet.',
        ])->assertRedirect('/fr/mon-compte')->assertSessionHas('status');

        $dog = $user->dogs()->sole();
        $this->assertSame('Filou', $dog->name);
        $this->assertTrue($dog->is_neutered);
        $this->assertSame([['name' => 'Rage', 'date' => '2025-03-02']], $dog->vaccinations);
    }

    public function test_dog_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/fr/mon-compte/chiens', [
            'name' => '',
            'sex' => 'unicorn',
            'birth_date' => now()->addYear()->toDateString(),
            'weight_kg' => '500',
            'vaccinations' => [['name' => '', 'date' => '2020-01-01']],
        ])->assertSessionHasErrors(['name', 'sex', 'birth_date', 'weight_kg', 'vaccinations.0.name']);

        $this->assertDatabaseCount('dogs', 0);
    }

    public function test_dashboard_only_lists_own_dogs(): void
    {
        $user = User::factory()->create();
        Dog::factory()->for($user)->create(['name' => 'Filou']);
        Dog::factory()->create(['name' => 'Rex']);

        $this->actingAs($user)->get('/fr/mon-compte')
            ->assertOk()
            ->assertSee('Filou')
            ->assertDontSee('Rex');
    }

    public function test_dashboard_shows_an_empty_state(): void
    {
        $this->actingAs(User::factory()->create())->get('/en/my-account')
            ->assertOk()
            ->assertSee(__('dogs.empty_title', [], 'en'));
    }

    public function test_owner_can_update_and_delete_their_dog(): void
    {
        $dog = Dog::factory()->create(['name' => 'Filou']);

        $this->actingAs($dog->user)->get("/fr/mon-compte/chiens/{$dog->id}/modifier")->assertOk()->assertSee('Filou');

        $this->actingAs($dog->user)->put("/fr/mon-compte/chiens/{$dog->id}", ['name' => 'Filou II'])
            ->assertRedirect('/fr/mon-compte');
        $this->assertSame('Filou II', $dog->fresh()->name);

        $this->actingAs($dog->user)->delete("/fr/mon-compte/chiens/{$dog->id}")->assertRedirect('/fr/mon-compte');
        $this->assertModelMissing($dog);
    }

    public function test_users_cannot_see_or_change_someone_elses_dog(): void
    {
        $dog = Dog::factory()->create(['name' => 'Filou']);
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get("/fr/mon-compte/chiens/{$dog->id}/modifier")->assertForbidden();
        $this->actingAs($intruder)->put("/fr/mon-compte/chiens/{$dog->id}", ['name' => 'Stolen'])->assertForbidden();
        $this->actingAs($intruder)->delete("/fr/mon-compte/chiens/{$dog->id}")->assertForbidden();

        $this->assertSame('Filou', $dog->fresh()->name);
    }

    public function test_admins_cannot_access_private_dog_profiles_either(): void
    {
        $dog = Dog::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get("/fr/mon-compte/chiens/{$dog->id}/modifier")
            ->assertForbidden();
    }

    public function test_unverified_users_cannot_manage_dogs(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->post('/fr/mon-compte/chiens', ['name' => 'Filou'])
            ->assertRedirect('/fr/verifier-email');

        $this->assertDatabaseCount('dogs', 0);
    }
}
