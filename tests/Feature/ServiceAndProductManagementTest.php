<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Product;
use App\Models\Service;
use Tests\TestCase;

class ServiceAndProductManagementTest extends TestCase
{
    private BusinessUnit $biz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->biz = BusinessUnit::factory()->create();
    }

    private function service(array $over = []): array
    {
        return array_merge(['business_unit_id' => $this->biz->id, 'name' => 'Deep Conditioning', 'slug' => 'deep-conditioning', 'price' => '350.00', 'duration_minutes' => 45, 'is_bookable' => '1', 'status' => 'published'], $over);
    }

    private function product(array $over = []): array
    {
        return array_merge(['business_unit_id' => $this->biz->id, 'name' => 'Argan Oil', 'slug' => 'argan-oil', 'sku' => 'ARG-001', 'cost_price' => '60', 'price' => '120', 'unit' => 'each', 'track_stock' => '1', 'status' => 'published'], $over);
    }

    public function test_manager_can_create_update_and_delete_a_service(): void
    {
        $m = $this->manager($this->biz);

        $this->actingAs($m)->post('/admin/r/services', $this->service())->assertRedirect('/admin/r/services');
        $service = Service::where('slug', 'deep-conditioning')->firstOrFail();
        $this->assertSame($this->biz->id, $service->business_unit_id);

        $this->actingAs($m)->put("/admin/r/services/{$service->id}", $this->service(['price' => '399.00']))->assertRedirect();
        $this->assertSame('399.00', (string) $service->fresh()->price);

        $this->actingAs($m)->delete("/admin/r/services/{$service->id}")->assertRedirect();
        $this->assertSoftDeleted($service);
    }

    public function test_service_validation(): void
    {
        $m = $this->manager($this->biz);

        $this->actingAs($m)->post('/admin/r/services', $this->service(['name' => '', 'price' => 'abc', 'duration_minutes' => 1]))->assertSessionHasErrors(['name', 'price', 'duration_minutes']);
        $this->assertDatabaseCount('services', 0);
    }

    public function test_manager_can_create_a_product_and_duplicate_sku_is_rejected(): void
    {
        $m = $this->manager($this->biz);

        $this->actingAs($m)->post('/admin/r/products', $this->product())->assertRedirect('/admin/r/products');
        $this->assertDatabaseHas('products', ['sku' => 'ARG-001', 'business_unit_id' => $this->biz->id]);

        $this->actingAs($m)->post('/admin/r/products', $this->product(['slug' => 'other']))->assertSessionHasErrors('sku');
    }

    public function test_product_edit_form_and_list_render(): void
    {
        $p = Product::factory()->create(['business_unit_id' => $this->biz->id]);
        $m = $this->manager($this->biz);

        $this->actingAs($m)->get('/admin/r/products')->assertOk()->assertSee($p->name);
        $this->actingAs($m)->get("/admin/r/products/{$p->id}/edit")->assertOk()->assertSee($p->sku);
    }

    public function test_search_filter_and_sort_work_on_lists(): void
    {
        Product::factory()->create(['business_unit_id' => $this->biz->id, 'name' => 'Needle Product']);
        Product::factory()->create(['business_unit_id' => $this->biz->id, 'name' => 'Haystack Product']);

        $this->actingAs($this->manager($this->biz))->get('/admin/r/products?q=Needle&sort=name&dir=asc')->assertOk()->assertSee('Needle Product')->assertDontSee('Haystack Product');
    }

    public function test_image_upload_is_validated(): void
    {
        $m = $this->manager($this->biz);
        $bad = \Illuminate\Http\UploadedFile::fake()->create('malware.php', 10, 'application/x-php');

        $this->actingAs($m)->post('/admin/r/services', $this->service(['image_path' => $bad]))->assertSessionHasErrors('image_path');
    }
}
