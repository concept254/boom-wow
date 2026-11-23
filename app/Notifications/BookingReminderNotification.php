<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification implements ShouldQueue
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
            ->subject('Reminder: your appointment tomorrow')
            ->greeting('Hello '.($notifiable->first_name ?? ''))
            ->line('Reminder: '.$this->booking->service->name.' on '.$this->booking->starts_at->format('D, j M Y \\a\\t H:i').'. Reference '.$this->booking->reference.'.')
            ->action('Open your portal', url('/portal'))
            ->line('Thank you for choosing Boom & Wow Holdings.');
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => 'Reminder for booking '.$this->booking->reference, 'booking_id' => $this->booking->id];
    }
}
