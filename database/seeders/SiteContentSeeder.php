<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\BusinessUnit;
use App\Models\PageHeader;
use Illuminate\Database\Seeder;

/**
 * Default page-header banners and (demo only) sample brand names for the logo carousel.
 * Idempotent: php artisan db:seed --class=SiteContentSeeder
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            'default' => [null, null],
            'businesses' => ['Our businesses', 'Every business in the Boom & Wow family, one account for all of them.'],
            'services' => ['Services', 'Book beauty, IT, property and event services with trusted professionals.'],
            'products' => ['Products', 'Shop products from across all of our stores.'],
            'events' => ['Events', 'What is coming up and how to get involved.'],
            'properties' => ['Properties', 'Homes and commercial spaces to rent or buy.'],
            'blog' => ['News', 'Updates, offers and stories from our businesses.'],
            'faqs' => ['Frequently asked questions', 'Quick answers about bookings, orders and our businesses.'],
            'about' => ['About us', 'One family of businesses, one customer account and one standard of service.'],
            'contact' => ['Contact us', 'Visit us, call us, or send a message - the right team will get back to you.'],
            'search' => ['Search', 'Find businesses, services, products, events and properties.'],
            'cart' => ['Your cart', 'Review your items before checkout.'],
            'checkout' => ['Checkout', 'Almost there - confirm your details and place your order.'],
        ];
        foreach ($rows as $key => [$title, $subtitle]) {
            PageHeader::firstOrCreate(['key' => $key], ['title' => null, 'subtitle' => $subtitle, 'use_fallback' => true, 'is_active' => true]);
        }

        if (config('boomwow.seed_demo') && Brand::count() === 0) {
            // Placeholder names only (no logos). Replace with the real brands in Admin > Brands.
            $biz = fn (string $slug) => BusinessUnit::where('slug', $slug)->value('id');
            foreach ([
                ['Northside Denim', 'clothing'], ['Urban Hive', 'clothing'], ['Copperline Spirits', 'liquor-store'], ['Vineyard & Co', 'liquor-store'],
                ['Glow Lab', 'salon'], ['Silk & Sage', 'salon'], ['Keystone Realty', 'property-management'], ['Pixel Forge', 'it-consultants'],
            ] as $i => [$name, $slug]) {
                Brand::create(['name' => $name, 'business_unit_id' => $biz($slug), 'sort_order' => $i + 1, 'show_on_home' => true, 'status' => 'published']);
            }
        }
    }
}
