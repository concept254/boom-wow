<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\StaffAvailability;
use App\Models\StaffProfile;
use Tests\TestCase;

class StaffScheduleIsolationTest extends TestCase
{
    public function test_manager_sees_only_own_business_staff_availability(): void
    {
        $a = BusinessUnit::factory()->create();
        $b = BusinessUnit::factory()->create();
        $mine = StaffProfile::factory()->create(['business_unit_id' => $a->id, 'display_name' => 'Alpha Stylist']);
        $theirs = StaffProfile::factory()->create(['business_unit_id' => $b->id, 'display_name' => 'Bravo Barista']);
        StaffAvailability::create(['staff_profile_id' => $mine->id, 'day_of_week' => 1, 'starts_at' => '09:00', 'ends_at' => '17:00']);
        StaffAvailability::create(['staff_profile_id' => $theirs->id, 'day_of_week' => 1, 'starts_at' => '09:00', 'ends_at' => '17:00']);

        $this->actingAs($this->userWithRole('Business Manager', [$a]))
            ->get('/admin/r/staff-availability')->assertOk()->assertSee('Alpha Stylist')->assertDontSee('Bravo Barista');
    }

    public function test_manager_cannot_create_availability_for_another_businesses_staff(): void
    {
        $a = BusinessUnit::factory()->create();
        $b = BusinessUnit::factory()->create();
        $theirs = StaffProfile::factory()->create(['business_unit_id' => $b->id]);

        $this->actingAs($this->userWithRole('Business Manager', [$a]))
            ->post('/admin/r/staff-availability', ['staff_profile_id' => $theirs->id, 'day_of_week' => 2, 'starts_at' => '09:00', 'ends_at' => '12:00'])
            ->assertForbidden();
        $this->assertSame(0, StaffAvailability::withoutGlobalScopes()->count());
    }
}
