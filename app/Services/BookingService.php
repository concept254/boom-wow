<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\BookingStatusHistory;
use App\Models\BusinessClosure;
use App\Models\BusinessHour;
use App\Models\BusinessUnit;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class BookingService
{
    public function slotMinutes(): int
    {
        return (int) config('boomwow.booking.slot_minutes', 30);
    }

    /** Resource that must be free: the chosen staff member, else the business itself. */
    public function resourceKey(Service $service, ?StaffProfile $staff): string
    {
        return $staff ? "staff:{$staff->id}" : "business:{$service->business_unit_id}:service:{$service->id}";
    }

    public function isDateOpen(BusinessUnit $business, CarbonInterface $date): bool
    {
        $closed = BusinessClosure::withoutGlobalScopes()
            ->where(fn ($q) => $q->whereNull('business_unit_id')->orWhere('business_unit_id', $business->id))
            ->whereDate('starts_on', '<=', $date)->whereDate('ends_on', '>=', $date)->exists();

        if ($closed) {
            return false;
        }

        $hours = BusinessHour::withoutGlobalScopes()->where('business_unit_id', $business->id)->where('day_of_week', $date->dayOfWeek)->first();

        return $hours && ! $hours->is_closed && $hours->opens_at && $hours->closes_at;
    }

    /** @return array<int,string> start times as "H:i" that are bookable on the given date. */
    public function availableSlots(Service $service, CarbonInterface $date, ?StaffProfile $staff = null): array
    {
        $business = $service->business()->withoutGlobalScopes()->first() ?? $service->business;
        $date = Carbon::parse($date)->startOfDay();

        if ($date->lt(today()) || $date->gt(today()->addDays((int) config('boomwow.booking.max_days_ahead', 60))) || ! $this->isDateOpen($business, $date)) {
            return [];
        }

        $hours = BusinessHour::withoutGlobalScopes()->where('business_unit_id', $business->id)->where('day_of_week', $date->dayOfWeek)->first();
        $opens = $date->copy()->setTimeFromTimeString($hours->opens_at);
        $closes = $date->copy()->setTimeFromTimeString($hours->closes_at);
        $duration = $service->duration_minutes;
        $step = $this->slotMinutes();
        $key = $this->resourceKey($service, $staff);

        $taken = BookingSlot::query()->where('resource_key', $key)
            ->whereBetween('slot_start', [$opens, $closes])->pluck('slot_start')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d H:i'))->flip();

        $slots = [];
        for ($start = $opens->copy(); $start->copy()->addMinutes($duration)->lte($closes); $start->addMinutes($step)) {
            $end = $start->copy()->addMinutes($duration);

            if ($start->isPast()) {
                continue;
            }
            if ($staff && ! $this->staffWorks($staff, $start, $end)) {
                continue;
            }
            if ($this->overlapsTaken($start, $end, $taken)) {
                continue;
            }
            $slots[] = $start->format('H:i');
        }

        return $slots;
    }

    private function overlapsTaken(Carbon $start, Carbon $end, $taken): bool
    {
        for ($t = $start->copy(); $t->lt($end); $t->addMinutes($this->slotMinutes())) {
            if ($taken->has($t->format('Y-m-d H:i'))) {
                return true;
            }
        }

        return false;
    }

    private function staffWorks(StaffProfile $staff, Carbon $start, Carbon $end): bool
    {
        if (! $staff->is_active || ! $staff->is_bookable) {
            return false;
        }

        $window = $staff->availabilities()->where('day_of_week', $start->dayOfWeek)
            ->where('starts_at', '<=', $start->format('H:i:s'))->where('ends_at', '>=', $end->format('H:i:s'))->exists();

        if (! $window) {
            return false;
        }

        return ! $staff->timeOff()->where('starts_at', '<', $end)->where('ends_at', '>', $start)->exists();
    }

    /**
     * Create a booking. Double-booking is prevented twice: an availability check
     * inside a transaction, and a UNIQUE(resource_key, slot_start) constraint that
     * rejects any race that slips past the check.
     */
    public function book(User $user, Service $service, CarbonInterface $startsAt, ?StaffProfile $staff = null, ?string $notes = null): Booking
    {
        if (! $service->is_bookable || $service->status !== 'published') {
            throw new RuntimeException('This service cannot be booked online.');
        }
        if ($service->requires_staff && ! $staff) {
            throw new RuntimeException('Please select a staff member.');
        }
        if ($staff && ($staff->business_unit_id !== $service->business_unit_id)) {
            throw new RuntimeException('That staff member does not belong to this business.');
        }

        $start = Carbon::parse($startsAt);
        $end = $start->copy()->addMinutes($service->duration_minutes);

        try {
            return DB::transaction(function () use ($user, $service, $start, $end, $staff, $notes) {
                if (! in_array($start->format('H:i'), $this->availableSlots($service, $start, $staff), true)) {
                    throw new RuntimeException('That time slot is no longer available.');
                }

                $booking = Booking::create([
                    'reference' => 'BK-'.strtoupper(Str::random(8)),
                    'business_unit_id' => $service->business_unit_id,
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'staff_profile_id' => $staff?->id,
                    'starts_at' => $start,
                    'ends_at' => $end,
                    'status' => 'pending',
                    'notes' => $notes,
                ]);

                $key = $this->resourceKey($service, $staff);
                for ($t = $start->copy(); $t->lt($end); $t->addMinutes($this->slotMinutes())) {
                    BookingSlot::create(['booking_id' => $booking->id, 'resource_key' => $key, 'slot_start' => $t->copy()]);
                }

                $this->log($booking, null, 'pending', $user, 'Booking created');

                return $booking;
            });
        } catch (QueryException $e) {
            // Unique constraint hit: someone else took the slot a moment earlier.
            throw new RuntimeException('That time slot was just taken. Please choose another.');
        }
    }

    public function changeStatus(Booking $booking, string $to, ?User $by = null, ?string $note = null): Booking
    {
        abort_unless(in_array($to, Booking::STATUSES, true), 422);

        return DB::transaction(function () use ($booking, $to, $by, $note) {
            $from = $booking->status;
            $booking->update(['status' => $to]);

            // Cancelled bookings release their slots so others can book.
            if ($to === 'cancelled') {
                $booking->slots()->delete();
            }

            $this->log($booking, $from, $to, $by, $note);

            return $booking;
        });
    }

    private function log(Booking $b, ?string $from, string $to, ?User $by, ?string $note): void
    {
        BookingStatusHistory::create(['booking_id' => $b->id, 'from_status' => $from, 'to_status' => $to, 'changed_by' => $by?->id, 'note' => $note]);
    }
}
