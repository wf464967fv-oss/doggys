<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Dog;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders(): void
    {
        $user = User::factory()->create(['name' => 'Camille']);

        $this->actingAs($user)->get('/fr/mon-compte/profil')->assertOk()->assertSee('Camille');
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::factory()->create(['password' => 'Sup3rSecretPass']);

        $this->actingAs($user)->from('/fr/mon-compte/profil')
            ->put('/fr/mon-compte/profil', ['name' => 'Camille D.', 'email' => $user->email])
            ->assertRedirect('/fr/mon-compte/profil');
        $this->assertSame('Camille D.', $user->fresh()->name);

        $this->actingAs($user)->from('/fr/mon-compte/profil')
            ->put('/fr/mon-compte/mot-de-passe', [
                'current_password' => 'Sup3rSecretPass',
                'password' => 'An0therSecretPass',
                'password_confirmation' => 'An0therSecretPass',
            ])->assertSessionHasNoErrors();
    }

    public function test_export_requires_password_confirmation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/fr/mon-compte/mes-donnees')
            ->assertRedirect('/fr/confirmer-mot-de-passe');
    }

    public function test_user_can_export_their_data_as_json(): void
    {
        $user = User::factory()->create(['email' => 'camille@example.be']);
        Dog::factory()->for($user)->create(['name' => 'Filou']);
        Review::factory()->for($user)->create(['body' => 'Super toiletteur, très doux avec Filou.']);
        Dog::factory()->create(['name' => 'Rex']);

        $response = $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get('/fr/mon-compte/mes-donnees');

        $response->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="doggys-'.now()->format('Y-m-d').'.json"')
            ->assertJsonPath('account.email', 'camille@example.be')
            ->assertJsonPath('dogs.0.name', 'Filou')
            ->assertJsonPath('reviews.0.body', 'Super toiletteur, très doux avec Filou.')
            ->assertJsonCount(1, 'dogs');
        $this->assertStringNotContainsString('password', $response->getContent());
    }

    public function test_account_deletion_requires_the_password(): void
    {
        $user = User::factory()->create(['password' => 'Sup3rSecretPass']);

        $this->actingAs($user)->delete('/fr/mon-compte', ['password' => 'wrong'])
            ->assertSessionHasErrorsIn('deleteAccount', 'password');

        $this->assertModelExists($user);
    }

    public function test_user_can_delete_their_account_and_data(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('claims/proof.pdf', 'proof');

        $user = User::factory()->pro()->create(['password' => 'Sup3rSecretPass']);
        $dog = Dog::factory()->for($user)->create();
        $claim = Claim::factory()->for($user)->create();
        $claim->forceFill(['proof_path' => 'claims/proof.pdf'])->save();

        $this->actingAs($user)->delete('/fr/mon-compte', ['password' => 'Sup3rSecretPass'])
            ->assertRedirect('/fr');

        $this->assertGuest();
        $this->assertModelMissing($user);
        $this->assertModelMissing($dog);
        $this->assertModelMissing($claim);
        Storage::disk('local')->assertMissing('claims/proof.pdf');
    }

    public function test_make_admin_command_promotes_a_user(): void
    {
        $user = User::factory()->create(['email' => 'erwan@example.be']);

        $this->artisan('doggys:make-admin', ['email' => 'Erwan@Example.be'])->assertSuccessful();
        $this->assertTrue($user->fresh()->isAdmin());

        $this->artisan('doggys:make-admin', ['email' => 'nobody@example.be'])->assertFailed();
    }
}
