<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'account_type' => 'owner',
            'name' => 'Camille Dupont',
            'email' => 'camille@example.be',
            'password' => 'Sup3rSecretPass',
            'password_confirmation' => 'Sup3rSecretPass',
            'terms' => '1',
        ], $overrides);
    }

    public function test_register_page_offers_both_account_types(): void
    {
        $this->get('/fr/inscription')
            ->assertOk()
            ->assertSee('value="owner"', false)
            ->assertSee('value="pro"', false)
            ->assertSee('name="website_url"', false);
    }

    public function test_dog_owner_can_register_and_must_verify_email(): void
    {
        Notification::fake();

        $this->post('/fr/inscription', $this->validPayload())->assertRedirect('/fr/mon-compte');

        $user = User::firstWhere('email', 'camille@example.be');
        $this->assertSame(UserRole::Owner, $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->get('/fr/mon-compte')->assertRedirect('/fr/verifier-email');
    }

    public function test_professional_can_register(): void
    {
        Notification::fake();

        $this->post('/nl/registreren', $this->validPayload(['account_type' => 'pro', 'email' => 'pro@example.be']))
            ->assertRedirect('/nl/mijn-account');

        $this->assertSame(UserRole::Pro, User::firstWhere('email', 'pro@example.be')->role);
    }

    public function test_admin_role_cannot_be_chosen_at_registration(): void
    {
        $this->post('/fr/inscription', $this->validPayload(['account_type' => 'admin']))
            ->assertSessionHasErrors('account_type');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_weak_password_and_missing_terms_are_rejected(): void
    {
        $this->post('/fr/inscription', $this->validPayload([
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => null,
        ]))->assertSessionHasErrors(['password', 'terms']);

        $this->assertGuest();
    }

    public function test_honeypot_blocks_bots(): void
    {
        $this->post('/fr/inscription', $this->validPayload(['website_url' => 'http://spam.example']))
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_is_rate_limited(): void
    {
        Notification::fake();

        for ($i = 0; $i < 10; $i++) {
            $this->post('/fr/inscription', $this->validPayload(['email' => "user{$i}@example.be"]));
            auth()->logout();
        }

        $this->post('/fr/inscription', $this->validPayload(['email' => 'late@example.be']))->assertTooManyRequests();
    }

    public function test_verification_link_verifies_the_email(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('fr.verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect('/fr/mon-compte?verified=1');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verification_email_links_to_the_current_locale(): void
    {
        Notification::fake();

        $this->post('/de/registrieren', $this->validPayload());

        Notification::assertSentTo(User::first(), VerifyEmail::class, function (VerifyEmail $notification, array $channels, User $user) {
            $mail = $notification->toMail($user);

            return str_contains($mail->actionUrl, '/de/e-mail-bestaetigen/') && $mail->subject === __('mail.verify.subject', [], 'de');
        });
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'Sup3rSecretPass']);

        $this->post('/en/login', ['email' => $user->email, 'password' => 'Sup3rSecretPass'])
            ->assertRedirect('/en/my-account');
        $this->assertAuthenticatedAs($user);

        $this->post('/en/logout')->assertRedirect('/en');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/fr/connexion', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $this->post('/fr/connexion', ['email' => $user->email, 'password' => 'wrong-password'])->assertTooManyRequests();
    }

    public function test_login_is_rate_limited_per_ip_across_accounts(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post('/fr/connexion', ['email' => "victim{$i}@example.be", 'password' => 'Summer2026!']);
        }

        $this->post('/fr/connexion', ['email' => 'victim99@example.be', 'password' => 'Summer2026!'])->assertTooManyRequests();
    }

    public function test_full_password_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'camille@example.be']);
        $token = Password::broker()->createToken($user);

        $this->get('/fr/reinitialiser-mot-de-passe/'.$token.'?email=camille@example.be')->assertOk();

        $this->post('/fr/reinitialiser-mot-de-passe', [
            'token' => $token,
            'email' => 'camille@example.be',
            'password' => 'Br4ndNewSecret',
            'password_confirmation' => 'Br4ndNewSecret',
        ])->assertRedirect('/fr/connexion')->assertSessionHas('status', __('passwords.reset', [], 'fr'));

        $this->assertTrue(Hash::check('Br4ndNewSecret', $user->fresh()->password));

        $this->post('/fr/reinitialiser-mot-de-passe', [
            'token' => 'invalid-token',
            'email' => 'camille@example.be',
            'password' => 'An0therSecretPass',
            'password_confirmation' => 'An0therSecretPass',
        ])->assertSessionHasErrors('email');
    }

    public function test_password_reset_does_not_reveal_unknown_addresses(): void
    {
        $this->post('/fr/mot-de-passe-oublie', ['email' => 'nobody@example.be'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __('passwords.sent', [], 'fr'));
    }

    public function test_guests_are_sent_to_the_localized_login_page(): void
    {
        $this->get('/nl/mijn-account')->assertRedirect('/nl/inloggen');
    }
}
