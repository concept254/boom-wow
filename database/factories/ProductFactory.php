<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['business_unit_id' => BusinessUnit::factory(), 'name' => $name, 'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'), 'sku' => 'SKU-'.fake()->unique()->numerify('#####'), 'cost_price' => 50, 'price' => 100, 'unit' => 'each', 'track_stock' => true, 'status' => 'published'];
    }
}
