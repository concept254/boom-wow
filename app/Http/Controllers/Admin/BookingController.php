<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingConfirmationNotification;
use App\Services\ActivityLogger;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private function gate(Request $request, string $ability = 'view'): void
    {
        abort_unless($request->user()->hasRole('Super Admin') || $request->user()->can("bookings.$ability"), 403);
    }

    public function index(Request $request)
    {
        $this->gate($request);
        $bookings = Booking::with(['user', 'service', 'business', 'staff'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->date, fn ($q, $d) => $q->whereDate('starts_at', $d))
            ->when($request->q, fn ($q, $t) => $q->where('reference', 'like', '%'.addcslashes($t, '%_\\').'%'))
            ->orderByDesc('starts_at')->paginate(15)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function update(Request $request, int $booking, BookingService $service)
    {
        $this->gate($request, 'update');
        $booking = Booking::findOrFail($booking); // business scope applied -> 404 for other businesses
        $data = $request->validate(['status' => ['required', 'in:'.implode(',', Booking::STATUSES)], 'note' => ['nullable', 'string', 'max:255']]);

        $service->changeStatus($booking, $data['status'], $request->user(), $data['note'] ?? null);
        $booking->user->notify(new BookingConfirmationNotification($booking->load('service')));
        ActivityLogger::log('booking_status', "Booking {$booking->reference} -> {$data['status']}", $booking);

        return back()->with('status', 'Booking updated.');
    }
}
