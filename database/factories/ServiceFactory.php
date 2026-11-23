<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return ['business_unit_id' => BusinessUnit::factory(), 'name' => $name, 'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'), 'price' => 300, 'duration_minutes' => 60, 'is_bookable' => true, 'status' => 'published'];
    }
}
