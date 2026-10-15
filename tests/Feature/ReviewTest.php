<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Provider;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private const BODY = 'Très douce avec notre chien, salon impeccable et conseils utiles.';

    private function postReview(User $user, Provider $provider, array $data = []): TestResponse
    {
        return $this->actingAs($user)->post('/fr/pro/'.$provider->slug.'/avis', $data + ['rating' => 5, 'body' => self::BODY]);
    }

    public function test_verified_user_publishes_a_review_and_the_rating_is_recomputed(): void
    {
        $provider = Provider::factory()->create();
        Review::factory()->for($provider)->create(['rating' => 2]);

        $this->postReview(User::factory()->create(['name' => 'Camille Dupont']), $provider, ['rating' => 5])
            ->assertRedirect('/fr/pro/'.$provider->slug.'#reviews')
            ->assertSessionHasNoErrors();

        $provider->refresh();
        $this->assertSame(2, $provider->reviews_count);
        $this->assertEquals(3.5, (float) $provider->rating_avg);

        $this->actingAsGuest()->get('/fr/pro/'.$provider->slug)->assertOk()->assertSee('Camille D.')->assertDontSee('Camille Dupont');
    }

    public function test_guests_and_unverified_users_cannot_review(): void
    {
        $provider = Provider::factory()->create();

        $this->post('/fr/pro/'.$provider->slug.'/avis', ['rating' => 5, 'body' => self::BODY])->assertRedirect('/fr/connexion');
        $this->postReview(User::factory()->unverified()->create(), $provider)->assertRedirect('/fr/verifier-email');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_rating_and_body_are_validated(): void
    {
        $provider = Provider::factory()->create();
        $user = User::factory()->create();

        $this->postReview($user, $provider, ['rating' => 6, 'body' => 'Trop court'])->assertSessionHasErrorsIn('review', ['rating', 'body']);
        $this->postReview($user, $provider, ['rating' => 0, 'body' => str_repeat('a', 2001)])->assertSessionHasErrorsIn('review', ['rating', 'body']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_one_review_per_provider_every_thirty_days(): void
    {
        $provider = Provider::factory()->create();
        $user = User::factory()->create();

        $this->postReview($user, $provider)->assertSessionHasNoErrors();
        $this->postReview($user, $provider)->assertSessionHasErrorsIn('review', 'body');
        $this->assertSame(1, $provider->reviews()->count());

        $this->travel(31)->days();
        $this->postReview($user, $provider)->assertSessionHasNoErrors();
        $this->assertSame(2, $provider->reviews()->count());
    }

    public function test_at_most_five_reviews_per_day(): void
    {
        $user = User::factory()->create();
        $providers = Provider::factory()->count(6)->create();

        foreach ($providers->take(5) as $provider) {
            $this->postReview($user, $provider)->assertSessionHasNoErrors();
        }

        $this->postReview($user, $providers->last())->assertSessionHasErrorsIn('review', 'body');
        $this->assertSame(5, $user->reviews()->count());

        $this->travel(25)->hours();
        $this->postReview($user, $providers->last())->assertSessionHasNoErrors();
    }

    public function test_professional_cannot_review_their_own_listing(): void
    {
        $pro = User::factory()->pro()->create();
        $provider = Provider::factory()->create();
        $provider->forceFill(['claimed_by_user_id' => $pro->id])->save();

        $this->postReview($pro, $provider)->assertSessionHasErrorsIn('review', 'body');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_hidden_and_removed_listings_cannot_be_reviewed(): void
    {
        $user = User::factory()->create();

        $this->postReview($user, Provider::factory()->hidden()->create())->assertNotFound();
        $removed = Provider::factory()->create();
        $removed->forceFill(['status' => 'removed'])->save();
        $this->postReview($user, $removed)->assertNotFound();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_author_edits_and_deletes_only_their_own_reviews(): void
    {
        $provider = Provider::factory()->create();
        $author = User::factory()->create();
        $review = Review::factory()->for($author)->for($provider)->create(['rating' => 1]);
        $other = User::factory()->create();

        $this->actingAs($other)->get('/fr/mon-compte/avis/'.$review->id.'/modifier')->assertForbidden();
        $this->actingAs($other)->put('/fr/mon-compte/avis/'.$review->id, ['rating' => 5, 'body' => self::BODY])->assertForbidden();
        $this->actingAs($other)->delete('/fr/mon-compte/avis/'.$review->id)->assertForbidden();

        $this->actingAs($author)->get('/fr/mon-compte/avis')->assertOk()->assertSee($provider->name);
        $this->actingAs($author)->put('/fr/mon-compte/avis/'.$review->id, ['rating' => 4, 'body' => self::BODY])->assertRedirect('/fr/mon-compte/avis');
        $this->assertEquals(4.0, (float) $provider->fresh()->rating_avg);

        $this->actingAs($author)->delete('/fr/mon-compte/avis/'.$review->id)->assertRedirect('/fr/mon-compte/avis');
        $this->assertModelMissing($review);
        $this->assertSame(0, $provider->fresh()->reviews_count);
        $this->assertNull($provider->fresh()->rating_avg);
    }

    public function test_hiding_a_review_and_deleting_the_author_account_recompute_the_rating(): void
    {
        $provider = Provider::factory()->create();
        $author = User::factory()->create(['password' => 'Sup3rSecretPass']);
        $review = Review::factory()->for($author)->for($provider)->create(['rating' => 1]);
        Review::factory()->for($provider)->create(['rating' => 5]);
        $this->assertEquals(3.0, (float) $provider->fresh()->rating_avg);

        $review->forceFill(['status' => ReviewStatus::Hidden])->save();
        $this->assertSame(1, $provider->fresh()->reviews_count);
        $this->assertEquals(5.0, (float) $provider->fresh()->rating_avg);

        $review->forceFill(['status' => ReviewStatus::Published])->save();
        $this->actingAs($author)->delete('/fr/mon-compte', ['password' => 'Sup3rSecretPass'])->assertRedirect('/fr');

        $this->assertModelMissing($review);
        $this->assertSame(1, $provider->fresh()->reviews_count);
        $this->assertEquals(5.0, (float) $provider->fresh()->rating_avg);
    }

    public function test_reviews_can_be_reported_once_per_user_but_not_by_their_author(): void
    {
        $review = Review::factory()->create();
        $reporter = User::factory()->create();
        $url = '/fr/avis/'.$review->id.'/signaler';

        $this->actingAs($review->user)->post($url, ['reason' => 'Je signale mon propre avis.'])->assertForbidden();

        $this->actingAs($reporter)->post($url, ['reason' => 'court'])->assertSessionHasErrorsIn('report'.$review->id, 'reason');
        $this->actingAs($reporter)->post($url, ['reason' => 'Propos injurieux envers la gérante.'])->assertSessionHasNoErrors();
        $this->actingAs($reporter)->post($url, ['reason' => 'Propos injurieux envers la gérante.'])->assertSessionHas('status', __('reviews.flash.already_reported'));

        $this->assertSame(1, ReviewReport::query()->count());
        $this->get('/fr/pro/'.$review->provider->slug)->assertSee(__('reviews.report.done'));
    }

    public function test_only_the_managing_professional_replies_to_a_review(): void
    {
        $pro = User::factory()->pro()->create();
        $provider = Provider::factory()->create();
        $provider->forceFill(['claimed_by_user_id' => $pro->id])->save();
        $review = Review::factory()->for($provider)->create();
        $url = '/fr/mon-compte/espace-pro/avis/'.$review->id.'/repondre';

        $this->actingAs(User::factory()->pro()->create())->put($url, ['reply' => 'Merci beaucoup'])->assertForbidden();

        $this->actingAs($pro)->put($url, ['reply' => 'Merci pour votre visite !'])->assertSessionHasNoErrors();
        $this->actingAs($pro)->put($url, ['reply' => 'Merci, à bientôt !'])->assertSessionHasNoErrors();

        $this->assertSame('Merci, à bientôt !', $review->fresh()->provider_reply);
        $this->get('/fr/pro/'.$provider->slug)->assertSee('Merci, à bientôt !');
    }

    public function test_review_list_is_paginated_newest_first(): void
    {
        $provider = Provider::factory()->create();
        Review::factory()->for($provider)->create(['body' => 'Le plus ancien des avis de la liste.', 'created_at' => now()->subYear()]);
        Review::factory()->count(10)->for($provider)->create(['created_at' => now()->subDay()]);

        $this->get('/fr/pro/'.$provider->slug)->assertOk()->assertDontSee('Le plus ancien des avis de la liste.');
        $this->get('/fr/pro/'.$provider->slug.'?page=2')->assertOk()
            ->assertSee('Le plus ancien des avis de la liste.')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_reviews_policy_page_is_translated(): void
    {
        $this->get('/fr/avis-verifies')->assertOk()->assertSee(__('reviews.policy.title', [], 'fr'));
        $this->get('/nl/'.trans('routes.reviews_policy', [], 'nl'))->assertOk()->assertSee(__('reviews.policy.title', [], 'nl'));
        $this->get('/de/'.trans('routes.reviews_policy', [], 'de'))->assertOk()->assertSee(__('reviews.policy.title', [], 'de'));
    }
}
