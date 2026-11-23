<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminderNotification;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Send reminders for confirmed/pending bookings starting in the next 24 hours';

    public function handle(): int
    {
        $bookings = Booking::withoutGlobalScopes()->with(['user', 'service'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addDay()])
            ->get();

        foreach ($bookings as $booking) {
            $booking->user->notify(new BookingReminderNotification($booking));
            $booking->update(['reminder_sent_at' => now()]);
        }

        $this->info("Sent {$bookings->count()} reminder(s).");

        return self::SUCCESS;
    }
}
