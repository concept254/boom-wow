<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessUnit;
use App\Models\Enquiry;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $super = $user->hasRole('Super Admin');
        $ids = $super ? null : $user->businessIds();
        $scope = fn ($q, $col = 'business_unit_id') => $ids === null ? $q : $q->whereIn($col, $ids);

        $stock = $scope(Inventory::query()->join('products', 'products.id', '=', 'inventory.product_id'), 'inventory.business_unit_id');

        // Orders span businesses, so order stats come from order_items for non-super users.
        $orderIds = $scope(OrderItem::query())->distinct()->pluck('order_id');
        $orders = Order::query()->whereIn('id', $orderIds);

        $stats = [
            'Businesses' => $scope(BusinessUnit::query(), 'id')->count(),
            'Customers' => $super ? User::role('Customer')->count() : User::role('Customer')->whereHas('bookings', fn ($q) => $q->whereIn('business_unit_id', $ids))->count(),
            'Services' => $scope(Service::query())->count(),
            'Products' => $scope(Product::query())->count(),
            'Stock value (R)' => number_format((float) (clone $stock)->sum(DB::raw('inventory.quantity * products.cost_price')), 2),
            'Low stock' => $scope(Inventory::query())->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0)->count(),
            'Out of stock' => $scope(Inventory::query())->where('quantity', 0)->count(),
            'Pending bookings' => $scope(Booking::query())->where('status', 'pending')->count(),
            'Upcoming appointments' => $scope(Booking::query())->whereIn('status', ['pending', 'confirmed'])->where('starts_at', '>=', now())->count(),
            'Total orders' => (clone $orders)->count(),
            'Pending orders' => (clone $orders)->where('status', 'pending')->count(),
            'Completed orders' => (clone $orders)->where('status', 'completed')->count(),
            'New enquiries' => $scope(Enquiry::query())->where('status', 'new')->count(),
        ];

        $sales = $scope(OrderItem::query())->join('business_units', 'business_units.id', '=', 'order_items.business_unit_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')->where('orders.status', '!=', 'cancelled')
            ->selectRaw('business_units.name as name, SUM(order_items.subtotal) as total, COUNT(DISTINCT orders.id) as orders')
            ->groupBy('business_units.name')->orderByDesc('total')->get();

        $bookingsByBusiness = $scope(Booking::query())->join('business_units', 'business_units.id', '=', 'bookings.business_unit_id')
            ->selectRaw('business_units.name as name, COUNT(*) as total')->groupBy('business_units.name')->get();

        return view('admin.dashboard', compact('stats', 'sales', 'bookingsByBusiness'));
    }
}
