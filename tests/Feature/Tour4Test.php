<?php

namespace Tests\Feature;

use App\Enums\ProviderStatus;
use App\Enums\ReviewStatus;
use App\Enums\VerificationLevel;
use App\Models\Claim;
use App\Models\ContactMessage;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fixes of the "tour 4" code review: review lock, pending claim cap, orphan listings, policies, mail escaping.
 */
class Tour4Test extends TestCase
{
    use RefreshDatabase;

    private function managed(User $pro, array $attributes = []): Provider
    {
        $provider = Provider::factory()->create($attributes);
        $provider->forceFill(['claimed_by_user_id' => $pro->id])->save();

        return $provider;
    }

    public function test_review_is_refused_while_the_users_review_lock_is_held(): void
    {
        $user = User::factory()->create();
        $provider = Provider::factory()->create();
        $lock = Cache::lock('review:'.$user->id, 10);
        $this->assertTrue($lock->get());

        $this->actingAs($user)->post('/fr/pro/'.$provider->slug.'/avis', ['rating' => 5, 'body' => str_repeat('Très bien. ', 3)])->assertStatus(429);

        $this->assertDatabaseCount('reviews', 0);
        $lock->release();
    }

    public function test_a_user_cannot_have_more_than_five_pending_claims(): void
    {
        Storage::fake('local');
        Notification::fake();
        $pro = User::factory()->pro()->create();
        Claim::factory()->count(5)->for($pro)->create();
        $provider = Provider::factory()->create();

        $this->actingAs($pro)->post('/fr/pro/'.$provider->slug.'/reclamer', [
            'action' => 'manage',
            'bce_number' => '0403 170 701',
            'position' => 'Gérante',
            'proof' => UploadedFile::fake()->createWithContent('p.pdf', "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n"),
        ])->assertSessionHasErrors('form');

        $this->assertDatabaseCount('claims', 5);
    }

    public function test_purging_a_pro_removes_claims_proofs_badge_requests_and_hidden_created_listing(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('proofs/claim.pdf', 'x');
        Storage::disk('local')->put('proofs/badge.pdf', 'x');
        Storage::disk('local')->put('proofs/listing.pdf', 'x');
        $pro = User::factory()->pro()->create();
        $provider = $this->managed($pro);
        $provider->forceFill(['verification_level' => VerificationLevel::Regulated, 'plan' => 'premium'])->save();
        Claim::factory()->for($provider)->for($pro)->create(['proof_path' => 'proofs/claim.pdf']);
        $badge = $provider->badgeRequests()->make(['regulatory_number' => 'ORD-1']);
        $badge->forceFill(['user_id' => $pro->id, 'proof_path' => 'proofs/badge.pdf'])->save();
        $hidden = Provider::factory()->hidden()->create();
        $hiddenClaim = Claim::factory()->for($hidden)->for($pro)->create(['proof_path' => 'proofs/listing.pdf']);
        $hiddenClaim->forceFill(['creates_listing' => true])->save();

        $pro->purge();

        foreach (['claim', 'badge', 'listing'] as $name) {
            Storage::disk('local')->assertMissing('proofs/'.$name.'.pdf');
        }
        $this->assertDatabaseCount('claims', 0);
        $this->assertModelMissing($badge);
        $this->assertDatabaseMissing('providers', ['id' => $hidden->id]);
        $provider->refresh();
        $this->assertNull($provider->claimed_by_user_id);
        $this->assertSame(VerificationLevel::None, $provider->verification_level);
        $this->assertSame('free', $provider->plan);
        $this->assertSame(ProviderStatus::Active, $provider->status);
    }

    public function test_a_pro_cannot_touch_another_pros_listing_or_review(): void
    {
        $proA = User::factory()->pro()->create();
        $proB = User::factory()->pro()->create();
        $provider = $this->managed($proA);
        $review = Review::factory()->for($provider)->create();
        $base = '/fr/mon-compte/espace-pro/';

        $this->actingAs($proB)->get($base.'fiches/'.$provider->id.'/modifier')->assertForbidden();
        $this->actingAs($proB)->put($base.'fiches/'.$provider->id, ['description' => 'Piraté'])->assertForbidden();
        $this->actingAs($proB)->get($base.'fiches/'.$provider->id.'/badge-agree')->assertForbidden();
        $this->actingAs($proB)->post($base.'fiches/'.$provider->id.'/badge-agree', ['regulatory_number' => 'X'])->assertForbidden();
        $this->actingAs($proB)->put($base.'avis/'.$review->id.'/repondre', ['reply' => 'Piraté'])->assertForbidden();

        $this->assertNull($review->fresh()->provider_reply);
    }

    public function test_removed_listings_and_unpublished_reviews_are_out_of_the_pros_reach(): void
    {
        $pro = User::factory()->pro()->create();
        $removed = $this->managed($pro);
        $removed->forceFill(['status' => ProviderStatus::Removed])->save();
        $active = $this->managed($pro);
        $hiddenReview = Review::factory()->for($active)->create(['status' => ReviewStatus::Hidden]);
        $base = '/fr/mon-compte/espace-pro/';

        $this->actingAs($pro)->get($base.'fiches/'.$removed->id.'/modifier')->assertForbidden();
        $this->actingAs($pro)->put($base.'avis/'.$hiddenReview->id.'/repondre', ['reply' => 'Merci'])->assertForbidden();
    }

    public function test_contact_mail_escapes_markdown_from_the_visitor(): void
    {
        $message = ContactMessage::factory()->create(['message' => '[Cliquez ici](http://evil.example) ![x](http://evil.example/a.png) <b>x</b>']);

        $lines = (new ContactMessageReceived($message))->toMail(new \stdClass)->introLines;
        $body = implode("\n", $lines);

        $this->assertStringNotContainsString('](', $body);
        $this->assertStringContainsString('\[Cliquez ici\]\(http\:\/\/evil\.example\)', $body);
    }

    public function test_contact_has_no_global_daily_cap(): void
    {
        Notification::fake();
        $data = ['name' => 'C', 'email' => 'c@example.be', 'subject' => 'Idée', 'message' => 'Un message assez long pour passer.'];

        $this->post('/fr/contact', $data, ['REMOTE_ADDR' => '203.0.113.1'])->assertRedirect();
        $this->post('/fr/contact', $data, ['REMOTE_ADDR' => '203.0.113.2'])->assertRedirect();
    }
}
