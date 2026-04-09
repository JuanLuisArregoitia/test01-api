<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthEmailPasswordTest extends TestCase
{
    use LazilyRefreshDatabase;

    // ─── Email Verification ──────────────────────────────────────

    public function test_registration_sends_verification_email(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(201);

        $user = User::where('email', 'jane@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);
        $path = $parsedUrl['path'].'?'.http_build_query($queryParams);

        $response = $this->getJson($path);

        $response->assertOk()
            ->assertJson(['message' => 'Email verified successfully']);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_fails_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);
        $path = $parsedUrl['path'].'?'.http_build_query($queryParams);

        $response = $this->getJson($path);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Invalid verification link']);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_fails_with_tampered_url(): void
    {
        $user = User::factory()->unverified()->create();

        // Valid hash but missing/invalid signature — signed middleware rejects with 403
        $path = "/api/v1/email/verify/{$user->id}/".sha1($user->email).'?expires='.now()->addMinutes(60)->timestamp;

        $response = $this->getJson($path);

        $response->assertStatus(403);
    }

    public function test_already_verified_email_returns_200(): void
    {
        $user = User::factory()->create(); // factory creates already-verified user

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'], $queryParams);
        $path = $parsedUrl['path'].'?'.http_build_query($queryParams);

        $response = $this->getJson($path);

        $response->assertOk()
            ->assertJson(['message' => 'Email already verified']);
    }

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/email/verification-notification');

        $response->assertOk()
            ->assertJson(['message' => 'Verification email sent']);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_verification_email_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/email/verification-notification');

        $response->assertStatus(401);
    }

    public function test_resend_returns_200_when_already_verified(): void
    {
        $user = User::factory()->create(); // already verified
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/email/verification-notification');

        $response->assertOk()
            ->assertJson(['message' => 'Email already verified']);
    }

    // ─── Password Reset ─────────────────────────────────────────

    public function test_forgot_password_returns_200_for_valid_email(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertOk();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_returns_200_even_for_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Always 200 — prevents email enumeration attacks
        $response->assertOk();
    }

    public function test_forgot_password_fails_without_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400);
    }

    public function test_reset_password_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/reset-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_reset_password_fails_when_passwords_do_not_match(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'different456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
