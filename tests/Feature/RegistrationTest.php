<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    private function payload(array $over = []): array
    {
        return array_merge([
            'first_name' => 'Thandi', 'last_name' => 'Nkosi', 'email' => 'thandi@example.com', 'phone' => '082 555 0101',
            'password' => 'Secret1234', 'password_confirmation' => 'Secret1234', 'terms' => '1',
        ], $over);
    }

    public function test_customer_registers_and_receives_verification_email(): void
    {
        Notification::fake();

        $this->post('/register', $this->payload())->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'thandi@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('Customer'));
        $this->assertNotNull($user->profile);
        $this->assertNull($user->email_verified_at);
        $this->assertNotSame('Secret1234', $user->password); // hashed
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->post('/register', $this->payload(['terms' => null]))->assertSessionHasErrors('terms');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_weak_or_unconfirmed_passwords_are_rejected(): void
    {
        $this->post('/register', $this->payload(['password' => 'short', 'password_confirmation' => 'short']))->assertSessionHasErrors('password');
        $this->post('/register', $this->payload(['password_confirmation' => 'different1']))->assertSessionHasErrors('password');
    }

    public function test_email_must_be_unique(): void
    {
        $this->customer()->update(['email' => 'thandi@example.com']);

        $this->post('/register', $this->payload())->assertSessionHasErrors('email');
    }

    public function test_unverified_customers_are_sent_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('Customer');

        $this->actingAs($user)->get('/portal')->assertRedirect(route('verification.notice'));
    }

    public function test_registration_cannot_assign_a_privileged_role(): void
    {
        $this->post('/register', $this->payload(['role' => 'Super Admin']));

        $this->assertFalse(User::where('email', 'thandi@example.com')->firstOrFail()->hasRole('Super Admin'));
    }
}
