<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\User;
use App\Notifications\Auth\ResetBuyerPasswordNotification;
use App\Notifications\Auth\VerifyBuyerEmailNotification;
use App\Services\Auth\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BuyerAuthAndTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function createGuestOrder(string $folio, string $email): Order
    {
        return Order::create([
            'order_number' => $folio,
            'subtotal' => 389.00,
            'shipping_cost' => 149.00,
            'discount_amount' => 0.00,
            'total' => 538.00,
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'currency' => 'MXN',
            'shipping_address' => [
                'name' => 'Comprador Invitado',
                'email' => $email,
                'phone' => '5512345678',
                'street' => 'Av. Reforma 100',
                'postal_code' => '06700',
                'city' => 'CDMX',
                'state' => 'CDMX',
            ],
        ]);
    }

    public function test_buyer_registration_sends_verification_and_links_guest_orders_on_verify(): void
    {
        Notification::fake();

        $guestOrder = $this->createGuestOrder('#ORD-2026-9001', 'nuevo@comprador.mx');
        $otherOrder = $this->createGuestOrder('#ORD-2026-9002', 'otro@comprador.mx');

        $response = $this->postJson('/auth/register', [
            'name' => 'Ana Perez',
            'email' => 'nuevo@comprador.mx',
            'phone' => '5512345678',
            'password' => 'SecretPass123!',
            'password_confirmation' => 'SecretPass123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'nuevo@comprador.mx')
            ->assertJsonPath('user.role', 'customer')
            ->assertJsonPath('user.email_verified', false)
            ->assertJsonPath('user.two_factor_enabled', false);

        $user = User::where('email', 'nuevo@comprador.mx')->firstOrFail();
        Notification::assertSentTo($user, VerifyBuyerEmailNotification::class);

        // Unverified user cannot enable 2FA
        $this->postJson('/auth/2fa/enable', ['current_password' => 'SecretPass123!'])
            ->assertStatus(403);

        // Signed verification URL verifies email and links matching guest order
        $signedUrl = URL::temporarySignedRoute(
            'auth.verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $this->getJson($signedUrl)
            ->assertOk()
            ->assertJsonPath('linked_orders', 1)
            ->assertJsonPath('user.email_verified', true);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame($user->id, $guestOrder->fresh()->user_id);
        $this->assertNull($otherOrder->fresh()->user_id);
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        User::create([
            'name' => 'Rate Limit User',
            'email' => 'ratelimit@comprador.mx',
            'password' => 'CorrectPass123!',
            'role' => 'customer',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/auth/login', [
                'email' => 'ratelimit@comprador.mx',
                'password' => 'WrongPass!',
            ])->assertStatus(422);
        }

        $this->postJson('/auth/login', [
            'email' => 'ratelimit@comprador.mx',
            'password' => 'WrongPass!',
        ])->assertStatus(429);
    }

    public function test_2fa_setup_challenge_trusted_device_and_single_use_recovery_code(): void
    {
        $user = User::create([
            'name' => 'Seguridad User',
            'email' => 'seguridad@comprador.mx',
            'password' => 'PassSeguro123!',
            'role' => 'customer',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user);

        // 1. Start 2FA setup
        $enableRes = $this->postJson('/auth/2fa/enable', [
            'current_password' => 'PassSeguro123!',
        ])->assertOk();

        $secret = $enableRes->json('secret');
        $this->assertNotEmpty($secret);
        $this->assertStringStartsWith('otpauth://totp/', $enableRes->json('otpauth_uri'));

        // 2. Confirm 2FA with valid TOTP code
        $totp = app(TotpService::class);
        $validCode = $totp->getCodeForTimestamp($secret, time());

        $confirmRes = $this->postJson('/auth/2fa/confirm', [
            'code' => $validCode,
        ])->assertOk()
            ->assertJsonPath('user.two_factor_enabled', true);

        $recoveryCodes = $confirmRes->json('recovery_codes');
        $this->assertCount(8, $recoveryCodes);
        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());

        // 3. Logout and login -> requires 2FA challenge
        $this->postJson('/auth/logout')->assertOk();

        $this->postJson('/auth/login', [
            'email' => 'seguridad@comprador.mx',
            'password' => 'PassSeguro123!',
        ])->assertOk()
            ->assertJsonPath('requires_two_factor', true);

        $this->assertGuest();

        // 4. Complete 2FA challenge with trust_device=true
        $challengeRes = $this->postJson('/auth/2fa/challenge', [
            'code' => $totp->getCodeForTimestamp($secret, time()),
            'trust_device' => true,
        ])->assertOk()
            ->assertJsonPath('user.email', 'seguridad@comprador.mx')
            ->assertCookie('mto_trusted_device');

        $this->assertAuthenticatedAs($user);
        $trustedCookie = $challengeRes->getCookie('mto_trusted_device', false);
        $decrypted = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($trustedCookie->getValue()), true);
        $this->assertSame($user->id, $decrypted['user_id'] ?? null);

        // 5. Logout and login WITH trusted cookie -> skips 2FA
        $this->postJson('/auth/logout')->assertOk();

        $this->withCredentials()
            ->withUnencryptedCookie('mto_trusted_device', $trustedCookie->getValue())
            ->postJson('/auth/login', [
                'email' => 'seguridad@comprador.mx',
                'password' => 'PassSeguro123!',
            ])->assertOk()
            ->assertJsonMissing(['requires_two_factor' => true])
            ->assertJsonPath('user.email', 'seguridad@comprador.mx');

        // 6. Logout and login WITHOUT cookie -> use single-use recovery code
        $this->unencryptedCookies = [];
        $this->withCredentials = false;
        $this->postJson('/auth/logout')->assertOk();

        $this->postJson('/auth/login', [
            'email' => 'seguridad@comprador.mx',
            'password' => 'PassSeguro123!',
        ])->assertOk()->assertJsonPath('requires_two_factor', true);

        $singleRecovery = $recoveryCodes[0];
        $this->postJson('/auth/2fa/challenge', [
            'code' => $singleRecovery,
        ])->assertOk();

        // Reusing the same recovery code must fail
        $this->postJson('/auth/logout')->assertOk();
        $this->postJson('/auth/login', [
            'email' => 'seguridad@comprador.mx',
            'password' => 'PassSeguro123!',
        ])->assertOk();

        $this->postJson('/auth/2fa/challenge', [
            'code' => $singleRecovery,
        ])->assertStatus(422);
    }

    public function test_password_reset_enforces_2fa_and_revokes_trusted_devices_and_sessions(): void
    {
        Notification::fake();

        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $codes = $totp->generateRecoveryCodes(8);

        $user = User::create([
            'name' => 'Reset User',
            'email' => 'reset@comprador.mx',
            'password' => 'OldPassword123!',
            'role' => 'customer',
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
            'two_factor_confirmed' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes['hashed'],
            'two_factor_trusted_version' => 1,
        ])->save();

        DB::table('sessions')->insert([
            'id' => 'stale-session-id-123',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'test',
            'last_activity' => time(),
        ]);

        // Neutral response for unknown email
        $this->postJson('/auth/forgot-password', ['email' => 'noexiste@comprador.mx'])
            ->assertOk();

        // Known email sends reset notification
        $this->postJson('/auth/forgot-password', ['email' => 'reset@comprador.mx'])
            ->assertOk();

        $sentToken = null;
        Notification::assertSentTo($user, ResetBuyerPasswordNotification::class, function ($notification) use (&$sentToken) {
            $sentToken = $notification->token;
            return true;
        });
        $this->assertNotEmpty($sentToken);

        // Reset without 2FA code must fail with requires_two_factor=true
        $this->postJson('/auth/reset-password', [
            'email' => 'reset@comprador.mx',
            'token' => $sentToken,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertStatus(422)
            ->assertJsonPath('requires_two_factor', true);

        // Reset with valid TOTP code succeeds, updates password, increments trusted version, deletes sessions
        $this->postJson('/auth/reset-password', [
            'email' => 'reset@comprador.mx',
            'token' => $sentToken,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
            'code' => $totp->getCodeForTimestamp($secret, time()),
        ])->assertOk();

        $freshUser = $user->fresh();
        $this->assertTrue(Hash::check('NewPassword123!', $freshUser->password));
        $this->assertSame(2, $freshUser->two_factor_trusted_version);
        $this->assertDatabaseMissing('sessions', ['id' => 'stale-session-id-123']);
    }

    public function test_in_session_security_mutations_require_password_and_2fa(): void
    {
        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $codes = $totp->generateRecoveryCodes(8);

        $user = User::create([
            'name' => 'InSession User',
            'email' => 'insession@comprador.mx',
            'password' => 'InitialPass123!',
            'role' => 'customer',
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
            'two_factor_confirmed' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes['hashed'],
            'two_factor_trusted_version' => 1,
        ])->save();

        $this->actingAs($user);

        // Regenerate recovery codes requires valid TOTP + current password
        $this->postJson('/auth/2fa/recovery-codes', [
            'current_password' => 'InitialPass123!',
            'code' => '000000',
        ])->assertStatus(422);

        $regenRes = $this->postJson('/auth/2fa/recovery-codes', [
            'current_password' => 'InitialPass123!',
            'code' => $totp->getCodeForTimestamp($secret, time()),
        ])->assertOk();
        $this->assertCount(8, $regenRes->json('recovery_codes'));

        // Change password requires current_password + 2FA
        $this->postJson('/auth/password/change', [
            'current_password' => 'InitialPass123!',
            'password' => 'UpdatedPass123!',
            'password_confirmation' => 'UpdatedPass123!',
            'code' => $totp->getCodeForTimestamp($secret, time()),
        ])->assertOk();

        $this->assertTrue(Hash::check('UpdatedPass123!', $user->fresh()->password));

        // Disable 2FA
        $this->postJson('/auth/2fa/disable', [
            'current_password' => 'UpdatedPass123!',
            'code' => $totp->getCodeForTimestamp($secret, time()),
        ])->assertOk()
            ->assertJsonPath('user.two_factor_enabled', false);

        $this->assertFalse($user->fresh()->hasTwoFactorEnabled());
    }
}
