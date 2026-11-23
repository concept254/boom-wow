<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            BusinessSeeder::class,
            ContentSeeder::class,
        ]);

        // Sample customers, bookings, orders, hero slides, adverts and team members. Turn off in production.
        if (config('boomwow.seed_demo')) {
            $this->call([
                DemoDataSeeder::class,
                HomepageContentSeeder::class,
            ]);
        }
    }
}
