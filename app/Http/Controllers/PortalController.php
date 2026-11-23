<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $u = $request->user();

        return view('portal.dashboard', [
            'upcoming' => $u->bookings()->with(['service', 'business'])->whereIn('status', ['pending', 'confirmed', 'reschedule_requested'])->where('starts_at', '>=', now())->orderBy('starts_at')->limit(5)->get(),
            'recentBookings' => $u->bookings()->with('service')->latest()->limit(5)->get(),
            'orders' => $u->orders()->latest()->limit(5)->get(),
            'enquiries' => $u->enquiries()->latest()->limit(5)->get(),
            'notifications' => $u->notifications()->latest()->limit(8)->get(),
        ]);
    }

    public function bookings(Request $request) { return view('portal.bookings', ['bookings' => $request->user()->bookings()->with(['service', 'business'])->latest('starts_at')->paginate(10)]); }

    public function orders(Request $request) { return view('portal.orders', ['orders' => $request->user()->orders()->latest()->paginate(10)]); }

    public function order(Request $request, Order $order)
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $order);

        return view('portal.order-show', ['order' => $order->load(['items.business', 'payments'])]);
    }

    public function settings(Request $request) { return view('portal.settings', ['user' => $request->user()->load('profile')]); }

    public function updateSettings(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->fill(collect($data)->only(['first_name', 'last_name', 'phone'])->all());
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();
        $user->profile()->updateOrCreate([], collect($data)->only(['address', 'city', 'postal_code'])->all());

        return back()->with('status', 'Settings saved.');
    }

    public function markNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
