<?php

namespace Tests;

use App\Models\BusinessHour;
use App\Models\BusinessUnit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite(); // tests must not depend on a compiled frontend
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** Make a verified user with a role, optionally assigned to businesses. */
    protected function userWithRole(string $role, array $businesses = []): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        if ($businesses) {
            $user->businesses()->sync(collect($businesses)->map(fn ($b) => $b instanceof BusinessUnit ? $b->id : $b)->all());
        }

        return $user;
    }

    protected function superAdmin(): User
    {
        return $this->userWithRole('Super Admin');
    }

    protected function customer(): User
    {
        return $this->userWithRole('Customer');
    }

    protected function manager(BusinessUnit ...$businesses): User
    {
        return $this->userWithRole('Business Manager', $businesses);
    }

    /** Open 09:00-17:00 every day so booking tests do not depend on the weekday they run. */
    protected function openEveryDay(BusinessUnit $business, string $from = '09:00', string $to = '17:00'): void
    {
        foreach (range(0, 6) as $day) {
            BusinessHour::create(['business_unit_id' => $business->id, 'day_of_week' => $day, 'opens_at' => $from, 'closes_at' => $to, 'is_closed' => false]);
        }
    }
}
