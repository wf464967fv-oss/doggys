<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Statement of reasons (DSA art. 17) sent to the author of a review hidden by moderation.
 */
class ReviewHidden extends Notification
{
    public function __construct(public Review $review, public ?string $reason = null) {}

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
            ->subject(__('reviews.mail.hidden_subject'))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name]))
            ->line(__('reviews.mail.hidden_intro', ['name' => $this->review->provider?->name]))
            ->line(__('reviews.mail.hidden_reason', ['reason' => filled($this->reason) ? $this->reason : __('reviews.mail.hidden_default_reason')]))
            ->line(__('reviews.mail.hidden_redress'))
            ->action(__('reviews.mail.hidden_action'), localized_route('contact'))
            ->salutation(__('mail.salutation'));
    }
}
