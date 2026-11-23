<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

/** Customers act only on their own bookings; back-office access is handled by permissions in admin controllers. */
class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking) && in_array($booking->status, ['pending', 'confirmed', 'reschedule_requested'], true);
    }

    public function reschedule(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking) && in_array($booking->status, ['pending', 'confirmed'], true);
    }
}
