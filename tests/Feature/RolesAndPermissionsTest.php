<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/portal')->assertRedirect(route('login'));
    }

    public function test_customers_cannot_open_the_admin_area(): void
    {
        $this->actingAs($this->customer())->get('/admin')->assertForbidden();
    }

    public function test_deactivated_back_office_users_are_blocked(): void
    {
        $manager = $this->manager(BusinessUnit::factory()->create());
        $manager->update(['is_active' => false]);

        $this->actingAs($manager)->get('/admin')->assertForbidden();
    }

    public function test_staff_can_view_bookings_but_not_manage_products(): void
    {
        $biz = BusinessUnit::factory()->create();
        $staff = $this->userWithRole('Staff', [$biz]);

        $this->actingAs($staff)->get('/admin/bookings')->assertOk();
        $this->actingAs($staff)->get('/admin/r/products')->assertForbidden();
        $this->actingAs($staff)->get('/admin/r/products/create')->assertForbidden();
    }

    public function test_business_manager_cannot_use_super_admin_only_areas(): void
    {
        $manager = $this->manager(BusinessUnit::factory()->create());

        foreach (['/admin/users', '/admin/roles', '/admin/settings', '/admin/r/business-units', '/admin/r/cms-pages', '/admin/activity-logs'] as $url) {
            $this->actingAs($manager)->get($url)->assertForbidden();
        }
        $this->actingAs($manager)->post('/admin/users', [])->assertForbidden();
    }

    public function test_permissions_are_enforced_on_the_server_even_if_the_menu_hides_links(): void
    {
        $biz = BusinessUnit::factory()->create();
        $staff = $this->userWithRole('Staff', [$biz]);

        $this->actingAs($staff)->post('/admin/r/products', ['business_unit_id' => $biz->id, 'name' => 'x'])->assertForbidden();
        $this->actingAs($staff)->delete('/admin/r/products/1')->assertForbidden();
    }

    public function test_customers_cannot_post_to_admin_endpoints(): void
    {
        $this->actingAs($this->customer())->post('/admin/users', [])->assertForbidden();
    }

    public function test_super_admin_can_change_role_permissions_but_not_their_own_role_away(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->patch('/admin/users/'.$admin->id, ['role' => 'Staff'])->assertStatus(422);
        $this->assertTrue($admin->fresh()->hasRole('Super Admin'));
    }
}
