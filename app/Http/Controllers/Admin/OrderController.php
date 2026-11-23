<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderStatusNotification;
use App\Services\ActivityLogger;
use App\Services\OrderService;
use App\Services\Payments\PaymentGateway;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderController extends Controller
{
    /** Orders can contain several businesses; a non-super user sees an order only if it has an item from one of their businesses. */
    private function visible(Request $request)
    {
        $q = Order::query();
        $u = $request->user();
        if (! $u->hasRole('Super Admin')) {
            $q->whereIn('id', OrderItem::withoutGlobalScopes()->whereIn('business_unit_id', $u->businessIds())->select('order_id'));
        }

        return $q;
    }

    private function gate(Request $request, string $ability = 'view'): void
    {
        abort_unless($request->user()->hasRole('Super Admin') || $request->user()->can("orders.$ability"), 403);
    }

    public function index(Request $request)
    {
        $this->gate($request);
        $orders = $this->visible($request)->with('user')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('order_number', 'like', '%'.addcslashes($t, '%_\\').'%')->orWhere('customer_name', 'like', '%'.addcslashes($t, '%_\\').'%')))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Request $request, int $order)
    {
        $this->gate($request);
        $order = $this->visible($request)->with(['items.business', 'payments', 'user'])->findOrFail($order);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, int $order, OrderService $orders)
    {
        $this->gate($request, 'update');
        $order = $this->visible($request)->findOrFail($order);
        $status = $request->validate(['status' => ['required', 'in:'.implode(',', Order::STATUSES)]])['status'];

        try {
            $status === 'cancelled' ? $orders->cancel($order) : $order->update(['status' => $status]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        $order->user->notify(new OrderStatusNotification($order->fresh()));
        ActivityLogger::log('order_status', "Order {$order->order_number} -> {$status}", $order);

        return back()->with('status', 'Order updated.');
    }

    public function markPaid(Request $request, int $order, PaymentGateway $gateway)
    {
        $this->gate($request, 'update');
        $order = $this->visible($request)->with('payments')->findOrFail($order);
        $payment = $order->payments->first() ?? $gateway->initiate($order);
        $gateway->confirm($payment, 'manual-'.$request->user()->id);
        ActivityLogger::log('payment', "Order {$order->order_number} marked paid", $order);

        return back()->with('status', 'Payment recorded.');
    }
}
