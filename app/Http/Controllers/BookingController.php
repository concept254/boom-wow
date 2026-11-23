<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BusinessUnit;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Notifications\BookingConfirmationNotification;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class BookingController extends Controller
{
    public function create(Request $request)
    {
        $businesses = BusinessUnit::published()->where('accepts_bookings', true)->with(['services' => fn ($q) => $q->published()->where('is_bookable', true)])->get();
        $service = $request->service ? Service::with(['business', 'staff'])->published()->find($request->service) : null;

        return view('portal.booking-new', ['businesses' => $businesses, 'service' => $service]);
    }

    /** JSON: available slots for a service/date (and optional staff member). */
    public function slots(Request $request, BookingService $bookings)
    {
        $data = $request->validate(['service' => ['required', 'exists:services,id'], 'date' => ['required', 'date'], 'staff' => ['nullable', 'exists:staff_profiles,id']]);
        $service = Service::findOrFail($data['service']);
        $staff = isset($data['staff']) ? StaffProfile::find($data['staff']) : null;

        return response()->json(['slots' => $bookings->availableSlots($service, Carbon::parse($data['date']), $staff)]);
    }

    public function store(Request $request, BookingService $bookings)
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'staff_profile_id' => ['nullable', 'exists:staff_profiles,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $staff = $data['staff_profile_id'] ?? null ? StaffProfile::findOrFail($data['staff_profile_id']) : null;

        try {
            $booking = $bookings->book($request->user(), $service, Carbon::parse($data['date'].' '.$data['time']), $staff, $data['notes'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['time' => $e->getMessage()]);
        }

        $request->user()->notify(new BookingConfirmationNotification($booking->load('service')));

        return redirect()->route('portal.bookings.show', $booking)->with('status', "Booking {$booking->reference} submitted.");
    }

    public function show(Request $request, Booking $booking)
    {
        Gate::authorize('view', $booking);

        return view('portal.booking-show', ['booking' => $booking->load(['service', 'business', 'staff', 'histories'])]);
    }

    public function cancel(Request $request, Booking $booking, BookingService $bookings)
    {
        Gate::authorize('cancel', $booking);

        $bookings->changeStatus($booking, 'cancelled', $request->user(), 'Cancelled by customer');

        return back()->with('status', 'Booking cancelled.');
    }

    public function reschedule(Request $request, Booking $booking, BookingService $bookings)
    {
        Gate::authorize('reschedule', $booking);
        $data = $request->validate(['reschedule_note' => ['required', 'string', 'max:500']]);

        $booking->update($data);
        $bookings->changeStatus($booking, 'reschedule_requested', $request->user(), $data['reschedule_note']);

        return back()->with('status', 'Reschedule requested. We will contact you to confirm a new time.');
    }
}
