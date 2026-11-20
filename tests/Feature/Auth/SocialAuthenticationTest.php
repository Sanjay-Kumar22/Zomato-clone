<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_sign_in_with_a_verified_google_account(): void
    {
        config([
            'services.google.client_id' => 'google-client',
            'services.google.client_secret' => 'google-secret',
            'services.google.redirect' => route('google.callback'),
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
            ]),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-123',
                'name' => 'Google Customer',
                'email' => 'google-customer@example.com',
                'email_verified' => true,
            ]),
        ]);

        $redirect = $this->get(route('google.redirect'));
        parse_str((string) parse_url($redirect->headers->get('Location'), PHP_URL_QUERY), $query);

        $response = $this->get(route('google.callback', [
            'state' => $query['state'],
            'code' => 'authorization-code',
        ]));

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google-customer@example.com',
            'google_id' => 'google-user-123',
            'role' => 'customer',
        ]);
    }

    public function test_customer_can_sign_in_after_successful_phone_verification(): void
    {
        config([
            'services.twilio.account_sid' => 'AC-test',
            'services.twilio.auth_token' => 'test-token',
            'services.twilio.verify_service_sid' => 'VA-test',
        ]);

        $user = User::factory()->create([
            'phone' => '+919876543210',
            'role' => 'customer',
        ]);

        Http::fakeSequence()
            ->push(['status' => 'pending'], 201)
            ->push(['status' => 'approved'], 200);

        $this->post(route('phone-login.send'), [
            'phone' => $user->phone,
        ])->assertRedirect(route('phone-login'));

        $this->post(route('phone-login.verify'), [
            'code' => '123456',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }
}
