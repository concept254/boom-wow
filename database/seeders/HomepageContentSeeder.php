<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\HeroSlide;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

/**
 * Sample homepage content: hero slides, adverts/specials, management team and map/WhatsApp settings.
 * Idempotent (safe to run repeatedly): php artisan db:seed --class=HomepageContentSeeder
 * Everything here is editable in the admin. Images are placeholder URLs - replace them with your own photos.
 */
class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $biz = fn (string $slug) => BusinessUnit::where('slug', $slug)->value('id');
        $img = fn (string $seed) => "https://picsum.photos/seed/{$seed}/1920/900";

        $slides = [
            ['heading' => 'One Destination. Endless Possibilities.', 'caption' => 'Boom & Wow Holdings', 'type' => 'general', 'subheading' => 'Salon, liquor, IT, property and events - discover exceptional services and products under one roof.', 'image_url' => $img('bw-welcome'), 'cta_label' => 'Explore our stores', 'cta_url' => '/businesses', 'cta2_label' => 'Contact us', 'cta2_url' => '/contact', 'sort_order' => 1],
            ['heading' => 'Treat yourself at the Salon', 'caption' => 'Store spotlight', 'type' => 'store', 'business' => 'salon', 'subheading' => 'Hair, skin and nails by experienced stylists. Book your appointment online in minutes.', 'image_url' => $img('bw-salon'), 'cta_label' => 'Book an appointment', 'cta_url' => '/portal/bookings/new', 'cta2_label' => 'Visit the salon', 'cta2_url' => '/businesses/salon', 'sort_order' => 2],
            ['heading' => 'Wine Weekend Specials', 'caption' => 'This month only', 'type' => 'promotion', 'business' => 'liquor-store', 'subheading' => 'Selected wines at special prices. Please enjoy responsibly - 18+ only.', 'image_url' => $img('bw-wine'), 'cta_label' => 'Shop the specials', 'cta_url' => '/products?business=liquor-store', 'cta2_label' => 'Visit the store', 'cta2_url' => '/businesses/liquor-store', 'sort_order' => 3],
            ['heading' => 'Technology that works for you', 'caption' => 'IT Consultants', 'type' => 'service', 'business' => 'it-consultants', 'subheading' => 'Support, projects and consulting for small and growing businesses.', 'image_url' => $img('bw-it'), 'cta_label' => 'Book a consultation', 'cta_url' => '/businesses/it-consultants', 'cta2_label' => 'Send an enquiry', 'cta2_url' => '/contact', 'sort_order' => 4],
            ['heading' => 'Find your next property', 'caption' => 'Property Management', 'type' => 'store', 'business' => 'property-management', 'subheading' => 'Homes and commercial spaces to rent or buy, with viewings arranged for you.', 'image_url' => $img('bw-property'), 'cta_label' => 'View properties', 'cta_url' => '/properties', 'cta2_label' => 'Request a viewing', 'cta2_url' => '/contact', 'sort_order' => 5],
            ['heading' => 'Events worth remembering', 'caption' => 'Events Management', 'type' => 'announcement', 'business' => 'events-management', 'subheading' => 'Weddings, launches and celebrations, planned and delivered flawlessly.', 'image_url' => $img('bw-events'), 'cta_label' => 'See upcoming events', 'cta_url' => '/events', 'cta2_label' => 'Plan my event', 'cta2_url' => '/contact', 'sort_order' => 6],
        ];
        foreach ($slides as $s) {
            $slug = $s['business'] ?? null;
            unset($s['business']);
            HeroSlide::updateOrCreate(['heading' => $s['heading']], $s + ['business_unit_id' => $slug ? $biz($slug) : null, 'status' => 'published']);
        }

        $promos = [
            ['title' => 'Free delivery over R500', 'placement' => 'top_bar', 'description' => 'On selected local orders this week.', 'cta_label' => 'Shop now', 'link_url' => '/products', 'theme' => 'orange'],
            ['title' => 'Wine Weekend', 'placement' => 'home_specials', 'badge' => 'SPECIAL', 'description' => 'Selected wines at special prices this month. 18+ only - please enjoy responsibly.', 'cta_label' => 'Shop wine', 'link_url' => '/products?business=liquor-store', 'theme' => 'charcoal', 'sort_order' => 1, 'business' => 'liquor-store', 'ends_on' => now()->addDays(14)->toDateString()],
            ['title' => 'First Facial 15% Off', 'placement' => 'home_specials', 'badge' => '-15%', 'description' => 'New salon clients enjoy 15% off a Signature Facial.', 'cta_label' => 'Book now', 'link_url' => '/portal/bookings/new', 'theme' => 'orange', 'sort_order' => 2, 'business' => 'salon'],
            ['title' => 'Free IT health check', 'placement' => 'home_specials', 'badge' => 'FREE', 'description' => 'A no-obligation review of your network, backups and security.', 'cta_label' => 'Claim yours', 'link_url' => '/businesses/it-consultants', 'theme' => 'light', 'sort_order' => 3, 'business' => 'it-consultants'],
            ['title' => 'Planning an event? Get a free quote', 'placement' => 'home_banner', 'badge' => 'EVENTS', 'description' => 'Tell us the date and guest count and our team will prepare a package for you.', 'cta_label' => 'Request a quote', 'link_url' => '/contact', 'theme' => 'charcoal'],
            ['title' => 'Join our newsletter for exclusive specials', 'placement' => 'inner_banner', 'badge' => 'NEWS', 'description' => 'Be the first to hear about offers across all our businesses.', 'cta_label' => 'Subscribe', 'link_url' => '/#subscribe', 'theme' => 'orange'],
        ];
        foreach ($promos as $p) {
            $slug = $p['business'] ?? null;
            unset($p['business']);
            Promotion::updateOrCreate(['title' => $p['title']], $p + ['business_unit_id' => $slug ? $biz($slug) : null, 'status' => 'published']);
        }

        foreach ([
            'contact_lat' => '-26.1076', 'contact_lng' => '28.0567',
            'whatsapp_number' => '27110000000',
            'trading_note' => 'Public holiday hours may differ - call ahead.',
        ] as $k => $v) {
            Setting::firstOrCreate(['key' => $k], ['value' => $v]);
        }
        cache()->forget('settings');

        // Sample people are fictional placeholders - replace in Admin > Team & Management.
        foreach ([
            ['Thandi Mokoena', 'Chief Executive Officer', 'management', 'Leads the group strategy and keeps every business focused on great service.', 1],
            ['Pieter van der Merwe', 'Chief Operating Officer', 'management', 'Oversees day-to-day operations across all of our businesses.', 2],
            ['Naledi Khumalo', 'Head of Finance', 'management', 'Looks after budgets, reporting and compliance.', 3],
            ['Sipho Dlamini', 'Head of Customer Experience', 'management', 'Makes sure every customer feels looked after.', 4],
            ['Lerato Nkosi', 'Senior Stylist', 'team', 'Colour specialist with ten years of salon experience.', 1],
            ['Ayanda Zulu', 'Store Supervisor', 'team', 'Runs the liquor store floor and keeps stock fresh.', 2],
            ['Johan Botha', 'IT Consultant', 'team', 'Solves support tickets and delivers client projects.', 3],
            ['Zanele Mahlangu', 'Events Coordinator', 'team', 'Turns event briefs into unforgettable occasions.', 4],
        ] as [$name, $title, $group, $bio, $order]) {
            TeamMember::updateOrCreate(['name' => $name], ['job_title' => $title, 'team_group' => $group, 'bio' => $bio, 'sort_order' => $order, 'status' => 'published']);
        }
    }
}
