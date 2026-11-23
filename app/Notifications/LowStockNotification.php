<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public \App\Models\Product $product, public int $quantity) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(($this->quantity === 0 ? 'Out of stock: ' : 'Low stock: ').$this->product->name)
            ->greeting('Hello '.($notifiable->first_name ?? ''))
            ->line($this->product->name.' (SKU '.$this->product->sku.') has '.$this->quantity.' unit(s) left.')
            ->action('Open your portal', url('/portal'))
            ->line('Thank you for choosing Boom & Wow Holdings.');
    }

    public function toArray(object $notifiable): array
    {
        return ['message' => ($this->quantity === 0 ? 'Out of stock: ' : 'Low stock: ').$this->product->name, 'product_id' => $this->product->id];
    }
}
