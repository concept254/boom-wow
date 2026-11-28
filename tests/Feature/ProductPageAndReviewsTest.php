<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ProductPageAndReviewsTest extends TestCase
{
    private function product(array $over = []): Product
    {
        $biz = BusinessUnit::factory()->create(['status' => 'published', 'sells_products' => true]);

        return Product::factory()->create($over + ['business_unit_id' => $biz->id, 'highlights' => ['Imported', 'Gift ready'], 'short_description' => 'A lovely thing']);
    }

    public function test_product_page_shows_buy_box_share_and_structured_data(): void
    {
        $p = $this->product(['promo_price' => 80]);

        $this->get(route('product', [$p->business, $p->slug]))->assertOk()
            ->assertSee('Add to cart')->assertSee('Buy now')->assertSee('Imported')->assertSee('Save 20%')
            ->assertSee('application/ld+json', false)->assertSee('Customer reviews');
    }

    public function test_product_without_photos_uses_local_placeholder(): void
    {
        $p = $this->product();
        $this->assertStringContainsString('placeholder.svg', $p->image_url);
        $this->assertStringNotContainsString('picsum', $p->image_url);
    }

    public function test_review_requires_login_and_starts_pending(): void
    {
        $p = $this->product();
        $payload = ['type' => 'product', 'id' => $p->id, 'rating' => 5, 'title' => 'Great', 'body' => 'Really lovely product, would buy again.'];

        $this->post(route('reviews.store'), $payload)->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())->post(route('reviews.store'), $payload)->assertRedirect();
        $this->assertSame('pending', Review::first()->status);
        $this->assertSame(0, Product::withRatings()->find($p->id)->rating_count ?? 0);

        Review::first()->update(['status' => 'published']);
        $this->assertEquals(1, Product::withRatings()->find($p->id)->rating_count);
    }

    public function test_one_review_per_user_is_updated_not_duplicated(): void
    {
        $p = $this->product();
        $u = User::factory()->create();
        $payload = ['type' => 'product', 'id' => $p->id, 'rating' => 4, 'title' => 'Good', 'body' => 'Pretty good overall, happy.'];
        $this->actingAs($u)->post(route('reviews.store'), $payload);
        $this->actingAs($u)->post(route('reviews.store'), ['rating' => 2] + $payload);

        $this->assertSame(1, Review::count());
        $this->assertSame(2, (int) Review::first()->rating);
    }

    public function test_invalid_rating_is_rejected(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create())->post(route('reviews.store'), ['type' => 'product', 'id' => $p->id, 'rating' => 9, 'body' => 'Short'])->assertSessionHasErrors(['rating', 'body']);
    }

    public function test_buy_now_goes_to_checkout(): void
    {
        $p = $this->product();
        $this->actingAs(User::factory()->create())->post(route('cart.add', $p), ['quantity' => 1, 'buy_now' => 1])->assertRedirect(route('checkout'));
    }

    public function test_service_page_renders(): void
    {
        $biz = BusinessUnit::factory()->create(['status' => 'published', 'accepts_bookings' => true]);
        $s = Service::factory()->create(['business_unit_id' => $biz->id, 'status' => 'published', 'highlights' => ['Includes wash']]);

        $this->get(route('service', $s))->assertOk()->assertSee('Book now')->assertSee('Includes wash');
        $this->get(route('services'))->assertOk()->assertSee($s->name);
    }

    public function test_blank_sort_order_is_saved_as_zero(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $biz = BusinessUnit::factory()->create();

        $this->actingAs($admin)->post(route('admin.resource.store', 'brands'), ['business_unit_id' => $biz->id, 'name' => 'Gordons', 'status' => 'published', 'sort_order' => ''])->assertSessionHasNoErrors();
        $this->assertSame(0, (int) \App\Models\Brand::where('name', 'Gordons')->value('sort_order'));
    }
}

