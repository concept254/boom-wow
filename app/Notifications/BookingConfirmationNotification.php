<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public \App\Models\Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking '.$this->booking->reference.' received')
            ->greeting('Hello '.($notifiable->first_name ?? ''))
            ->line('Your booking for '.$this->booking->service->name.' on '.$this->booking->starts_at->format('D, j M Y \\a\\t H:i').' is '.$this->booking->status.'.')
            ->action('Open your portal', url('/portal'))
            ->line('Thank you for choosing Boom & Wow Holdings.');
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => 'Booking '.$this->booking->reference.' ('.$this->booking->status.')', 'booking_id' => $this->booking->id];
    }
}
