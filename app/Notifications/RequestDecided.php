<?php

namespace App\Notifications;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a professional the admin's decision on a request. $kind: "claim_manage", "claim_delete",
 * "listing" (new listing) or "badge". Sent in the language the request was made in.
 */
class RequestDecided extends Notification
{
    public function __construct(public string $kind, public bool $approved, public Provider $provider, public ?string $note = null) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $outcome = $this->approved ? 'approved' : 'rejected';

        $message = (new MailMessage)
            ->subject(__('pro.mail.decided_subject_'.$outcome, ['name' => $this->provider->name]))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name]))
            ->line(__('pro.mail.'.$outcome.'_'.$this->kind, ['name' => $this->provider->name]));

        if (filled($this->note)) {
            $message->line(__('pro.mail.note', ['note' => $this->note]));
        }

        return $message
            ->action(__('pro.mail.action_space'), localized_route('pro.index'))
            ->salutation(__('mail.salutation'));
    }
}
