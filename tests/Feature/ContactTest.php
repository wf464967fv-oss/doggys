<?php

namespace Tests\Feature;

use App\Http\Middleware\ProtectAgainstBots;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private const DATA = [
        'name' => 'Camille',
        'email' => 'camille@example.be',
        'subject' => 'Une idée',
        'message' => 'Pourriez-vous ajouter les plages où les chiens sont admis ?',
    ];

    public function test_contact_page_renders_in_every_language(): void
    {
        foreach (['fr', 'nl', 'en', 'de'] as $locale) {
            $this->get('/'.$locale.'/'.trans('routes.contact', [], $locale))->assertOk()->assertSee(__('contact.title', [], $locale));
        }
    }

    public function test_message_is_stored_and_forwarded_to_the_doggys_mailbox(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/nl/contact', self::DATA)->assertRedirect('/nl/contact')->assertSessionHas('status');

        $message = ContactMessage::query()->sole();
        $this->assertSame('nl', $message->locale);
        $this->assertSame($user->id, $message->user_id);
        Notification::assertSentTo(new AnonymousNotifiable, ContactMessageReceived::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === config('mail.from.address'));
    }

    public function test_message_is_validated_and_bots_are_stopped(): void
    {
        Notification::fake();

        $this->post('/fr/contact', ['email' => 'pas-un-email'] + self::DATA)->assertSessionHasErrors('email');
        $this->post('/fr/contact', ['message' => 'court'] + self::DATA)->assertSessionHasErrors('message');
        $this->post('/fr/contact', [ProtectAgainstBots::HONEYPOT_FIELD => 'http://spam.example'] + self::DATA)->assertSessionHasErrors('form');

        $this->assertDatabaseCount('contact_messages', 0);
        Notification::assertNothingSent();
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/fr/contact', self::DATA)->assertRedirect();
        }

        $this->post('/fr/contact', self::DATA)->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 5);
    }
}
