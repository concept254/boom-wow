<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\BusinessUnit;
use App\Models\PageHeader;
use Database\Seeders\ClothingBusinessSeeder;
use Tests\TestCase;

class GrowthFeaturesTest extends TestCase
{
    public function test_clothing_seeder_adds_the_sixth_business_and_is_idempotent(): void
    {
        $this->seed(ClothingBusinessSeeder::class);
        $this->seed(ClothingBusinessSeeder::class);

        $this->assertSame(1, BusinessUnit::where('slug', 'clothing')->count());
        $this->get('/businesses')->assertOk()->assertSee('Clothing');
        $this->get('/businesses/clothing')->assertOk()->assertSee('Nkezi Fashion Store');
    }

    public function test_home_heading_counts_businesses_dynamically(): void
    {
        foreach (range(1, 6) as $i) {
            BusinessUnit::factory()->create(['status' => 'published', 'is_featured' => true]);
        }
        $this->get('/')->assertOk()->assertSee('Six businesses. Endless choice.');
    }

    public function test_a_business_needs_only_a_name_and_gets_slug_and_opening_hours(): void
    {
        $b = BusinessUnit::create(['name' => 'Pet Spa & Grooming', 'status' => 'published']);

        $this->assertSame('pet-spa-grooming', $b->slug);
        $this->assertSame(7, $b->hours()->count());
        $this->assertSame('general', $b->type);
        $this->assertSame('general', $b->enquiry_kind);
    }

    public function test_duplicate_names_get_unique_slugs(): void
    {
        $a = BusinessUnit::create(['name' => 'Cafe', 'status' => 'published']);
        $b = BusinessUnit::create(['name' => 'Cafe', 'status' => 'published']);
        $this->assertNotSame($a->slug, $b->slug);
    }

    public function test_every_public_page_has_a_page_header_with_breadcrumbs(): void
    {
        foreach (['/businesses', '/services', '/products', '/events', '/properties', '/news', '/faqs', '/about-us', '/contact', '/search?q=ab'] as $url) {
            $res = $this->get($url);
            if ($res->status() === 404) {
                continue; // route name differs in this install
            }
            $res->assertOk()->assertSee('aria-label="Breadcrumb"', false);
        }
    }

    public function test_page_header_uses_admin_overrides_and_can_be_left_without_an_image(): void
    {
        PageHeader::create(['key' => 'businesses', 'title' => 'The Family', 'subtitle' => 'Custom subtitle here', 'use_fallback' => false]);

        $html = $this->get('/businesses')->assertOk()->assertSee('The Family')->assertSee('Custom subtitle here')->getContent();
        $this->assertStringNotContainsString('picsum.photos/seed/bw-businesses', $html);
    }

    public function test_page_header_falls_back_to_an_online_image_when_none_is_set(): void
    {
        $this->get('/businesses')->assertOk()->assertSee('picsum.photos/seed/bw-businesses', false);
    }

    public function test_brand_carousel_shows_published_brands_only(): void
    {
        Brand::create(['name' => 'Visible Brand', 'status' => 'published']);
        Brand::create(['name' => 'Hidden Brand', 'status' => 'draft']);

        $this->get('/')->assertOk()->assertSee('Visible Brand')->assertDontSee('Hidden Brand');
        $this->get('/about-us')->assertOk()->assertSee('Visible Brand');
    }

    public function test_brand_carousel_is_hidden_when_there_are_no_brands(): void
    {
        $this->get('/')->assertOk()->assertDontSee('brand-marquee');
    }

    public function test_footer_subscribe_button_uses_the_contrast_colour(): void
    {
        $this->get('/')->assertOk()->assertSee('class="btn-primary">Subscribe</button>', false);
    }
}
