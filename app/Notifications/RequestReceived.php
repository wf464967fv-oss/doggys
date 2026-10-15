<?php

namespace App\Notifications;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms to a professional that their claim ("claim") or badge request ("badge") was received.
 */
class RequestReceived extends Notification
{
    public function __construct(public string $kind, public Provider $provider) {}

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
            ->subject(__('pro.mail.received_subject_'.$this->kind, ['name' => $this->provider->name]))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name]))
            ->line(__('pro.mail.received_intro_'.$this->kind, ['name' => $this->provider->name]))
            ->line(__('pro.mail.received_delay'))
            ->action(__('pro.mail.action_space'), localized_route('pro.index'))
            ->salutation(__('mail.salutation'));
    }
}
