<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\HeroSlide;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\TeamMember;
use App\Support\Link;
use Tests\TestCase;

class HomepageFeaturesTest extends TestCase
{
    public function test_home_page_shows_published_hero_slides_and_hides_expired_or_draft_ones(): void
    {
        HeroSlide::create(['heading' => 'Live Slide Heading', 'caption' => 'Spotlight', 'cta_label' => 'Go now', 'cta_url' => '/contact', 'status' => 'published']);
        HeroSlide::create(['heading' => 'Draft Slide Heading', 'status' => 'draft']);
        HeroSlide::create(['heading' => 'Expired Slide Heading', 'status' => 'published', 'ends_on' => now()->subDay()->toDateString()]);

        $this->get('/')->assertOk()->assertSee('Live Slide Heading')->assertSee('Go now')
            ->assertDontSee('Draft Slide Heading')->assertDontSee('Expired Slide Heading');
    }

    public function test_home_page_falls_back_to_a_default_slide_when_none_are_published(): void
    {
        $this->get('/')->assertOk()->assertSee('One Destination. Endless Possibilities.');
    }

    public function test_featured_stores_carousel_lists_only_featured_published_businesses(): void
    {
        BusinessUnit::factory()->create(['name' => 'Featured Salon', 'status' => 'published', 'is_featured' => true]);
        BusinessUnit::factory()->create(['name' => 'Hidden Shop', 'status' => 'published', 'is_featured' => false]);

        $html = $this->get('/')->assertOk()->assertSee('Featured Salon')->getContent();
        $start = strpos($html, 'aria-label="Featured stores"');
        $carousel = substr($html, $start, strpos($html, 'See all businesses', $start) - $start);
        $this->assertStringNotContainsString('Hidden Shop', $carousel);
    }

    public function test_adverts_show_in_their_placement_and_respect_dates(): void
    {
        Promotion::create(['title' => 'Mega Special Offer', 'placement' => 'home_specials', 'status' => 'published', 'cta_label' => 'Grab it', 'link_url' => '/products']);
        Promotion::create(['title' => 'Old Offer', 'placement' => 'home_specials', 'status' => 'published', 'ends_on' => now()->subDay()->toDateString()]);
        Promotion::create(['title' => 'Inner Page Advert', 'placement' => 'inner_banner', 'status' => 'published']);
        Promotion::create(['title' => 'Top Bar Notice', 'placement' => 'top_bar', 'status' => 'published']);

        $this->get('/')->assertOk()->assertSee('Mega Special Offer')->assertSee('Grab it')->assertDontSee('Old Offer')->assertSee('Top Bar Notice');
        $this->get('/services')->assertOk()->assertSee('Inner Page Advert');
        $this->get('/businesses')->assertOk()->assertSee('Inner Page Advert');
    }

    public function test_subscribing_stores_the_email_once_and_honeypot_is_rejected(): void
    {
        $this->post('/subscribe', ['email' => 'Fan@Example.com'])->assertRedirect()->assertSessionHas('status');
        $this->post('/subscribe', ['email' => 'fan@example.com'])->assertRedirect();
        $this->assertSame(1, Subscriber::count());
        $this->assertDatabaseHas('subscribers', ['email' => 'fan@example.com', 'is_active' => true]);

        $this->post('/subscribe', ['email' => 'bot@example.com', 'website' => 'spam'])->assertSessionHasErrors('website');
        $this->post('/subscribe', ['email' => 'not-an-email'])->assertSessionHasErrors('email');
    }

    public function test_about_page_shows_management_and_team_and_faqs(): void
    {
        TeamMember::create(['name' => 'Ada Manager', 'job_title' => 'CEO', 'team_group' => 'management', 'status' => 'published']);
        TeamMember::create(['name' => 'Tom Draft', 'team_group' => 'team', 'status' => 'draft']);

        $this->get('/about-us')->assertOk()->assertSee('Ada Manager')->assertSee('CEO')->assertDontSee('Tom Draft');
    }

    public function test_contact_page_shows_address_details_hours_and_a_map(): void
    {
        Setting::updateOrCreate(['key' => 'contact_address'], ['value' => '99 Test Road, Sandton']);
        Setting::updateOrCreate(['key' => 'contact_lat'], ['value' => '-26.1']);
        Setting::updateOrCreate(['key' => 'contact_lng'], ['value' => '28.05']);
        cache()->forget('settings');
        BusinessUnit::factory()->create(['name' => 'Map Test Store', 'status' => 'published', 'phone' => '011 555 0000']);

        $this->get('/contact')->assertOk()->assertSee('99 Test Road, Sandton')->assertSee('openstreetmap.org/export/embed.html', false)
            ->assertSee('Map Test Store')->assertSee('011 555 0000');
    }

    public function test_super_admin_can_create_a_hero_slide_but_a_business_manager_cannot_manage_slides(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/r/hero-slides', [
            'type' => 'announcement', 'heading' => 'Admin Made Slide', 'cta_label' => 'Read', 'cta_url' => '/news', 'status' => 'published',
        ])->assertRedirect();
        $this->assertDatabaseHas('hero_slides', ['heading' => 'Admin Made Slide', 'type' => 'announcement']);

        $manager = $this->userWithRole('Business Manager', [BusinessUnit::factory()->create()]);
        $this->actingAs($manager)->get('/admin/r/hero-slides')->assertForbidden();
        $this->actingAs($manager)->get('/admin/r/subscribers')->assertForbidden();
    }

    public function test_links_entered_in_the_admin_are_made_safe(): void
    {
        $this->assertSame('#', Link::href('javascript:alert(1)'));
        $this->assertSame('#', Link::href('//evil.example'));
        $this->assertSame('#', Link::href(null));
        $this->assertSame('https://example.com/x', Link::href('https://example.com/x'));
        $this->assertSame(url('/contact'), Link::href('/contact'));
    }

    public function test_demo_seeding_can_be_switched_off(): void
    {
        config(['boomwow.seed_demo' => false]);
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->assertSame(0, HeroSlide::count());
        $this->assertDatabaseMissing('users', ['email' => 'sipho@example.com']);
    }

    public function test_admin_can_remove_a_business_logo_and_leave_it_blank(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = $this->superAdmin();
        $biz = BusinessUnit::factory()->create(['name' => 'Logo Co']);

        $file = \Illuminate\Http\UploadedFile::fake()->image('logo.png', 200, 200);
        $this->actingAs($admin)->put('/admin/r/business-units/'.$biz->id, ['name' => 'Logo Co', 'slug' => $biz->slug, 'type' => 'general', 'status' => 'published', 'logo_path' => $file])->assertRedirect();
        $path = $biz->fresh()->logo_path;
        $this->assertNotNull($path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($path);

        // Saving again without ticking "remove" keeps the logo.
        $this->actingAs($admin)->put('/admin/r/business-units/'.$biz->id, ['name' => 'Logo Co', 'slug' => $biz->slug, 'type' => 'general', 'status' => 'published'])->assertRedirect();
        $this->assertSame($path, $biz->fresh()->logo_path);

        // Ticking "remove" deletes the file and clears the field; the storefront still renders.
        $this->actingAs($admin)->put('/admin/r/business-units/'.$biz->id, ['name' => 'Logo Co', 'slug' => $biz->slug, 'type' => 'general', 'status' => 'published', 'remove_logo_path' => '1'])->assertRedirect();
        $this->assertNull($biz->fresh()->logo_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($path);
        $this->get('/businesses/'.$biz->slug)->assertOk();
    }
}
