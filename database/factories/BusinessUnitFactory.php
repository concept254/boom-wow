<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BusinessUnitFactory extends Factory
{
    protected $model = BusinessUnit::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return ['name' => $name, 'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'), 'type' => 'general', 'status' => 'published', 'accepts_bookings' => true, 'sells_products' => true];
    }
}
