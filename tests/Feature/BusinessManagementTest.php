<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Product;
use App\Models\Service;
use Tests\TestCase;

class BusinessManagementTest extends TestCase
{
    private function payload(array $over = []): array
    {
        return array_merge(['name' => 'Fresh Florist', 'slug' => 'fresh-florist', 'type' => 'general', 'status' => 'published', 'accepts_bookings' => '0', 'sells_products' => '1', 'age_restricted' => '0'], $over);
    }

    public function test_super_admin_can_add_a_business_and_its_storefront_appears_without_new_code(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/r/business-units', $this->payload())->assertRedirect('/admin/r/business-units');

        $this->assertDatabaseHas('business_units', ['slug' => 'fresh-florist']);
        $this->get('/businesses/fresh-florist')->assertOk()->assertSee('Fresh Florist');
        $this->get('/businesses')->assertSee('Fresh Florist');
    }

    public function test_slug_must_be_unique(): void
    {
        BusinessUnit::factory()->create(['slug' => 'fresh-florist']);

        $this->actingAs($this->superAdmin())->post('/admin/r/business-units', $this->payload())->assertSessionHasErrors('slug');
    }

    public function test_business_managers_cannot_create_or_edit_business_units(): void
    {
        $biz = BusinessUnit::factory()->create();
        $manager = $this->manager($biz);

        $this->actingAs($manager)->post('/admin/r/business-units', $this->payload())->assertForbidden();
        $this->actingAs($manager)->put("/admin/r/business-units/{$biz->id}", $this->payload())->assertForbidden();
    }

    public function test_draft_and_archived_businesses_are_not_public(): void
    {
        BusinessUnit::factory()->create(['name' => 'Hidden Draft Co', 'slug' => 'hidden-draft', 'status' => 'draft']);

        $this->get('/businesses')->assertDontSee('Hidden Draft Co');
        $this->get('/businesses/hidden-draft')->assertNotFound();
    }

    public function test_storefront_lists_that_businesss_services_and_products_only(): void
    {
        $a = BusinessUnit::factory()->create(['slug' => 'alpha']);
        $b = BusinessUnit::factory()->create(['slug' => 'bravo']);
        Service::factory()->create(['business_unit_id' => $a->id, 'name' => 'Alpha Cut']);
        Service::factory()->create(['business_unit_id' => $b->id, 'name' => 'Bravo Trim']);
        Product::factory()->create(['business_unit_id' => $b->id, 'name' => 'Bravo Gel']);

        $this->get('/businesses/alpha')->assertOk()->assertSee('Alpha Cut')->assertDontSee('Bravo Trim')->assertDontSee('Bravo Gel');
    }
}
