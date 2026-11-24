<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Adds the sixth business: Clothing (streetwear, headed by Nkezi Fashion Store, est. 2024).
 * Idempotent: php artisan db:seed --class=ClothingBusinessSeeder
 * Edit everything afterwards in Admin > Business Units.
 */
class ClothingBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $b = BusinessUnit::withTrashed()->updateOrCreate(['slug' => 'clothing'], [
            'name' => 'Clothing',
            'type' => 'Clothing store',
            'enquiry_type' => 'general',
            'tagline' => 'Streetwear with a story.',
            'description' => "A streetwear brand headed by Nkezi Fashion Store (est. 2024). Fresh drops, bold fits and everyday essentials for people who like their style to speak first.\n\nShop tees, hoodies, caps and more - in store or online.",
            'sells_products' => true,
            'accepts_bookings' => false,
            'is_featured' => true,
            'status' => 'published',
            'sort_order' => 6,
            'social_links' => ['instagram' => 'https://instagram.com/', 'facebook' => 'https://facebook.com/'],
        ]);
        if ($b->trashed()) {
            $b->restore();
        }

        if (! config('boomwow.seed_demo')) {
            return;
        }

        $tops = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'tops'], ['name' => 'Tees & Hoodies']);
        $bottoms = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'bottoms'], ['name' => 'Bottoms']);
        $acc = ProductCategory::updateOrCreate(['business_unit_id' => $b->id, 'slug' => 'accessories'], ['name' => 'Caps & Accessories']);

        foreach ([
            [$tops, 'Nkezi Logo Tee', 'CLO-TEE-01', 110, 249, 40, true],
            [$tops, 'Oversized Street Hoodie', 'CLO-HOO-01', 280, 599, 25, true],
            [$bottoms, 'Utility Cargo Pants', 'CLO-CAR-01', 260, 549, 18, false],
            [$acc, 'Five-Panel Cap', 'CLO-CAP-01', 70, 179, 30, false],
        ] as [$cat, $name, $sku, $cost, $price, $qty, $featured]) {
            $p = Product::updateOrCreate(['business_unit_id' => $b->id, 'sku' => $sku], [
                'product_category_id' => $cat->id, 'slug' => Str::slug($name), 'name' => $name, 'brand' => 'Nkezi',
                'cost_price' => $cost, 'price' => $price, 'description' => $name.' from Nkezi Fashion Store.',
                'status' => 'published', 'is_featured' => $featured,
                'barcode' => '600'.str_pad((string) crc32($sku), 10, '0', STR_PAD_LEFT),
            ]);
            Inventory::updateOrCreate(['product_id' => $p->id], ['business_unit_id' => $b->id, 'quantity' => $qty, 'reorder_level' => 5]);
        }
    }
}
