<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public \App\Models\Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order '.$this->order->order_number.' update')
            ->greeting('Hello '.($notifiable->first_name ?? ''))
            ->line('Your order status is now: '.str_replace('_', ' ', $this->order->status).'.')
            ->action('Open your portal', url('/portal'))
            ->line('Thank you for choosing Boom & Wow Holdings.');
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => 'Order '.$this->order->order_number.' is '.$this->order->status, 'order_id' => $this->order->id];
    }
}
