<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    public function test_super_admin_can_open_every_admin_screen(): void
    {
        BusinessUnit::factory()->create();
        $admin = $this->superAdmin();

        $urls = ['/admin', '/admin/orders', '/admin/bookings', '/admin/inventory', '/admin/stock-movements', '/admin/reports',
            '/admin/customers', '/admin/users', '/admin/roles', '/admin/media', '/admin/notifications', '/admin/activity-logs', '/admin/settings'];
        foreach (array_keys(config('admin')) as $module) {
            $urls[] = "/admin/r/$module";
        }

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_super_admin_sees_data_from_all_businesses(): void
    {
        BusinessUnit::factory()->create(['name' => 'Alpha Unit']);
        BusinessUnit::factory()->create(['name' => 'Beta Unit']);

        $this->actingAs($this->superAdmin())->get('/admin/r/business-units')->assertSee('Alpha Unit')->assertSee('Beta Unit');
    }
}
