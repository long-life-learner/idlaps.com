<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_must_sign_in_to_use_the_cms(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_non_admins_cannot_access_or_write_cms_content(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('manage-cms'));
        $this->actingAs($user)->post('/admin/events', [])->assertForbidden();
        $this->assertDatabaseCount('events', 0);
    }

    public function test_admin_can_login_and_logout(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'password' => 'ValidPassword2026']);
        $this->assertTrue(Gate::forUser($user)->allows('manage-cms'));
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'ValidPassword2026'])->assertRedirect(route('admin.events.index'));
        $this->assertAuthenticatedAs($user);
        $this->post('/admin/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_non_admin_credentials_do_not_create_an_admin_session(): void
    {
        $user = User::factory()->create(['password' => 'ValidPassword2026']);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'ValidPassword2026'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/admin/login', ['email' => 'missing@example.test', 'password' => 'incorrect'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => 'missing@example.test', 'password' => 'incorrect'])->assertTooManyRequests();
    }

    public function test_malformed_login_is_rejected_without_a_server_error(): void
    {
        $this->post('/admin/login', ['email' => ['invalid'], 'password' => 'incorrect'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_changes_require_the_current_password(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'OldPassword2026']);
        $this->actingAs($admin)->put('/admin/account/password', [
            'current_password' => 'incorrect', 'password' => 'NewPassword2026', 'password_confirmation' => 'NewPassword2026',
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword2026', $admin->fresh()->password));
    }

    public function test_malformed_current_password_is_rejected_without_a_server_error(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->put('/admin/account/password', [
            'current_password' => ['incorrect'], 'password' => 'NewPassword2026', 'password_confirmation' => 'NewPassword2026',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_admin_can_change_password_and_keep_the_current_session(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'OldPassword2026']);
        $this->actingAs($admin)->put('/admin/account/password', [
            'current_password' => 'OldPassword2026', 'password' => 'NewPassword2026', 'password_confirmation' => 'NewPassword2026',
        ])->assertRedirect(route('admin.account'));
        $this->assertTrue(Hash::check('NewPassword2026', $admin->fresh()->password));
        $this->get('/admin')->assertOk();
    }
}
