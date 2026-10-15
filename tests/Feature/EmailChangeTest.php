<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EmailChangeRequested;
use App\Notifications\VerifyNewEmail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_email_requires_the_current_password(): void
    {
        $user = User::factory()->create(['email' => 'old@example.be', 'password' => 'Sup3rSecretPass']);

        $this->actingAs($user)->put('/fr/mon-compte/profil', ['name' => $user->name, 'email' => 'attacker@evil.test'])
            ->assertSessionHasErrorsIn('updateProfileInformation', 'current_password');

        $this->actingAs($user)->put('/fr/mon-compte/profil', [
            'name' => $user->name,
            'email' => 'attacker@evil.test',
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrorsIn('updateProfileInformation', 'current_password');

        $this->assertSame('old@example.be', $user->fresh()->email);
        $this->assertNull($user->fresh()->pending_email);
    }

    public function test_name_can_change_without_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/fr/mon-compte/profil', ['name' => 'Camille D.', 'email' => $user->email])
            ->assertSessionHasNoErrors();

        $this->assertSame('Camille D.', $user->fresh()->name);
    }

    public function test_new_email_stays_pending_until_confirmed(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'old@example.be', 'password' => 'Sup3rSecretPass']);

        $this->actingAs($user)->put('/nl/mijn-account/profiel', [
            'name' => $user->name,
            'email' => 'New@Example.be',
            'current_password' => 'Sup3rSecretPass',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('old@example.be', $user->email);
        $this->assertSame('new@example.be', $user->pending_email);
        $this->assertNotNull($user->email_verified_at);

        Notification::assertSentTo($user, EmailChangeRequested::class);
        Notification::assertSentOnDemand(VerifyNewEmail::class, function (VerifyNewEmail $notification, array $channels, AnonymousNotifiable $notifiable) use ($user) {
            $url = $notification->toMail($notifiable)->actionUrl;

            return $notifiable->routes['mail'] === 'new@example.be' && str_contains($url, '/nl/mijn-account/e-mail-bevestigen/'.$user->id.'/');
        });

        // The account area stays fully usable with the old, still verified address.
        $this->actingAs($user)->get('/nl/mijn-account')->assertOk();
    }

    public function test_signed_link_swaps_the_address(): void
    {
        $user = User::factory()->create(['email' => 'old@example.be']);
        $user->forceFill(['pending_email' => 'new@example.be'])->save();

        $this->actingAs($user)->get($this->confirmUrl($user->id, 'new@example.be'))
            ->assertRedirect('/fr/mon-compte/profil')
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('new@example.be', $user->email);
        $this->assertNull($user->pending_email);
    }

    public function test_confirmation_link_is_refused_for_a_wrong_hash_or_another_user(): void
    {
        $user = User::factory()->create(['email' => 'old@example.be']);
        $user->forceFill(['pending_email' => 'new@example.be'])->save();
        $other = User::factory()->create();

        $this->actingAs($user)->get($this->confirmUrl($user->id, 'evil@example.be'))->assertForbidden();
        $this->actingAs($other)->get($this->confirmUrl($user->id, 'new@example.be'))->assertForbidden();
        $this->actingAs($user)->get(str_replace('signature=', 'signature=x', $this->confirmUrl($user->id, 'new@example.be')))->assertForbidden();

        $this->assertSame('old@example.be', $user->fresh()->email);
    }

    public function test_unverified_user_can_fix_a_mistyped_address_and_reach_gdpr_actions(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'tpyo@example.be', 'password' => 'Sup3rSecretPass']);

        $this->actingAs($user)->get('/fr/mon-compte/profil')->assertOk();
        $this->actingAs($user)->put('/fr/mon-compte/profil', [
            'name' => $user->name,
            'email' => 'typo@example.be',
            'current_password' => 'Sup3rSecretPass',
        ])->assertSessionHasNoErrors();

        $this->assertSame('typo@example.be', $user->fresh()->email);
        Notification::assertSentTo($user, VerifyEmail::class);

        $this->actingAs($user)->delete('/fr/mon-compte', ['password' => 'Sup3rSecretPass'])->assertRedirect('/fr');
        $this->assertModelMissing($user);
    }

    public function test_verification_link_with_wrong_hash_or_other_user_is_refused(): void
    {
        $user = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();

        $wrongHash = URL::temporarySignedRoute('fr.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1('nope@example.be')]);
        $this->actingAs($user)->get($wrongHash)->assertForbidden();

        $othersLink = URL::temporarySignedRoute('fr.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($other)->get($othersLink)->assertForbidden();

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertNull($other->fresh()->email_verified_at);
    }

    private function confirmUrl(int $id, string $email): string
    {
        return URL::temporarySignedRoute('fr.account.email.verify', now()->addHour(), ['id' => $id, 'hash' => sha1($email)]);
    }
}
