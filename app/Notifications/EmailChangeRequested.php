<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Warns the current address that a change of e-mail was requested on the account.
 */
class EmailChangeRequested extends Notification
{
    public function __construct(public string $newEmail) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('mail.email_change.subject'))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name]))
            ->line(__('mail.email_change.intro', ['email' => $this->newEmail]))
            ->line(__('mail.email_change.outro'))
            ->action(__('mail.email_change.action'), localized_route('password.request'))
            ->salutation(__('mail.salutation'));
    }
}
