<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Forwards a contact form message to the Doggys mailbox (answer with "reply": it goes to the sender).
 */
class ContactMessageReceived extends Notification
{
    public function __construct(public ContactMessage $contactMessage) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('contact.mail.subject', ['subject' => $this->contactMessage->subject]))
            ->replyTo($this->contactMessage->email, $this->contactMessage->name)
            ->line(__('contact.mail.from', ['name' => $this->contactMessage->name, 'email' => $this->contactMessage->email]))
            ->line($this->contactMessage->message)
            ->action(__('contact.mail.action'), route('admin.contact.index'));
    }
}
