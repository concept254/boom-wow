<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BusinessClosure;
use App\Models\BusinessUnit;
use App\Models\Service;
use App\Models\StaffAvailability;
use App\Models\StaffProfile;
use App\Services\BookingService;
use Carbon\Carbon;
use RuntimeException;
use Tests\TestCase;

class BookingTest extends TestCase
{
    private BusinessUnit $biz;
    private Service $service;
    private BookingService $bookings;
    private Carbon $day;

    protected function setUp(): void
    {
        parent::setUp();
        $this->biz = BusinessUnit::factory()->create(['accepts_bookings' => true]);
        $this->openEveryDay($this->biz);
        $this->service = Service::factory()->create(['business_unit_id' => $this->biz->id, 'duration_minutes' => 60, 'is_bookable' => true]);
        $this->bookings = app(BookingService::class);
        $this->day = now()->addDays(3)->startOfDay();
    }

    private function at(string $time): Carbon
    {
        return Carbon::parse($this->day->toDateString().' '.$time);
    }

    public function test_slots_respect_opening_hours_and_service_duration(): void
    {
        $slots = $this->bookings->availableSlots($this->service, $this->day);

        $this->assertSame('09:00', $slots[0]);
        $this->assertContains('16:00', $slots);       // 16:00-17:00 fits
        $this->assertNotContains('16:30', $slots);    // would finish after closing
        $this->assertNotContains('08:30', $slots);
    }

    public function test_booking_creates_a_reference_and_pending_status_with_history(): void
    {
        $booking = $this->bookings->book($this->customer(), $this->service, $this->at('10:00'));

        $this->assertMatchesRegularExpression('/^BK-[A-Z0-9]{8}$/', $booking->reference);
        $this->assertSame('pending', $booking->status);
        $this->assertSame('11:00', $booking->ends_at->format('H:i'));
        $this->assertDatabaseHas('booking_status_histories', ['booking_id' => $booking->id, 'to_status' => 'pending']);
    }

    public function test_the_same_slot_cannot_be_booked_twice(): void
    {
        $this->bookings->book($this->customer(), $this->service, $this->at('10:00'));

        $this->expectException(RuntimeException::class);
        $this->bookings->book($this->customer(), $this->service, $this->at('10:00'));
    }

    public function test_overlapping_bookings_are_blocked(): void
    {
        $this->bookings->book($this->customer(), $this->service, $this->at('10:00')); // 10:00-11:00

        foreach (['09:30', '10:30'] as $overlap) {
            try {
                $this->bookings->book($this->customer(), $this->service, $this->at($overlap));
                $this->fail("{$overlap} overlaps an existing booking");
            } catch (RuntimeException) {
                $this->assertTrue(true);
            }
        }
        $this->assertSame(1, Booking::count());
        $this->assertNotContains('10:30', $this->bookings->availableSlots($this->service, $this->day));
        $this->assertContains('11:00', $this->bookings->availableSlots($this->service, $this->day)); // back-to-back is fine
    }

    public function test_database_constraint_rejects_a_double_booking_even_if_the_availability_check_is_bypassed(): void
    {
        $first = $this->bookings->book($this->customer(), $this->service, $this->at('12:00'));

        $this->expectException(\Illuminate\Database\QueryException::class);
        \App\Models\BookingSlot::create(['booking_id' => $first->id, 'resource_key' => $this->bookings->resourceKey($this->service, null), 'slot_start' => $this->at('12:00')]);
    }

    public function test_cancelling_releases_the_slot(): void
    {
        $user = $this->customer();
        $booking = $this->bookings->book($user, $this->service, $this->at('10:00'));
        $this->bookings->changeStatus($booking, 'cancelled', $user, 'Changed my mind');

        $this->assertContains('10:00', $this->bookings->availableSlots($this->service, $this->day));
        $this->assertNotNull($this->bookings->book($this->customer(), $this->service, $this->at('10:00')));
    }

    public function test_closed_days_and_holidays_have_no_availability(): void
    {
        $this->biz->hours()->where('day_of_week', $this->day->dayOfWeek)->update(['is_closed' => true]);
        $this->assertSame([], $this->bookings->availableSlots($this->service, $this->day));

        $other = $this->day->copy()->addDay();
        $this->assertNotEmpty($this->bookings->availableSlots($this->service, $other));
        BusinessClosure::create(['business_unit_id' => $this->biz->id, 'starts_on' => $other, 'ends_on' => $other, 'reason' => 'Public holiday']);
        $this->assertSame([], $this->bookings->availableSlots($this->service, $other));
    }

    public function test_past_dates_and_dates_beyond_the_booking_window_are_unavailable(): void
    {
        $this->assertSame([], $this->bookings->availableSlots($this->service, now()->subDay()));
        $this->assertSame([], $this->bookings->availableSlots($this->service, now()->addDays(config('boomwow.booking.max_days_ahead') + 5)));
    }

    public function test_staff_availability_and_per_staff_double_booking(): void
    {
        $alice = StaffProfile::factory()->create(['business_unit_id' => $this->biz->id]);
        $bob = StaffProfile::factory()->create(['business_unit_id' => $this->biz->id]);
        foreach ([$alice, $bob] as $s) {
            StaffAvailability::create(['staff_profile_id' => $s->id, 'day_of_week' => $this->day->dayOfWeek, 'starts_at' => '10:00', 'ends_at' => '14:00']);
        }

        $slots = $this->bookings->availableSlots($this->service, $this->day, $alice);
        $this->assertSame('10:00', $slots[0]);
        $this->assertNotContains('09:00', $slots);
        $this->assertNotContains('14:00', $slots);

        $this->bookings->book($this->customer(), $this->service, $this->at('11:00'), $alice);
        $this->assertNotContains('11:00', $this->bookings->availableSlots($this->service, $this->day, $alice));
        $this->assertContains('11:00', $this->bookings->availableSlots($this->service, $this->day, $bob)); // Bob is still free
        $this->assertNotNull($this->bookings->book($this->customer(), $this->service, $this->at('11:00'), $bob));
    }

    public function test_staff_from_another_business_cannot_be_booked(): void
    {
        $foreign = StaffProfile::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->bookings->book($this->customer(), $this->service, $this->at('10:00'), $foreign);
    }

    public function test_non_bookable_or_unpublished_services_are_refused(): void
    {
        $this->service->update(['is_bookable' => false]);

        $this->expectException(RuntimeException::class);
        $this->bookings->book($this->customer(), $this->service, $this->at('10:00'));
    }

    public function test_customer_http_flow_book_view_and_cancel(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->get('/portal/bookings/new')->assertOk();
        $this->actingAs($user)->getJson("/portal/bookings/slots?service={$this->service->id}&date={$this->day->toDateString()}")->assertOk()->assertJsonStructure(['slots']);
        $this->actingAs($user)->post('/portal/bookings', ['service_id' => $this->service->id, 'date' => $this->day->toDateString(), 'time' => '10:00'])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->actingAs($user)->get("/portal/bookings/{$booking->id}")->assertOk()->assertSee($booking->reference);
        $this->actingAs($user)->post("/portal/bookings/{$booking->id}/reschedule", ['reschedule_note' => 'Could we do Friday?'])->assertRedirect();
        $this->assertSame('reschedule_requested', $booking->fresh()->status);
        $this->actingAs($user)->post("/portal/bookings/{$booking->id}/cancel")->assertRedirect();
        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_http_double_booking_is_rejected_with_an_error(): void
    {
        $a = $this->customer();
        $b = $this->customer();
        $payload = ['service_id' => $this->service->id, 'date' => $this->day->toDateString(), 'time' => '10:00'];

        $this->actingAs($a)->post('/portal/bookings', $payload)->assertRedirect();
        $this->actingAs($b)->from('/portal/bookings/new')->post('/portal/bookings', $payload)->assertSessionHasErrors('time');
        $this->assertSame(1, Booking::count());
    }

    public function test_customers_cannot_see_or_cancel_other_customers_bookings(): void
    {
        $owner = $this->customer();
        $booking = $this->bookings->book($owner, $this->service, $this->at('10:00'));
        $stranger = $this->customer();

        $this->actingAs($stranger)->get("/portal/bookings/{$booking->id}")->assertForbidden();
        $this->actingAs($stranger)->post("/portal/bookings/{$booking->id}/cancel")->assertForbidden();
        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_admin_manages_bookings_within_their_business_only(): void
    {
        $booking = $this->bookings->book($this->customer(), $this->service, $this->at('10:00'));
        $mine = $this->manager($this->biz);
        $outsider = $this->manager(BusinessUnit::factory()->create());

        $this->actingAs($outsider)->patch("/admin/bookings/{$booking->id}", ['status' => 'confirmed'])->assertNotFound();
        $this->actingAs($mine)->patch("/admin/bookings/{$booking->id}", ['status' => 'confirmed'])->assertRedirect();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->actingAs($mine)->get('/admin/bookings')->assertOk()->assertSee($booking->reference);
        $this->actingAs($outsider)->get('/admin/bookings')->assertDontSee($booking->reference);
    }

    public function test_concurrent_style_race_second_insert_fails_cleanly(): void
    {
        // Two different customers submit the same slot back-to-back: exactly one wins.
        $results = [];
        foreach ([$this->customer(), $this->customer()] as $u) {
            try {
                $this->bookings->book($u, $this->service, $this->at('15:00'));
                $results[] = 'ok';
            } catch (RuntimeException) {
                $results[] = 'taken';
            }
        }

        $this->assertSame(['ok', 'taken'], $results);
    }
}
