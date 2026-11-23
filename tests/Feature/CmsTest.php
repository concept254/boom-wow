<?php

namespace Tests\Feature;

use App\Models\CmsPage;
use Tests\TestCase;

class CmsTest extends TestCase
{
    public function test_super_admin_can_create_and_publish_a_page(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/r/cms-pages', [
            'title' => 'About Us', 'slug' => 'about-us', 'content' => 'We are Boom & Wow.', 'status' => 'published',
        ])->assertRedirect();

        $this->assertDatabaseHas('cms_pages', ['slug' => 'about-us', 'status' => 'published']);
        $this->get('/about-us')->assertOk()->assertSee('We are Boom');
    }

    public function test_draft_and_archived_pages_are_not_public(): void
    {
        CmsPage::create(['title' => 'Terms', 'slug' => 'terms-and-conditions', 'content' => 'x', 'status' => 'draft']);
        $this->get('/terms-and-conditions')->assertNotFound();

        CmsPage::where('slug', 'terms-and-conditions')->update(['status' => 'archived']);
        $this->get('/terms-and-conditions')->assertNotFound();
    }

    public function test_cms_is_closed_to_managers_staff_and_customers(): void
    {
        $this->actingAs($this->customer())->get('/admin/r/cms-pages')->assertForbidden();
        $this->actingAs($this->userWithRole('Staff'))->get('/admin/r/cms-pages')->assertForbidden();
    }

    public function test_page_slug_must_be_unique(): void
    {
        CmsPage::create(['title' => 'A', 'slug' => 'dupe', 'content' => 'x', 'status' => 'draft']);

        $this->actingAs($this->superAdmin())->post('/admin/r/cms-pages', ['title' => 'B', 'slug' => 'dupe', 'status' => 'draft'])
            ->assertSessionHasErrors('slug');
    }
}
