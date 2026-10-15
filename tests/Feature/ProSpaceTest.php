<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Models\BadgeRequest;
use App\Models\Category;
use App\Models\Provider;
use App\Models\User;
use App\Notifications\RequestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProSpaceTest extends TestCase
{
    use RefreshDatabase;

    private function managedProvider(User $pro): Provider
    {
        $provider = Provider::factory()->create(['name' => 'Salon Wouf', 'street' => 'Rue Haute', 'city' => 'Namur']);
        $provider->forceFill(['claimed_by_user_id' => $pro->id])->save();

        return $provider;
    }

    public function test_pro_space_lists_only_the_pros_listings(): void
    {
        $pro = User::factory()->pro()->create();
        $this->managedProvider($pro);
        Provider::factory()->create(['name' => 'Salon du voisin']);

        $this->actingAs($pro)->get('/fr/mon-compte/espace-pro')->assertOk()->assertSee('Salon Wouf')->assertDontSee('Salon du voisin');
        $this->actingAs(User::factory()->create())->get('/fr/mon-compte/espace-pro')->assertForbidden();
    }

    public function test_pro_edits_the_allowed_fields_of_their_listing_only(): void
    {
        $pro = User::factory()->pro()->create();
        $provider = $this->managedProvider($pro);
        $category = Category::factory()->create();

        $payload = [
            'description' => 'Toilettage tout en douceur.',
            'phone' => '081 22 33 44',
            'email' => 'bonjour@wouf.be',
            'website' => 'https://wouf.be',
            'opening_hours' => 'Mo-Fr 09:00-18:00',
            'is_mobile' => '1',
            'service_radius_km' => 20,
            'categories' => [$category->slug],
            // Not editable by the professional: silently ignored.
            'name' => 'Nouveau nom',
            'street' => 'Autre rue',
            'verification_level' => 'regulated',
        ];

        $this->actingAs($pro)->get('/fr/mon-compte/espace-pro/fiches/'.$provider->id.'/modifier')->assertOk();
        $this->actingAs($pro)->put('/fr/mon-compte/espace-pro/fiches/'.$provider->id, $payload)->assertSessionHasNoErrors();

        $provider->refresh();
        $this->assertSame('Toilettage tout en douceur.', $provider->description);
        $this->assertSame('+3281223344', $provider->phone);
        $this->assertTrue($provider->is_mobile);
        $this->assertSame(20, $provider->service_radius_km);
        $this->assertSame('Salon Wouf', $provider->name);
        $this->assertSame('Rue Haute', $provider->street);
        $this->assertFalse($provider->isVerified());
        $this->assertTrue($provider->categories->contains($category));

        $this->actingAs($pro)->put('/fr/mon-compte/espace-pro/fiches/'.$provider->id, ['website' => 'javascript:alert(1)'] + $payload)->assertSessionHasErrors('website');

        $other = Provider::factory()->create();
        $this->actingAs($pro)->get('/fr/mon-compte/espace-pro/fiches/'.$other->id.'/modifier')->assertForbidden();
        $this->actingAs($pro)->put('/fr/mon-compte/espace-pro/fiches/'.$other->id, $payload)->assertForbidden();
    }

    public function test_pro_requests_the_blue_badge_once(): void
    {
        Storage::fake('local');
        Notification::fake();
        $pro = User::factory()->pro()->create();
        $provider = $this->managedProvider($pro);
        $url = '/fr/mon-compte/espace-pro/fiches/'.$provider->id.'/badge-agree';
        $proof = fn () => UploadedFile::fake()->createWithContent('ordre.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));

        $this->actingAs($pro)->get($url)->assertOk();
        $this->actingAs($pro)->post($url, ['regulatory_number' => 'F1234', 'proof' => $proof()])->assertRedirect('/fr/mon-compte/espace-pro');
        $this->actingAs($pro)->post($url, ['regulatory_number' => 'F1234', 'proof' => $proof()]);

        $badgeRequest = BadgeRequest::query()->sole();
        $this->assertSame(ClaimStatus::Pending, $badgeRequest->status);
        Storage::disk('local')->assertExists($badgeRequest->proof_path);
        Notification::assertSentTo($pro, RequestReceived::class);

        $this->actingAs(User::factory()->pro()->create())->post($url, ['regulatory_number' => 'X', 'proof' => $proof()])->assertForbidden();
    }
}
