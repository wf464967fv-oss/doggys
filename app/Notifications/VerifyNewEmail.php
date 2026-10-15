<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Sent to the new address of a user changing e-mail; the change only applies once the link is clicked.
 */
class VerifyNewEmail extends Notification
{
    public function __construct(public User $user) {}

    /**
     * @return array<int, string>
     */
    public function via(AnonymousNotifiable|User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(AnonymousNotifiable|User $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(app()->getLocale().'.account.email.verify', now()->addHour(), [
            'id' => $this->user->getKey(),
            'hash' => sha1((string) $this->user->pending_email),
        ]);

        return (new MailMessage)
            ->subject(__('mail.new_email.subject'))
            ->greeting(__('mail.greeting', ['name' => $this->user->name]))
            ->line(__('mail.new_email.intro'))
            ->action(__('mail.new_email.action'), $url)
            ->line(__('mail.new_email.outro'))
            ->salutation(__('mail.salutation'));
    }
}
