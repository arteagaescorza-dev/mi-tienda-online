<?php

namespace Tests\Feature\Storefront;

use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerAccountCrudAndArcoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_buyer_can_update_personal_info_and_discrete_packaging(): void
    {
        $user = User::create([
            'name' => 'Miguel Inicial',
            'email' => 'miguel@comprador.mx',
            'password' => 'PassSeguro123!',
            'role' => 'customer',
        ]);

        $this->actingAs($user);

        $this->putJson('/auth/account/personal-info', [
            'name' => 'Miguel Actualizado',
            'phone' => '+52 5512345678',
            'discrete_packaging' => false,
        ])->assertOk()
            ->assertJsonPath('user.name', 'Miguel Actualizado')
            ->assertJsonPath('user.phone', '+52 5512345678')
            ->assertJsonPath('user.discrete_packaging', false);

        $fresh = $user->fresh();
        $this->assertSame('Miguel Actualizado', $fresh->name);
        $this->assertSame('+52 5512345678', $fresh->phone);
        $this->assertFalse($fresh->discrete_packaging);
    }

    public function test_buyer_address_book_crud_and_ownership_isolation(): void
    {
        $user = User::create([
            'name' => 'Dueño Direcciones',
            'email' => 'direcciones@comprador.mx',
            'password' => 'PassSeguro123!',
            'role' => 'customer',
        ]);
        $other = User::create([
            'name' => 'Otro Usuario',
            'email' => 'otro@comprador.mx',
            'password' => 'PassSeguro123!',
            'role' => 'customer',
        ]);

        $this->actingAs($user);

        $create1 = $this->postJson('/auth/account/addresses', [
            'label_type' => 'casa',
            'street' => 'Av. Reforma 123',
            'postal_code' => '06600',
            'colony' => 'Juárez',
            'city' => 'Cuauhtémoc',
            'state' => 'CDMX',
            'references' => 'Depto 4B',
        ])->assertCreated()
            ->assertJsonPath('address.isDefault', true);

        $addr1Id = $create1->json('address.id');

        $create2 = $this->postJson('/auth/account/addresses', [
            'label_type' => 'trabajo',
            'street' => 'Insurgentes Sur 500',
            'postal_code' => '03100',
            'colony' => 'Del Valle',
            'city' => 'Benito Juárez',
            'state' => 'CDMX',
        ])->assertCreated()
            ->assertJsonPath('address.isDefault', false);

        $addr2Id = $create2->json('address.id');

        $this->postJson("/auth/account/addresses/{$addr2Id}/default")->assertOk();
        $this->assertFalse(CustomerAddress::findOrFail($addr1Id)->is_default);
        $this->assertTrue(CustomerAddress::findOrFail($addr2Id)->is_default);

        $this->putJson("/auth/account/addresses/{$addr1Id}", [
            'label_type' => 'casa',
            'street' => 'Av. Reforma 999',
            'postal_code' => '06600',
            'colony' => 'Juárez',
            'city' => 'Cuauhtémoc',
            'state' => 'CDMX',
        ])->assertOk();
        $this->assertSame('Av. Reforma 999', CustomerAddress::findOrFail($addr1Id)->street);

        // Other user cannot modify or delete this address
        $this->actingAs($other);
        $this->putJson("/auth/account/addresses/{$addr1Id}", [
            'label_type' => 'casa',
            'street' => 'Hack',
            'postal_code' => '06600',
            'colony' => 'Juárez',
            'city' => 'Cuauhtémoc',
            'state' => 'CDMX',
        ])->assertNotFound();
        $this->deleteJson("/auth/account/addresses/{$addr1Id}")->assertNotFound();

        // Owner deletes default address -> remaining becomes default
        $this->actingAs($user);
        $this->deleteJson("/auth/account/addresses/{$addr2Id}")->assertOk();
        $this->assertDatabaseMissing('customer_addresses', ['id' => $addr2Id]);
        $this->assertTrue(CustomerAddress::findOrFail($addr1Id)->is_default);
    }

    public function test_arco_account_deletion_requires_password_and_2fa_and_anonymizes_user(): void
    {
        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $codes = $totp->generateRecoveryCodes(8);

        $user = User::create([
            'name' => 'ARCO User',
            'email' => 'arco@comprador.mx',
            'phone' => '+52 5511223344',
            'password' => 'ArcoPass123!',
            'role' => 'customer',
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
            'two_factor_confirmed' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes['hashed'],
        ])->save();

        $user->addresses()->create([
            'label_type' => 'casa',
            'street' => 'Calle Test 10',
            'postal_code' => '06700',
            'colony' => 'Roma',
            'city' => 'Cuauhtémoc',
            'state' => 'CDMX',
            'is_default' => true,
        ]);

        $this->actingAs($user);

        // Wrong password
        $this->deleteJson('/auth/account', [
            'current_password' => 'Wrong!',
        ])->assertStatus(422);

        // Missing 2FA code
        $this->deleteJson('/auth/account', [
            'current_password' => 'ArcoPass123!',
        ])->assertStatus(422)
            ->assertJsonPath('requires_two_factor', true);

        // Valid password + TOTP code
        $this->deleteJson('/auth/account', [
            'current_password' => 'ArcoPass123!',
            'code' => $totp->getCodeForTimestamp($secret, time()),
        ])->assertOk();

        $this->assertGuest();

        $fresh = $user->fresh();
        $this->assertSame('Cuenta Eliminada', $fresh->name);
        $this->assertStringEndsWith('@anonymized.local', $fresh->email);
        $this->assertNull($fresh->phone);
        $this->assertFalse($fresh->hasTwoFactorEnabled());
        $this->assertNotNull($fresh->anonymized_at);
        $this->assertDatabaseCount('customer_addresses', 0);
    }
}
