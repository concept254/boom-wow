<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Services\InventoryService;
use App\Services\OrderService;
use Tests\TestCase;

class BusinessIsolationTest extends TestCase
{
    private BusinessUnit $a;
    private BusinessUnit $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->a = BusinessUnit::factory()->create(['name' => 'Alpha Salon']);
        $this->b = BusinessUnit::factory()->create(['name' => 'Bravo Liquor']);
    }

    public function test_manager_only_sees_their_own_business_products(): void
    {
        Product::factory()->create(['business_unit_id' => $this->a->id, 'name' => 'Alpha Shampoo']);
        Product::factory()->create(['business_unit_id' => $this->b->id, 'name' => 'Bravo Whisky']);

        $this->actingAs($this->manager($this->a))->get('/admin/r/products')->assertOk()->assertSee('Alpha Shampoo')->assertDontSee('Bravo Whisky');
    }

    public function test_manager_cannot_edit_or_delete_another_businesses_record(): void
    {
        $theirs = Product::factory()->create(['business_unit_id' => $this->b->id]);
        $manager = $this->manager($this->a);

        $this->actingAs($manager)->get("/admin/r/products/{$theirs->id}/edit")->assertNotFound();
        $this->actingAs($manager)->put("/admin/r/products/{$theirs->id}", ['name' => 'hacked'])->assertNotFound();
        $this->actingAs($manager)->delete("/admin/r/products/{$theirs->id}")->assertNotFound();
        $this->assertNotNull(Product::withoutGlobalScopes()->find($theirs->id));
    }

    public function test_manager_cannot_create_records_in_another_business_by_tampering_with_the_form(): void
    {
        $payload = ['business_unit_id' => $this->b->id, 'name' => 'Sneaky', 'slug' => 'sneaky', 'sku' => 'SNK-1', 'cost_price' => 1, 'price' => 2, 'unit' => 'each', 'status' => 'published'];

        $this->actingAs($this->manager($this->a))->post('/admin/r/products', $payload)->assertForbidden();
        $this->assertDatabaseMissing('products', ['sku' => 'SNK-1']);
    }

    public function test_manager_cannot_adjust_stock_for_another_business(): void
    {
        $product = Product::factory()->create(['business_unit_id' => $this->b->id]);

        $this->actingAs($this->manager($this->a))->post("/admin/inventory/{$product->id}/adjust", ['action' => 'receive', 'quantity' => 5, 'reason' => 'x'])->assertNotFound();
        $this->assertDatabaseMissing('inventory', ['product_id' => $product->id]);
    }

    public function test_inventory_and_stock_movement_lists_are_scoped(): void
    {
        $pa = Product::factory()->create(['business_unit_id' => $this->a->id, 'name' => 'Alpha Item']);
        $pb = Product::factory()->create(['business_unit_id' => $this->b->id, 'name' => 'Bravo Item']);
        app(InventoryService::class)->receive($pa, 5);
        app(InventoryService::class)->receive($pb, 5);

        $this->actingAs($this->manager($this->a))->get('/admin/inventory')->assertSee('Alpha Item')->assertDontSee('Bravo Item');
        $this->actingAs($this->manager($this->a))->get('/admin/stock-movements')->assertSee('Alpha Item')->assertDontSee('Bravo Item');
    }

    public function test_manager_only_sees_orders_that_contain_their_business(): void
    {
        $pa = Product::factory()->create(['business_unit_id' => $this->a->id]);
        $pb = Product::factory()->create(['business_unit_id' => $this->b->id]);
        $inv = app(InventoryService::class);
        $inv->receive($pa, 10);
        $inv->receive($pb, 10);
        $svc = app(OrderService::class);
        $mixed = $svc->place($this->customer(), [$pa->id => 1, $pb->id => 1], ['fulfilment' => 'collection', 'age_confirmed' => true]);
        $onlyB = $svc->place($this->customer(), [$pb->id => 1], ['fulfilment' => 'collection', 'age_confirmed' => true]);
        $manager = $this->manager($this->a);

        $this->actingAs($manager)->get("/admin/orders/{$mixed->id}")->assertOk()->assertDontSee($pb->name);
        $this->actingAs($manager)->get("/admin/orders/{$onlyB->id}")->assertNotFound();
        $this->actingAs($manager)->patch("/admin/orders/{$onlyB->id}", ['status' => 'completed'])->assertNotFound();
    }

    public function test_enquiries_and_services_are_isolated(): void
    {
        Enquiry::create(['business_unit_id' => $this->a->id, 'type' => 'general', 'name' => 'Visible Vera', 'email' => 'v@example.com', 'message' => 'hi']);
        Enquiry::create(['business_unit_id' => $this->b->id, 'type' => 'general', 'name' => 'Hidden Hank', 'email' => 'h@example.com', 'message' => 'hi']);
        Service::factory()->create(['business_unit_id' => $this->b->id, 'name' => 'Bravo Only Service']);
        $manager = $this->manager($this->a);

        $this->actingAs($manager)->get('/admin/r/enquiries')->assertSee('Visible Vera')->assertDontSee('Hidden Hank');
        $this->actingAs($manager)->get('/admin/r/services')->assertDontSee('Bravo Only Service');
    }

    public function test_super_admin_sees_every_business(): void
    {
        Product::factory()->create(['business_unit_id' => $this->a->id, 'name' => 'Alpha Shampoo']);
        Product::factory()->create(['business_unit_id' => $this->b->id, 'name' => 'Bravo Whisky']);

        $this->actingAs($this->superAdmin())->get('/admin/r/products')->assertSee('Alpha Shampoo')->assertSee('Bravo Whisky');
    }
}
