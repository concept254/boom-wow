<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\OrderService;
use InvalidArgumentException;
use Tests\TestCase;

class OrderTest extends TestCase
{
    private OrderService $orders;
    private InventoryService $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orders = app(OrderService::class);
        $this->stock = app(InventoryService::class);
    }

    private function stocked(int $qty, array $attrs = []): Product
    {
        $p = Product::factory()->create($attrs);
        $this->stock->receive($p, $qty);

        return $p;
    }

    public function test_placing_an_order_deducts_stock_and_snapshots_prices(): void
    {
        $p = $this->stocked(10, ['price' => 100, 'name' => 'Test Gel']);
        $order = $this->orders->place($this->customer(), [$p->id => 3], ['fulfilment' => 'collection']);

        $this->assertSame(7, $p->fresh()->inventory->quantity);
        $this->assertSame('300.00', (string) $order->total);
        $item = $order->items()->first();
        $this->assertSame(3, $item->quantity);
        $this->assertSame('Test Gel', $item->product_name);
        $this->assertSame($p->business_unit_id, $item->business_unit_id);
        $this->assertMatchesRegularExpression('/^BW-\d{6}-[A-Z0-9]{5}$/', $order->order_number);
        $this->assertSame('pending', $order->status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $p->id, 'type' => 'sale', 'change' => -3]);
    }

    public function test_cannot_order_more_than_is_in_stock_and_nothing_is_saved(): void
    {
        $ok = $this->stocked(5);
        $short = $this->stocked(1);

        try {
            $this->orders->place($this->customer(), [$ok->id => 2, $short->id => 2], ['fulfilment' => 'collection']);
            $this->fail('Expected an insufficient stock error.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Insufficient stock', $e->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(5, $ok->fresh()->inventory->quantity); // whole transaction rolled back
        $this->assertSame(1, $short->fresh()->inventory->quantity);
    }

    public function test_cart_can_mix_businesses_and_each_item_keeps_its_origin(): void
    {
        $a = $this->stocked(5);
        $b = $this->stocked(5);
        $order = $this->orders->place($this->customer(), [$a->id => 1, $b->id => 2], ['fulfilment' => 'collection']);

        $this->assertEqualsCanonicalizing([$a->business_unit_id, $b->business_unit_id], $order->items->pluck('business_unit_id')->all());
    }

    public function test_active_promotion_price_is_used_and_discount_recorded(): void
    {
        $p = $this->stocked(5, ['price' => 100, 'promo_price' => 80, 'promo_ends_at' => now()->addDay()]);
        $order = $this->orders->place($this->customer(), [$p->id => 2], ['fulfilment' => 'collection']);

        $this->assertSame('160.00', (string) $order->total);
        $this->assertSame('40.00', (string) $order->discount_total);
    }

    public function test_expired_promotion_is_ignored(): void
    {
        $p = $this->stocked(5, ['price' => 100, 'promo_price' => 80, 'promo_ends_at' => now()->subDay()]);

        $this->assertSame('200.00', (string) $this->orders->place($this->customer(), [$p->id => 2], ['fulfilment' => 'collection'])->total);
    }

    public function test_age_restricted_products_require_age_confirmation(): void
    {
        $liquor = $this->stocked(5, ['is_age_restricted' => true]);

        try {
            $this->orders->place($this->customer(), [$liquor->id => 1], ['fulfilment' => 'collection']);
            $this->fail('Expected age confirmation error.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('18', $e->getMessage());
        }
        $this->assertSame(5, $liquor->fresh()->inventory->quantity);

        $order = $this->orders->place($this->customer(), [$liquor->id => 1], ['fulfilment' => 'collection', 'age_confirmed' => true]);
        $this->assertTrue($order->age_confirmed);
    }

    public function test_age_gate_can_be_switched_off_by_configuration(): void
    {
        config(['boomwow.liquor.age_gate' => false]);
        $liquor = $this->stocked(5, ['is_age_restricted' => true]);

        $this->assertNotNull($this->orders->place($this->customer(), [$liquor->id => 1], ['fulfilment' => 'collection']));
    }

    public function test_delivery_needs_an_address(): void
    {
        $liquor = $this->stocked(5, ['is_age_restricted' => true]);

        $this->expectException(InvalidArgumentException::class);
        $this->orders->place($this->customer(), [$liquor->id => 1], ['fulfilment' => 'delivery', 'age_confirmed' => true]);
    }

    public function test_cancelling_returns_stock_once(): void
    {
        $p = $this->stocked(10);
        $order = $this->orders->place($this->customer(), [$p->id => 4], ['fulfilment' => 'collection']);

        $this->orders->cancel($order);
        $this->assertSame(10, $p->fresh()->inventory->quantity);
        $this->assertSame('cancelled', $order->fresh()->status);

        $this->expectException(InvalidArgumentException::class);
        $this->orders->cancel($order->fresh());
    }

    public function test_empty_cart_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->orders->place($this->customer(), [], ['fulfilment' => 'collection']);
    }

    public function test_full_checkout_flow_over_http(): void
    {
        $p = $this->stocked(5, ['price' => 50]);
        $user = $this->customer();

        $this->actingAs($user)->post("/cart/add/{$p->id}", ['quantity' => 2])->assertRedirect();
        $this->actingAs($user)->get('/cart')->assertOk()->assertSee($p->name);
        $this->actingAs($user)->post('/checkout', ['fulfilment' => 'collection', 'phone' => '0825550101'])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(3, $p->fresh()->inventory->quantity);
        $this->assertSame(0, app(\App\Services\CartService::class)->count()); // cart emptied
    }

    public function test_checkout_requires_login(): void
    {
        $this->get('/checkout')->assertRedirect(route('login'));
    }

    public function test_customers_only_see_their_own_orders(): void
    {
        $p = $this->stocked(5);
        $mine = $this->customer();
        $other = $this->customer();
        $order = $this->orders->place($mine, [$p->id => 1], ['fulfilment' => 'collection']);

        $this->actingAs($mine)->get("/portal/orders/{$order->id}")->assertOk();
        $this->actingAs($other)->get("/portal/orders/{$order->id}")->assertForbidden();
    }

    public function test_business_manager_can_progress_an_order_and_record_payment(): void
    {
        $p = $this->stocked(5);
        $order = $this->orders->place($this->customer(), [$p->id => 1], ['fulfilment' => 'collection']);
        $m = $this->manager($p->business);

        $this->actingAs($m)->patch("/admin/orders/{$order->id}", ['status' => 'processing'])->assertRedirect();
        $this->actingAs($m)->post("/admin/orders/{$order->id}/paid")->assertRedirect();

        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }
}
