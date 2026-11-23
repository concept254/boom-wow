<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnquiryReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public \App\Models\Enquiry $enquiry) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New enquiry from '.$this->enquiry->name)
            ->greeting('Hello '.($notifiable->first_name ?? ''))
            ->line('Enquiry: '.\Illuminate\Support\Str::limit($this->enquiry->message, 200))
            ->action('Open your portal', url('/portal'))
            ->line('Thank you for choosing Boom & Wow Holdings.');
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => 'New enquiry from '.$this->enquiry->name, 'enquiry_id' => $this->enquiry->id];
    }
}
