<?php

namespace Database\Factories;

use App\Models\BusinessUnit;
use App\Models\StaffProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffProfileFactory extends Factory
{
    protected $model = StaffProfile::class;

    public function definition(): array
    {
        return ['business_unit_id' => BusinessUnit::factory(), 'display_name' => fake()->name(), 'job_title' => 'Stylist', 'is_bookable' => true, 'is_active' => true];
    }
}
