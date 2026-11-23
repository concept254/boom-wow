<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Enquiry;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    private function payload(array $over = []): array
    {
        return array_merge([
            'type' => 'general', 'name' => 'Thandi Dlamini', 'email' => 'thandi@example.com',
            'message' => 'Please contact me about your services.',
        ], $over);
    }

    public function test_guest_can_submit_an_enquiry_to_a_business(): void
    {
        $biz = BusinessUnit::factory()->create();

        $this->post('/enquiries', $this->payload(['business_unit_id' => $biz->id]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('enquiries', ['email' => 'thandi@example.com', 'business_unit_id' => $biz->id]);
    }

    public function test_validation_rejects_bad_input_and_honeypot(): void
    {
        $this->post('/enquiries', $this->payload(['email' => 'not-an-email']))->assertSessionHasErrors('email');
        $this->post('/enquiries', $this->payload(['website' => 'http://spam.example']))->assertSessionHasErrors('website');
        $this->assertSame(0, Enquiry::count());
    }

    public function test_manager_only_sees_enquiries_for_their_own_business(): void
    {
        $mine = BusinessUnit::factory()->create();
        $other = BusinessUnit::factory()->create();
        Enquiry::create(['business_unit_id' => $mine->id, 'type' => 'general', 'name' => 'Visible Person', 'email' => 'a@example.com', 'message' => 'hello', 'status' => 'new']);
        Enquiry::create(['business_unit_id' => $other->id, 'type' => 'general', 'name' => 'Hidden Person', 'email' => 'b@example.com', 'message' => 'hello', 'status' => 'new']);

        $this->actingAs($this->manager($mine))->get('/admin/r/enquiries')
            ->assertOk()->assertSee('Visible Person')->assertDontSee('Hidden Person');
    }
}
