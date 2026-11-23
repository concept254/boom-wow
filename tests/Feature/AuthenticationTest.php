<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_customer_can_log_in_and_lands_in_portal(): void
    {
        $user = $this->customer();

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_back_office_user_lands_in_admin(): void
    {
        $admin = $this->superAdmin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'Password123'])->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->customer();

        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deactivated_users_cannot_log_in(): void
    {
        $user = $this->customer();
        $user->update(['is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->customer();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'bad']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertStatus(429);
    }

    public function test_logout(): void
    {
        $this->actingAs($this->customer())->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_password_reset_link_request_does_not_reveal_whether_email_exists(): void
    {
        $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertSessionHas('status');
        $this->post('/forgot-password', ['email' => $this->customer()->email])->assertSessionHas('status');
    }
}
