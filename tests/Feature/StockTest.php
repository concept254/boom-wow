<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Product;
use App\Models\StockMovement;
use App\Notifications\LowStockNotification;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Tests\TestCase;

class StockTest extends TestCase
{
    private Product $product;
    private InventoryService $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->product = Product::factory()->create();
        $this->stock = app(InventoryService::class);
    }

    public function test_receiving_stock_records_previous_change_new_reason_user_and_date(): void
    {
        $user = $this->manager($this->product->business);
        $this->stock->receive($this->product, 20, 'Supplier delivery', $user);
        $this->stock->receive($this->product, 5, 'Top-up', $user);

        $m = StockMovement::latest('id')->first();
        $this->assertSame(20, $m->previous_quantity);
        $this->assertSame(5, $m->change);
        $this->assertSame(25, $m->new_quantity);
        $this->assertSame('Top-up', $m->reason);
        $this->assertSame($user->id, $m->user_id);
        $this->assertNotNull($m->created_at);
        $this->assertSame(25, $this->product->fresh()->inventory->quantity);
    }

    public function test_stock_can_never_go_negative(): void
    {
        $this->stock->receive($this->product, 3);

        $this->expectException(InvalidArgumentException::class);
        try {
            $this->stock->deduct($this->product, 4, 'Damaged');
        } finally {
            $this->assertSame(3, $this->product->fresh()->inventory->quantity);
            $this->assertDatabaseCount('stock_movements', 1); // the failed deduction left no trace
        }
    }

    public function test_adjust_to_sets_an_exact_quantity(): void
    {
        $this->stock->receive($this->product, 10);
        $this->stock->adjustTo($this->product->fresh(), 4, 'Stock take');

        $this->assertSame(4, $this->product->fresh()->inventory->quantity);
        $this->assertSame(-6, StockMovement::latest('id')->first()->change);
    }

    public function test_low_stock_alert_is_sent_once_when_crossing_the_reorder_level(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $this->stock->receive($this->product, 10);
        $this->product->inventory->update(['reorder_level' => 5]);

        $this->stock->deduct($this->product->fresh(), 5, 'Sold'); // 10 -> 5 (<= reorder)
        $this->stock->deduct($this->product->fresh(), 1, 'Sold'); // already low, no second alert

        Notification::assertSentToTimes($admin, LowStockNotification::class, 1);
    }

    public function test_admin_endpoint_adjusts_stock_and_rejects_negative_results(): void
    {
        $m = $this->manager($this->product->business);

        $this->actingAs($m)->post("/admin/inventory/{$this->product->id}/adjust", ['action' => 'receive', 'quantity' => 8, 'reason' => 'Delivery'])->assertRedirect();
        $this->assertSame(8, $this->product->fresh()->inventory->quantity);

        $this->actingAs($m)->post("/admin/inventory/{$this->product->id}/adjust", ['action' => 'deduct', 'quantity' => 9, 'reason' => 'Oops'])->assertSessionHasErrors('quantity');
        $this->assertSame(8, $this->product->fresh()->inventory->quantity);
    }

    public function test_reason_is_mandatory(): void
    {
        $this->actingAs($this->manager($this->product->business))->post("/admin/inventory/{$this->product->id}/adjust", ['action' => 'receive', 'quantity' => 1])->assertSessionHasErrors('reason');
    }
}
