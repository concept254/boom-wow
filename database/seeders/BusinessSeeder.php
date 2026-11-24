<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use App\Models\BusinessUnit;
use App\Models\Event;
use App\Models\Inventory;
use App\Models\PortfolioItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Property;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StaffAvailability;
use App\Models\StaffProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $defs = [
            ['name' => 'Salon', 'slug' => 'salon', 'type' => 'Salon', 'enquiry_type' => 'general', 'tagline' => 'Beauty, refined.', 'description' => 'A premium hair and beauty salon offering cuts, colour, styling, nails and treatments, delivered by experienced stylists.', 'accepts_bookings' => true, 'sells_products' => true, 'sort_order' => 1, 'phone' => '+27 11 000 0001', 'email' => 'salon@example.com', 'address' => '12 Rivonia Road', 'city' => 'Sandton'],
            ['name' => 'Liquor Store', 'slug' => 'liquor-store', 'type' => 'Liquor store', 'enquiry_type' => 'general', 'tagline' => 'Fine spirits, wine and craft beer.', 'description' => 'A curated selection of wines, spirits, beers and mixers. Licensed retailer. Strictly 18+.', 'sells_products' => true, 'age_restricted' => true, 'sort_order' => 2, 'phone' => '+27 11 000 0002', 'email' => 'liquor@example.com', 'address' => '14 Rivonia Road', 'city' => 'Sandton'],
            ['name' => 'IT Consultants', 'slug' => 'it-consultants', 'type' => 'IT consultants', 'enquiry_type' => 'project', 'tagline' => 'Technology that works for your business.', 'description' => 'Managed IT support, cloud, networking, cybersecurity and custom software for small and medium businesses.', 'accepts_bookings' => true, 'sort_order' => 3, 'phone' => '+27 11 000 0003', 'email' => 'it@example.com', 'address' => '16 Rivonia Road', 'city' => 'Sandton'],
            ['name' => 'Property Management', 'slug' => 'property-management', 'type' => 'Property management', 'enquiry_type' => 'viewing', 'tagline' => 'Letting, sales and management you can trust.', 'description' => 'Residential and commercial property letting, sales and full management services.', 'sort_order' => 4, 'phone' => '+27 11 000 0004', 'email' => 'property@example.com', 'address' => '18 Rivonia Road', 'city' => 'Sandton'],
            ['name' => 'Events Management', 'slug' => 'events-management', 'type' => 'Events management', 'enquiry_type' => 'event', 'tagline' => 'Unforgettable occasions, flawlessly delivered.', 'description' => 'Weddings, corporate functions and private celebrations - planned, styled and run end to end.', 'sort_order' => 5, 'phone' => '+27 11 000 0005', 'email' => 'events@example.com', 'address' => '20 Rivonia Road', 'city' => 'Sandton'],
        ];

        foreach ($defs as $d) {
            $b = BusinessUnit::updateOrCreate(['slug' => $d['slug']], $d + ['status' => 'published', 'social_links' => ['facebook' => 'https://facebook.com/', 'instagram' => 'https://instagram.com/']]);

            foreach (range(0, 6) as $dow) {
                BusinessHour::updateOrCreate(['business_unit_id' => $b->id, 'day_of_week' => $dow], match (true) {
                    $dow === 0 => ['is_closed' => true, 'opens_at' => null, 'closes_at' => null],
                    $dow === 6 => ['is_closed' => false, 'opens_at' => '09:00', 'closes_at' => '14:00'],
                    default => ['is_closed' => false, 'opens_at' => '08:30', 'closes_at' => '17:30'],
                });
            }
        }

        $this->salon(BusinessUnit::where('slug', 'salon')->first());
        $this->liquor(BusinessUnit::where('slug', 'liquor-store')->first());
        $this->it(BusinessUnit::where('slug', 'it-consultants')->first());
        $this->property(BusinessUnit::where('slug', 'property-management')->first());
        $this->events(BusinessUnit::where('slug', 'events-management')->first());
    }

    private function service(BusinessUnit $b, ?ServiceCategory $c, string $name, float $price, int $mins, array $extra = []): Service
    {
        return Service::updateOrCreate(['business_unit_id' => $b->id, 'slug' => Str::slug($name)], [
            'service_category_id' => $c?->id, 'name' => $name, 'price' => $price, 'duration_minutes' => $mins,
            'description' => "$name by our experienced team.", 'status' => 'published', 'is_bookable' => true, ...$extra,
        ]);
    }

    private function product(BusinessUnit $b, ProductCategory $c, string $name, string $sku, float $cost, float $price, int $qty, array $extra = []): Product
    {
        $p = Product::updateOrCreate(['business_unit_id' => $b->id, 'sku' => $sku], [
            'product_category_id' => $c->id, 'slug' => Str::slug($name), 'name' => $name, 'cost_price' => $cost, 'price' => $price,
            'description' => $name.'.', 'status' => 'published', 'barcode' => '600'.str_pad((string) crc32($sku), 10, '0', STR_PAD_LEFT), ...$extra,
        ]);
        Inventory::updateOrCreate(['product_id' => $p->id], ['business_unit_id' => $b->id, 'quantity' => $qty, 'reorder_level' => 5]);

        return $p;
    }

    private function salon(BusinessUnit $b): void
    {
        $hair = ServiceCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'hair'], ['name' => 'Hair']);
        $nails = ServiceCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'nails'], ['name' => 'Nails']);
        $spa = ServiceCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'treatments'], ['name' => 'Treatments']);

        $staff = collect([['Thandi Mokoena', 'Senior Stylist'], ['Lerato Dlamini', 'Colourist'], ['Naledi Khumalo', 'Nail Technician']])->map(function ($s) use ($b) {
            $sp = StaffProfile::updateOrCreate(['business_unit_id' => $b->id, 'display_name' => $s[0]], ['job_title' => $s[1], 'is_bookable' => true, 'is_active' => true]);
            foreach ([1, 2, 3, 4, 5] as $dow) {
                StaffAvailability::updateOrCreate(['staff_profile_id' => $sp->id, 'day_of_week' => $dow], ['starts_at' => '09:00', 'ends_at' => '17:00']);
            }
            StaffAvailability::updateOrCreate(['staff_profile_id' => $sp->id, 'day_of_week' => 6], ['starts_at' => '09:00', 'ends_at' => '13:30']);

            return $sp;
        });

        $cut = $this->service($b, $hair, "Women's Cut & Blow-dry", 380, 60, ['is_featured' => true, 'requires_staff' => true]);
        $col = $this->service($b, $hair, 'Full Colour', 850, 120, ['price_label' => 'from', 'requires_staff' => true]);
        $mani = $this->service($b, $nails, 'Gel Manicure', 320, 45, ['is_featured' => true, 'requires_staff' => true]);
        $facial = $this->service($b, $spa, 'Signature Facial', 560, 60, ['is_featured' => true]);
        $cut->staff()->sync([$staff[0]->id, $staff[1]->id]);
        $col->staff()->sync([$staff[1]->id]);
        $mani->staff()->sync([$staff[2]->id]);

        $care = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'hair-care'], ['name' => 'Hair Care']);
        $this->product($b, $care, 'Repair Shampoo 250ml', 'SAL-SH-250', 95, 189, 24, ['brand' => 'Boom Care', 'is_featured' => true]);
        $this->product($b, $care, 'Nourishing Conditioner 250ml', 'SAL-CO-250', 98, 195, 18, ['brand' => 'Boom Care']);
        $this->product($b, $care, 'Argan Oil Treatment 100ml', 'SAL-AO-100', 140, 299, 3, ['brand' => 'Boom Care']);
    }

    private function liquor(BusinessUnit $b): void
    {
        $wine = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'wine'], ['name' => 'Wine']);
        $spirits = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'spirits'], ['name' => 'Spirits']);
        $beer = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'beer-cider'], ['name' => 'Beer & Cider']);
        $x = ['is_age_restricted' => true];
        $this->product($b, $wine, 'Cabernet Sauvignon 750ml', 'LIQ-CS-750', 70, 135, 48, ['brand' => 'Cape Estate', 'size' => '750ml', 'is_featured' => true] + $x);
        $this->product($b, $wine, 'Sauvignon Blanc 750ml', 'LIQ-SB-750', 62, 119, 36, ['brand' => 'Cape Estate', 'size' => '750ml', 'promo_price' => 99, 'promo_ends_at' => now()->addDays(14)] + $x);
        $this->product($b, $spirits, 'Single Malt Whisky 750ml', 'LIQ-WH-750', 520, 899, 12, ['brand' => 'Highland', 'size' => '750ml', 'is_featured' => true] + $x);
        $this->product($b, $spirits, 'London Dry Gin 750ml', 'LIQ-GN-750', 190, 349, 20, ['brand' => 'Cape Botanical', 'size' => '750ml'] + $x);
        $this->product($b, $beer, 'Craft Lager 6 x 330ml', 'LIQ-LG-6', 70, 129, 40, ['brand' => 'Joburg Brewing', 'size' => '6 x 330ml'] + $x);
        $this->product($b, $beer, 'Dry Cider 6 x 330ml', 'LIQ-CD-6', 66, 119, 0, ['brand' => 'Orchard & Co', 'size' => '6 x 330ml'] + $x);
    }

    private function it(BusinessUnit $b): void
    {
        $sup = ServiceCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'support'], ['name' => 'Support']);
        $proj = ServiceCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'projects'], ['name' => 'Projects']);
        $this->service($b, $sup, 'IT Consultation (1 hour)', 650, 60, ['is_featured' => true]);
        $this->service($b, $sup, 'On-site Support Visit', 950, 90);
        $this->service($b, $proj, 'Network & Wi-Fi Design', 4500, 120, ['price_label' => 'from']);
        $this->service($b, $proj, 'Cybersecurity Assessment', 6500, 120, ['price_label' => 'from']);

        ServicePackage::updateOrCreate(['business_unit_id' => $b->id, 'name' => 'Essential Support'], ['price' => 1500, 'billing' => 'monthly', 'description' => 'Helpdesk and monitoring for small teams.', 'features' => ['Business-hours helpdesk', 'Remote support', 'Patch management'], 'status' => 'published']);
        ServicePackage::updateOrCreate(['business_unit_id' => $b->id, 'name' => 'Business Care'], ['price' => 3900, 'billing' => 'monthly', 'description' => 'Proactive IT management.', 'features' => ['Everything in Essential', 'On-site visits', 'Backups & security monitoring'], 'status' => 'published']);

        PortfolioItem::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'retail-network-rollout'], ['title' => 'Retail network rollout', 'kind' => 'case_study', 'client_name' => 'Regional retail group', 'summary' => 'Wi-Fi and POS network across 8 stores.', 'body' => 'Designed and deployed a segmented network with central monitoring.', 'status' => 'published']);
        PortfolioItem::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'booking-portal'], ['title' => 'Custom booking portal', 'kind' => 'portfolio', 'client_name' => 'Wellness studio', 'summary' => 'Online booking and payments.', 'status' => 'published']);
    }

    private function property(BusinessUnit $b): void
    {
        $rows = [
            ['2-Bedroom Apartment, Sandton', 'apartment', 'rent', 14500, 2, 2, 85, 'Sandton', 'available'],
            ['Family Home with Garden', 'house', 'sale', 2850000, 4, 3, 320, 'Bryanston', 'available'],
            ['Open-plan Office Suite', 'commercial', 'rent', 32000, null, 2, 140, 'Rosebank', 'available'],
            ['Modern Studio Apartment', 'apartment', 'rent', 8900, 1, 1, 38, 'Melrose', 'let'],
        ];
        foreach ($rows as $r) {
            Property::updateOrCreate(['business_unit_id' => $b->id, 'slug' => Str::slug($r[0])], [
                'title' => $r[0], 'property_type' => $r[1], 'listing_type' => $r[2], 'price' => $r[3], 'bedrooms' => $r[4], 'bathrooms' => $r[5], 'floor_area' => $r[6],
                'suburb' => $r[7], 'city' => 'Johannesburg', 'availability' => $r[8], 'is_featured' => true, 'status' => 'published',
                'description' => 'Well-presented property in a sought-after location. Contact us to arrange a viewing.',
            ]);
        }
    }

    private function events(BusinessUnit $b): void
    {
        $rows = [
            ['Corporate Year-End Function', 30, 'Sandton Convention Hall', 450, 200],
            ['Garden Wedding Showcase', 45, 'Bryanston Estate', null, 120],
            ['Product Launch Evening', 60, 'Rosebank Rooftop', 350, 150],
        ];
        foreach ($rows as $r) {
            Event::updateOrCreate(['business_unit_id' => $b->id, 'slug' => Str::slug($r[0])], [
                'title' => $r[0], 'venue' => $r[2], 'starts_at' => now()->addDays($r[1])->setTime(18, 0), 'ends_at' => now()->addDays($r[1])->setTime(23, 0),
                'price' => $r[3], 'capacity' => $r[4], 'is_featured' => true, 'status' => 'published',
                'description' => 'A beautifully produced event with full planning, styling and on-the-day management.',
                'packages' => [['name' => 'Classic', 'price' => 15000, 'includes' => 'Venue styling, coordinator, sound'], ['name' => 'Premium', 'price' => 32000, 'includes' => 'Classic + catering, décor, photography']],
            ]);
        }
    }
}
