<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BusinessImage;
use App\Models\BusinessUnit;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class BusinessPortalTest extends TestCase
{
    private function business(array $over = []): BusinessUnit
    {
        return BusinessUnit::factory()->create($over + ['status' => 'published', 'about_body' => "First paragraph of our story.\n\nSecond paragraph.", 'highlights' => ['Friendly staff']]);
    }

    public function test_business_page_shows_story_highlights_gallery_promo_and_articles(): void
    {
        $b = $this->business();
        BusinessImage::create(['business_unit_id' => $b->id, 'path' => 'businesses/gallery/a.jpg', 'alt' => 'Front of store', 'sort_order' => 1]);
        Promotion::create(['business_unit_id' => $b->id, 'title' => 'Winter special', 'placement' => 'business_page', 'status' => 'published', 'theme' => 'orange']);
        BlogPost::create(['business_unit_id' => $b->id, 'title' => 'Our latest drop', 'body' => 'Body text', 'status' => 'published']);

        $this->get(route('business', $b))->assertOk()
            ->assertSee('First paragraph of our story.')->assertSee('Friendly staff')
            ->assertSee('Take a look inside')->assertSee('Front of store')
            ->assertSee('Winter special')->assertSee('Our latest drop');
    }

    public function test_sections_can_be_switched_off(): void
    {
        $b = $this->business(['show_about' => false, 'show_gallery' => false, 'show_promos' => false, 'show_articles' => false]);
        BusinessImage::create(['business_unit_id' => $b->id, 'path' => 'businesses/gallery/a.jpg', 'alt' => 'Front of store', 'sort_order' => 1]);
        Promotion::create(['business_unit_id' => $b->id, 'title' => 'Winter special', 'placement' => 'business_page', 'status' => 'published', 'theme' => 'orange']);
        BlogPost::create(['business_unit_id' => $b->id, 'title' => 'Our latest drop', 'body' => 'x', 'status' => 'published']);

        $this->get(route('business', $b))->assertOk()
            ->assertDontSee('First paragraph of our story.')->assertDontSee('Take a look inside')
            ->assertDontSee('Winter special')->assertDontSee('Our latest drop');
    }

    public function test_promotions_only_appear_on_their_own_business(): void
    {
        $a = $this->business();
        $other = $this->business();
        Promotion::create(['business_unit_id' => $a->id, 'title' => 'Only for A', 'placement' => 'business_page', 'status' => 'published', 'theme' => 'orange']);

        $this->get(route('business', $other))->assertOk()->assertDontSee('Only for A');
    }

    public function test_news_can_be_filtered_by_business_and_posts_render(): void
    {
        $a = $this->business(['slug' => 'biz-a']);
        $b = $this->business(['slug' => 'biz-b']);
        BlogPost::create(['business_unit_id' => $a->id, 'title' => 'Post for A', 'body' => 'Hello', 'status' => 'published']);
        $pb = BlogPost::create(['business_unit_id' => $b->id, 'title' => 'Post for B', 'body' => "Para one\n\nPara two", 'status' => 'published']);

        $this->get(route('blog', ['business' => 'biz-a']))->assertOk()->assertSee('Post for A')->assertDontSee('Post for B');
        $this->get(route('blog.post', $pb->slug))->assertOk()->assertSee('Para two')->assertSee('application/ld+json', false);
        $this->assertNotNull($pb->fresh()->published_at);
    }

    public function test_price_on_request_product_hides_price_and_cannot_be_carted(): void
    {
        $biz = $this->business(['sells_products' => true]);
        $p = Product::factory()->create(['business_unit_id' => $biz->id, 'price' => 999, 'price_on_request' => true]);

        $this->get(route('product', [$biz, $p->slug]))->assertOk()
            ->assertSee('Price on request')->assertSee('Request a quote')->assertDontSee('Add to cart')->assertDontSee('999.00');

        $this->post(route('cart.add', $p), ['quantity' => 1])->assertRedirect($p->quote_url);
        $this->assertSame(0, app(\App\Services\CartService::class)->count());
    }

    public function test_price_on_request_service_shows_quote_button(): void
    {
        $biz = $this->business(['accepts_bookings' => true]);
        $s = Service::factory()->create(['business_unit_id' => $biz->id, 'status' => 'published', 'price_on_request' => true]);

        $this->get(route('service', $s))->assertOk()->assertSee('Price on request')->assertSee('Request a quote');
    }

    public function test_product_can_be_saved_without_a_price_when_on_request(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('Super Admin');
        $biz = $this->business(['sells_products' => true]);

        $this->actingAs($admin)->post(route('admin.resource.store', 'products'), [
            'business_unit_id' => $biz->id, 'name' => 'Exclusive Piece', 'sku' => 'EXC-1', 'unit' => 'each', 'status' => 'published', 'price_on_request' => 1, 'price' => '',
        ])->assertSessionHasNoErrors();
        $this->assertSame(0.0, (float) Product::where('sku', 'EXC-1')->value('price'));

        $this->actingAs($admin)->post(route('admin.resource.store', 'products'), [
            'business_unit_id' => $biz->id, 'name' => 'Normal', 'sku' => 'N-1', 'unit' => 'each', 'status' => 'published', 'price' => '',
        ])->assertSessionHasErrors('price');
    }
}
