<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Services\Payments\PaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function __construct(private InventoryService $inventory, private PaymentGateway $payments) {}

    /**
     * @param  array<int,int>  $lines  product_id => quantity
     * @param  array{fulfilment:string,phone?:?string,delivery_address?:?string,notes?:?string,age_confirmed?:bool}  $details
     */
    public function place(User $user, array $lines, array $details): Order
    {
        if (empty($lines)) {
            throw new InvalidArgumentException('Your cart is empty.');
        }

        $order = DB::transaction(function () use ($user, $lines, $details) {
            // Lock products in a stable order to avoid deadlocks under concurrent checkouts.
            ksort($lines);
            $products = Product::withoutGlobalScopes()->with('business')->published()->whereIn('id', array_keys($lines))->orderBy('id')->get()->keyBy('id');

            if ($products->count() !== count($lines)) {
                throw new InvalidArgumentException('One or more products are no longer available.');
            }

            $needsAge = $products->contains(fn ($p) => $p->is_age_restricted || $p->business->age_restricted);
            if ($needsAge && config('boomwow.liquor.age_gate') && empty($details['age_confirmed'])) {
                throw new InvalidArgumentException('You must confirm you are '.config('boomwow.liquor.min_age').' or older to buy age-restricted products.');
            }
            if ($needsAge && ($details['fulfilment'] ?? 'collection') === 'delivery' && empty($details['delivery_address'])) {
                throw new InvalidArgumentException('A delivery address is required.');
            }

            $order = Order::create([
                'order_number' => 'BW-'.now()->format('ymd').'-'.strtoupper(Str::random(5)),
                'user_id' => $user->id,
                'fulfilment' => $details['fulfilment'] ?? 'collection',
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $details['phone'] ?? $user->phone,
                'delivery_address' => $details['delivery_address'] ?? null,
                'notes' => $details['notes'] ?? null,
                'age_confirmed' => (bool) ($details['age_confirmed'] ?? false),
            ]);

            $subtotal = 0.0;
            $discount = 0.0;

            foreach ($lines as $productId => $qty) {
                $product = $products[$productId];
                $qty = max(1, (int) $qty);
                $list = (float) $product->price;
                $unit = (float) $product->current_price;
                $lineDiscount = round(($list - $unit) * $qty, 2);
                $lineTotal = round($unit * $qty, 2);

                if ($product->track_stock) {
                    // Throws (and rolls the whole order back) if stock would go negative.
                    $this->inventory->move($product, -$qty, 'sale', "Order {$order->order_number}", $order->user, $order);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'business_unit_id' => $product->business_unit_id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'discount' => $lineDiscount,
                    'subtotal' => $lineTotal,
                ]);

                $subtotal += $list * $qty;
                $discount += $lineDiscount;
            }

            $order->update([
                'subtotal' => round($subtotal, 2),
                'discount_total' => round($discount, 2),
                'total' => round($subtotal - $discount, 2),
            ]);

            $this->payments->initiate($order);

            return $order;
        });

        $user->notify(new OrderPlacedNotification($order));

        return $order;
    }

    public function cancel(Order $order): Order
    {
        if (in_array($order->status, ['completed', 'cancelled'], true)) {
            throw new InvalidArgumentException('This order can no longer be cancelled.');
        }

        return DB::transaction(function () use ($order) {
            foreach ($order->items()->with('product')->get() as $item) {
                if ($item->product && $item->product->track_stock) {
                    $this->inventory->move($item->product, $item->quantity, 'return', "Order {$order->order_number} cancelled");
                }
            }
            $order->update(['status' => 'cancelled']);

            return $order;
        });
    }
}
