<?php

namespace Tests\Feature;

use App\Enums\ClaimAction;
use App\Enums\ClaimStatus;
use App\Enums\ProviderReportType;
use App\Enums\ProviderStatus;
use App\Enums\ReportStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Enums\VerificationLevel;
use App\Models\AdminAction;
use App\Models\BadgeRequest;
use App\Models\Category;
use App\Models\Claim;
use App\Models\ContactMessage;
use App\Models\Provider;
use App\Models\ProviderReport;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use App\Notifications\RequestDecided;
use App\Notifications\ReviewHidden;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Destructive actions need a recently typed password.
     */
    private function withConfirmedPassword(): static
    {
        return $this->actingAs($this->admin)->withSession(['auth.password_confirmed_at' => time()]);
    }

    public function test_admin_area_is_refused_to_guests_owners_and_pros(): void
    {
        $this->get('/admin')->assertRedirect();
        $this->assertGuest();

        foreach ([User::factory()->create(), User::factory()->pro()->create()] as $user) {
            $this->actingAs($user)->get('/admin')->assertForbidden();
            $this->actingAs($user)->get('/admin/claims')->assertForbidden();
            $this->actingAs($user)->delete('/admin/users/'.$this->admin->id)->assertForbidden();
        }

        $this->assertModelExists($this->admin);
    }

    public function test_dashboard_shows_the_counters(): void
    {
        Claim::factory()->count(2)->create();
        ProviderReport::factory()->create(['status' => ReportStatus::Pending]);

        $response = $this->actingAs($this->admin)->get('/admin')->assertOk()->assertSee(__('admin.dashboard.claims'));
        $this->assertSame(2, collect($response->viewData('counters'))->firstWhere('label', 'claims')['count']);
    }

    public function test_every_admin_screen_renders(): void
    {
        $claim = Claim::factory()->create();
        $badgeRequest = new BadgeRequest(['regulatory_number' => 'F123']);
        $badgeRequest->forceFill(['provider_id' => $claim->provider_id, 'user_id' => $claim->user_id])->save();
        ReviewReport::factory()->create();
        ContactMessage::factory()->create();

        foreach (['/admin/claims', '/admin/claims/'.$claim->id, '/admin/badges', '/admin/badges/'.$badgeRequest->id, '/admin/providers',
            '/admin/providers/create', '/admin/providers/'.$claim->provider_id.'/edit', '/admin/provider-reports', '/admin/reviews',
            '/admin/users', '/admin/messages', '/admin/guides', '/admin/log'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_approving_a_manage_claim_hands_over_the_listing_with_the_green_badge(): void
    {
        $claim = Claim::factory()->create(['bce_number' => '0403.170.701', 'action' => ClaimAction::Manage]);

        $this->actingAs($this->admin)->post('/admin/claims/'.$claim->id.'/approve', ['bce_verified' => '1', 'note' => 'Bienvenue'])
            ->assertRedirect('/admin/claims');

        $provider = $claim->provider->fresh();
        $this->assertSame($claim->user_id, $provider->claimed_by_user_id);
        $this->assertSame(VerificationLevel::Verified, $provider->verification_level);
        $this->assertSame('0403.170.701', $provider->bce_number);
        $this->assertSame(ClaimStatus::Approved, $claim->fresh()->status);
        $this->assertTrue(AdminAction::query()->where('action', 'claim.approve')->where('subject_id', $claim->id)->where('user_id', $this->admin->id)->exists());
        Notification::assertSentTo($claim->user, RequestDecided::class, fn (RequestDecided $notification) => $notification->approved && $notification->kind === 'claim_manage');
    }

    public function test_approving_without_bce_check_gives_no_badge_and_publishes_new_listings(): void
    {
        $provider = Provider::factory()->hidden()->create();
        $claim = Claim::factory()->for($provider)->create(['creates_listing' => true]);

        $this->actingAs($this->admin)->post('/admin/claims/'.$claim->id.'/approve');

        $provider->refresh();
        $this->assertSame(ProviderStatus::Active, $provider->status);
        $this->assertSame(VerificationLevel::None, $provider->verification_level);
        $this->assertSame($claim->user_id, $provider->claimed_by_user_id);
    }

    public function test_approving_a_delete_claim_removes_the_listing_for_good(): void
    {
        $claim = Claim::factory()->create(['action' => ClaimAction::Delete]);

        $this->actingAs($this->admin)->post('/admin/claims/'.$claim->id.'/approve');

        $this->assertSame(ProviderStatus::Removed, $claim->provider->fresh()->status);
        $this->get('/fr/pro/'.$claim->provider->slug)->assertNotFound();
    }

    public function test_rejecting_a_claim_sends_the_note_in_the_claimant_language(): void
    {
        $claim = Claim::factory()->create(['locale' => 'nl']);

        $this->actingAs($this->admin)->post('/admin/claims/'.$claim->id.'/reject', ['note' => 'Bewijs onleesbaar']);

        $this->assertSame(ClaimStatus::Rejected, $claim->fresh()->status);
        $this->assertSame('Bewijs onleesbaar', $claim->fresh()->admin_note);
        $this->assertNull($claim->provider->fresh()->claimed_by_user_id);
        Notification::assertSentTo($claim->user, RequestDecided::class, fn (RequestDecided $notification) => ! $notification->approved && $notification->locale === 'nl');

        $this->actingAs($this->admin)->post('/admin/claims/'.$claim->id.'/approve')->assertStatus(409);
    }

    public function test_proof_is_served_through_an_expiring_signed_url(): void
    {
        Storage::disk('local')->put('proofs/extrait-test.pdf', '%PDF-1.4 test');
        $this->beforeApplicationDestroyed(fn () => Storage::disk('local')->delete('proofs/extrait-test.pdf'));
        $claim = Claim::factory()->create(['proof_path' => 'proofs/extrait-test.pdf']);

        $proofUrl = $this->actingAs($this->admin)->get('/admin/claims/'.$claim->id)->assertOk()->viewData('proofUrl');
        $this->assertStringContainsString('signature=', $proofUrl);
        $this->assertStringContainsString('expires=', $proofUrl);

        $this->get($proofUrl)->assertOk();
        $this->get(preg_replace('/signature=[^&]+/', 'signature=forged', $proofUrl))->assertForbidden();

        $this->travel(11)->minutes();
        $this->get($proofUrl)->assertForbidden();
    }

    public function test_badge_approval_gives_the_blue_badge(): void
    {
        $provider = Provider::factory()->create();
        $badgeRequest = new BadgeRequest(['regulatory_number' => 'F-4521']);
        $badgeRequest->forceFill(['provider_id' => $provider->id, 'user_id' => User::factory()->pro()->create()->id])->save();

        $this->actingAs($this->admin)->post('/admin/badges/'.$badgeRequest->id.'/approve')->assertRedirect('/admin/badges');

        $this->assertSame(VerificationLevel::Regulated, $provider->fresh()->verification_level);
        $this->assertSame('F-4521', $provider->fresh()->regulatory_number);
    }

    public function test_admin_searches_edits_hides_and_creates_listings(): void
    {
        $provider = Provider::factory()->create(['name' => 'Toilettage Câlin', 'city' => 'Liège']);
        Provider::factory()->create(['name' => 'Pension des Collines']);
        $category = Category::factory()->create();

        $this->actingAs($this->admin)->get('/admin/providers?q=Câlin')->assertSee('Toilettage Câlin')->assertDontSee('Pension des Collines');

        $data = [
            'name' => 'Toilettage Câlin & Co', 'slug' => 'toilettage-calin-liege', 'street' => 'Rue Neuve', 'postal_code' => '4000', 'city' => 'Liège',
            'lat' => 50.63, 'lng' => 5.57, 'status' => 'active', 'verification_level' => 'verified', 'bce_number' => '0403170701',
            'website' => 'https://calin.be', 'categories' => [$category->slug],
        ];
        $this->actingAs($this->admin)->put('/admin/providers/'.$provider->id, $data)->assertSessionHasNoErrors();
        $provider->refresh();
        $this->assertSame('Toilettage Câlin & Co', $provider->name);
        $this->assertSame('0403.170.701', $provider->bce_number);
        $this->assertSame(VerificationLevel::Verified, $provider->verification_level);

        $this->actingAs($this->admin)->put('/admin/providers/'.$provider->id, ['website' => 'javascript:alert(1)'] + $data)->assertSessionHasErrors('website');

        $this->actingAs($this->admin)->post('/admin/providers/'.$provider->id.'/toggle');
        $this->assertSame(ProviderStatus::Hidden, $provider->fresh()->status);

        $this->actingAs($this->admin)->post('/admin/providers', ['name' => 'Parc canin du Bois', 'slug' => ''] + $data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('providers', ['name' => 'Parc canin du Bois', 'source' => 'manual']);
    }

    public function test_destructive_actions_require_a_recent_password(): void
    {
        $provider = Provider::factory()->create();

        $this->actingAs($this->admin)->withHeader('Accept-Language', 'fr-BE,fr;q=0.9')->from('/admin/providers/'.$provider->id.'/edit')
            ->delete('/admin/providers/'.$provider->id)
            ->assertRedirect('/fr/confirmer-mot-de-passe');
        $this->assertSame(ProviderStatus::Active, $provider->fresh()->status);

        $this->withConfirmedPassword()->delete('/admin/providers/'.$provider->id)->assertRedirect('/admin/providers');
        $this->assertSame(ProviderStatus::Removed, $provider->fresh()->status);
    }

    public function test_gdpr_removal_request_is_applied_from_the_reports_screen(): void
    {
        $report = ProviderReport::factory()->create(['type' => ProviderReportType::RemovalRequest, 'status' => ReportStatus::Pending]);
        $error = ProviderReport::factory()->create(['type' => ProviderReportType::Error, 'status' => ReportStatus::Pending]);

        $this->withConfirmedPassword()->post('/admin/provider-reports/'.$report->id.'/apply-removal');
        $this->assertSame(ProviderStatus::Removed, $report->provider->fresh()->status);
        $this->assertSame(ReportStatus::Handled, $report->fresh()->status);

        $this->withConfirmedPassword()->post('/admin/provider-reports/'.$error->id.'/apply-removal')->assertStatus(409);
        $this->actingAs($this->admin)->post('/admin/provider-reports/'.$error->id.'/close', ['status' => 'dismissed']);
        $this->assertSame(ReportStatus::Dismissed, $error->fresh()->status);
    }

    public function test_review_reports_are_moderated(): void
    {
        $provider = Provider::factory()->create();
        $review = Review::factory()->for($provider)->create(['rating' => 1]);
        $report = ReviewReport::factory()->for($review)->create();
        $other = ReviewReport::factory()->create();

        $this->actingAs($this->admin)->post('/admin/reviews/'.$review->id.'/hide', ['note' => 'Propos injurieux']);
        $this->assertSame(ReviewStatus::Hidden, $review->fresh()->status);
        $this->assertSame(ReportStatus::Handled, $report->fresh()->status);
        $this->assertSame(0, $provider->fresh()->reviews_count);
        Notification::assertSentTo($review->user, ReviewHidden::class);

        $this->actingAs($this->admin)->post('/admin/review-reports/'.$other->id.'/dismiss');
        $this->assertSame(ReportStatus::Dismissed, $other->fresh()->status);
        $this->assertSame(ReviewStatus::Published, $other->review->fresh()->status);

        $this->withConfirmedPassword()->delete('/admin/reviews/'.$review->id);
        $this->assertModelMissing($review);
    }

    public function test_admin_manages_users_but_not_themselves(): void
    {
        $user = User::factory()->create(['email' => 'camille@example.be']);
        Review::factory()->for($user)->create();

        $this->actingAs($this->admin)->get('/admin/users?q=camille')->assertSee('camille@example.be');

        $this->withConfirmedPassword()->put('/admin/users/'.$user->id.'/role', ['role' => 'pro']);
        $this->assertSame(UserRole::Pro, $user->fresh()->role);

        $this->withConfirmedPassword()->put('/admin/users/'.$this->admin->id.'/role', ['role' => 'owner'])->assertForbidden();
        $this->withConfirmedPassword()->delete('/admin/users/'.$this->admin->id)->assertForbidden();

        $this->withConfirmedPassword()->delete('/admin/users/'.$user->id);
        $this->assertModelMissing($user);
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_contact_messages_and_guides(): void
    {
        $message = ContactMessage::factory()->create(['status' => ReportStatus::Pending]);

        $this->actingAs($this->admin)->get('/admin/messages')->assertSee($message->email);
        $this->actingAs($this->admin)->post('/admin/messages/'.$message->id.'/handle');
        $this->assertSame(ReportStatus::Handled, $message->fresh()->status);
    }
}
